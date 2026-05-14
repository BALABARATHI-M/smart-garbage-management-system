<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit();
}

$success = $error = '';

// Add Schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $area    = trim($_POST['area']);
    $place   = trim($_POST['place']);
    $day     = $_POST['collection_day'];
    $time    = $_POST['collection_time'];
    $vehicle = trim($_POST['vehicle_number']);
    $notes   = trim($_POST['notes']);

    if ($area && $place && $day && $time) {
        $stmt = $pdo->prepare("INSERT INTO schedules (area, place, collection_day, collection_time, vehicle_number, notes) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$area, $place, $day, $time, $vehicle, $notes]);
        $success = "✅ Schedule added successfully!";
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Edit Schedule (Save)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id      = (int)$_POST['edit_id'];
    $area    = trim($_POST['area']);
    $place   = trim($_POST['place']);
    $day     = $_POST['collection_day'];
    $time    = $_POST['collection_time'];
    $vehicle = trim($_POST['vehicle_number']);
    $notes   = trim($_POST['notes']);

    if ($area && $place && $day && $time) {
        $stmt = $pdo->prepare("UPDATE schedules SET area=?, place=?, collection_day=?, collection_time=?, vehicle_number=?, notes=? WHERE id=?");
        $stmt->execute([$area, $place, $day, $time, $vehicle, $notes, $id]);
        $success = "✅ Schedule updated successfully!";
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Delete Schedule
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM schedules WHERE id=?")->execute([$_GET['delete']]);
    $success = "✅ Schedule deleted.";
}

// Fetch schedule to edit (if edit mode)
$editSchedule = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM schedules WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $editSchedule = $stmt->fetch();
}

// Fetch all schedules
$schedules = $pdo->query("SELECT * FROM schedules ORDER BY area, collection_day")->fetchAll();
$pageTitle = "Manage Schedules";
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
        <a href="schedule.php" class="active"><i class="fa-solid fa-calendar-days"></i> Schedules</a>
        <a href="dustbins.php"><i class="fa-solid fa-map-location-dot"></i> Dustbin Locations</a>
        <a href="complaints.php"><i class="fa-solid fa-triangle-exclamation"></i> Complaints</a>
        <div class="menu-label">Account</div>
        <a href="users.php"><i class="fa-solid fa-users"></i> Manage Users</a>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h4>Garbage Collection Schedules</h4>
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

        <!-- Add / Edit Form -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header" style="background:var(--green-dark);color:#fff;border-radius:12px 12px 0 0;padding:16px 22px;">
                <h6 class="mb-0">
                    <?php if ($editSchedule): ?>
                        <i class="fa-solid fa-pen me-2"></i>Edit Schedule — ID #<?= $editSchedule['id'] ?>
                    <?php else: ?>
                        <i class="fa-solid fa-plus me-2"></i>Add New Schedule
                    <?php endif; ?>
                </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $editSchedule ? 'edit' : 'add' ?>"/>
                    <?php if ($editSchedule): ?>
                        <input type="hidden" name="edit_id" value="<?= $editSchedule['id'] ?>"/>
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-500">Zone / Area <span class="text-danger">*</span></label>
                            <input type="text" name="area" class="form-control"
                                placeholder="e.g. North Zone"
                                value="<?= $editSchedule ? htmlspecialchars($editSchedule['area']) : '' ?>"
                                required/>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Place / Street <span class="text-danger">*</span></label>
                            <input type="text" name="place" class="form-control"
                                placeholder="e.g. Anna Nagar"
                                value="<?= $editSchedule ? htmlspecialchars($editSchedule['place']) : '' ?>"
                                required/>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Collection Day <span class="text-danger">*</span></label>
                            <select name="collection_day" class="form-select" required>
                                <option value="">Select Day</option>
                                <?php foreach(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
                                <option value="<?= $d ?>" <?= ($editSchedule && $editSchedule['collection_day']===$d) ? 'selected' : '' ?>>
                                    <?= $d ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Collection Time <span class="text-danger">*</span></label>
                            <input type="time" name="collection_time" class="form-control"
                                value="<?= $editSchedule ? $editSchedule['collection_time'] : '' ?>"
                                required/>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Vehicle Number</label>
                            <input type="text" name="vehicle_number" class="form-control"
                                placeholder="e.g. TN-01-AB-1234"
                                value="<?= $editSchedule ? htmlspecialchars($editSchedule['vehicle_number']) : '' ?>"/>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Notes</label>
                            <input type="text" name="notes" class="form-control"
                                placeholder="Any additional info"
                                value="<?= $editSchedule ? htmlspecialchars($editSchedule['notes']) : '' ?>"/>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <?php if ($editSchedule): ?>
                                <button type="submit" class="btn btn-warning fw-bold px-4">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                                </button>
                                <a href="schedule.php" class="btn btn-secondary px-4">
                                    <i class="fa-solid fa-xmark me-1"></i> Cancel
                                </a>
                            <?php else: ?>
                                <button type="submit" class="btn-green btn">
                                    <i class="fa-solid fa-plus me-1"></i> Add Schedule
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Schedules Table -->
        <h5 style="color:#1a4731;font-weight:600;" class="mb-3">All Schedules (<?= count($schedules) ?>)</h5>
        <div class="custom-table">
            <table class="table mb-0">
                <thead>
                    <tr><th>#</th><th>Zone / Area</th><th>Place</th><th>Day</th><th>Time</th><th>Vehicle</th><th>Notes</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($schedules)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No schedules added yet.</td></tr>
                    <?php else: ?>
                    <?php foreach ($schedules as $s): ?>
                    <tr <?= ($editSchedule && $editSchedule['id'] == $s['id']) ? 'style="background:#fff8e1;border-left:4px solid #ffc107;"' : '' ?>>
                        <td><?= $s['id'] ?></td>
                        <td><span style="background:#d4edda;color:#155724;padding:3px 10px;border-radius:20px;font-size:12px;"><?= htmlspecialchars($s['area']) ?></span></td>
                        <td><?= htmlspecialchars($s['place']) ?></td>
                        <td><?= $s['collection_day'] ?></td>
                        <td><?= date('h:i A', strtotime($s['collection_time'])) ?></td>
                        <td><?= htmlspecialchars($s['vehicle_number'] ?: '—') ?></td>
                        <td><?= htmlspecialchars($s['notes'] ?: '—') ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="schedule.php?edit=<?= $s['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="schedule.php?delete=<?= $s['id'] ?>" class="btn btn-sm btn-danger"
                                   onclick="return confirm('Delete this schedule?')" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
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