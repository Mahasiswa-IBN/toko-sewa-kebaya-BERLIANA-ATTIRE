<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check authorization
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$currentFile = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($adminTitle) ? $adminTitle . " - Admin Berliana Attire" : "Admin Dashboard - Berliana Attire"; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">BERLIANA</div>
        <div class="sidebar-subtitle">Attire Panel</div>
        
        <ul class="sidebar-menu">
            <li class="<?php echo ($currentFile == 'admin_dashboard.php') ? 'active' : ''; ?>">
                <a href="admin_dashboard.php"><i class="fa-solid fa-gauge" style="width: 20px;"></i> <span>Dashboard</span></a>
            </li>
            <li class="<?php echo ($currentFile == 'admin_kebaya.php') ? 'active' : ''; ?>">
                <a href="admin_kebaya.php"><i class="fa-solid fa-shirt" style="width: 20px;"></i> <span>Kelola Katalog</span></a>
            </li>
            <li>
                <a href="index.php" target="_blank"><i class="fa-solid fa-earth-americas" style="width: 20px;"></i> <span>Lihat Website</span></a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <a href="admin_login.php?action=logout"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
        </div>
    </aside>

    <!-- Main Content Container (will be closed in individual dashboard files) -->
    <main class="main-content">
        <header class="admin-header">
            <div class="admin-title">
                <h1><?php echo isset($adminTitle) ? $adminTitle : "Panel Utama"; ?></h1>
                <p>Kelola data peminjam kebaya, status sewa, dan koleksi katalog.</p>
            </div>
            <div class="admin-profile">
                <div class="admin-avatar">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div class="admin-name">
                    <?php echo htmlspecialchars($_SESSION['admin_nama'] ?? 'Administrator'); ?>
                </div>
            </div>
        </header>
