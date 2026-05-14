<?php
session_start();
require_once 'includes/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: citizen/dashboard.php"); exit();
}

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone    = trim($_POST['phone']);
    $area     = trim($_POST['area']);

    if (!$name || !$email || !$password || !$phone || !$area) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = "This email is already registered.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (full_name, email, password, phone, area, role) VALUES (?,?,?,?,?,'citizen')")
                ->execute([$name, $email, $hashed, $phone, $area]);
            $success = "Account created! You can now login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Smart GMS – Register</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <!-- ✅ External CSS -->
    <link href="/smart_gms/assets/css/style.css" rel="stylesheet"/>
</head>
<body class="register-page-body">

<div class="card register-card">
    <div class="card-body">
        <div class="register-brand">
            <div class="register-brand-icon"><i class="fa-solid fa-recycle"></i></div>
            <h2>Create Account</h2>
            <p style="color:#888;font-size:14px;">Register as a Citizen to access SmartGMS</p>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?> <a href="login.php">Login here</a></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="Your full name" required/>
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="you@example.com" required/>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="e.g. 9876543210" required/>
            </div>
            <div class="mb-3">
                <label class="form-label">Your Area / Zone</label>
                <input type="text" name="area" class="form-control" placeholder="e.g. North Zone, Anna Nagar" required/>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required/>
            </div>
            <button type="submit" class="btn-register">
                <i class="fa-solid fa-user-plus me-2"></i>Create Account
            </button>
        </form>

        <div style="text-align:center;margin-top:18px;font-size:14px;color:#888;">
            Already have an account? <a href="login.php" style="color:#2d6a4f;font-weight:600;text-decoration:none;">Login here</a>
        </div>
    </div>
</div>

</body>
</html>
