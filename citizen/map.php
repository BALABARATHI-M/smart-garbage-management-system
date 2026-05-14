<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: ../login.php"); exit();
}

$dustbins     = $pdo->query("SELECT * FROM dustbins WHERE status='active' ORDER BY area")->fetchAll();
$dustbinsJson = json_encode($dustbins);
$pageTitle    = "Dustbin Map";
?>
<?php include '../includes/header.php'; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

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
        <a href="map.php" class="active"><i class="fa-solid fa-map-location-dot"></i> Dustbin Map</a>
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
        <h4>Dustbin Locations — Karur City</h4>
        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div>
                <div style="font-weight:600;color:#333;"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <div style="font-size:11px;color:#999;">Citizen</div>
            </div>
        </div>
    </div>

    <div class="page-body">

        <!-- Legend -->
        <div style="background:#fff;border-radius:12px;padding:16px 20px;border:1px solid #e8f0eb;margin-bottom:16px;display:flex;align-items:center;gap:24px;flex-wrap:wrap;">
            <span style="font-weight:600;color:#1a4731;font-size:14px;"><i class="fa-solid fa-circle-info me-1"></i> Map Legend:</span>
            <span style="font-size:13px;">🗑️ General</span>
            <span style="font-size:13px;">♻️ Recyclable</span>
            <span style="font-size:13px;">🌿 Organic</span>
            <span style="font-size:13px;color:#888;">📍 Click a marker for details</span>
            <span style="font-size:13px;color:#2d6a4f;font-weight:500;margin-left:auto;">
                Active Locations: <?= count($dustbins) ?>
            </span>
        </div>

        <!-- Map -->
        <div style="background:#fff;border-radius:14px;overflow:hidden;border:1px solid #e8f0eb;box-shadow:0 2px 12px rgba(0,0,0,.04);">
            <div id="map" style="height:500px;"></div>
        </div>

        <!-- Dustbin List -->
        <?php if (!empty($dustbins)): ?>
        <h5 style="color:#1a4731;font-weight:600;" class="mt-4 mb-3">All Dustbin Locations in Karur</h5>
        <div class="row g-3">
            <?php foreach ($dustbins as $d): ?>
            <div class="col-md-4">
                <div class="stat-card" style="cursor:pointer;" onclick="flyTo(<?= $d['latitude'] ?>, <?= $d['longitude'] ?>)">
                    <div class="d-flex justify-content-between align-items-start">
                        <span style="font-size:26px;">
                            <?= $d['dustbin_type'] === 'recyclable' ? '♻️' : ($d['dustbin_type'] === 'organic' ? '🌿' : '🗑️') ?>
                        </span>
                        <span class="badge-active">Active</span>
                    </div>
                    <div style="font-weight:600;color:#1a4731;margin:8px 0 4px;font-size:15px;"><?= htmlspecialchars($d['location_name']) ?></div>
                    <div style="font-size:13px;color:#666;"><i class="fa-solid fa-location-dot me-1" style="color:#2d6a4f;"></i><?= htmlspecialchars($d['area']) ?></div>
                    <div style="font-size:12px;color:#999;margin-top:4px;"><?= ucfirst($d['dustbin_type']) ?> dustbin &nbsp;·&nbsp; <span style="color:#2d6a4f;font-weight:500;">Click to locate</span></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:40px;background:#fff;border-radius:14px;border:1px solid #e8f0eb;margin-top:16px;">
            <div style="font-size:50px;margin-bottom:12px;">🗑️</div>
            <h5 style="color:#1a4731;">No dustbin locations added yet</h5>
            <p style="color:#888;font-size:14px;">Admin will add dustbin locations on the map soon.</p>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const dustbins = <?= $dustbinsJson ?>;

    // ✅ Centered on Karur City, Tamil Nadu
    const map = L.map('map').setView([10.9601, 78.0766], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors | Karur City'
    }).addTo(map);

    // Add dustbin markers
    const markers = [];
    dustbins.forEach(d => {
        const emoji = d.dustbin_type === 'recyclable' ? '♻️' : (d.dustbin_type === 'organic' ? '🌿' : '🗑️');
        const icon  = L.divIcon({
            html: `<div style="font-size:26px;filter:drop-shadow(0 2px 4px rgba(0,0,0,.3));">${emoji}</div>`,
            iconSize: [30, 30],
            className: ''
        });
        const m = L.marker([d.latitude, d.longitude], { icon }).addTo(map);
        m.bindPopup(`
            <div style="font-family:sans-serif;min-width:160px;">
                <b style="color:#1a4731;font-size:14px;">${d.location_name}</b><br>
                <span style="color:#666;font-size:12px;">📍 ${d.area}</span><br>
                <span style="background:#d4edda;color:#155724;padding:2px 8px;border-radius:10px;font-size:11px;display:inline-block;margin-top:4px;">${d.dustbin_type}</span>
            </div>
        `);
        markers.push({ lat: parseFloat(d.latitude), lng: parseFloat(d.longitude), marker: m });
    });

    // Try user's live location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const { latitude, longitude } = pos.coords;
            const userIcon = L.divIcon({
                html: '<div style="background:#2d6a4f;color:#fff;border-radius:50%;width:16px;height:16px;border:3px solid #fff;box-shadow:0 0 0 3px #2d6a4f;"></div>',
                iconSize: [16, 16],
                className: ''
            });
            L.marker([latitude, longitude], { icon: userIcon })
                .addTo(map)
                .bindPopup('<b style="color:#1a4731;">📍 Your Location</b>')
                .openPopup();
            map.setView([latitude, longitude], 14);
        }, () => {
            // If user denies location, stay on Karur center
        });
    }

    function flyTo(lat, lng) {
        map.flyTo([lat, lng], 17, { animate: true, duration: 1.2 });
        markers.forEach(m => {
            if (m.lat === lat && m.lng === lng) m.marker.openPopup();
        });
    }
</script>
