<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit();
}

$success = $error = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    $full_name = trim($_POST['full_name']);
    $phone     = trim($_POST['phone']);
    if ($full_name) {
        $pdo->prepare("UPDATE users SET full_name=?, phone=? WHERE id=?")
            ->execute([$full_name, $phone, $_SESSION['user_id']]);
        $_SESSION['full_name'] = $full_name;
        $success = "✅ Profile updated!";
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'password') {
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['user_id']]);
        $success = "✅ Password changed successfully!";
    }
}

$pageTitle = "Admin Profile";
?>
<?php include '../includes/header.php'; ?>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="icon"><i class="fa-solid fa-recycle"></i></div>
        <div class="name">SmartGMS <small>Admin Panel</small></div>
    </div>
    <div class="sidebar-menu">
        <div class="menu-label">Main Menu</div>
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> Schedules</a>
        <a href="dustbins.php"><i class="fa-solid fa-map-location-dot"></i> Dustbin Locations</a>
        <a href="complaints.php"><i class="fa-solid fa-triangle-exclamation"></i> Complaints</a>
        <div class="menu-label">Account</div>
        <a href="users.php"><i class="fa-solid fa-users"></i> Manage Users</a>
        <a href="profile.php" class="active"><i class="fa-solid fa-user-gear"></i> My Profile</a>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h4>Admin Profile</h4>
        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div>
                <div style="font-weight:600;color:#333;"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <div style="font-size:11px;color:#999;">Administrator</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <?php if ($success): ?><div class="alert-success-custom mb-4"><i class="fa-solid fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert-error-custom mb-4"><i class="fa-solid fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header" style="background:var(--green-dark);color:#fff;border-radius:12px 12px 0 0;padding:16px 22px;">
                        <h6 class="mb-0"><i class="fa-solid fa-user-shield me-2"></i>Update Admin Info</h6>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="update"/>
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:#f8f9fa;"/>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"/>
                            </div>
                            <button type="submit" class="btn-green btn w-100"><i class="fa-solid fa-save me-1"></i> Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header" style="background:var(--green-dark);color:#fff;border-radius:12px 12px 0 0;padding:16px 22px;">
                        <h6 class="mb-0"><i class="fa-solid fa-lock me-2"></i>Change Password</h6>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="password"/>
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required/>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required/>
                            </div>
                            <button type="submit" class="btn-green btn w-100"><i class="fa-solid fa-key me-1"></i> Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
