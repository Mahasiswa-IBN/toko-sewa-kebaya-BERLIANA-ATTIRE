<?php
session_start();
require_once 'includes/db.php';

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_nama']);
    session_destroy();
    header("Location: admin_login.php");
    exit;
}

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $dbInstalled = false;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `username` = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            $dbInstalled = true;

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_nama'] = $user['nama_lengkap'];
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error = 'Username atau password salah.';
            }
        } catch (\PDOException $e) {
            $dbInstalled = false;
        }
    }

    // Fallback login if database is not set up yet (Admin/Admin123)
    if (!$dbInstalled) {
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = 'admin';
            $_SESSION['admin_nama'] = 'Admin (MOCK)';
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Berliana Attire</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4A0E17;
            --primary-light: #6B1D28;
            --primary-dark: #2F050B;
            --accent: #D4AF37;
            --bg: #1A0407;
            --card-bg: #FFFFFF;
            --text-dark: #2C2C2C;
            --border: #F2E6D9;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--bg) 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            max-width: 420px;
            width: 100%;
            background-color: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(212, 175, 55, 0.2);
            overflow: hidden;
            padding: 50px 35px;
            text-align: center;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 2.3rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: 2px;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 0.8rem;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 40px;
            font-weight: 600;
        }

        .form-group {
            position: relative;
            margin-bottom: 25px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .input-container {
            position: relative;
        }

        .input-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #AAA;
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 1px solid #DDD;
            border-radius: 10px;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
            background-color: #FAF8F6;
        }

        .form-control:focus {
            border-color: var(--primary);
            background-color: #FFF;
            box-shadow: 0 0 0 3px rgba(74, 14, 23, 0.05);
        }

        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(74, 14, 23, 0.2);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgba(74, 14, 23, 0.35);
        }

        .error-alert {
            background-color: #FDF2F2;
            border: 1px solid #F8D7DA;
            color: #C53030;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            font-size: 0.85rem;
            color: #888;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo">BERLIANA</div>
    <div class="subtitle">Attire</div>

    <?php if (!empty($error)): ?>
        <div class="error-alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?php echo $error; ?></span>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <div class="input-container">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" id="username" class="form-control" placeholder="admin" required autocomplete="username">
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-container">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
            </div>
        </div>

        <button type="submit" class="btn">Masuk</button>
    </form>

    <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left-long" style="margin-right: 8px;"></i>Kembali ke Website</a>
</div>

</body>
</html>
