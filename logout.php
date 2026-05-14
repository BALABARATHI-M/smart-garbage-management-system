<?php
// Start the session
session_start();

// Destroy ALL session data completely
$_SESSION = array();
session_destroy();

// Delete the session cookie too
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page
header("Location: /smart_gms/login.php");
exit();
?>
