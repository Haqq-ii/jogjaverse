<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/koneksi.php";
session_start();

$token = trim($_GET['token'] ?? '');
$error = $_SESSION['reset_error'] ?? '';
$success = $_SESSION['reset_success'] ?? '';
unset($_SESSION['reset_error'], $_SESSION['reset_success']);

$valid_token = false;
if ($token !== '') {
    $stmt = $koneksi->prepare("
        SELECT pr.id_pengguna, pr.expires_at
        FROM password_resets pr
        WHERE pr.token = ?
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row && strtotime($row['expires_at']) > time()) {
            $valid_token = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - JogjaVerse</title>
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
      <p class="text-muted small mb-0">Atur kata sandi baru Anda.</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!$valid_token): ?>
      <div class="alert alert-warning">Token reset tidak valid atau sudah kedaluwarsa.</div>
      <div class="text-center">
        <a href="<?= BASE_URL ?>/public/forgot_password.php" class="text-decoration-none small">Minta reset ulang</a>
      </div>
    <?php else: ?>
      <form method="POST" action="<?= BASE_URL ?>/public/proses_reset_password.php">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3">
          <label class="form-label">Password Baru</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Konfirmasi Password Baru</label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-dark w-100">Simpan Password</button>
      </form>
    <?php endif; ?>

    <div class="text-center mt-3">
      <a href="<?= BASE_URL ?>/public/login.php" class="text-decoration-none small">Kembali ke Login</a>
    </div>
  </div>
</body>
</html>
