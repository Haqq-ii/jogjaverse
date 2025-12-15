<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/koneksi.php";
session_start();

$identitas = trim($_POST['identitas'] ?? '');
$password  = $_POST['password'] ?? '';

if ($identitas === '' || $password === '') {
    $_SESSION['login_error'] = "Semua field wajib diisi.";
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

$stmt = $koneksi->prepare("
    SELECT 
        id_pengguna,
        nama_lengkap,
        username,
        email,
        kata_sandi_hash,
        peran,
        status_aktif
    FROM pengguna
    WHERE (username = ? OR email = ?)
    LIMIT 1
");
$stmt->bind_param("ss", $identitas, $identitas);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['login_error'] = "Akun tidak ditemukan.";
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

if ($user['status_aktif'] != 1) {
    $_SESSION['login_error'] = "Akun tidak aktif.";
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

if ($user['peran'] !== 'admin') {
    $_SESSION['login_error'] = "Akun ini bukan admin.";
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

// cek password (MD5)
if (md5($password) !== $user['kata_sandi_hash']) {
    $_SESSION['login_error'] = "Password salah.";
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

// LOGIN SUKSES
$_SESSION['login'] = true;
$_SESSION['id_pengguna'] = $user['id_pengguna'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = 'admin';

header("Location: " . BASE_URL . "/admin/dashboard.php");
exit();
