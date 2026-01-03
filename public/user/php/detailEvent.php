<?php
require_once __DIR__ . "/../../../config/koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function h($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function format_harga($harga) {
  if ($harga === null || $harga === '') {
    return 'Belum tersedia';
  }
  $harga = (int)$harga;
  if ($harga <= 0) {
    return 'Gratis';
  }
  return 'Rp ' . number_format($harga, 0, ',', '.');
}

function format_datetime_label($value) {
  if ($value === null || $value === '') {
    return 'Belum tersedia';
  }
  $ts = strtotime($value);
  if ($ts === false) {
    return (string)$value;
  }
  return date('d M Y H:i', $ts);
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$event = null;

if ($id) {
  $stmt = $koneksi->prepare("
    SELECT e.*, k.nama AS kategori
    FROM event e
    LEFT JOIN kategori k ON e.id_kategori = k.id_kategori AND k.tipe = 'event'
    WHERE e.id_event = ? AND e.status = 'publish'
    LIMIT 1
  ");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res) {
    $event = $res->fetch_assoc();
  }
  $stmt->close();
}

$not_found = !$id || !$event;
if ($not_found) {
  http_response_code(404);
}

$detailGallery = [];
if (!$not_found) {
  $stmtGal = $koneksi->prepare("SELECT gambar_url, keterangan FROM galeri WHERE jenis_target = 'event' AND id_target = ? ORDER BY urutan ASC, id_galeri ASC");
  $stmtGal->bind_param("i", $id);
  $stmtGal->execute();
  $resGal = $stmtGal->get_result();
  if ($resGal) {
    while ($row = $resGal->fetch_assoc()) {
      $detailGallery[] = $row;
    }
  }
  $stmtGal->close();
}

$hero_image = '../img/hero-yogyakarta.jpg';
if (!$not_found && !empty($event['gambar_sampul_url'])) {
  $hero_image = $event['gambar_sampul_url'];
}

$kategori_label = $event ? ($event['kategori'] ?? 'Event') : 'Event';
$lokasi_label = $event ? trim((string)($event['lokasi'] ?? '')) : '';
$lokasi_display = $lokasi_label !== '' ? $lokasi_label : 'Yogyakarta';
$mulai_label = $event ? format_datetime_label($event['mulai_pada'] ?? null) : 'Belum tersedia';
$selesai_label = $event ? format_datetime_label($event['selesai_pada'] ?? null) : 'Belum tersedia';
$harga_label = $event ? format_harga($event['harga'] ?? null) : 'Belum tersedia';
$kuota_label = $event && isset($event['kuota']) && $event['kuota'] !== null && $event['kuota'] !== ''
  ? ((int)$event['kuota'] . ' Orang')
  : 'Belum tersedia';

$deskripsi_display = $event && trim((string)($event['deskripsi'] ?? '')) !== ''
  ? $event['deskripsi']
  : 'Deskripsi belum tersedia.';

$lat_raw = $event['latitude'] ?? '';
$lng_raw = $event['longitude'] ?? '';
$has_map = $event
  && is_numeric($lat_raw)
  && is_numeric($lng_raw)
  && (float)$lat_raw != 0.0
  && (float)$lng_raw != 0.0;
$map_lat = $has_map ? (float)$lat_raw : null;
$map_lng = $has_map ? (float)$lng_raw : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $not_found ? 'Event Tidak Ditemukan - JogjaVerse' : h($event['judul']) . ' - JogjaVerse' ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style2.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <link rel="stylesheet" href="/assets/css/leaflet.css">

  <style>
    :root {
        --primary-color: #2D1B20;
        --secondary-color: #C69C6D;
    }
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #FDFBF7;
    }
    h1, h2, h3, .navbar-brand, .font-serif {
        font-family: 'Playfair Display', serif;
    }
    .navbar {
        background: transparent;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 10;
    }
    .hero-event {
        width: 100%;
        height: 460px;
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
        position: relative;
    }
    .hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45));
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #fff;
        padding: 0 16px;
    }
    .detail-gambar img {
        width: 100%;
        height: auto;
        display: block;
        object-fit: cover;
        border-radius: 16px;
        margin-bottom: 18px;
    }
    .detail-gambar img:last-child {
        margin-bottom: 0;
    }
    #mapEvent {
        width: 100%;
        height: 300px;
        border-radius: 16px;
    }
  </style>
</head>
<body>

<!-- 2. STRUKTUR HTML -->
<nav class="navbar navbar-expand-lg fixed-top navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">
        Jogja<span style="color: #C69C6D;">Verse.</span>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center">
        <li class="nav-item">
          <a class="nav-link" href="destinasiLainnya.php">Destinasi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="eventLainnya.php">Event & Atraksi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="kulinerlainnya.php">Kuliner</a>
        </li>
      </ul>

      <div class="d-flex justify-content-center">
      <?php
      if (!empty($_SESSION['login']) && $_SESSION['login'] === true) {
        $displayName = htmlspecialchars($_SESSION['nama_lengkap'] ?? ($_SESSION['username'] ?? 'User'));
        $avatarPath = '/public/user/img/default_avatar.png';
        
        echo '<a href="/public/dashboard_user.php" class="d-flex align-items-center text-decoration-none">';
        echo '<img src="' . $avatarPath . '" alt="Profile" style="width:35px; height:35px; border-radius:50%; object-fit:cover; margin-right:8px;">';
        echo '<span class="text-white fw-medium d-none d-md-inline" style="font-size: 0.95rem;">' . $displayName . '</span>';
        echo '</a>';
      } else {
        echo '<a href="/public/login.php" class="btn btn-gold px-4">Login</a>';
      }
      ?>
    </div>
    </div>
  </div>
</nav>

<?php if ($not_found): ?>
  <div class="hero-event" style="background-image: url('../img/hero-yogyakarta.jpg');">
    <div class="hero-overlay">
      <div class="text-center">
        <h1 class="fw-bold display-4 mb-2">Event Tidak Ditemukan</h1>
        <p class="text-white-50 fs-5">Data event tidak tersedia atau belum dipublikasikan.</p>
      </div>
    </div>
  </div>

  <section class="py-5">
    <div class="container text-center">
      <p class="text-muted mb-4">Coba kembali ke daftar event untuk melihat pilihan lain.</p>
      <a href="eventLainnya.php" class="btn btn-outline-dark rounded-pill px-4">Kembali ke Event</a>
    </div>
  </section>
<?php else: ?>
  <div class="hero-event" style="background-image: url('<?= h($hero_image) ?>');">
    <div class="hero-overlay">
      <div class="text-center">
        <h1 class="fw-bold display-4 mb-2"><?= h($event['judul']) ?></h1>
        <p class="text-white-50 fs-5"><?= h($lokasi_display) ?></p>
      </div>
    </div>
  </div>

  <section class="py-4">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="landingpageclean.php" class="text-decoration-none text-dark">Home</a></li>
          <li class="breadcrumb-item"><a href="eventLainnya.php" class="text-decoration-none text-dark">Event</a></li>
          <li class="breadcrumb-item active" aria-current="page"><?= h($event['judul']) ?></li>
        </ol>
      </nav>
    </div>
  </section>

  <section class="pb-5">
    <div class="container">
      <div class="row g-4 align-items-start">
        <div class="col-lg-8">
          <div class="mb-4">
            <div class="d-flex flex-wrap gap-2 mb-3">
              <span class="d-inline-flex align-items-center gap-2 bg-white border rounded-pill px-3 py-2 shadow-sm small">
                <i class="bi bi-tag text-warning"></i>
                <?= h($kategori_label) ?>
              </span>
              <span class="d-inline-flex align-items-center gap-2 bg-white border rounded-pill px-3 py-2 shadow-sm small">
                <i class="bi bi-geo-alt text-warning"></i>
                <?= h($lokasi_display) ?>
              </span>
              <span class="d-inline-flex align-items-center gap-2 bg-white border rounded-pill px-3 py-2 shadow-sm small">
                <i class="bi bi-calendar-event text-warning"></i>
                <?= h($mulai_label) ?>
              </span>
              <span class="d-inline-flex align-items-center gap-2 bg-white border rounded-pill px-3 py-2 shadow-sm small">
                <i class="bi bi-ticket-perforated text-warning"></i>
                <?= h($harga_label) ?>
              </span>
              <span class="d-inline-flex align-items-center gap-2 bg-white border rounded-pill px-3 py-2 shadow-sm small">
                <i class="bi bi-people text-warning"></i>
                <?= h($kuota_label) ?>
              </span>
            </div>
          </div>

          <div class="bg-white rounded-4 p-4 shadow-sm mb-4">
            <h2 class="fw-bold font-serif mb-3">Deskripsi</h2>
            <div class="text-muted" style="line-height:1.8;">
              <?= nl2br(h($deskripsi_display)) ?>
            </div>
          </div>

          <div class="bg-white rounded-4 p-4 shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <h3 class="fw-bold font-serif mb-0">Peta Lokasi</h3>
              <span class="text-muted small"><?= h($lokasi_display) ?></span>
            </div>
            <?php if ($has_map): ?>
              <div class="rounded-4 overflow-hidden shadow-sm">
                <div id="mapEvent"></div>
              </div>
            <?php else: ?>
              <div class="text-muted">Lokasi peta belum tersedia.</div>
            <?php endif; ?>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
              <h5 class="fw-bold font-serif mb-3">Informasi</h5>
              <ul class="list-unstyled text-muted small mb-0">
                <li class="mb-2"><i class="bi bi-geo-alt me-2 text-warning"></i><?= h($lokasi_display) ?></li>
                <li class="mb-2"><i class="bi bi-calendar-event me-2 text-warning"></i><?= h($mulai_label) ?></li>
                <li class="mb-2"><i class="bi bi-calendar-check me-2 text-warning"></i><?= h($selesai_label) ?></li>
                <li class="mb-2"><i class="bi bi-ticket-perforated me-2 text-warning"></i><?= h($harga_label) ?></li>
                <li class="mb-2"><i class="bi bi-people me-2 text-warning"></i><?= h($kuota_label) ?></li>
                <li><i class="bi bi-tag me-2 text-warning"></i><?= h($kategori_label) ?></li>
              </ul>
            </div>
          </div>

          <section class="detail-gambar">
            <h5 class="fw-bold font-serif mb-3">Detail Gambar</h5>
            <?php if (!empty($detailGallery)): ?>
              <?php foreach ($detailGallery as $img): ?>
                <img src="<?= h($img['gambar_url']) ?>" alt="<?= h($img['keterangan'] ?? $event['judul']) ?>" loading="lazy">
              <?php endforeach; ?>
            <?php else: ?>
              <small class="text-muted">Belum ada gambar detail.</small>
            <?php endif; ?>
          </section>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>

<footer class="footer-custom pt-5 mt-5">
  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-4 pe-lg-5">
        <div class="d-flex align-items-center mb-3">
          <h5 class="mb-0 fw-bold footer-brand">
             Jogja<span style="color: #C69C6D;">Verse.</span>
          </h5>
        </div>
        <p class="small text-light opacity-75 mb-4">
          Platform pariwisata digital terlengkap untuk menjelajahi keistimewaan Yogyakarta, dari destinasi budaya hingga kuliner legendaris.
        </p>
        <ul class="list-unstyled small opacity-75">
          <li class="mb-2 d-flex align-items-start">
            <i class="bi bi-geo-alt-fill icon-gold me-2 mt-1"></i>
            <span>Jl. Malioboro No. 1, Yogyakarta 55271</span>
          </li>
          <li class="mb-2 d-flex align-items-center">
            <i class="bi bi-envelope-fill icon-gold me-2"></i>
            <span>halo@jogjaverse.id</span>
          </li>
          <li class="d-flex align-items-center">
            <i class="bi bi-telephone-fill icon-gold me-2"></i>
            <span>(0274) 123456</span>
          </li>
        </ul>
      </div>

      <div class="col-lg-2 col-6">
        <h6 class="fw-bold mb-3 text-white">Jelajah</h6>
        <ul class="list-unstyled footer-link">
          <li><a href="#destinasi">Destinasi Populer</a></li>
          <li><a href="#event">Kalender Event</a></li>
          <li><a href="#kuliner">Kuliner Khas</a></li>
          <li><a href="#">Virtual Tour</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-6">
        <h6 class="fw-bold mb-3 text-white">Layanan</h6>
        <ul class="list-unstyled footer-link">
          <li><a href="#">Pusat Bantuan</a></li>
          <li><a href="#">Panduan Perjalanan</a></li>
          <li><a href="#">Kerjasama Mitra</a></li>
          <li><a href="#">Kontak Kami</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-6">
        <h6 class="fw-bold mb-3 text-white">Tentang</h6>
        <ul class="list-unstyled footer-link">
          <li><a href="#">Tentang JogjaVerse</a></li>
          <li><a href="#">Kebijakan Privasi</a></li>
          <li><a href="#">Syarat & Ketentuan</a></li>
          <li><a href="#">Karir</a></li>
        </ul>
      </div>
    </div>

    <hr class="border-light opacity-10 my-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pb-4 gap-3">
      <small class="opacity-50">
        &copy; 2025 JogjaVerse. Disponsori oleh Pemerintah Kota Yogyakarta.
      </small>

      <div class="d-flex gap-2">
        <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
        <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
        <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
        <a href="#" class="social-icon"><i class="bi bi-youtube"></i></a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!$not_found && $has_map): ?>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const lat = parseFloat(<?= json_encode($map_lat) ?>);
      const lng = parseFloat(<?= json_encode($map_lng) ?>);
      const mapEl = document.getElementById('mapEvent');
      if (!mapEl || Number.isNaN(lat) || Number.isNaN(lng)) {
        return;
      }
      if (window._mapEvent) {
        return;
      }
      window._mapEvent = L.map('mapEvent').setView([lat, lng], 14);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
      }).addTo(window._mapEvent);
      L.marker([lat, lng]).addTo(window._mapEvent);
    });
  </script>
<?php endif; ?>

<script>
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
</script>

</body>
</html>
