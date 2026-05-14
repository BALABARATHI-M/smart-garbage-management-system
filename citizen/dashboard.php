<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: ../login.php"); exit();
}

// Citizen's own complaints
$myComplaints = $pdo->prepare("SELECT * FROM complaints WHERE citizen_id=? ORDER BY created_at DESC LIMIT 5");
$myComplaints->execute([$_SESSION['user_id']]);
$myComplaints = $myComplaints->fetchAll();

$totalMyComplaints = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE citizen_id=?");
$totalMyComplaints->execute([$_SESSION['user_id']]);
$totalMyComplaints = $totalMyComplaints->fetchColumn();

$resolvedComplaints = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE citizen_id=? AND status='resolved'");
$resolvedComplaints->execute([$_SESSION['user_id']]);
$resolvedComplaints = $resolvedComplaints->fetchColumn();

$pageTitle = "Citizen Dashboard";
?>
<?php include '../includes/header.php'; ?>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="icon"><i class="fa-solid fa-recycle"></i></div>
        <div class="name">SmartGMS <small>Citizen Portal</small></div>
    </div>
    <div class="sidebar-menu">
        <div class="menu-label">Main Menu</div>
        <a href="dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> Collection Schedule</a>
        <a href="complaint.php"><i class="fa-solid fa-triangle-exclamation"></i> Submit Complaint</a>
        <a href="map.php"><i class="fa-solid fa-map-location-dot"></i> Dustbin Map</a>
        <div class="menu-label">Account</div>
        <a href="my_complaints.php"><i class="fa-solid fa-list"></i> My Complaints</a>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h4>Welcome, <?= htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]) ?> 👋</h4>
        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div><div style="font-weight:600;color:#333;"><?= htmlspecialchars($_SESSION['full_name']) ?></div><div style="font-size:11px;color:#999;">Citizen</div></div>
        </div>
    </div>

    <div class="page-body">

        <!-- Quick Links -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="schedule.php" style="text-decoration:none;">
                    <div class="stat-card text-center" style="cursor:pointer;">
                        <div class="icon mx-auto mb-2" style="background:#d4edda;"><i class="fa-solid fa-calendar-days" style="color:#1a4731;"></i></div>
                        <div style="font-weight:600;color:#1a4731;margin-top:8px;">View Schedule</div>
                        <div class="label">Check collection times for your area</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="complaint.php" style="text-decoration:none;">
                    <div class="stat-card text-center" style="cursor:pointer;">
                        <div class="icon mx-auto mb-2" style="background:#fff3cd;"><i class="fa-solid fa-triangle-exclamation" style="color:#856404;"></i></div>
                        <div style="font-weight:600;color:#1a4731;margin-top:8px;">Submit Complaint</div>
                        <div class="label">Report a garbage issue with photo</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="map.php" style="text-decoration:none;">
                    <div class="stat-card text-center" style="cursor:pointer;">
                        <div class="icon mx-auto mb-2" style="background:#cce5ff;"><i class="fa-solid fa-map-location-dot" style="color:#004085;"></i></div>
                        <div style="font-weight:600;color:#1a4731;margin-top:8px;">Find Dustbins</div>
                        <div class="label">See nearby dustbin locations on map</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="icon" style="background:#d4edda;"><i class="fa-solid fa-list" style="color:#1a4731;"></i></div>
                    <div class="number"><?= $totalMyComplaints ?></div>
                    <div class="label">Total Complaints Submitted</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <div class="icon" style="background:#d4edda;"><i class="fa-solid fa-check-circle" style="color:#1a4731;"></i></div>
                    <div class="number"><?= $resolvedComplaints ?></div>
                    <div class="label">Complaints Resolved</div>
                </div>
            </div>
        </div>

        <!-- My Recent Complaints -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color:#1a4731;font-weight:600;margin:0;">My Recent Complaints</h5>
            <a href="my_complaints.php" class="btn-green btn">View All</a>
        </div>
        <div class="custom-table">
            <table class="table mb-0">
                <thead>
                    <tr><th>#</th><th>Area / Place</th><th>Description</th><th>Status</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($myComplaints)): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">You haven't submitted any complaints yet.</td></tr>
                    <?php else: ?>
                    <?php foreach ($myComplaints as $c): ?>
                    <tr>
                        <td>#<?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['area']) ?> / <?= htmlspecialchars($c['place']) ?></td>
                        <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($c['description']) ?></td>
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
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
