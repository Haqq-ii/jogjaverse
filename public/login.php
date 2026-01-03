<?php
require_once __DIR__ . "/../config/config.php";
session_start();

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: " . BASE_URL . "/admin/dashboard.php");
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
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">
</head>
<body>

<div class="login-container">
  <!-- Left Side - Image Section -->
  <div class="left-section">
    <a href="<?= BASE_URL ?>/public/user/php/landingpageclean.php" class="back-button">
      ← Kembali ke beranda
    </a>
    <div class="image-content">
      <h1>YOGYAKARTA</h1>
      <p>BUDAYA DALAM SETIAP LANGKAH</p>
    </div>
  </div>

  <!-- Right Side - Form Section -->
  <div class="right-section">
    <div class="form-container">
      <h2>Masuk</h2>
      <p class="subtitle">Selamat datang!, Silahkan masuk ke akun Anda</p>

      <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/public/proses_login.php">
        <div class="form-group">
          <input type="text" name="identitas" placeholder="Email atau Username" required autofocus>
        </div>

        <div class="form-group">
          <div class="password-input">
            <input type="password" name="password" placeholder="Masukkan password" required>
            <span class="toggle-password">👁</span>
          </div>
        </div>

        <button class="btn-primary" type="submit">Masuk</button>
      </form>

      <div class="divider">
        <span>atau</span>
      </div>

      <div class="social-buttons">
        <button class="btn-social btn-google">
          <svg width="18" height="18" viewBox="0 0 18 18">
            <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.875 2.684-6.615z"/>
            <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z"/>
            <path fill="#FBBC05" d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707 0-.593.102-1.17.282-1.709V4.958H.957C.347 6.173 0 7.548 0 9c0 1.452.348 2.827.957 4.042l3.007-2.335z"/>
            <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z"/>
          </svg>
          Google
        </button>
      </div>

      <div class="register-link">
        Belum punya akun? <a href="<?= BASE_URL ?>/public/register.php">Daftar di sini</a>
      </div>
    </div>
  </div>
</div>

<script>
  // Toggle password visibility
  document.querySelector('.toggle-password')?.addEventListener('click', function() {
    const input = this.previousElementSibling;
    input.type = input.type === 'password' ? 'text' : 'password';
    this.textContent = input.type === 'password' ? '👁' : '👁';
  });
</script>

</body>
</html>