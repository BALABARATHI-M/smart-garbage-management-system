<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); exit();
}

$karurAreas = [
    'North Zone' => ['Karur Town', 'K.K. Nagar', 'Vengamedu'],
    'South Zone' => ['Manmangalam', 'Thanthonimalai', 'Jawahar Nagar'],
    'East Zone'  => ['Kovilpalayam', 'Kadavur', 'Krishnarayapuram'],
    'West Zone'  => ['Aravakurichi', 'Kulithalai', 'Pallapatti'],
];

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name   = trim($_POST['location_name']);
    $area   = trim($_POST['area']);
    $lat    = trim($_POST['latitude']);
    $lng    = trim($_POST['longitude']);
    $type   = $_POST['dustbin_type'];
    $status = $_POST['status'];

    if ($name && $area && $lat && $lng) {
        $pdo->prepare("INSERT INTO dustbins (location_name, area, latitude, longitude, dustbin_type, status) VALUES (?,?,?,?,?,?)")
            ->execute([$name, $area, $lat, $lng, $type, $status]);
        $success = "✅ Dustbin location added successfully!";
    } else {
        $error = "Please fill in all required fields.";
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM dustbins WHERE id=?")->execute([$_GET['delete']]);
    $success = "✅ Dustbin location deleted.";
}

$dustbins     = $pdo->query("SELECT * FROM dustbins ORDER BY area")->fetchAll();
$dustbinsJson = json_encode($dustbins);
$pageTitle    = "Dustbin Locations";
?>
<?php include '../includes/header.php'; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="icon"><i class="fa-solid fa-recycle"></i></div>
        <div class="name">SmartGMS <small>Admin Panel</small></div>
    </div>
    <div class="sidebar-menu">
        <div class="menu-label">Main Menu</div>
        <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> Schedules</a>
        <a href="dustbins.php" class="active"><i class="fa-solid fa-map-location-dot"></i> Dustbin Locations</a>
        <a href="complaints.php"><i class="fa-solid fa-triangle-exclamation"></i> Complaints</a>
        <div class="menu-label">Account</div>
        <a href="users.php"><i class="fa-solid fa-users"></i> Manage Users</a>
        <a href="profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a>
    </div>
    <div class="sidebar-footer">
        <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h4>Dustbin Locations — Karur City</h4>
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

        <div class="row g-4">

            <!-- Add Form -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header-green card-header">
                        <h6><i class="fa-solid fa-plus me-2"></i>Add Dustbin Location</h6>
                    </div>
                    <div class="card-body p-4">
                        <p style="font-size:13px;color:#888;margin-bottom:16px;">
                            📍 <b>Click anywhere on the map</b> to auto-fill the coordinates below.
                        </p>
                        <form method="POST">
                            <input type="hidden" name="action" value="add"/>
                            <div class="mb-3">
                                <label class="form-label">Location Name <span class="text-danger">*</span></label>
                                <input type="text" name="location_name" class="form-control" placeholder="e.g. Karur Bus Stand" required/>
                            </div>

                            <!-- Zone Dropdown -->
                            <div class="mb-3">
                                <label class="form-label">Zone <span class="text-danger">*</span></label>
                                <select id="zoneSelect" class="form-select" onchange="updateAreas()">
                                    <option value="">-- Select Zone --</option>
                                    <?php foreach ($karurAreas as $zone => $areas): ?>
                                    <option value="<?= $zone ?>"><?= $zone ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Area Dropdown -->
                            <div class="mb-3">
                                <label class="form-label">Area <span class="text-danger">*</span></label>
                                <select id="areaSelect" class="form-select">
                                    <option value="">-- Select Zone First --</option>
                                </select>
                                <input type="hidden" name="area" id="areaHidden"/>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="text" name="latitude" id="lat-input" class="form-control" placeholder="Click map to fill" required/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Longitude <span class="text-danger">*</span></label>
                                <input type="text" name="longitude" id="lng-input" class="form-control" placeholder="Click map to fill" required/>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dustbin Type</label>
                                <select name="dustbin_type" class="form-select">
                                    <option value="general">🗑️ General</option>
                                    <option value="recyclable">♻️ Recyclable</option>
                                    <option value="organic">🌿 Organic</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="full">Full</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-green btn w-100">
                                <i class="fa-solid fa-plus me-1"></i> Add Location
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Map -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header" style="background:#fff;border-bottom:1px solid #e8f0eb;padding:16px 22px;border-radius:12px 12px 0 0;">
                        <h6 style="color:#1a4731;margin:0;"><i class="fa-solid fa-map me-2"></i>Karur City Map — Click to pick location</h6>
                    </div>
                    <div id="map" style="height:420px;border-radius:0 0 12px 12px;"></div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <h5 style="color:#1a4731;font-weight:600;" class="mt-4 mb-3">All Dustbin Locations (<?= count($dustbins) ?>)</h5>
        <div class="custom-table">
            <table class="table mb-0">
                <thead>
                    <tr><th>#</th><th>Location Name</th><th>Area</th><th>Latitude</th><th>Longitude</th><th>Type</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($dustbins)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">No dustbin locations added yet.</td></tr>
                    <?php else: ?>
                    <?php foreach ($dustbins as $d): ?>
                    <tr>
                        <td><?= $d['id'] ?></td>
                        <td style="font-weight:500;"><?= htmlspecialchars($d['location_name']) ?></td>
                        <td><?= htmlspecialchars($d['area']) ?></td>
                        <td><?= $d['latitude'] ?></td>
                        <td><?= $d['longitude'] ?></td>
                        <td><?= ucfirst($d['dustbin_type']) ?></td>
                        <td>
                            <?php if ($d['status'] === 'active'): ?>
                                <span class="badge-active">Active</span>
                            <?php elseif ($d['status'] === 'full'): ?>
                                <span class="badge-full">Full</span>
                            <?php else: ?>
                                <span class="badge-pending">Maintenance</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="dustbins.php?delete=<?= $d['id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this location?')">
                                <i class="fa-solid fa-trash"></i>
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

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const karurAreas = {
    'North Zone': ['Karur Town', 'K.K. Nagar', 'Vengamedu'],
    'South Zone': ['Manmangalam', 'Thanthonimalai', 'Jawahar Nagar'],
    'East Zone':  ['Kovilpalayam', 'Kadavur', 'Krishnarayapuram'],
    'West Zone':  ['Aravakurichi', 'Kulithalai', 'Pallapatti'],
};

function updateAreas() {
    const zone       = document.getElementById('zoneSelect').value;
    const areaSelect = document.getElementById('areaSelect');
    const areaHidden = document.getElementById('areaHidden');

    areaSelect.innerHTML = '<option value="">-- Select Area --</option>';
    areaHidden.value = zone;

    if (zone && karurAreas[zone]) {
        karurAreas[zone].forEach(area => {
            const opt = document.createElement('option');
            opt.value = area;
            opt.textContent = area;
            areaSelect.appendChild(opt);
        });
    }
}

document.getElementById('areaSelect').addEventListener('change', function() {
    document.getElementById('areaHidden').value = this.value || document.getElementById('zoneSelect').value;
});

const dustbins = <?= $dustbinsJson ?>;

// ✅ Centered on Karur City
const map = L.map('map').setView([10.9601, 78.0766], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

dustbins.forEach(d => {
    const emoji = d.dustbin_type === 'recyclable' ? '♻️' : (d.dustbin_type === 'organic' ? '🌿' : '🗑️');
    const icon  = L.divIcon({ html: `<div style="font-size:24px;">${emoji}</div>`, iconSize:[28,28], className:'' });
    L.marker([d.latitude, d.longitude], { icon }).addTo(map)
     .bindPopup(`<b>${d.location_name}</b><br>${d.area}<br><small>${d.dustbin_type} — ${d.status}</small>`);
});

// Click to fill lat/lng
map.on('click', function(e) {
    document.getElementById('lat-input').value = e.latlng.lat.toFixed(6);
    document.getElementById('lng-input').value = e.latlng.lng.toFixed(6);
    L.popup()
     .setLatLng(e.latlng)
     .setContent('📍 Location selected! Fill the form and click Add.')
     .openOn(map);
});
</script>
