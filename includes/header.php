<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . " - Berliana Attire" : "Berliana Attire - Sewa Kebaya Premium & Eksklusif"; ?></title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="Sewa kebaya premium dan eksklusif di Berliana Attire. Menyediakan kebaya wisuda, lamaran, pernikahan, modern, dan tradisional dengan kualitas butik mewah. Hubungi kami untuk penyewaan mudah via WhatsApp.">
    <meta name="keywords" content="sewa kebaya, kebaya modern, kebaya wisuda, kebaya lamaran, kebaya pernikahan, sewa kebaya murah, kebaya premium, berliana attire">
    <meta name="author" content="Berliana Attire">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="container">
            <a href="index.php" class="brand" id="brandLink">
                <span class="brand-logo">BERLIANA</span>
                <span class="brand-tagline">Attire</span>
            </a>
            
            <div class="mobile-menu-btn" id="mobileMenuBtn">
                <span></span>
                <span></span>
                <span></span>
            </div>
            
            <nav>
                <ul id="navLinks">
                    <li><a href="index.php#beranda" class="<?php echo ($activePage == 'home') ? 'active' : ''; ?>">Beranda</a></li>
                    <li><a href="index.php#katalog" class="<?php echo ($activePage == 'catalog') ? 'active' : ''; ?>">Katalog</a></li>
                    <li><a href="index.php#syarat" class="<?php echo ($activePage == 'terms') ? 'active' : ''; ?>">Syarat Sewa</a></li>
                    <li><a href="index.php#tentang" class="<?php echo ($activePage == 'about') ? 'active' : ''; ?>">Tentang Kami</a></li>
                    <li><a href="index.php#kontak" class="<?php echo ($activePage == 'contact') ? 'active' : ''; ?>">Kontak</a></li>
                    <li><a href="admin_login.php" class="<?php echo ($activePage == 'admin') ? 'active' : ''; ?>"><i class="fa-solid fa-user-shield" style="margin-right: 6px;"></i>Admin</a></li>
                    <li><a href="booking.php" class="nav-cta" id="bookingNavBtn"><i class="fa-solid fa-calendar-check" style="margin-right: 8px;"></i>Sewa Sekarang</a></li>
                </ul>
            </nav>
        </div>
    </header>
