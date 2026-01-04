<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/koneksi.php";
session_start();

$identitas = trim($_POST['identitas'] ?? '');
$password  = $_POST['password'] ?? '';
$redirect_to = trim($_POST['redirect_to'] ?? '');
$redirect_to_safe = '';
if ($redirect_to !== '' && str_starts_with($redirect_to, '/') && !str_contains($redirect_to, '://') && !str_starts_with($redirect_to, '//')) {
    $redirect_to_safe = $redirect_to;
}

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
        status_aktif,
        foto_profil_url
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

// cek password (MD5 atau bcrypt)
$hash = $user['kata_sandi_hash'] ?? '';
$verified = false;
if (preg_match('/^\$2[aby]\$/', $hash)) {
    $verified = password_verify($password, $hash);
} else {
    $verified = md5($password) === $hash;
}

if (!$verified) {
    $_SESSION['login_error'] = "Password salah.";
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

// LOGIN SUKSES - Set session
$_SESSION['login'] = true;
$_SESSION['id_pengguna'] = $user['id_pengguna'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['peran'];
$_SESSION['foto_profil_url'] = $user['foto_profil_url'] ?? '';

// Update waktu login terakhir
$stmt = $koneksi->prepare("UPDATE pengguna SET diubah_pada = NOW() WHERE id_pengguna = ?");
$stmt->bind_param("i", $user['id_pengguna']);
$stmt->execute();

// Redirect
if ($redirect_to_safe !== '') {
    header("Location: " . BASE_URL . $redirect_to_safe);
} elseif (($user['peran'] ?? '') === 'admin') {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
} else {
    header("Location: " . BASE_URL . "/public/user/php/landingpageclean.php");
}
exit();
?>
