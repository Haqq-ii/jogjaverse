<?php
require_once __DIR__ . "/../config/config.php";
session_start();

$redirect_to = trim($_GET['redirect_to'] ?? '');
$redirect_to_safe = '';
if ($redirect_to !== '' && str_starts_with($redirect_to, '/') && !str_contains($redirect_to, '://') && !str_starts_with($redirect_to, '//')) {
    $redirect_to_safe = $redirect_to;
}

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: " . BASE_URL . "/admin/dashboard.php");
    } elseif ($redirect_to_safe !== '') {
        header("Location: " . BASE_URL . $redirect_to_safe);
    } else {
        header("Location: " . BASE_URL . "/public/user/php/landingpageclean.php");
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

        .sidebar-title { font-size: 2.5rem; margin-bottom: 0.8rem; text-shadow: 0 2px 10px rgba(0,0,0,0.2); }
        .sidebar-text { font-size: 0.80rem; opacity: 0.9; font-weight: 300; line-height: 1.5; letter-spacing:6px; }

      .navbar-brand {
        font-family: 'Playfair Display', serif;
        font-size: 2.6rem; /* Tetap 2rem sesuai request */
      }

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

    <!-- Header Background untuk Mobile -->
    <div class="mobile-header"></div>

    <!-- CENTERED CARD CONTAINER -->
    <div class="auth-box">
        
        <!-- BAGIAN KIRI: GAMBAR (SIDEBAR) -->
        <div class="auth-sidebar">
            <div class="sidebar-content">
                <h1 class="navbar-brand fw-bold" href="#">
                    Jogja<span style="color: #C69C6D;">Verse.</span>
                </h1>
                <p class="sidebar-text">
                    BUDAYA DALAM SETIAP LANGKAH
                </p>
            </div>
        </div>

        <!-- BAGIAN KANAN: FORM LOGIN -->
        <div class="auth-form-side">
            <div class="text-center mb-4">
                <a href="<?= BASE_URL ?>/public/user/php/landingpageclean.php" class="brand-logo d-lg-none">
                    Jogja<span style="color: var(--secondary-color);">Verse.</span>
                </a>
                <h4 class="fw-bold font-serif mb-1" style="color: var(--primary-color); font-size: 1.5rem;">Masuk Akun</h4>
                <p class="text-muted small mb-0">Silakan masuk menggunakan email atau username.</p>
            </div>

            <!-- ALERT ERROR -->
            <?php if ($error): ?>
                <div class="alert alert-danger py-2 px-3 small rounded-3 border-0 bg-danger-subtle text-danger mb-3 d-flex align-items-center">
                    <i class="bi bi-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- ALERT SUCCESS -->
            <?php if ($success): ?>
                <div class="alert alert-success py-2 px-3 small rounded-3 border-0 bg-success-subtle text-success mb-3 d-flex align-items-center">
                    <i class="bi bi-check-circle me-2"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <!-- FORM -->
            <form method="POST" action="<?= BASE_URL ?>/public/proses_login.php">
                <?php if ($redirect_to_safe !== ''): ?>
                    <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirect_to_safe, ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>
                
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="identitas" name="identitas" placeholder="Email atau Username" required autofocus>
                    <label for="identitas">Email atau Username</label>
                </div>

                <div class="mb-3 password-container">
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password">Kata Sandi</label>
                    </div>
                    <button type="button" class="btn-toggle-pw" onclick="togglePw()">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <a href="<?= BASE_URL ?>/public/forgot_password.php" class="text-gold small fw-semibold" style="font-size: 0.85rem;">Lupa kata sandi?</a>
                </div>

                <button type="submit" class="btn btn-auth">
                    Masuk Sekarang <i class="bi bi-arrow-right ms-1"></i>
                </button>

                <div class="divider">atau</div>

                <button type="button" class="btn-google">
                    <svg width="18" height="18" viewBox="0 0 18 18">
                        <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/>
                        <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z"/>
                        <path fill="#FBBC05" d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707 0-.593.102-1.17.282-1.709V4.958H.957C.347 6.173 0 7.548 0 9c0 1.452.348 2.827.957 4.042l3.007-2.335z"/>
                        <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z"/>
                    </svg>
                    Masuk dengan Google
                </button>

                <div class="text-center mt-4">
                    <p class="small text-muted mb-0">
                        Belum punya akun? 
                        <a href="<?= BASE_URL ?>/public/register.php" class="text-gold fw-bold">Daftar di sini</a>
                    </p>
                </div>

                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>/public/user/php/landingpageclean.php" class="link-back">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
                    </a>
                </div>

            </form>
        </div>
    </div>

    <!-- Script Toggle Password -->
    <script>
        function togglePw() {
            const input = document.getElementById('password');
            const icon = document.querySelector('.btn-toggle-pw i');
            
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            } else {
                input.type = "password";
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        }
    </script>

</body>
</html>
