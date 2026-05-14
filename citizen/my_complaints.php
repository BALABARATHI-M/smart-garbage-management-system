<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: ../login.php"); exit();
}

$complaints = $pdo->prepare("SELECT * FROM complaints WHERE citizen_id=? ORDER BY created_at DESC");
$complaints->execute([$_SESSION['user_id']]);
$complaints = $complaints->fetchAll();

$pageTitle = "My Complaints";
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
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> Collection Schedule</a>
        <a href="complaint.php"><i class="fa-solid fa-triangle-exclamation"></i> Submit Complaint</a>
        <a href="map.php"><i class="fa-solid fa-map-location-dot"></i> Dustbin Map</a>
        <div class="menu-label">Account</div>
        <a href="my_complaints.php" class="active"><i class="fa-solid fa-list"></i> My Complaints</a>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h4>My Complaints</h4>
        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div><div style="font-weight:600;color:#333;"><?= htmlspecialchars($_SESSION['full_name']) ?></div><div style="font-size:11px;color:#999;">Citizen</div></div>
        </div>
    </div>

    <div class="page-body">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color:#1a4731;font-weight:600;margin:0;">All My Complaints (<?= count($complaints) ?>)</h5>
            <a href="complaint.php" class="btn-green btn"><i class="fa-solid fa-plus me-1"></i> New Complaint</a>
        </div>

        <?php if (empty($complaints)): ?>
        <div style="text-align:center;padding:80px;background:#fff;border-radius:14px;border:1px solid #e8f0eb;">
            <div style="font-size:60px;margin-bottom:16px;">📋</div>
            <h5 style="color:#1a4731;">No complaints submitted yet</h5>
            <p style="color:#888;font-size:14px;">If you see a garbage problem, click the button below to report it.</p>
            <a href="complaint.php" class="btn-green btn mt-2"><i class="fa-solid fa-plus me-1"></i> Submit a Complaint</a>
        </div>
        <?php else: ?>

        <div class="row g-3">
            <?php foreach ($complaints as $c): ?>
            <?php
                // Get admin response if any
                $resp = $pdo->prepare("SELECT cr.response, cr.responded_at, u.full_name FROM complaint_responses cr JOIN users u ON cr.admin_id=u.id WHERE cr.complaint_id=? ORDER BY cr.responded_at DESC LIMIT 1");
                $resp->execute([$c['id']]);
                $adminReply = $resp->fetch();
            ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3" style="border-left: 4px solid <?= $c['status']==='resolved' ? '#52b788' : ($c['status']==='in_progress' ? '#4d94ff' : '#f0a500') ?> !important; border-left-style:solid !important;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div>
                                <span style="font-size:12px;color:#888;">Complaint #<?= $c['id'] ?></span>
                                <h5 style="color:#1a4731;font-weight:600;margin:4px 0 0;"><?= htmlspecialchars($c['area']) ?> — <?= htmlspecialchars($c['place']) ?></h5>
                            </div>
                            <?php if ($c['status'] === 'pending'): ?>
                                <span class="badge-pending">⏳ Pending</span>
                            <?php elseif ($c['status'] === 'in_progress'): ?>
                                <span class="badge-progress">🔧 In Progress</span>
                            <?php else: ?>
                                <span class="badge-resolved">✅ Resolved</span>
                            <?php endif; ?>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-<?= $c['image'] ? '6' : '8' ?>">
                                <div style="font-size:13px;color:#888;margin-bottom:4px;">DESCRIPTION</div>
                                <p style="font-size:14px;color:#333;margin:0;"><?= nl2br(htmlspecialchars($c['description'])) ?></p>
                                <div style="font-size:12px;color:#999;margin-top:10px;"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($c['phone']) ?> &nbsp;|&nbsp; <i class="fa-solid fa-clock me-1"></i><?= date('d M Y, h:i A', strtotime($c['created_at'])) ?></div>
                            </div>

                            <?php if ($c['image']): ?>
                            <div class="col-md-3">
                                <div style="font-size:13px;color:#888;margin-bottom:4px;">PHOTO</div>
                                <img src="../assets/uploads/<?= htmlspecialchars($c['image']) ?>" style="width:100%;border-radius:10px;border:1px solid #e0e0e0;max-height:120px;object-fit:cover;" alt="Complaint Photo"/>
                            </div>
                            <?php endif; ?>

                            <?php if ($adminReply): ?>
                            <div class="col-md-<?= $c['image'] ? '3' : '4' ?>">
                                <div style="font-size:13px;color:#888;margin-bottom:4px;">ADMIN RESPONSE</div>
                                <div style="background:#eaf7ee;border-radius:10px;padding:12px;border-left:3px solid #52b788;">
                                    <p style="font-size:13px;margin:0;color:#333;"><?= nl2br(htmlspecialchars($adminReply['response'])) ?></p>
                                    <div style="font-size:11px;color:#888;margin-top:6px;">— <?= htmlspecialchars($adminReply['full_name']) ?>, <?= date('d M Y', strtotime($adminReply['responded_at'])) ?></div>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="col-md-<?= $c['image'] ? '3' : '4' ?>">
                                <div style="font-size:13px;color:#888;margin-bottom:4px;">ADMIN RESPONSE</div>
                                <div style="background:#fff8e6;border-radius:10px;padding:12px;font-size:13px;color:#888;">
                                    ⏳ Waiting for admin response...
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
