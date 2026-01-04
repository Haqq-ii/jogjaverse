<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/koneksi.php";
session_start();

$identitas = trim($_POST['identitas'] ?? '');

if ($identitas === '') {
    $_SESSION['forgot_error'] = "Email atau username wajib diisi.";
    header("Location: " . BASE_URL . "/public/forgot_password.php");
    exit();
}

$stmt = $koneksi->prepare("
    SELECT id_pengguna, email, username
    FROM pengguna
    WHERE email = ? OR username = ?
    LIMIT 1
");
if (!$stmt) {
    $_SESSION['forgot_error'] = "Gagal memproses permintaan.";
    header("Location: " . BASE_URL . "/public/forgot_password.php");
    exit();
}
$stmt->bind_param("ss", $identitas, $identitas);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['forgot_error'] = "Akun tidak ditemukan.";
    header("Location: " . BASE_URL . "/public/forgot_password.php");
    exit();
}

$token = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', time() + 3600);

$stmt = $koneksi->prepare("DELETE FROM password_resets WHERE id_pengguna = ?");
if ($stmt) {
    $stmt->bind_param("i", $user['id_pengguna']);
    $stmt->execute();
    $stmt->close();
}

$stmt = $koneksi->prepare("
    INSERT INTO password_resets (id_pengguna, token, expires_at, created_at)
    VALUES (?, ?, ?, NOW())
");
if (!$stmt) {
    $_SESSION['forgot_error'] = "Gagal membuat token reset.";
    header("Location: " . BASE_URL . "/public/forgot_password.php");
    exit();
}
$stmt->bind_param("iss", $user['id_pengguna'], $token, $expires_at);
$stmt->execute();
$stmt->close();

$_SESSION['forgot_success'] = "Silakan atur ulang kata sandi Anda.";
header("Location: " . BASE_URL . "/public/reset_password.php?token=" . urlencode($token));
exit();
