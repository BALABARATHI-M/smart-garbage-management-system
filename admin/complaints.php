<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit();
}

$success = $error = '';

// Submit response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'respond') {
    $complaint_id = (int)$_POST['complaint_id'];
    $response     = trim($_POST['response']);
    $status       = $_POST['status'];

    if ($response) {
        $stmt = $pdo->prepare("INSERT INTO complaint_responses (complaint_id, admin_id, response) VALUES (?,?,?)");
        $stmt->execute([$complaint_id, $_SESSION['user_id'], $response]);

        $pdo->prepare("UPDATE complaints SET status=? WHERE id=?")->execute([$status, $complaint_id]);
        $success = "✅ Response sent successfully!";
    } else {
        $error = "Please write a response.";
    }
}

// Fetch complaints
$filter = $_GET['filter'] ?? 'all';
$query  = "SELECT c.*, u.full_name AS citizen_name FROM complaints c JOIN users u ON c.citizen_id = u.id";
if ($filter !== 'all') $query .= " WHERE c.status = '$filter'";
$query .= " ORDER BY c.created_at DESC";
$complaints = $pdo->query($query)->fetchAll();

// Single complaint view
$selectedComplaint = null;
$responses = [];
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT c.*, u.full_name AS citizen_name FROM complaints c JOIN users u ON c.citizen_id=u.id WHERE c.id=?");
    $stmt->execute([$_GET['id']]);
    $selectedComplaint = $stmt->fetch();

    $rStmt = $pdo->prepare("SELECT cr.*, u.full_name AS admin_name FROM complaint_responses cr JOIN users u ON cr.admin_id=u.id WHERE cr.complaint_id=? ORDER BY cr.responded_at ASC");
    $rStmt->execute([$_GET['id']]);
    $responses = $rStmt->fetchAll();
}

$pageTitle = "Complaints";
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
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> Schedules</a>
        <a href="dustbins.php"><i class="fa-solid fa-map-location-dot"></i> Dustbin Locations</a>
        <a href="complaints.php" class="active"><i class="fa-solid fa-triangle-exclamation"></i> Complaints</a>
        <div class="menu-label">Account</div>
        <a href="users.php"><i class="fa-solid fa-users"></i> Manage Users</a>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h4>Citizen Complaints</h4>
        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div><div style="font-weight:600;color:#333;"><?= htmlspecialchars($_SESSION['full_name']) ?></div><div style="font-size:11px;color:#999;">Administrator</div></div>
        </div>
    </div>

    <div class="page-body">
        <?php if ($success): ?><div class="alert-success-custom mb-3"><i class="fa-solid fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert-error-custom mb-3"><i class="fa-solid fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

        <?php if ($selectedComplaint): ?>
        <!-- SINGLE COMPLAINT VIEW -->
        <a href="complaints.php" class="btn btn-sm mb-3" style="background:#f0f5f2;color:#1a4731;border:none;border-radius:8px;padding:8px 16px;text-decoration:none;"><i class="fa-solid fa-arrow-left me-1"></i> Back to All</a>

        <div class="row g-4">
            <div class="col-md-7">
                <div class="card border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 style="color:#1a4731;">Complaint #<?= $selectedComplaint['id'] ?></h5>
                            <?php if ($selectedComplaint['status'] === 'pending'): ?>
                                <span class="badge-pending">Pending</span>
                            <?php elseif ($selectedComplaint['status'] === 'in_progress'): ?>
                                <span class="badge-progress">In Progress</span>
                            <?php else: ?>
                                <span class="badge-resolved">Resolved</span>
                            <?php endif; ?>
                        </div>
                        <table class="table table-sm" style="font-size:14px;">
                            <tr><td style="color:#888;width:140px;">Citizen</td><td><b><?= htmlspecialchars($selectedComplaint['citizen_name']) ?></b></td></tr>
                            <tr><td style="color:#888;">Phone</td><td><?= htmlspecialchars($selectedComplaint['phone']) ?></td></tr>
                            <tr><td style="color:#888;">Area</td><td><?= htmlspecialchars($selectedComplaint['area']) ?></td></tr>
                            <tr><td style="color:#888;">Place</td><td><?= htmlspecialchars($selectedComplaint['place']) ?></td></tr>
                            <tr><td style="color:#888;">Date</td><td><?= date('d M Y, h:i A', strtotime($selectedComplaint['created_at'])) ?></td></tr>
                        </table>
                        <div style="background:#f8f9fa;border-radius:10px;padding:16px;margin-top:8px;">
                            <div style="font-size:12px;color:#888;margin-bottom:6px;">DESCRIPTION</div>
                            <p style="margin:0;font-size:14px;"><?= nl2br(htmlspecialchars($selectedComplaint['description'])) ?></p>
                        </div>

                        <?php if ($selectedComplaint['image']): ?>
                        <div class="mt-3">
                            <div style="font-size:12px;color:#888;margin-bottom:8px;">UPLOADED IMAGE</div>
                            <img src="../assets/uploads/<?= htmlspecialchars($selectedComplaint['image']) ?>" style="max-width:100%;border-radius:10px;border:1px solid #e0e0e0;" alt="Complaint Image"/>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Previous Responses -->
                <?php if (!empty($responses)): ?>
                <div style="background:#eaf7ee;border-radius:12px;padding:18px;margin-bottom:16px;">
                    <h6 style="color:#1a4731;margin-bottom:14px;"><i class="fa-solid fa-comments me-2"></i>Previous Responses</h6>
                    <?php foreach ($responses as $r): ?>
                    <div style="background:#fff;border-radius:8px;padding:12px;margin-bottom:10px;border-left:3px solid #52b788;">
                        <div style="font-size:12px;color:#888;margin-bottom:4px;"><?= htmlspecialchars($r['admin_name']) ?> &mdash; <?= date('d M Y, h:i A', strtotime($r['responded_at'])) ?></div>
                        <p style="margin:0;font-size:14px;"><?= nl2br(htmlspecialchars($r['response'])) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Response Form -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header" style="background:var(--green-dark);color:#fff;border-radius:12px 12px 0 0;padding:16px 22px;">
                        <h6 class="mb-0"><i class="fa-solid fa-reply me-2"></i>Send Response</h6>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="respond"/>
                            <input type="hidden" name="complaint_id" value="<?= $selectedComplaint['id'] ?>"/>
                            <div class="mb-3">
                                <label class="form-label">Update Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending"     <?= $selectedComplaint['status']==='pending'?'selected':'' ?>>Pending</option>
                                    <option value="in_progress" <?= $selectedComplaint['status']==='in_progress'?'selected':'' ?>>In Progress</option>
                                    <option value="resolved"    <?= $selectedComplaint['status']==='resolved'?'selected':'' ?>>Resolved</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Your Response <span class="text-danger">*</span></label>
                                <textarea name="response" class="form-control" rows="6" placeholder="Type your response to the citizen..." required></textarea>
                            </div>
                            <button type="submit" class="btn-green btn w-100"><i class="fa-solid fa-paper-plane me-1"></i> Send Response</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- ALL COMPLAINTS LIST -->

        <!-- Filter Tabs -->
        <div class="mb-3 d-flex gap-2">
            <a href="complaints.php?filter=all"         class="btn btn-sm <?= $filter==='all'?'btn-green btn':'btn-outline-secondary' ?>">All</a>
            <a href="complaints.php?filter=pending"      class="btn btn-sm <?= $filter==='pending'?'btn-green btn':'btn-outline-secondary' ?>">Pending</a>
            <a href="complaints.php?filter=in_progress"  class="btn btn-sm <?= $filter==='in_progress'?'btn-green btn':'btn-outline-secondary' ?>">In Progress</a>
            <a href="complaints.php?filter=resolved"     class="btn btn-sm <?= $filter==='resolved'?'btn-green btn':'btn-outline-secondary' ?>">Resolved</a>
        </div>

        <div class="custom-table">
            <table class="table mb-0">
                <thead>
                    <tr><th>#</th><th>Citizen</th><th>Phone</th><th>Area / Place</th><th>Description</th><th>Image</th><th>Status</th><th>Date</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($complaints)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">No complaints found.</td></tr>
                    <?php else: ?>
                    <?php foreach ($complaints as $c): ?>
                    <tr>
                        <td>#<?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['citizen_name']) ?></td>
                        <td><?= htmlspecialchars($c['phone']) ?></td>
                        <td><?= htmlspecialchars($c['area']) ?> / <?= htmlspecialchars($c['place']) ?></td>
                        <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($c['description']) ?></td>
                        <td><?= $c['image'] ? '<span style="color:green;"><i class="fa-solid fa-image"></i> Yes</span>' : '—' ?></td>
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
                        <td><a href="complaints.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-green">View</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
