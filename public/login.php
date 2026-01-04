<?php
require_once __DIR__ . "/../config/config.php";
session_start();

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: " . BASE_URL . "/admin/dashboard.php");
    } else {
        header("Location: " . BASE_URL . "/public/dashboard_user.php");
    }
    exit();
}

$error = $_SESSION['login_error'] ?? '';
$success = $_SESSION['register_success'] ?? '';
unset($_SESSION['login_error'], $_SESSION['register_success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JogjaVerse</title>

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* 1. VARIABLE WARNA */
        :root {
            --primary-color: #2D1B20; /* Dark Maroon */
            --secondary-color: #C69C6D; /* Gold */
            --bg-light: #FDFBF7; /* Krem Hangat */
            --text-muted: #6c757d;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            /* Background Pattern Halus */
            background-image: radial-gradient(#e0ded9 1px, transparent 1px);
            background-size: 20px 20px;
        }

        h1, h2, h3, .font-serif {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
        }

        /* --- CENTERED CARD LAYOUT --- */
        .auth-box {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(45, 27, 32, 0.08);
            overflow: hidden;
            min-height: 500px;
            position: relative;
        }

        /* BAGIAN KIRI: SIDEBAR GAMBAR */
        .auth-sidebar {
            flex: 1; 
            background: linear-gradient(135deg, rgba(45, 27, 32, 0.8), rgba(74, 27, 34, 0.6)), 
                        url('https://images.unsplash.com/photo-1596402184320-417e7178b2cd?q=80&w=1920&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            color: #fff;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .sidebar-title { font-size: 2.5rem; margin-bottom: 0.8rem; text-shadow: 0 2px 10px rgba(0,0,0,0.2);}
        .sidebar-text { font-size: 0.80rem; opacity: 0.9; font-weight: 300; line-height: 1.5;  letter-spacing:6px;}

        /* BAGIAN KANAN: FORM */
        .auth-form-side {
            flex: 1.2;
            padding: 2.5rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #fff;
            position: relative;
        }

        /* Brand Logo di Form */
        .brand-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            display: inline-block;
            margin-bottom: 0.2rem;
        }

        .navbar-brand {
        font-family: 'Playfair Display', serif;
        font-size: 2.5rem; /* Tetap 2rem sesuai request */
      }

        /* INPUT STYLING */
        .form-floating > .form-control {
            border: 2px solid transparent;
            background-color: #f8f9fa;
            border-radius: 10px;
            height: 48px;
            padding-left: 15px;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .form-floating > .form-control:focus {
            background-color: #fff;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(198, 156, 109, 0.1);
        }
        
        .form-floating > label { 
            padding-left: 15px; 
            color: #999; 
            font-size: 0.85rem; 
            padding-top: 0.85rem;
        }

        /* BUTTONS */
        .btn-auth {
            background: linear-gradient(135deg, var(--primary-color), #4a2c33);
            color: #fff;
            width: 100%;
            padding: 10px;
            border-radius: 50px;
            font-weight: 600;
            border: none;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            transition: all 0.4s;
            margin-top: 1rem;
            box-shadow: 0 8px 15px rgba(45, 27, 32, 0.15);
        }
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(45, 27, 32, 0.25);
            background: linear-gradient(135deg, #4a2c33, var(--primary-color));
        }

        /* Google Button */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 10px;
            border-radius: 50px;
            border: 1px solid #ddd;
            background: #fff;
            color: #555;
            font-weight: 500;
            font-size: 0.9rem;
            transition: 0.3s;
            gap: 10px;
        }
        .btn-google:hover {
            background-color: #f8f9fa;
            border-color: #ccc;
        }

        .text-gold { color: var(--secondary-color) !important; text-decoration: none; font-weight: 600; }
        .text-gold:hover { text-decoration: underline; }

        .divider {
            display: flex; align-items: center; text-align: center;
            margin: 1rem 0; color: #ccc; font-size: 0.75rem;
        }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid #eee; }
        .divider::before { margin-right: .5em; } .divider::after { margin-left: .5em; }

        /* Link Animasi */
        .link-back {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.8rem;
            position: relative;
            transition: color 0.3s;
            display: inline-flex;
            align-items: center;
        }
        .link-back:hover { color: var(--primary-color); }
        .link-back::after {
            content: ''; position: absolute; width: 0; height: 1px; bottom: -2px; left: 50%;
            background-color: var(--secondary-color); transition: all 0.3s ease; transform: translateX(-50%);
        }
        .link-back:hover::after { width: 100%; }

        /* Password Toggle */
        .password-container { position: relative; }
        .btn-toggle-pw {
            position: absolute; top: 50%; right: 10px; transform: translateY(-50%);
            background: none; border: none; color: #888; cursor: pointer; z-index: 10; font-size: 0.9rem;
        }

        /* RESPONSIF MOBILE */
        @media (max-width: 992px) {
            .auth-box { flex-direction: column; max-width: 450px; height: auto; min-height: auto; }
            .auth-sidebar { display: none; }
            .auth-form-side { padding: 2rem; width: 100%; }
            body { align-items: center; padding-top: 1rem; height: auto; }
            
            .mobile-header {
                display: block; height: 120px;
                background: linear-gradient(135deg, var(--primary-color), #4a2c33);
                position: absolute; top: 0; left: 0; right: 0; z-index: -1;
            }
        }
        @media (min-width: 993px) { .mobile-header { display: none; } }
    </style>
</head>
<body>

<div class="login-wrapper">
  <div class="login-card">
    <h1>Login JogjaVerse</h1>
    <p>Masuk ke dashboard pengelolaan JogjaVerse</p>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/public/proses_login.php">
      <div class="form-group">
        <label>Email / Username</label>
        <input type="text" name="identitas" placeholder="Masukkan email atau username" required autofocus>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password" required>
      </div>

      <button class="btn-login" type="submit">Masuk</button>
    </form>

    <div class="register-link">
      Belum punya akun? <a href="<?= BASE_URL ?>/public/register.php">Daftar di sini</a>
    </div>

</body>
</html>