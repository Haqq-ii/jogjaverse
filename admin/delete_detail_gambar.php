<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../config/upload.php";
wajib_admin();

$id = filter_input(INPUT_GET, 'id_galeri', FILTER_VALIDATE_INT);
$redirect = $_GET['redirect'] ?? '';
$refId = $_GET['ref_id'] ?? '';
$allowed = ['destinasi', 'event', 'kuliner'];

if (!$id) {
  $_SESSION['flash'] = "Gambar detail tidak ditemukan.";
  header("Location: " . BASE_URL . "/admin/dashboard.php");
  exit();
}

$stmt = $koneksi->prepare("SELECT gambar_url FROM galeri WHERE id_galeri = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($row) {
  delete_uploaded_image($row['gambar_url'] ?? '');
  $stmtDel = $koneksi->prepare("DELETE FROM galeri WHERE id_galeri = ? LIMIT 1");
  $stmtDel->bind_param("i", $id);
  $stmtDel->execute();
  $stmtDel->close();
  $_SESSION['flash'] = "Gambar detail berhasil dihapus.";
} else {
  $_SESSION['flash'] = "Gambar detail tidak ditemukan.";
}

if (in_array($redirect, $allowed, true) && $refId !== '') {
  header("Location: " . BASE_URL . "/admin/" . $redirect . ".php?edit=" . urlencode((string)$refId));
} else {
  header("Location: " . BASE_URL . "/admin/dashboard.php");
}
exit();
?>
