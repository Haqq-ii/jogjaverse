<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/koneksi.php";
session_start();

// Ambil data dari form
$nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$nomor_hp = trim($_POST['nomor_hp'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$peran = $_POST['peran'] ?? 'user';

// Simpan data untuk ditampilkan kembali jika error
$_SESSION['old_register_data'] = [
    'nama_lengkap' => $nama_lengkap,
    'username' => $username,
    'email' => $email,
    'nomor_hp' => $nomor_hp,
    'peran' => $peran
];

// Validasi input kosong
if ($nama_lengkap === '' || $username === '' || $email === '' || $password === '') {
    $_SESSION['register_error'] = "Nama lengkap, username, email, dan password wajib diisi!";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Validasi panjang password
if (strlen($password) < 6) {
    $_SESSION['register_error'] = "Password minimal 6 karakter!";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Validasi password match
if ($password !== $confirm_password) {
    $_SESSION['register_error'] = "Password dan konfirmasi password tidak cocok!";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Validasi format email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = "Format email tidak valid!";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Validasi username (hanya huruf, angka, underscore)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $_SESSION['register_error'] = "Username hanya boleh berisi huruf, angka, dan underscore!";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Validasi nomor HP jika diisi
if ($nomor_hp !== '' && !preg_match('/^[0-9]{10,15}$/', $nomor_hp)) {
    $_SESSION['register_error'] = "Format nomor HP tidak valid! (10-15 digit angka)";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Validasi peran
if (!in_array($peran, ['user', 'admin'])) {
    $_SESSION['register_error'] = "Peran tidak valid!";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Cek apakah username sudah digunakan
$stmt = $koneksi->prepare("SELECT id_pengguna FROM pengguna WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['register_error'] = "Username sudah digunakan! Silakan pilih username lain.";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Cek apakah email sudah digunakan
$stmt = $koneksi->prepare("SELECT id_pengguna FROM pengguna WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['register_error'] = "Email sudah terdaftar! Silakan gunakan email lain.";
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}

// Hash password dengan MD5 (sesuai sistem yang ada)
$kata_sandi_hash = md5($password);

// Insert data ke database
$stmt = $koneksi->prepare("
    INSERT INTO pengguna 
    (nama_lengkap, username, email, kata_sandi_hash, nomor_hp, peran, status_aktif, dibuat_pada, diubah_pada) 
    VALUES 
    (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
");

$stmt->bind_param("ssssss", $nama_lengkap, $username, $email, $kata_sandi_hash, $nomor_hp, $peran);

if ($stmt->execute()) {
    // Registrasi berhasil
    unset($_SESSION['old_register_data']);
    $_SESSION['register_success'] = "Registrasi berhasil! Silakan login dengan akun Anda.";
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
} else {
    $_SESSION['register_error'] = "Terjadi kesalahan saat mendaftar: " . $koneksi->error;
    header("Location: " . BASE_URL . "/public/register.php");
    exit();
}
?>