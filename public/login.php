<?php
require_once __DIR__ . "/../config/config.php";
session_start();

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: " . BASE_URL . "/admin/dashboard.php");
    } else {
        header("Location: " . BASE_URL . "/public/user/php/langingPage.php");
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
  </div>
</div>

</body>
</html>