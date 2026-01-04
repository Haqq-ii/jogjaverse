<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/koneksi.php";
session_start();

$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($token === '' || $password === '' || $confirm === '') {
    $_SESSION['reset_error'] = "Semua field wajib diisi.";
    header("Location: " . BASE_URL . "/public/reset_password.php?token=" . urlencode($token));
    exit();
}

if (strlen($password) < 6) {
    $_SESSION['reset_error'] = "Password minimal 6 karakter.";
    header("Location: " . BASE_URL . "/public/reset_password.php?token=" . urlencode($token));
    exit();
}

if ($password !== $confirm) {
    $_SESSION['reset_error'] = "Konfirmasi password tidak sama.";
    header("Location: " . BASE_URL . "/public/reset_password.php?token=" . urlencode($token));
    exit();
}

$stmt = $koneksi->prepare("
    SELECT id_pengguna, expires_at
    FROM password_resets
    WHERE token = ?
    LIMIT 1
");
if (!$stmt) {
    $_SESSION['reset_error'] = "Gagal memproses reset password.";
    header("Location: " . BASE_URL . "/public/reset_password.php?token=" . urlencode($token));
    exit();
}
$stmt->bind_param("s", $token);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || strtotime($row['expires_at']) <= time()) {
    $_SESSION['reset_error'] = "Token reset tidak valid atau sudah kedaluwarsa.";
    header("Location: " . BASE_URL . "/public/reset_password.php?token=" . urlencode($token));
    exit();
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $koneksi->prepare("UPDATE pengguna SET kata_sandi_hash = ? WHERE id_pengguna = ? LIMIT 1");
if (!$stmt) {
    $_SESSION['reset_error'] = "Gagal memperbarui password.";
    header("Location: " . BASE_URL . "/public/reset_password.php?token=" . urlencode($token));
    exit();
}
$stmt->bind_param("si", $hash, $row['id_pengguna']);
$stmt->execute();
$stmt->close();

$stmt = $koneksi->prepare("DELETE FROM password_resets WHERE id_pengguna = ?");
if ($stmt) {
    $stmt->bind_param("i", $row['id_pengguna']);
    $stmt->execute();
    $stmt->close();
}

$_SESSION['register_success'] = "Password berhasil direset. Silakan login kembali.";
header("Location: " . BASE_URL . "/public/login.php");
exit();
