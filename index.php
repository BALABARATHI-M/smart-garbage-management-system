<?php
session_start();

// Redirect based on login status and role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: citizen/dashboard.php");
    }
} else {
    header("Location: login.php");
}
exit();
?>
