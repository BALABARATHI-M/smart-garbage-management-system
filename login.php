<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['full_name'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'citizen/dashboard.php'));
    exit();
}

require_once 'includes/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? 'citizen';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['area']      = $user['area'] ?? '';

            header("Location: " . ($role === 'admin' ? 'admin/dashboard.php' : 'citizen/dashboard.php'));
            exit();
        } else {
            $error = "Invalid email or password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Smart GMS – Login</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet"/>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <!-- ✅ External CSS -->
    <link href="/smart_gms/assets/css/style.css" rel="stylesheet"/>
    <style>
        /* Login page needs dark background on body */
        body { background: var(--green-dark); }
    </style>
</head>
<body>

<div class="container-login">

    <!-- LEFT PANEL -->
    <div class="login-left">
        <div class="login-brand">
            <div class="brand-icon"><i class="fa-solid fa-recycle"></i></div>
            <div class="brand-name">Smart<span>GMS</span><br><small>Garbage Management System</small></div>
        </div>
        <div class="hero-text">
            <h1>Keeping Our City<br><em>Clean & Smart</em></h1>
            <p>An intelligent waste management platform connecting citizens, administrators, and dustbin networks for a cleaner tomorrow.</p>
        </div>
        <div class="login-stats">
            <div><div class="stat-num">500+</div><div class="stat-label">Dustbins Tracked</div></div>
            <div><div class="stat-num">12K</div><div class="stat-label">Citizens Served</div></div>
            <div><div class="stat-num">98%</div><div class="stat-label">Collection Rate</div></div>
        </div>
        <div class="leaf-deco">🌿</div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="login-right">
        <div class="form-wrap">
            <div class="form-header">
                <h2>Welcome Back 👋</h2>
                <p>Sign in to continue to your dashboard</p>
            </div>

            <div class="role-toggle">
                <button class="role-btn active" id="btn-citizen" type="button" onclick="setRole('citizen')">
                    <i class="fa-solid fa-user"></i> Citizen
                </button>
                <button class="role-btn" id="btn-admin" type="button" onclick="setRole('admin')">
                    <i class="fa-solid fa-shield-halved"></i> Administrator
                </button>
            </div>

            <?php if ($error): ?>
            <div class="error-box"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input type="hidden" name="role" id="roleInput" value="citizen"/>

                <div class="login-field">
                    <label>Email Address</label>
                    <div class="login-input-wrap">
                        <i class="fa-solid fa-envelope icon-left"></i>
                        <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required/>
                    </div>
                </div>

                <div class="login-field">
                    <label>Password</label>
                    <div class="login-input-wrap">
                        <i class="fa-solid fa-lock icon-left"></i>
                        <input type="password" name="password" id="password" placeholder="••••••••" required/>
                        <button type="button" class="toggle-pw" onclick="togglePassword()">
                            <i class="fa-solid fa-eye" id="pw-icon"></i>
                        </button>
                    </div>
                </div>

                <div class="options-row">
                    <label class="remember"><input type="checkbox"/> Remember me</label>
                    <a href="#" class="forgot">Forgot password?</a>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span id="btn-text">Sign In as Citizen</span>
                </button>
            </form>

            <div class="register-link">New citizen? <a href="register.php">Create an account</a></div>
            <div class="form-footer">&copy; <?= date('Y') ?> SmartGMS — All rights reserved.</div>
        </div>
    </div>
</div>

<script>
    function setRole(role) {
        document.getElementById('roleInput').value = role;
        document.getElementById('btn-citizen').classList.toggle('active', role === 'citizen');
        document.getElementById('btn-admin').classList.toggle('active', role === 'admin');
        document.getElementById('btn-text').textContent =
            role === 'citizen' ? 'Sign In as Citizen' : 'Sign In as Administrator';
    }
    function togglePassword() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('pw-icon');
        input.type  = input.type === 'password' ? 'text' : 'password';
        icon.className = input.type === 'password' ? 'fa-solid fa-eye' : 'fa-solid fa-eye-slash';
    }
</script>
</body>
</html>
