<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$base_url = defined('BASE_URL') ? BASE_URL : '';

if (!function_exists('h')) {
  function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
  }
}

if (!function_exists('build_asset_url')) {
  function build_asset_url(string $path): string {
    if ($path === '') {
      return '';
    }
    if (preg_match('/^https?:\\/\\//i', $path)) {
      return $path;
    }
    $base_url = defined('BASE_URL') ? BASE_URL : '';
    return $base_url . $path;
  }
}

if (!function_exists('active_class')) {
  function active_class(bool $condition): string {
    return $condition ? 'active' : '';
  }
}

$is_logged_in = !empty($_SESSION['login']) && $_SESSION['login'] === true;
$nav_user = null;
if ($is_logged_in && isset($current_user) && is_array($current_user)) {
  $nav_user = $current_user;
} elseif ($is_logged_in && isset($koneksi) && $koneksi instanceof mysqli) {
  $user_id = (int)($_SESSION['id_pengguna'] ?? 0);
  if ($user_id > 0) {
    $stmt = $koneksi->prepare("
      SELECT id_pengguna, nama_lengkap, username, foto_profil_url
      FROM pengguna
      WHERE id_pengguna = ?
      LIMIT 1
    ");
    if ($stmt) {
      $stmt->bind_param("i", $user_id);
      $stmt->execute();
      $nav_user = $stmt->get_result()->fetch_assoc();
      $stmt->close();
    }
  }
}

$display_name = $nav_user['nama_lengkap']
  ?? $nav_user['username']
  ?? ($_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'User');
$avatar_url = trim((string)($nav_user['foto_profil_url'] ?? ($_SESSION['foto_profil_url'] ?? '')));
$initial_source = $display_name !== '' ? $display_name : 'U';
$avatar_initial = strtoupper(substr($initial_source, 0, 1));

$current_page = basename($_SERVER['PHP_SELF'] ?? '');
$dest_pages = ['destinasiLainnya.php', 'detailDestinasi.php'];
$event_pages = ['eventLainnya.php', 'detailEvent.php', 'checkoutEvent.php', 'pembayaranEvent.php', 'suksesPembayaranEvent.php', 'tiketEvent.php'];
$kuliner_pages = ['kulinerLainnya.php', 'detailKuliner.php'];

$is_dest = in_array($current_page, $dest_pages, true);
$is_event = in_array($current_page, $event_pages, true);
$is_kuliner = in_array($current_page, $kuliner_pages, true);
?>
<nav id="mainNavbar" class="navbar navbar-expand-lg fixed-top navbar-dark navbar-transparent">
  <div class="container navbar-shell">
    <!-- Logo Brand -->
    <a class="navbar-brand fw-bold" href="<?= $base_url ?>/public/user/php/landingpageclean.php">
      Jogja<span style="color: #C69C6D;">Verse.</span>
    </a>

    <!-- Tombol Toggle Mobile -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav navbar-center mx-auto mb-2 mb-lg-0 text-center">
        <li class="nav-item">
          <a class="nav-link <?= active_class($is_dest) ?>" href="<?= $base_url ?>/public/user/php/destinasiLainnya.php">Destinasi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= active_class($is_event) ?>" href="<?= $base_url ?>/public/user/php/eventLainnya.php">Event & Atraksi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= active_class($is_kuliner) ?>" href="<?= $base_url ?>/public/user/php/kulinerLainnya.php">Kuliner</a>
        </li>
      </ul>

      <!-- Tombol Login / Akun -->
      <div class="navbar-account d-flex justify-content-center align-items-center gap-2">
        <?php if ($is_logged_in): ?>
          <a href="<?= $base_url ?>/public/user.php?tab=overview" class="d-flex align-items-center text-decoration-none">
            <?php if ($avatar_url !== ''): ?>
              <img src="<?= h(build_asset_url($avatar_url)) ?>" alt="Profile" style="width:35px; height:35px; border-radius:50%; object-fit:cover; margin-right:8px;">
            <?php else: ?>
              <span class="d-inline-flex align-items-center justify-content-center text-white fw-semibold" style="width:35px; height:35px; border-radius:50%; background:#C69C6D; margin-right:8px; font-size:0.9rem;">
                <?= h($avatar_initial) ?>
              </span>
            <?php endif; ?>
            <span class="text-white fw-medium d-none d-md-inline" style="font-size: 0.95rem;">Akun</span>
          </a>
        <?php else: ?>
          <a href="<?= $base_url ?>/public/login.php" class="btn btn-gold px-4">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
