<?php
require_once __DIR__ . "/../config/config.php";
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: " . BASE_URL . "/admin/dashboard.php");
    } else {
        header("Location: " . BASE_URL . "/public/dashboard_user.php");
    }
    exit();
}

$error = $_SESSION['register_error'] ?? '';
$success = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_error'], $_SESSION['register_success']);

// Simpan input sebelumnya jika ada error
$old_data = $_SESSION['old_register_data'] ?? [];
unset($_SESSION['old_register_data']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi - JogjaVerse</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/register.css">
</head>
<body>

<div class="register-wrapper">
  <div class="register-card">
    <h1>Buat Akun Baru</h1>
    <p>Daftar untuk mengakses JogjaVerse</p>

    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/public/proses_register.php">
      <div class="form-group">
        <label>Nama Lengkap *</label>
        <input type="text" name="nama_lengkap" 
               value="<?= htmlspecialchars($old_data['nama_lengkap'] ?? '') ?>" 
               placeholder="Masukkan nama lengkap Anda" required>
      </div>

      <div class="form-group">
        <label>Username *</label>
        <input type="text" name="username" 
               value="<?= htmlspecialchars($old_data['username'] ?? '') ?>" 
               placeholder="Pilih username unik" required>
        <small>Username hanya boleh huruf, angka, dan underscore</small>
      </div>

      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" 
               value="<?= htmlspecialchars($old_data['email'] ?? '') ?>" 
               placeholder="contoh@email.com" required>
      </div>

      <div class="form-group">
        <label>Nomor HP</label>
        <input type="text" name="nomor_hp" 
               value="<?= htmlspecialchars($old_data['nomor_hp'] ?? '') ?>" 
               placeholder="081234567890">
        <small>Opsional, format: 08xxxxxxxxxx</small>
      </div>

      <div class="form-group">
        <label>Password *</label>
        <input type="password" name="password" 
               placeholder="Minimal 6 karakter" required>
      </div>

      <div class="form-group">
        <label>Konfirmasi Password *</label>
        <input type="password" name="confirm_password" 
               placeholder="Ketik ulang password" required>
      </div>

      <div class="form-group">
        <label>Daftar Sebagai</label>
        <select name="peran" required>
          <option value="user" <?= ($old_data['peran'] ?? '') === 'user' ? 'selected' : '' ?>>User Biasa</option>
          <option value="admin" <?= ($old_data['peran'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
        <small>Pilih peran akun Anda</small>
      </div>

      <button class="btn-register" type="submit">Daftar Sekarang</button>
    </form>

    <div class="login-link">
      Sudah punya akun? <a href="<?= BASE_URL ?>/public/login.php">Login di sini</a>
    </div>
  </div>
</div>

</body>
</html>