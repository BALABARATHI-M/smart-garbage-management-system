<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: ../login.php"); exit();
}

// All Karur areas organized by zone
$karurAreas = [
    'North Zone' => ['Karur Town', 'K.K. Nagar', 'Vengamedu'],
    'South Zone' => ['Manmangalam', 'Thanthonimalai', 'Jawahar Nagar'],
    'East Zone'  => ['Kovilpalayam', 'Kadavur', 'Krishnarayapuram'],
    'West Zone'  => ['Aravakurichi', 'Kulithalai', 'Pallapatti'],
];

$schedules  = [];
$searched   = false;
$searchArea = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchArea = trim($_POST['area']);
    $searched   = true;

    if ($searchArea) {
        $stmt = $pdo->prepare("SELECT * FROM schedules WHERE area = ? OR place = ? ORDER BY collection_day");
        $stmt->execute([$searchArea, $searchArea]);
        $schedules = $stmt->fetchAll();

        // If no exact match, try broader search
        if (empty($schedules)) {
            $stmt = $pdo->prepare("SELECT * FROM schedules WHERE area LIKE ? OR place LIKE ? ORDER BY collection_day");
            $stmt->execute(["%$searchArea%", "%$searchArea%"]);
            $schedules = $stmt->fetchAll();
        }
    }
}

$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$pageTitle = "Collection Schedule";
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
        <a href="schedule.php" class="active"><i class="fa-solid fa-calendar-days"></i> Collection Schedule</a>
        <a href="complaint.php"><i class="fa-solid fa-triangle-exclamation"></i> Submit Complaint</a>
        <a href="map.php"><i class="fa-solid fa-map-location-dot"></i> Dustbin Map</a>
        <div class="menu-label">Account</div>
        <a href="my_complaints.php"><i class="fa-solid fa-list"></i> My Complaints</a>
        <a href="profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h4>Garbage Collection Schedule</h4>
        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div>
                <div style="font-weight:600;color:#333;"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <div style="font-size:11px;color:#999;">Citizen</div>
            </div>
        </div>
    </div>

    <div class="page-body">

        <!-- Search Box with Dropdown -->
        <div class="card border-0 shadow-sm rounded-3 mb-4" style="max-width:650px;">
            <div class="card-body p-4">
                <h5 style="color:#1a4731;font-weight:600;margin-bottom:6px;">
                    <i class="fa-solid fa-magnifying-glass me-2"></i>Find Schedule for Your Area
                </h5>
                <p style="color:#888;font-size:13px;margin-bottom:18px;">
                    Select your zone first, then pick your area to see the collection schedule.
                </p>

                <form method="POST">
                    <div class="row g-3 align-items-end">

                        <!-- Zone Dropdown -->
                        <div class="col-md-4">
                            <label class="form-label">Select Zone</label>
                            <select id="zoneSelect" class="form-select" onchange="updateAreas()">
                                <option value="">-- Select Zone --</option>
                                <?php foreach ($karurAreas as $zone => $areas): ?>
                                <option value="<?= $zone ?>"><?= $zone ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Area Dropdown (populated by JS) -->
                        <div class="col-md-4">
                            <label class="form-label">Select Area</label>
                            <select name="area" id="areaSelect" class="form-select" required>
                                <option value="">-- Select Area --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <button type="submit" class="btn-green btn w-100">
                                <i class="fa-solid fa-search me-1"></i> Find Schedule
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search Results -->
        <?php if ($searched): ?>
            <?php if (empty($schedules)): ?>
            <div style="text-align:center;padding:60px;background:#fff;border-radius:14px;border:1px solid #e8f0eb;">
                <div style="font-size:60px;margin-bottom:16px;">🔍</div>
                <h5 style="color:#1a4731;">No schedule found for "<?= htmlspecialchars($searchArea) ?>"</h5>
                <p style="color:#888;font-size:14px;">The admin hasn't added a schedule for this area yet.<br>Please check back later or contact your local municipality.</p>
            </div>

            <?php else: ?>
            <div class="d-flex align-items-center mb-3">
                <h5 style="color:#1a4731;font-weight:600;margin:0;">
                    Schedule for <span style="color:#2d6a4f;">"<?= htmlspecialchars($searchArea) ?>"</span>
                    — <?= count($schedules) ?> result(s)
                </h5>
            </div>
            <div class="row g-3">
                <?php foreach ($schedules as $s): ?>
                <div class="col-md-6 col-lg-4">
                    <div style="background:#fff;border-radius:14px;padding:22px;border:1px solid #e8f0eb;border-left:4px solid #2d6a4f;box-shadow:0 2px 12px rgba(0,0,0,.04);">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span style="background:#d4edda;color:#155724;padding:3px 10px;border-radius:20px;font-size:12px;"><?= htmlspecialchars($s['area']) ?></span>
                            <i class="fa-solid fa-calendar-days" style="color:#2d6a4f;font-size:18px;"></i>
                        </div>
                        <h5 style="color:#1a4731;font-weight:700;margin:10px 0 12px;"><?= htmlspecialchars($s['place']) ?></h5>
                        <div style="font-size:14px;color:#555;">
                            <div class="mb-2"><i class="fa-solid fa-calendar me-2" style="color:#2d6a4f;"></i><b><?= $s['collection_day'] ?></b></div>
                            <div class="mb-2"><i class="fa-solid fa-clock me-2" style="color:#2d6a4f;"></i><?= date('h:i A', strtotime($s['collection_time'])) ?></div>
                            <?php if ($s['vehicle_number']): ?>
                            <div class="mb-2"><i class="fa-solid fa-truck me-2" style="color:#2d6a4f;"></i><?= htmlspecialchars($s['vehicle_number']) ?></div>
                            <?php endif; ?>
                            <?php if ($s['notes']): ?>
                            <div style="background:#f8fffe;border-radius:8px;padding:8px;margin-top:8px;font-size:13px;color:#666;">
                                <i class="fa-solid fa-circle-info me-1" style="color:#2d6a4f;"></i><?= htmlspecialchars($s['notes']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        <?php else: ?>

        <!-- Default: Show all schedules grouped by day -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color:#1a4731;font-weight:600;margin:0;">All Karur City Schedules</h5>
            <span style="font-size:13px;color:#888;">Select your area above to filter</span>
        </div>

        <?php
            $allSchedules = $pdo->query("SELECT * FROM schedules ORDER BY FIELD(collection_day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')")->fetchAll();
            $byDay = [];
            foreach ($allSchedules as $s) { $byDay[$s['collection_day']][] = $s; }
        ?>

        <?php if (empty($allSchedules)): ?>
        <div style="text-align:center;padding:60px;background:#fff;border-radius:14px;border:1px solid #e8f0eb;">
            <div style="font-size:60px;margin-bottom:16px;">📅</div>
            <h5 style="color:#1a4731;">No schedules added yet</h5>
            <p style="color:#888;font-size:14px;">The admin will add schedules soon. Please check back later.</p>
        </div>
        <?php else: ?>
        <?php foreach ($days as $day): ?>
        <?php if (isset($byDay[$day])): ?>
        <div class="mb-4">
            <h6 style="color:#fff;background:#2d6a4f;display:inline-block;padding:6px 18px;border-radius:20px;margin-bottom:12px;">
                <i class="fa-solid fa-calendar-day me-1"></i> <?= $day ?>
            </h6>
            <div class="custom-table">
                <table class="table mb-0">
                    <thead>
                        <tr><th>Zone</th><th>Area / Place</th><th>Time</th><th>Vehicle</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($byDay[$day] as $s): ?>
                        <tr>
                            <td><span style="background:#d4edda;color:#155724;padding:3px 10px;border-radius:20px;font-size:12px;"><?= htmlspecialchars($s['area']) ?></span></td>
                            <td><?= htmlspecialchars($s['place']) ?></td>
                            <td><?= date('h:i A', strtotime($s['collection_time'])) ?></td>
                            <td><?= htmlspecialchars($s['vehicle_number'] ?: '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
// All Karur areas organized by zone
const karurAreas = {
    'North Zone': ['Karur Town', 'K.K. Nagar', 'Vengamedu'],
    'South Zone': ['Manmangalam', 'Thanthonimalai', 'Jawahar Nagar'],
    'East Zone':  ['Kovilpalayam', 'Kadavur', 'Krishnarayapuram'],
    'West Zone':  ['Aravakurichi', 'Kulithalai', 'Pallapatti'],
};

function updateAreas() {
    const zone     = document.getElementById('zoneSelect').value;
    const areaSelect = document.getElementById('areaSelect');

    // Clear old options
    areaSelect.innerHTML = '<option value="">-- Select Area --</option>';

    if (zone && karurAreas[zone]) {
        karurAreas[zone].forEach(area => {
            const opt = document.createElement('option');
            opt.value = area;
            opt.textContent = area;
            areaSelect.appendChild(opt);
        });
    }
}
</script>
