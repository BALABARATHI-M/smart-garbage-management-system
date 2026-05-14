<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: ../login.php"); exit();
}

$success = $error = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $full_name = trim($_POST['full_name']);
    $phone     = trim($_POST['phone']);
    $area      = trim($_POST['area']);

    if (!$full_name || !$phone || !$area) {
        $error = "All fields are required.";
    } else {
        $pdo->prepare("UPDATE users SET full_name=?, phone=?, area=? WHERE id=?")
            ->execute([$full_name, $phone, $area, $_SESSION['user_id']]);
        $_SESSION['full_name'] = $full_name;
        $_SESSION['area']      = $area;
        $success = "✅ Profile updated successfully!";

        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'password') {
    $current  = $_POST['current_password'];
    $new      = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    if (!password_verify($current, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hashed, $_SESSION['user_id']]);
        $success = "✅ Password changed successfully!";
    }
}

$pageTitle = "My Profile";
?>
<?php include '../includes/header.php'; ?>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="icon"><i class="fa-solid fa-recycle"></i></div>
        <div class="name">SmartGMS <small>Citizen Portal</small></div>
    </div>
    <div class="sidebar-menu">
        <div class="menu-label">Main Menu</div>
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> Collection Schedule</a>
        <a href="complaint.php"><i class="fa-solid fa-triangle-exclamation"></i> Submit Complaint</a>
        <a href="map.php"><i class="fa-solid fa-map-location-dot"></i> Dustbin Map</a>
        <div class="menu-label">Account</div>
        <a href="my_complaints.php"><i class="fa-solid fa-list"></i> My Complaints</a>
        <a href="profile.php" class="active"><i class="fa-solid fa-user-gear"></i> My Profile</a>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h4>My Profile</h4>
        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div>
                <div style="font-weight:600;color:#333;"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <div style="font-size:11px;color:#999;">Citizen</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <?php if ($success): ?><div class="alert-success-custom mb-4"><i class="fa-solid fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert-error-custom mb-4"><i class="fa-solid fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <div class="row g-4">

            <!-- Profile Info -->
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header" style="background:var(--green-dark);color:#fff;border-radius:12px 12px 0 0;padding:16px 22px;">
                        <h6 class="mb-0"><i class="fa-solid fa-user me-2"></i>Update Profile Info</h6>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="update"/>
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background:#f8f9fa;"/>
                                <div style="font-size:12px;color:#999;margin-top:4px;">Email cannot be changed.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required/>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Your Area / Zone</label>
                                <input type="text" name="area" class="form-control" value="<?= htmlspecialchars($user['area'] ?? '') ?>" required/>
                            </div>
                            <button type="submit" class="btn-green btn w-100"><i class="fa-solid fa-save me-1"></i> Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
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
                                <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters" required/>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat new password" required/>
                            </div>
                            <button type="submit" class="btn-green btn w-100"><i class="fa-solid fa-key me-1"></i> Change Password</button>
                        </form>
                    </div>
                </div>

                <!-- Account Info Card -->
                <div class="stat-card mt-4">
                    <div style="font-size:13px;color:#888;margin-bottom:12px;text-transform:uppercase;letter-spacing:.5px;">Account Details</div>
                    <table style="width:100%;font-size:14px;">
                        <tr><td style="color:#888;padding:5px 0;width:120px;">Member Since</td><td style="font-weight:500;"><?= date('d M Y', strtotime($user['created_at'])) ?></td></tr>
                        <tr><td style="color:#888;padding:5px 0;">Role</td><td><span style="background:#d4edda;color:#155724;padding:2px 10px;border-radius:20px;font-size:12px;">Citizen</span></td></tr>
                        <tr><td style="color:#888;padding:5px 0;">Area</td><td style="font-weight:500;"><?= htmlspecialchars($user['area'] ?: 'Not set') ?></td></tr>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
