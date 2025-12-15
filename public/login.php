<?php
require_once __DIR__ . "/../config/config.php";
session_start();

if (isset($_SESSION['login']) && $_SESSION['login'] === true && ($_SESSION['role'] ?? '') === 'admin') {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit();
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin - JogjaVerse</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">
</head>
<body>

<div class="login-wrapper">
  <div class="login-card">
    <h1>Login Admin</h1>
    <p>Masuk ke dashboard pengelolaan JogjaVerse</p>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/public/proses_login.php">
      <div class="form-group">
        <label>Email / Username</label>
        <input type="text" name="identitas" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>

      <button class="btn-login" type="submit">Masuk</button>
    </form>
  </div>
</div>

</body>
</html>
