<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in, if not redirect to login page immediately
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}
?>
