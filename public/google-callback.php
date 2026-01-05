<?php
// Callback Google OAuth: ambil data user, cek/insert ke DB, lalu login.
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload Composer untuk google/apiclient dan vlucas/phpdotenv.
require_once __DIR__ . "/../vendor/autoload.php";

// Load .env (aman jika file tidak ada).
$root = dirname(__DIR__);
$dotenv = Dotenv\Dotenv::createImmutable($root);
$dotenv->safeLoad();

// Ambil kredensial dari .env.
$client_id = $_ENV['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?? '';
$client_secret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? getenv('GOOGLE_CLIENT_SECRET') ?? '';
$redirect_uri = $_ENV['GOOGLE_REDIRECT_URI'] ?? getenv('GOOGLE_REDIRECT_URI') ?? '';

// Validasi konfigurasi.
if ($client_id === '' || $client_secret === '' || $redirect_uri === '') {
    $_SESSION['login_error'] = 'Konfigurasi Google OAuth belum lengkap.';
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/public/login.php");
    exit();
}

// Jika user membatalkan login di Google.
if (!empty($_GET['error'])) {
    $_SESSION['login_error'] = 'Login Google dibatalkan.';
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/public/login.php");
    exit();
}

// Cek state (CSRF protection).
$state = $_GET['state'] ?? '';
if ($state === '' || !isset($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
    $_SESSION['login_error'] = 'State OAuth tidak valid.';
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/public/login.php");
    exit();
}
unset($_SESSION['oauth_state']);

// Ambil kode otorisasi dari Google.
$code = $_GET['code'] ?? '';
if ($code === '') {
    $_SESSION['login_error'] = 'Kode otorisasi Google tidak ditemukan.';
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/public/login.php");
    exit();
}

// Inisialisasi client Google.
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);

// Tukar code dengan access token.
$token = $client->fetchAccessTokenWithAuthCode($code);
if (isset($token['error'])) {
    $_SESSION['login_error'] = 'Gagal mengambil token Google.';
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/public/login.php");
    exit();
}

$client->setAccessToken($token['access_token']);

// Ambil data user dari Google.
$oauth = new Google_Service_Oauth2($client);
$google_user = $oauth->userinfo->get();

$nama = trim((string)($google_user->name ?? ''));
$email = trim((string)($google_user->email ?? ''));
$google_id = trim((string)($google_user->id ?? '')); // Tidak disimpan karena tabel pengguna tidak punya kolom google_id.
$avatar = trim((string)($google_user->picture ?? ''));

// Email wajib ada untuk login.
if ($email === '') {
    $_SESSION['login_error'] = 'Email Google tidak tersedia.';
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/public/login.php");
    exit();
}

// Fallback nama jika kosong.
if ($nama === '') {
    $nama = $email;
}

// Helper: normalisasi username dari email.
function normalize_username(string $text): string {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text !== '' ? $text : 'user';
}

// Helper: pastikan username unik.
function generate_unique_username(mysqli $koneksi, string $base): string {
    $base = substr($base, 0, 50);
    $candidate = $base;
    $i = 1;
    while (true) {
        $stmt = $koneksi->prepare("SELECT COUNT(*) AS total FROM pengguna WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $candidate);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ((int)($row['total'] ?? 0) === 0) {
            return $candidate;
        }
        $i++;
        $suffix = '-' . $i;
        $max_len = 60 - strlen($suffix);
        $candidate = substr($base, 0, $max_len) . $suffix;
    }
}

// Cek apakah email sudah ada.
$stmt = $koneksi->prepare("
    SELECT id_pengguna, nama_lengkap, username, email, peran, status_aktif, foto_profil_url
    FROM pengguna
    WHERE email = ?
    LIMIT 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user) {
    // Jika akun tidak aktif, hentikan login.
    if ((int)($user['status_aktif'] ?? 0) !== 1) {
        $_SESSION['login_error'] = 'Akun tidak aktif.';
        header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/public/login.php");
        exit();
    }

    // Update foto profil jika tersedia dari Google.
    if ($avatar !== '') {
        $stmtUp = $koneksi->prepare("UPDATE pengguna SET foto_profil_url = ?, diubah_pada = NOW() WHERE id_pengguna = ?");
        $stmtUp->bind_param("si", $avatar, $user['id_pengguna']);
        $stmtUp->execute();
        $stmtUp->close();
        $user['foto_profil_url'] = $avatar;
    }
} else {
    // Buat username unik dari email.
    $email_prefix = explode('@', $email)[0] ?? 'user';
    $base_username = normalize_username($email_prefix);
    $username = generate_unique_username($koneksi, $base_username);

    // Password dummy karena login via Google (tidak dipakai untuk login manual).
    $random_password = bin2hex(random_bytes(16));
    $password_hash = password_hash($random_password, PASSWORD_BCRYPT);

    // Insert user baru.
    $stmtIns = $koneksi->prepare("
        INSERT INTO pengguna
            (nama_lengkap, username, email, kata_sandi_hash, peran, foto_profil_url, status_aktif, dibuat_pada)
        VALUES (?, ?, ?, ?, 'user', ?, 1, NOW())
    ");
    $stmtIns->bind_param("sssss", $nama, $username, $email, $password_hash, $avatar);
    $stmtIns->execute();
    $new_id = $stmtIns->insert_id;
    $stmtIns->close();

    $user = [
        'id_pengguna' => $new_id,
        'nama_lengkap' => $nama,
        'username' => $username,
        'email' => $email,
        'peran' => 'user',
        'status_aktif' => 1,
        'foto_profil_url' => $avatar,
    ];
}

// Set session login sesuai pola login manual.
$_SESSION['login'] = true;
$_SESSION['id_pengguna'] = $user['id_pengguna'];
$_SESSION['nama_lengkap'] = $user['nama_lengkap'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $user['peran'];
$_SESSION['foto_profil_url'] = $user['foto_profil_url'] ?? '';

// Redirect sesuai role atau redirect_to sebelumnya.
$redirect_to = $_SESSION['oauth_redirect_to'] ?? '';
unset($_SESSION['oauth_redirect_to']);

if ($redirect_to !== '') {
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . $redirect_to);
} elseif (($user['peran'] ?? '') === 'admin') {
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/admin/dashboard.php");
} else {
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/public/user/php/landingpageclean.php");
}
exit();
