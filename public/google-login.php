<?php
// Entry point untuk Google OAuth (redirect ke halaman login Google).
require_once __DIR__ . "/../config/config.php";
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

// Jika konfigurasi belum lengkap, kembalikan ke login.
if ($client_id === '' || $client_secret === '' || $redirect_uri === '') {
    $_SESSION['login_error'] = 'Konfigurasi Google OAuth belum lengkap.';
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/public/login.php");
    exit();
}

// Simpan redirect_to jika ada (agar setelah login kembali ke halaman asal).
$redirect_to = trim($_GET['redirect_to'] ?? '');
if ($redirect_to !== '' && str_starts_with($redirect_to, '/') && !str_contains($redirect_to, '://') && !str_starts_with($redirect_to, '//')) {
    $_SESSION['oauth_redirect_to'] = $redirect_to;
} else {
    unset($_SESSION['oauth_redirect_to']);
}

// State untuk mencegah CSRF.
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

// Inisialisasi client Google.
$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->setScopes(['email', 'profile']);
$client->setPrompt('select_account');
$client->setState($state);

// Redirect ke halaman login Google.
$auth_url = $client->createAuthUrl();
header("Location: " . $auth_url);
exit();
