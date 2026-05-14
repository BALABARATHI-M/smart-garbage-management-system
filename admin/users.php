<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit();
}

$success = $error = '';

// Delete user
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if ($deleteId !== $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'citizen'")->execute([$deleteId]);
        $success = "✅ Citizen account deleted successfully.";
    } else {
        $error = "❌ You cannot delete your own account.";
    }
}

// Fetch all citizens
$citizens = $pdo->query("SELECT * FROM users WHERE role='citizen' ORDER BY created_at DESC")->fetchAll();
$totalCitizens = count($citizens);

$pageTitle = "Manage Users";
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
        <a href="users.php" class="active"><i class="fa-solid fa-users"></i> Manage Users</a>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h4>Manage Citizens</h4>
        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div>
                <div style="font-weight:600;color:#333;"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <div style="font-size:11px;color:#999;">Administrator</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <?php if ($success): ?><div class="alert-success-custom mb-3"><i class="fa-solid fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert-error-custom mb-3"><i class="fa-solid fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon" style="background:#d4edda;"><i class="fa-solid fa-users" style="color:#1a4731;"></i></div>
                    <div class="number"><?= $totalCitizens ?></div>
                    <div class="label">Total Registered Citizens</div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color:#1a4731;font-weight:600;margin:0;">All Citizen Accounts</h5>
        </div>

        <div class="custom-table">
            <table class="table mb-0">
                <thead>
                    <tr><th>#</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Area</th><th>Registered On</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($citizens)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No citizens registered yet.</td></tr>
                    <?php else: ?>
                    <?php foreach ($citizens as $u): ?>
                    <tr>
                        <td>
                            <div class="avatar" style="width:32px;height:32px;font-size:13px;background:#d8f3dc;color:#1a4731;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;">
                                <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                            </div>
                        </td>
                        <td style="font-weight:500;"><?= htmlspecialchars($u['full_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($u['area'] ?: '—') ?></td>
                        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <a href="users.php?delete=<?= $u['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this citizen account?')">
                                <i class="fa-solid fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
