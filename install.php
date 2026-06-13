<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'berliana_attire';

$message = '';
$success = false;

if (isset($_POST['install'])) {
    try {
        // Connect to MySQL server without selecting DB
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create Database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
        $pdo->exec("USE `$db`;");

        // Table 1: users (for Admin Login)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `nama_lengkap` VARCHAR(100) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;");

        // Table 2: kebaya (Katalog Kebaya)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `kebaya` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nama` VARCHAR(100) NOT NULL,
            `deskripsi` TEXT,
            `harga_sewa` DECIMAL(10,2) NOT NULL,
            `stok` INT DEFAULT 1,
            `kategori` VARCHAR(50) NOT NULL,
            `gambar` VARCHAR(255) DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;");

        // Table 3: peminjaman (Data Peminjam)
        $pdo->exec("CREATE TABLE IF NOT EXISTS `peminjaman` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `nama_peminjam` VARCHAR(100) NOT NULL,
            `nik` VARCHAR(20) NOT NULL,
            `whatsapp` VARCHAR(20) NOT NULL,
            `alamat` TEXT NOT NULL,
            `tanggal_sewa` DATE NOT NULL,
            `tanggal_kembali` DATE NOT NULL,
            `durasi` INT NOT NULL, -- in days
            `total_harga` DECIMAL(10,2) NOT NULL,
            `kebaya_id` INT NOT NULL,
            `status` ENUM('Pending', 'Dipinjam', 'Dikembalikan', 'Terlambat', 'Dibatalkan') DEFAULT 'Pending',
            `catatan` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`kebaya_id`) REFERENCES `kebaya`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB;");

        // Insert Default Admin (username: admin, password: admin123)
        $checkUser = $pdo->prepare("SELECT COUNT(*) FROM `users` WHERE `username` = 'admin'");
        $checkUser->execute();
        if ($checkUser->fetchColumn() == 0) {
            $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO `users` (`username`, `password`, `nama_lengkap`) VALUES (?, ?, ?)");
            $stmt->execute(['admin', $hashedPassword, 'Administrator Berliana Attire']);
        }

        // Insert Dummy Kebaya
        $checkKebaya = $pdo->prepare("SELECT COUNT(*) FROM `kebaya`");
        $checkKebaya->execute();
        if ($checkKebaya->fetchColumn() == 0) {
            // Create uploads directory if not exist
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }

            $dummyKebayas = [
                [
                    'nama' => 'Kebaya Brokat Modern Rose Gold',
                    'deskripsi' => 'Kebaya brokat modern dengan sentuhan warna rose gold mewah. Sangat cocok untuk wisuda, kondangan, maupun lamaran. Dilengkapi dengan furing premium yang nyaman di kulit dan detail payet mutiara di bagian dada.',
                    'harga_sewa' => 150000.00,
                    'stok' => 3,
                    'kategori' => 'Modern',
                    'gambar' => 'kebaya1.png'
                ],
                [
                    'nama' => 'Kebaya Beludru Klasik Hitam Gold',
                    'deskripsi' => 'Kebaya kutubaru bahan beludru premium hitam pekat bermotif bordir emas khas Jawa. Memberikan kesan agung, anggun, dan tradisional namun tetap elegan. Cocok untuk acara pernikahan adat maupun formal.',
                    'harga_sewa' => 200000.00,
                    'stok' => 2,
                    'kategori' => 'Tradisional',
                    'gambar' => 'kebaya2.png'
                ],
                [
                    'nama' => 'Kebaya Encim Kartini Putih Gading',
                    'deskripsi' => 'Kebaya model Encim Kartini dengan warna putih gading (broken white). Bordiran bunga halus di sepanjang kerah dan lengan bawah. Bahan katun premium yang sejuk dan menyerap keringat. Ideal untuk acara nasional, tunangan, atau upacara.',
                    'harga_sewa' => 125000.00,
                    'stok' => 4,
                    'kategori' => 'Klasik',
                    'gambar' => 'kebaya3.png'
                ],
                [
                    'nama' => 'Kebaya Brokat Premium Burgundy',
                    'deskripsi' => 'Kebaya dengan warna burgundy pekat dan potongan ekor panjang menawan. Detail brokat Prancis yang rapat dihiasi dengan payet Swarovski berkilau. Sempurna untuk penampilan yang bold dan berkelas di acara malam hari.',
                    'harga_sewa' => 175000.00,
                    'stok' => 2,
                    'kategori' => 'Modern',
                    'gambar' => 'kebaya4.png'
                ]
            ];

            $stmt = $pdo->prepare("INSERT INTO `kebaya` (`nama`, `deskripsi`, `harga_sewa`, `stok`, `kategori`, `gambar`) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($dummyKebayas as $k) {
                $stmt->execute([$k['nama'], $k['deskripsi'], $k['harga_sewa'], $k['stok'], $k['kategori'], $k['gambar']]);
            }
        }

        $message = "Database dan tabel berhasil dikonfigurasi! Kredensial admin default:<br>Username: <strong>admin</strong><br>Password: <strong>admin123</strong>";
        $success = true;
    } catch (PDOException $e) {
        $message = "Kesalahan Instalasi: " . $e->getMessage();
        $success = false;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database - Berliana Attire</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4A0E17;
            --primary-light: #6b1d28;
            --accent: #D4AF37;
            --bg: #FFFDF9;
            --card-bg: #ffffff;
            --text: #2c2c2c;
            --border: #f2e6d9;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            width: 100%;
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(74, 14, 23, 0.08);
            border: 1px solid var(--border);
            overflow: hidden;
            text-align: center;
            padding: 40px 30px;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 0.9rem;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 30px;
            font-weight: 600;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }

        p {
            font-size: 0.95rem;
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 14px 28px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 30px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(74, 14, 23, 0.2);
        }

        .btn:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(74, 14, 23, 0.3);
        }

        .alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: left;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .alert-success {
            background-color: #f4fbf7;
            border: 1px solid #d1eedb;
            color: #1e7e34;
        }

        .alert-danger {
            background-color: #fdf5f5;
            border: 1px solid #f9d6d6;
            color: #dc3545;
        }

        .link-group {
            margin-top: 25px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .link-group a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .link-group a:hover {
            color: var(--accent);
            text-decoration: underline;
        }

        .divider {
            height: 1px;
            background-color: var(--border);
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="logo">BERLIANA</div>
    <div class="subtitle">Attire</div>
    
    <h2>Instalasi Database</h2>
    <p>Selamat datang di sistem instalasi otomatis Berliana Attire. Script ini akan membuat database <strong>berliana_attire</strong> dan tabel-tabel yang diperlukan di XAMPP MySQL lokal Anda.</p>

    <?php if (!empty($message)): ?>
        <div class="alert <?= $success ? 'alert-success' : 'alert-danger' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <form method="POST">
            <button type="submit" name="install" class="btn">Mulai Instalasi Database</button>
        </form>
    <?php else: ?>
        <div class="divider"></div>
        <div class="link-group">
            <a href="index.php">Ke Beranda Utama &rarr;</a>
            <a href="admin_login.php">Masuk Panel Admin &rarr;</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
