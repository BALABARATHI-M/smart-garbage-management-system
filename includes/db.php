<?php
// ============================================
//  DATABASE CONNECTION
//  This file connects to your MySQL database
// ============================================

$host     = "localhost";       // Your server (don't change this)
$dbname   = "smart_gms";      // Your database name
$username = "root";            // Default XAMPP username
$password = "";                // Default XAMPP password (empty)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<h2 style='color:red; font-family:sans-serif; text-align:center; margin-top:50px;'>
        ❌ Database Connection Failed!<br>
        <small style='font-size:14px;'>" . $e->getMessage() . "</small>
    </h2>");
}
?>
