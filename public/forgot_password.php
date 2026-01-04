<?php
require_once __DIR__ . "/../config/config.php";
session_start();

$error = $_SESSION['forgot_error'] ?? '';
$success = $_SESSION['forgot_success'] ?? '';
unset($_SESSION['forgot_error'], $_SESSION['forgot_success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Kata Sandi - JogjaVerse</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #FDFBF7;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }
    .card-reset {
      max-width: 480px;
      width: 100%;
      padding: 32px;
      border-radius: 18px;
      border: none;
      box-shadow: 0 12px 30px rgba(45, 27, 32, 0.12);
    }
    .brand {
      font-family: 'Playfair Display', serif;
      font-weight: 700;
      font-size: 1.6rem;
      color: #2D1B20;
    }
  </style>
</head>
<body>
  <div class="card card-reset">
    <div class="text-center mb-4">
      <div class="brand">Jogja<span style="color:#C69C6D;">Verse.</span></div>
      <p class="text-muted small mb-0">Masukkan email atau username untuk reset kata sandi.</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/public/proses_forgot_password.php">
      <div class="mb-3">
        <label class="form-label">Email / Username</label>
        <input type="text" name="identitas" class="form-control" placeholder="contoh@email.com" required>
      </div>
      <button type="submit" class="btn btn-dark w-100">Lanjutkan</button>
    </form>

    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>/public/login.php" class="text-decoration-none small">Kembali ke Login</a>
    </div>
  </div>
</body>
</html>
