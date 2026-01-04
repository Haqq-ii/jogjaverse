<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (empty($_SESSION['login']) || $_SESSION['login'] !== true) {
  $redirect_to = '/public/user/php/pelaporan.php';
  header("Location: " . BASE_URL . "/public/login.php?redirect_to=" . urlencode($redirect_to));
  exit();
}

$user_id = (int)($_SESSION['id_pengguna'] ?? 0);
$jenis_target = trim($_POST['jenis_target'] ?? '');
$id_target = (int)($_POST['id_target'] ?? 0);
$judul = trim($_POST['judul'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');

$allowed = ['destinasi', 'event', 'kuliner'];
if ($user_id <= 0 || !in_array($jenis_target, $allowed, true) || $id_target <= 0 || $judul === '' || $deskripsi === '') {
  $_SESSION['flash_pelaporan_error'] = "Data pelaporan belum lengkap.";
  header("Location: " . BASE_URL . "/public/user/php/pelaporan.php");
  exit();
}

$stmt = $koneksi->prepare("
  INSERT INTO pelaporan (id_pengguna, jenis_target, id_target, judul, deskripsi, status, dibuat_pada)
  VALUES (?, ?, ?, ?, ?, 'baru', NOW())
");
if (!$stmt) {
  $_SESSION['flash_pelaporan_error'] = "Gagal menyimpan laporan.";
  header("Location: " . BASE_URL . "/public/user/php/pelaporan.php");
  exit();
}
$stmt->bind_param("isiss", $user_id, $jenis_target, $id_target, $judul, $deskripsi);
if ($stmt->execute()) {
  $_SESSION['flash_pelaporan_success'] = "Laporan berhasil dikirim.";
} else {
  $_SESSION['flash_pelaporan_error'] = "Gagal menyimpan laporan.";
}
$stmt->close();

header("Location: " . BASE_URL . "/public/user/php/pelaporan.php");
exit();
