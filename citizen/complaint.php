<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'citizen') {
    header("Location: ../login.php"); exit();
}

$karurAreas = [
    'North Zone' => ['Karur Town', 'K.K. Nagar', 'Vengamedu'],
    'South Zone' => ['Manmangalam', 'Thanthonimalai', 'Jawahar Nagar'],
    'East Zone'  => ['Kovilpalayam', 'Kadavur', 'Krishnarayapuram'],
    'West Zone'  => ['Aravakurichi', 'Kulithalai', 'Pallapatti'],
];

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone       = trim($_POST['phone']);
    $area        = trim($_POST['area']);
    $place       = trim($_POST['place']);
    $description = trim($_POST['description']);
    $imageName   = null;

    if (!$phone || !$area || !$place || !$description) {
        $error = "Please fill in all required fields.";
    } else {
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = "Only image files (jpg, png, webp) are allowed.";
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $error = "Image must be less than 5MB.";
            } else {
                $uploadDir = '../assets/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $imageName = 'complaint_' . time() . '_' . rand(100,999) . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
            }
        }

        if (!$error) {
            $pdo->prepare("INSERT INTO complaints (citizen_id, full_name, phone, area, place, description, image) VALUES (?,?,?,?,?,?,?)")
                ->execute([$_SESSION['user_id'], $_SESSION['full_name'], $phone, $area, $place, $description, $imageName]);
            $success = "✅ Your complaint has been submitted successfully! We will respond shortly.";
        }
    }
}

$pageTitle = "Submit Complaint";
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
        <a href="complaint.php" class="active"><i class="fa-solid fa-triangle-exclamation"></i> Submit Complaint</a>
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
        <h4>Submit a Complaint</h4>
        <div class="user-info">
            <div class="avatar"><?= strtoupper(substr($_SESSION['full_name'], 0, 1)) ?></div>
            <div>
                <div style="font-weight:600;color:#333;"><?= htmlspecialchars($_SESSION['full_name']) ?></div>
                <div style="font-size:11px;color:#999;">Citizen</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">

                <?php if ($success): ?><div class="alert-success-custom mb-4"><i class="fa-solid fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
                <?php if ($error):   ?><div class="alert-error-custom mb-4"><i class="fa-solid fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header-green card-header">
                        <h6><i class="fa-solid fa-triangle-exclamation me-2"></i>Report a Garbage Problem in Karur</h6>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" enctype="multipart/form-data">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Your Name</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['full_name']) ?>" readonly style="background:#f8f9fa;"/>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" placeholder="Your contact number" required/>
                                </div>

                                <!-- Zone Dropdown -->
                                <div class="col-md-6">
                                    <label class="form-label">Zone <span class="text-danger">*</span></label>
                                    <select id="zoneSelect" class="form-select" onchange="updateAreas()">
                                        <option value="">-- Select Zone --</option>
                                        <?php foreach ($karurAreas as $zone => $areas): ?>
                                        <option value="<?= $zone ?>"><?= $zone ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Area Dropdown -->
                                <div class="col-md-6">
                                    <label class="form-label">Area <span class="text-danger">*</span></label>
                                    <select id="areaSelect" class="form-select">
                                        <option value="">-- Select Zone First --</option>
                                    </select>
                                    <input type="hidden" name="area" id="areaHidden"/>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Exact Place / Street <span class="text-danger">*</span></label>
                                    <input type="text" name="place" class="form-control" placeholder="e.g. Near Karur Bus Stand, Main Road" required/>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Problem Description <span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control" rows="5"
                                        placeholder="Describe the garbage problem in detail..." required></textarea>
                                </div>

                                <!-- Image Upload -->
                                <div class="col-12">
                                    <label class="form-label">Upload Photo <span style="color:#888;font-weight:400;">(Optional)</span></label>
                                    <div id="upload-area"
                                         style="border:2px dashed #c0deca;border-radius:12px;padding:30px;text-align:center;cursor:pointer;background:#f8fffe;transition:all .2s;"
                                         onclick="document.getElementById('image').click()">
                                        <i class="fa-solid fa-cloud-arrow-up" style="font-size:32px;color:#2d6a4f;margin-bottom:10px;display:block;"></i>
                                        <p style="color:#2d6a4f;font-weight:500;margin:0;">Click to upload a photo</p>
                                        <p style="color:#888;font-size:12px;margin:4px 0 0;">JPG, PNG, WEBP — Max 5MB</p>
                                        <div id="file-name" style="margin-top:10px;font-size:13px;color:#555;"></div>
                                    </div>
                                    <input type="file" name="image" id="image" accept="image/*"
                                           style="display:none;" onchange="previewImage(event)"/>
                                    <div id="image-preview" style="margin-top:12px;display:none;">
                                        <img id="preview-img" src="" style="max-width:100%;border-radius:10px;border:1px solid #e0e0e0;max-height:250px;object-fit:cover;"/>
                                        <button type="button" onclick="removeImage()"
                                            style="display:block;margin-top:8px;background:#f8d7da;color:#721c24;border:none;border-radius:6px;padding:6px 14px;font-size:13px;cursor:pointer;">
                                            <i class="fa-solid fa-trash me-1"></i> Remove Image
                                        </button>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn-green btn w-100" style="padding:14px;font-size:15px;">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Submit Complaint
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

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
    document.getElementById('areaHidden').value = this.value;
});

function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
            document.getElementById('file-name').textContent = '📎 ' + file.name;
            document.getElementById('upload-area').style.borderColor = '#52b788';
        };
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    document.getElementById('image').value = '';
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('file-name').textContent = '';
    document.getElementById('upload-area').style.borderColor = '#c0deca';
}
</script>
