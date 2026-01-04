<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/koneksi.php";
session_start();

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function build_asset_url(?string $path): string {
    if ($path === null || $path === '') {
        return '';
    }
    if (preg_match('/^https?:\\/\\//i', $path)) {
        return $path;
    }
    return BASE_URL . $path;
}

function table_exists(mysqli $koneksi, string $table): bool {
    $stmt = $koneksi->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("s", $table);
    $stmt->execute();
    $res = $stmt->get_result();
    $exists = $res && $res->num_rows > 0;
    $stmt->close();
    return $exists;
}

function pick_field(array $row, array $candidates, string $default = '-'): string {
    foreach ($candidates as $field) {
        if (array_key_exists($field, $row) && $row[$field] !== null && $row[$field] !== '') {
            return (string)$row[$field];
        }
    }
    return $default;
}

// Cek apakah user sudah login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

// Ambil data user dari database
$user_id = (int)($_SESSION['id_pengguna'] ?? 0);
if ($user_id <= 0) {
    session_destroy();
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}
$stmt = $koneksi->prepare("SELECT * FROM pengguna WHERE id_pengguna = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header("Location: " . BASE_URL . "/public/login.php");
    exit();
}

$flash_success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$profile_error = '';
$profile_values = $user;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nomor_hp = trim($_POST['nomor_hp'] ?? '');
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $password_konfirmasi = $_POST['password_konfirmasi'] ?? '';

    $profile_values = array_merge($profile_values, [
        'nama_lengkap' => $nama_lengkap,
        'username' => $username,
        'email' => $email,
        'nomor_hp' => $nomor_hp,
    ]);

    if ($nama_lengkap === '') {
        $profile_error = 'Nama lengkap wajib diisi.';
    } elseif ($username === '') {
        $profile_error = 'Username wajib diisi.';
    } elseif (strlen($nomor_hp) > 30) {
        $profile_error = 'Nomor HP maksimal 30 karakter.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profile_error = 'Format email tidak valid.';
    }

    if ($profile_error === '') {
        $stmt = $koneksi->prepare("SELECT 1 FROM pengguna WHERE username = ? AND id_pengguna != ? LIMIT 1");
        if (!$stmt) {
            $profile_error = 'Gagal memeriksa username.';
        } else {
            $stmt->bind_param("si", $username, $user_id);
            $stmt->execute();
            $exists = $stmt->get_result();
            if ($exists && $exists->num_rows > 0) {
                $profile_error = 'Username sudah digunakan.';
            }
            $stmt->close();
        }
    }

    if ($profile_error === '' && $email !== '') {
        $stmt = $koneksi->prepare("SELECT 1 FROM pengguna WHERE email = ? AND id_pengguna != ? LIMIT 1");
        if (!$stmt) {
            $profile_error = 'Gagal memeriksa email.';
        } else {
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $exists = $stmt->get_result();
            if ($exists && $exists->num_rows > 0) {
                $profile_error = 'Email sudah digunakan.';
            }
            $stmt->close();
        }
    }

    $new_password_hash = null;
    if ($profile_error === '' && ($password_lama !== '' || $password_baru !== '' || $password_konfirmasi !== '')) {
        if ($password_lama === '') {
            $profile_error = 'Password lama wajib diisi untuk mengganti password.';
        } elseif (!password_verify($password_lama, $user['kata_sandi_hash'] ?? '')) {
            $profile_error = 'Password lama tidak sesuai.';
        } elseif (strlen($password_baru) < 6) {
            $profile_error = 'Password baru minimal 6 karakter.';
        } elseif ($password_baru !== $password_konfirmasi) {
            $profile_error = 'Konfirmasi password tidak sama.';
        } else {
            $new_password_hash = password_hash($password_baru, PASSWORD_BCRYPT);
        }
    }

    $foto_path = null;
    if ($profile_error === '' && isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] !== UPLOAD_ERR_NO_FILE) {
        $foto_error = $_FILES['foto_profil']['error'];
        if ($foto_error !== UPLOAD_ERR_OK) {
            $profile_error = 'Upload foto gagal.';
        } elseif (($_FILES['foto_profil']['size'] ?? 0) > 2 * 1024 * 1024) {
            $profile_error = 'Ukuran foto maksimal 2MB.';
        } else {
            $tmp_name = $_FILES['foto_profil']['tmp_name'] ?? '';
            $mime = $tmp_name ? mime_content_type($tmp_name) : '';
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ];
            if (!$mime || !isset($allowed[$mime])) {
                $profile_error = 'Format foto harus JPG, PNG, atau WEBP.';
            } else {
                $upload_dir = __DIR__ . '/user/img/profiles';
                if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true)) {
                    $profile_error = 'Gagal membuat folder upload.';
                } else {
                    $ext = $allowed[$mime];
                    $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                    $target = $upload_dir . '/' . $filename;
                    if (!move_uploaded_file($tmp_name, $target)) {
                        $profile_error = 'Gagal menyimpan foto profil.';
                    } else {
                        $foto_path = '/public/user/img/profiles/' . $filename;
                    }
                }
            }
        }
    }

    if ($profile_error === '') {
        $set_parts = [];
        $types = '';
        $params = [];

        $set_parts[] = "nama_lengkap = ?";
        $types .= "s";
        $params[] = $nama_lengkap;

        $set_parts[] = "username = ?";
        $types .= "s";
        $params[] = $username;

        $set_parts[] = "email = ?";
        $types .= "s";
        $params[] = $email !== '' ? $email : null;

        $set_parts[] = "nomor_hp = ?";
        $types .= "s";
        $params[] = $nomor_hp !== '' ? $nomor_hp : null;

        if ($foto_path !== null) {
            $set_parts[] = "foto_profil_url = ?";
            $types .= "s";
            $params[] = $foto_path;
        }

        if ($new_password_hash !== null) {
            $set_parts[] = "kata_sandi_hash = ?";
            $types .= "s";
            $params[] = $new_password_hash;
        }

        $types .= "i";
        $params[] = $user_id;

        $sql = "UPDATE pengguna SET " . implode(", ", $set_parts) . " WHERE id_pengguna = ? LIMIT 1";
        $stmt = $koneksi->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
            $refresh = $koneksi->prepare("
                SELECT id_pengguna, nama_lengkap, username, email, foto_profil_url, peran
                FROM pengguna
                WHERE id_pengguna = ?
                LIMIT 1
            ");
            if ($refresh) {
                $refresh->bind_param("i", $user_id);
                $refresh->execute();
                $updated = $refresh->get_result()->fetch_assoc();
                $refresh->close();
                if ($updated) {
                    $_SESSION['nama_lengkap'] = $updated['nama_lengkap'] ?? '';
                    $_SESSION['username'] = $updated['username'] ?? '';
                    $_SESSION['email'] = $updated['email'] ?? '';
                    $_SESSION['role'] = $updated['peran'] ?? ($_SESSION['role'] ?? 'user');
                    $_SESSION['foto_profil_url'] = $updated['foto_profil_url'] ?? '';
                }
            }
            $_SESSION['flash_success'] = 'Profil berhasil diperbarui.';
            header("Location: " . BASE_URL . "/public/user.php?tab=profile");
            exit();
        } else {
            $profile_error = 'Gagal memperbarui profil.';
        }
    }
}

$reservasi_event = [];
$reservasi_message = '';
if (table_exists($koneksi, 'reservasi_event')) {
    $stmt = $koneksi->prepare("
        SELECT r.id_reservasi, r.jumlah_tiket, r.total_harga, r.status, r.dibuat_pada,
               e.judul AS nama_event
        FROM reservasi_event r
        LEFT JOIN event e ON r.id_event = e.id_event
        WHERE r.id_pengguna = ?
        ORDER BY r.dibuat_pada DESC
    ");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $reservasi_event[] = $row;
            }
        }
        $stmt->close();
    } else {
        $reservasi_message = 'Belum ada reservasi event.';
    }
} else {
    $reservasi_message = 'Belum ada reservasi event.';
}

$ulasan_list = [];
$ulasan_message = '';
if (table_exists($koneksi, 'ulasan')) {
    $stmt = $koneksi->prepare("
        SELECT rating, komentar, jenis_target, status, dibuat_pada
        FROM ulasan
        WHERE id_pengguna = ?
          AND jenis_target IN ('destinasi','event','kuliner')
        ORDER BY dibuat_pada DESC
    ");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $ulasan_list[] = $row;
            }
        }
        $stmt->close();
    } else {
        $ulasan_message = 'Belum ada ulasan.';
    }
} else {
    $ulasan_message = 'Belum ada ulasan.';
}

$pelaporan_list = [];
$pelaporan_message = '';
$pelaporan_available = false;
if (table_exists($koneksi, 'pelaporan')) {
    $cols = [];
    $resCols = $koneksi->query("SHOW COLUMNS FROM pelaporan");
    if ($resCols) {
        while ($c = $resCols->fetch_assoc()) {
            $cols[] = $c['Field'];
        }
    }
    if (in_array('id_pengguna', $cols, true)) {
        $pelaporan_available = true;
        $stmt = $koneksi->prepare("SELECT * FROM pelaporan WHERE id_pengguna = ? ORDER BY dibuat_pada DESC");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $pelaporan_list[] = $row;
                }
            }
            $stmt->close();
        } else {
            $pelaporan_message = 'Fitur pelaporan belum tersedia.';
        }
    } else {
        $pelaporan_message = 'Fitur pelaporan belum tersedia.';
    }
} else {
    $pelaporan_message = 'Fitur pelaporan belum tersedia.';
}

$allowed_tabs = ['overview', 'bookings', 'reviews', 'reports', 'profile'];
$tab = $_GET['tab'] ?? 'overview';
if (!in_array($tab, $allowed_tabs, true)) {
    $tab = 'overview';
}

$total_reservasi = count($reservasi_event);
$total_ulasan = count($ulasan_list);
$total_pelaporan = count($pelaporan_list);
$display_name = $user['nama_lengkap'] ?? $user['username'] ?? 'User';
$avatar_initial = strtoupper(substr($display_name, 0, 1));
$avatar_url = trim((string)($user['foto_profil_url'] ?? ''));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - JogjaVerse</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="user/css/style2.css">
  <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background: #F7F5F2;
        color: #333;
    }
    
    /* Header */
    .header {
        background: #4D2832;
        color: white;
        padding: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 100;
    }
    
    .header-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        color: white;
        font-size: 22px;
        font-weight: 700;
    }
    
    .logo span {
        color: #E88324;
    }
    
    .header-nav {
        display: flex;
        align-items: center;
        gap: 30px;
    }
    
    .header-nav a {
        color: white;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.3s;
    }
    
    .header-nav a:hover {
        color: #E88324;
    }
    
    .user-menu {
        position: relative;
    }
    .user-links {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .user-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,0.1);
        padding: 8px 14px;
        border-radius: 20px;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid rgba(255,255,255,0.2);
        transition: all 0.3s;
    }
    .user-link:hover {
        background: rgba(255,255,255,0.2);
    }
    .user-link.logout {
        background: #fff;
        color: #6F2232;
    }
    
    /* Container */
    .account-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 110px 20px 30px;
    }
    
    /* Breadcrumb */
    .breadcrumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
    }
    
    .breadcrumb a {
        color: #6F2232;
        text-decoration: none;
    }
    
    .breadcrumb a:hover {
        text-decoration: underline;
    }
    
    /* Main Layout */
    .main-layout {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 30px;
        align-items: start;
    }
    
    /* Sidebar */
    .sidebar {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        position: sticky;
        top: 90px;
    }
    
    .sidebar-header {
        padding: 25px 20px;
        background: linear-gradient(135deg, #6F2232 0%, #4D2832 100%);
        color: white;
        text-align: center;
    }
    
    .sidebar-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: #E88324;
        margin: 0 auto 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
        border: 3px solid rgba(255,255,255,0.3);
    }

    .sidebar-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        display: block;
    }
    
    .sidebar-name {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .sidebar-email {
        font-size: 13px;
        opacity: 0.9;
    }
    
    .sidebar-menu {
        list-style: none;
    }
    
    .sidebar-menu li a {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px 20px;
        color: #333;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .sidebar-menu li a::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: transparent;
    }
    
    .sidebar-menu li a:hover,
    .sidebar-menu li a.active {
        background: #F7F5F2;
        color: #6F2232;
    }

    .sidebar-menu li a:hover::before,
    .sidebar-menu li a.active::before {
        background: #E88324;
    }
    
    .sidebar-menu li a svg {
        width: 20px;
        height: 20px;
    }
    
    /* Content Area */
    .content-area {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        min-height: 500px;
    }
    
    .content-header {
        border-bottom: 2px solid #F7F5F2;
        padding-bottom: 20px;
        margin-bottom: 30px;
    }
    
    .content-header h1 {
        font-size: 28px;
        color: #4D2832;
        margin-bottom: 8px;
    }
    
    .content-header p {
        color: #666;
        font-size: 14px;
    }
    
    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #F7F5F2 0%, #EDE9E4 100%);
        padding: 20px;
        border-radius: 10px;
        border-left: 4px solid #E88324;
    }
    
    .stat-card-icon {
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        color: #6F2232;
    }
    
    .stat-card-value {
        font-size: 24px;
        font-weight: 700;
        color: #4D2832;
        margin-bottom: 5px;
    }
    
    .stat-card-label {
        font-size: 13px;
        color: #666;
    }
    
    /* Info Grid */
    .info-section {
        margin-bottom: 30px;
    }
    
    .info-section h2 {
        font-size: 18px;
        color: #4D2832;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .info-item {
        padding: 15px;
        background: #F7F5F2;
        border-radius: 8px;
    }
    
    .info-item-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        font-weight: 600;
    }
    
    .info-item-value {
        font-size: 15px;
        color: #333;
        font-weight: 500;
    }

    .profile-form {
        display: grid;
        gap: 18px;
    }

    .profile-form .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }

    .profile-form label {
        font-size: 13px;
        font-weight: 600;
        color: #4D2832;
        margin-bottom: 6px;
        display: block;
    }

    .profile-form input,
    .profile-form textarea {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #ddd;
        font-size: 14px;
    }

    .profile-preview {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }

    .profile-preview img {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #EDE9E4;
    }

    .profile-fallback {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: #E88324;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 700;
        border: 2px solid #EDE9E4;
    }

    .profile-help {
        font-size: 12px;
        color: #666;
    }

    .table-simple {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }
    .table-simple th,
    .table-simple td {
        padding: 10px;
        border-bottom: 1px solid #eee;
        text-align: left;
        font-size: 14px;
        vertical-align: top;
    }
    .table-simple th {
        background: #F7F5F2;
        color: #4D2832;
        font-weight: 700;
    }
    .section-empty {
        color: #666;
        font-size: 14px;
    }
    
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .badge-success {
        background: #d4edda;
        color: #155724;
    }
    
    .badge-danger {
        background: #f8d7da;
        color: #721c24;
    }
    
    .badge-primary {
        background: #e8d5d5;
        color: #6F2232;
    }
    
    .badge-warning {
        background: #fff3cd;
        color: #856404;
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 30px;
    }
    
    .content-area .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }
    
    .content-area .btn-primary {
        background: linear-gradient(135deg, #6F2232 0%, #4D2832 100%);
        color: white;
    }
    
    .content-area .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(111, 34, 50, 0.3);
    }
    
    .content-area .btn-secondary {
        background: white;
        color: #6F2232;
        border: 2px solid #EDE9E4;
    }
    
    .content-area .btn-secondary:hover {
        background: #F7F5F2;
        border-color: #E88324;
    }
    
    .content-area .btn-logout {
        background: white;
        color: #dc3545;
        border: 2px solid #dc3545;
    }
    
    .content-area .btn-logout:hover {
        background: #dc3545;
        color: white;
    }
    
    /* Quick Links */
    .quick-links {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .quick-link {
        padding: 20px;
        background: #F7F5F2;
        border-radius: 8px;
        text-align: center;
        text-decoration: none;
        color: #333;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    
    .quick-link:hover {
        background: white;
        border-color: #E88324;
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(232, 131, 36, 0.2);
    }
    
    .quick-link-icon {
        font-size: 32px;
        margin-bottom: 10px;
    }
    
    .quick-link-text {
        font-size: 13px;
        font-weight: 600;
    }
    
    /* Responsive */
    @media (max-width: 968px) {
        .main-layout {
            grid-template-columns: 1fr;
        }
        
        .sidebar {
            position: static;
        }
        
        .header-nav {
            display: none;
        }
    }
    
    @media (max-width: 600px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<?php $current_user = $user; include __DIR__ . "/user/php/includes/navbar.php"; ?>

<!-- Container -->
<div class="account-container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="user/php/landingpageclean.php">Home</a>
        <span>&gt;</span>
        <span>My Account</span>
    </div>

    
    <!-- Main Layout -->
    <div class="main-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-avatar">
                    <?php if ($avatar_url !== ''): ?>
                        <img src="<?= h(build_asset_url($avatar_url)) ?>" alt="Avatar">
                    <?php else: ?>
                        <?= h($avatar_initial) ?>
                    <?php endif; ?>
                </div>
                <div class="sidebar-name"><?= h($user['nama_lengkap'] ?? '-') ?></div>
                <div class="sidebar-email"><?= h($user['email'] ?? '-') ?></div>
            </div>
            <ul class="sidebar-menu">
                <li>
                    <a href="user.php?tab=overview" class="<?= $tab === 'overview' ? 'active' : '' ?>">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        Account Overview
                    </a>
                </li>
                <li>
                    <a href="user.php?tab=bookings" class="<?= $tab === 'bookings' ? 'active' : '' ?>">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        My Bookings
                    </a>
                </li>
                <li>
                    <a href="user.php?tab=reviews" class="<?= $tab === 'reviews' ? 'active' : '' ?>">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                        Ulasan Saya
                    </a>
                </li>
                <li>
                    <a href="user.php?tab=reports" class="<?= $tab === 'reports' ? 'active' : '' ?>">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"/>
                        </svg>
                        Pelaporan Saya
                    </a>
                </li>
                <li>
                    <a href="user.php?tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M11.983 1.002a1 1 0 00-1.966 0l-.157.943a6.03 6.03 0 00-1.52.63l-.86-.51a1 1 0 00-1.37.366l-.75 1.3a1 1 0 00.367 1.366l.86.497a6.086 6.086 0 000 1.26l-.86.498a1 1 0 00-.366 1.366l.75 1.3a1 1 0 001.37.366l.86-.51c.485.28.996.49 1.52.63l.157.943a1 1 0 001.966 0l.157-.943c.524-.14 1.035-.35 1.52-.63l.86.51a1 1 0 001.37-.366l.75-1.3a1 1 0 00-.366-1.366l-.86-.498a6.086 6.086 0 000-1.26l.86-.497a1 1 0 00.366-1.366l-.75-1.3a1 1 0 00-1.37-.366l-.86.51a6.03 6.03 0 00-1.52-.63l-.157-.943zM10 7a3 3 0 100 6 3 3 0 000-6z"/>
                        </svg>
                        Profile Settings
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Content -->
        <main class="content-area">
    <?php
    $page_title = 'Account Overview';
    $page_desc = 'Ringkasan profil dan aktivitas akun.';
    if ($tab === 'bookings') {
        $page_title = 'My Bookings';
        $page_desc = 'Riwayat reservasi event Anda.';
    } elseif ($tab === 'reviews') {
        $page_title = 'Ulasan Saya';
        $page_desc = 'Riwayat ulasan yang pernah Anda kirim.';
    } elseif ($tab === 'reports') {
        $page_title = 'Pelaporan Saya';
        $page_desc = 'Riwayat pelaporan yang pernah Anda buat.';
    } elseif ($tab === 'profile') {
        $page_title = 'Profile Settings';
        $page_desc = 'Perbarui informasi akun Anda.';
    }
    ?>
    <div class="content-header">
        <h1><?= h($page_title) ?></h1>
        <p><?= h($page_desc) ?></p>
    </div>

    <?php if ($tab === 'overview'): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-icon"><i class="bi bi-ticket-perforated"></i></div>
                <div class="stat-card-value"><?= h($total_reservasi) ?></div>
                <div class="stat-card-label">Total Reservasi</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon"><i class="bi bi-star-fill"></i></div>
                <div class="stat-card-value"><?= h($total_ulasan) ?></div>
                <div class="stat-card-label">Total Ulasan</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon"><i class="bi bi-flag-fill"></i></div>
                <div class="stat-card-value"><?= h($total_pelaporan) ?></div>
                <div class="stat-card-label">Total Pelaporan</div>
            </div>
        </div>

        <div class="info-section">
            <h2>Profil Singkat</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-item-label">Nama</div>
                    <div class="info-item-value"><?= h($user['nama_lengkap'] ?? '-') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Username</div>
                    <div class="info-item-value"><?= h($user['username'] ?? '-') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Email</div>
                    <div class="info-item-value"><?= h($user['email'] ?? '-') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Role</div>
                    <div class="info-item-value"><?= h($user['peran'] ?? '-') ?></div>
                </div>
            </div>
        </div>

        <div class="info-section">
            <h2>Status Akun</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-item-label">Status</div>
                    <div class="info-item-value"><?= ($user['status_aktif'] ?? 0) ? 'Aktif' : 'Non-aktif' ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Member Since</div>
                    <div class="info-item-value"><?= h($user['dibuat_pada'] ?? '-') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Last Login</div>
                    <div class="info-item-value"><?= h($user['diubah_pada'] ?? '-') ?></div>
                </div>
            </div>
        </div>
    <?php elseif ($tab === 'bookings'): ?>
        <div class="info-section">
            <h2>Riwayat Reservasi Event</h2>
            <?php if ($reservasi_message !== ''): ?>
                <div class="section-empty"><?= h($reservasi_message) ?></div>
            <?php elseif (empty($reservasi_event)): ?>
                <div class="section-empty">Belum ada reservasi event.</div>
            <?php else: ?>
                <table class="table-simple">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservasi_event as $row): ?>
                            <?php
                                $status_reservasi = strtoupper((string)($row['status'] ?? ''));
                                $can_ticket = in_array($status_reservasi, ['DIKONFIRMASI', 'LUNAS', 'BERHASIL', 'SUDAH_BAYAR'], true);
                            ?>
                            <tr>
                                <td><?= h($row['id_reservasi'] ?? '-') ?></td>
                                <td><?= h($row['nama_event'] ?? '-') ?></td>
                                <td><?= h($row['jumlah_tiket'] ?? '-') ?></td>
                                <td><?= h(number_format((int)($row['total_harga'] ?? 0), 0, ',', '.')) ?></td>
                                <td><?= h($row['status'] ?? '-') ?></td>
                                <td><?= h($row['dibuat_pada'] ?? '-') ?></td>
                                <td>
                                    <?php if ($can_ticket): ?>
                                        <a class="btn btn-sm btn-gold" href="user/php/tiketEvent.php?id_reservasi=<?= h($row['id_reservasi']) ?>">Lihat Tiket</a>
                                    <?php else: ?>
                                        <span class="text-muted small">Belum tersedia</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php elseif ($tab === 'reviews'): ?>
        <div class="info-section">
            <h2>Riwayat Ulasan</h2>
            <?php if ($ulasan_message !== ''): ?>
                <div class="section-empty"><?= h($ulasan_message) ?></div>
            <?php elseif (empty($ulasan_list)): ?>
                <div class="section-empty">Belum ada ulasan.</div>
            <?php else: ?>
                <table class="table-simple">
                    <thead>
                        <tr>
                            <th>Rating</th>
                            <th>Komentar</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ulasan_list as $row): ?>
                            <tr>
                                <td><?= h($row['rating'] ?? '-') ?></td>
                                <td><small><?= nl2br(h($row['komentar'] ?? '-')) ?></small></td>
                                <td><?= h($row['jenis_target'] ?? '-') ?></td>
                                <td><?= h($row['status'] ?? '-') ?></td>
                                <td><?= h($row['dibuat_pada'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php elseif ($tab === 'reports'): ?>
        <div class="info-section">
            <h2>Riwayat Pelaporan</h2>
            <?php if (!$pelaporan_available): ?>
                <div class="section-empty"><?= h($pelaporan_message) ?></div>
            <?php elseif (empty($pelaporan_list)): ?>
                <div class="section-empty">Belum ada pelaporan.</div>
            <?php else: ?>
                <table class="table-simple">
                    <thead>
                        <tr>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Isi</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pelaporan_list as $row): ?>
                            <tr>
                                <td><?= h(pick_field($row, ['jenis', 'jenis_laporan', 'tipe', 'kategori'])) ?></td>
                                <td><?= h(pick_field($row, ['status', 'status_laporan', 'status_pelaporan'])) ?></td>
                                <td><small><?= nl2br(h(pick_field($row, ['deskripsi', 'keterangan', 'pesan', 'isi', 'detail']))) ?></small></td>
                                <td><?= h(pick_field($row, ['dibuat_pada', 'created_at', 'tanggal', 'waktu'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php elseif ($tab === 'profile'): ?>
        <div class="info-section">
            <h2>Profile Settings</h2>
            <?php if ($flash_success): ?>
                <div class="alert alert-success"><?= h($flash_success) ?></div>
            <?php endif; ?>
            <?php if ($profile_error): ?>
                <div class="alert alert-danger"><?= h($profile_error) ?></div>
            <?php endif; ?>
            <form class="profile-form" method="POST" action="user.php?tab=profile" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?= h($profile_values['nama_lengkap'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?= h($profile_values['username'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= h($profile_values['email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="nomor_hp">Nomor HP</label>
                        <input type="text" id="nomor_hp" name="nomor_hp" value="<?= h($profile_values['nomor_hp'] ?? '') ?>" maxlength="30">
                    </div>
                </div>

                <div class="form-group">
                    <label for="foto_profil">Foto Profil</label>
                    <?php if (!empty($profile_values['foto_profil_url'])): ?>
                        <div class="profile-preview">
                            <img src="<?= h(build_asset_url($profile_values['foto_profil_url'])) ?>" alt="Foto profil">
                            <span class="profile-help">Foto saat ini</span>
                        </div>
                    <?php else: ?>
                        <div class="profile-preview">
                            <span class="profile-fallback"><?= h($avatar_initial) ?></span>
                            <span class="profile-help">Belum ada foto.</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="foto_profil" name="foto_profil" accept=".jpg,.jpeg,.png,.webp">
                    <div class="profile-help">Format JPG/PNG/WEBP, maksimal 2MB.</div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="password_lama">Password Lama</label>
                        <input type="password" id="password_lama" name="password_lama" autocomplete="current-password">
                    </div>
                    <div class="form-group">
                        <label for="password_baru">Password Baru</label>
                        <input type="password" id="password_baru" name="password_baru" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label for="password_konfirmasi">Konfirmasi Password Baru</label>
                        <input type="password" id="password_konfirmasi" name="password_konfirmasi" autocomplete="new-password">
                    </div>
                </div>
                <div class="profile-help">Isi password lama jika ingin mengganti password.</div>

                <div class="action-buttons">
                    <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                    <a class="btn btn-logout" href="logout.php">Logout</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="user/js/navbar.js"></script>
</body>
</html>
