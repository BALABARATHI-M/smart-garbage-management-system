<?php
session_start();
require_once '../includes/db.php';

// Block non-admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit();
}

// Fetch stats
$totalCitizens   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='citizen'")->fetchColumn();
$totalSchedules  = $pdo->query("SELECT COUNT(*) FROM schedules")->fetchColumn();
$totalDustbins   = $pdo->query("SELECT COUNT(*) FROM dustbins")->fetchColumn();
$totalComplaints = $pdo->query("SELECT COUNT(*) FROM complaints")->fetchColumn();
$pendingComplaints = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status='pending'")->fetchColumn();

// Recent complaints
$recentComplaints = $pdo->query("SELECT c.*, u.full_name FROM complaints c JOIN users u ON c.citizen_id = u.id ORDER BY c.created_at DESC LIMIT 5")->fetchAll();

$pageTitle = "Admin Dashboard";
?>
<?php include '../includes/header.php'; ?>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="icon"><i class="fa-solid fa-recycle"></i></div>
        <div class="name">SmartGMS <small>Admin Panel</small></div>
    </div>
    <div class="sidebar-menu">
        <div class="menu-label">Main Menu</div>
        <a href="dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> Schedules</a>
        <a href="dustbins.php"><i class="fa-solid fa-map-location-dot"></i> Dustbin Locations</a>
        <a href="complaints.php"><i class="fa-solid fa-triangle-exclamation"></i> Complaints
            <?php if($pendingComplaints > 0): ?>
            <span style="background:#ff6b6b;color:#fff;border-radius:20px;padding:2px 8px;font-size:11px;margin-left:auto;"><?= $pendingComplaints ?></span>
            <?php endif; ?>
        </a>
        <div class="menu-label">Account</div>
        <a href="users.php"><i class="fa-solid fa-users"></i> Manage Users</a>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="topbar">
        <h4>Dashboard Overview</h4>
        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div>
                <div style="font-weight:600;color:#333;"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <div style="font-size:11px;color:#999;">Administrator</div>
            </div>
        </div>
    </div>

    <div class="page-body">

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon" style="background:#d4edda;"><i class="fa-solid fa-users" style="color:#1a4731;"></i></div>
                    <div class="number"><?= $totalCitizens ?></div>
                    <div class="label">Registered Citizens</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon" style="background:#cce5ff;"><i class="fa-solid fa-calendar-check" style="color:#004085;"></i></div>
                    <div class="number"><?= $totalSchedules ?></div>
                    <div class="label">Collection Schedules</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon" style="background:#fff3cd;"><i class="fa-solid fa-trash-can" style="color:#856404;"></i></div>
                    <div class="number"><?= $totalDustbins ?></div>
                    <div class="label">Dustbin Locations</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon" style="background:#f8d7da;"><i class="fa-solid fa-triangle-exclamation" style="color:#721c24;"></i></div>
                    <div class="number"><?= $pendingComplaints ?></div>
                    <div class="label">Pending Complaints</div>
                </div>
            </div>
        </div>

        <!-- Recent Complaints Table -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color:#1a4731;font-weight:600;margin:0;">Recent Complaints</h5>
            <a href="complaints.php" class="btn-green btn">View All</a>
        </div>

        <div class="custom-table">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Citizen</th>
                        <th>Phone</th>
                        <th>Area / Place</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentComplaints)): ?>
                    <tr><td colspan="7" style="text-align:center;color:#999;padding:30px;">No complaints yet.</td></tr>
                    <?php else: ?>
                    <?php foreach ($recentComplaints as $c): ?>
                    <tr>
                        <td>#<?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['full_name']) ?></td>
                        <td><?= htmlspecialchars($c['phone']) ?></td>
                        <td><?= htmlspecialchars($c['area']) ?> / <?= htmlspecialchars($c['place']) ?></td>
                        <td>
                            <?php if ($c['status'] === 'pending'): ?>
                                <span class="badge-pending">Pending</span>
                            <?php elseif ($c['status'] === 'in_progress'): ?>
                                <span class="badge-progress">In Progress</span>
                            <?php else: ?>
                                <span class="badge-resolved">Resolved</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                        <td><a href="complaints.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-green">Respond</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
