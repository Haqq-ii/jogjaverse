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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$destinasi = null;

if ($id) {
  $stmt = $koneksi->prepare("
    SELECT 
      d.id_destinasi,
      d.id_kategori,
      d.nama,
      d.deskripsi,
      d.alamat,
      d.kota,
      d.latitude,
      d.longitude,
      d.jam_operasional,
      d.harga_tiket,
      d.nomor_kontak,
      d.gambar_sampul_url,
      k.nama AS kategori
    FROM destinasi d
    JOIN kategori k ON d.id_kategori = k.id_kategori
    WHERE d.id_destinasi = ? AND d.status = 'publish' AND k.tipe = 'destinasi'
    LIMIT 1
  ");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result) {
    $destinasi = $result->fetch_assoc();
  }
  $stmt->close();
}

$not_found = !$id || !$destinasi;
if ($not_found) {
  http_response_code(404);
}

$related = [];
if (!$not_found) {
  $kategori_id = (int)($destinasi['id_kategori'] ?? 0);
  if ($kategori_id > 0) {
    $stmt_rel = $koneksi->prepare("
      SELECT 
        d.id_destinasi AS id,
        d.nama AS nama_destinasi,
        d.deskripsi AS deskripsi_singkat,
        k.nama AS kategori,
        d.gambar_sampul_url AS gambar,
        d.jam_operasional AS estimasi_waktu
      FROM destinasi d
      JOIN kategori k ON d.id_kategori = k.id_kategori
      WHERE d.status = 'publish' AND k.tipe = 'destinasi'
        AND d.id_kategori = ? AND d.id_destinasi != ?
      ORDER BY d.dibuat_pada DESC
      LIMIT 3
    ");
    $stmt_rel->bind_param("ii", $kategori_id, $id);
  } else {
    $stmt_rel = $koneksi->prepare("
      SELECT 
        d.id_destinasi AS id,
        d.nama AS nama_destinasi,
        d.deskripsi AS deskripsi_singkat,
        k.nama AS kategori,
        d.gambar_sampul_url AS gambar,
        d.jam_operasional AS estimasi_waktu
      FROM destinasi d
      JOIN kategori k ON d.id_kategori = k.id_kategori
      WHERE d.status = 'publish' AND k.tipe = 'destinasi'
        AND d.id_destinasi != ?
      ORDER BY d.dibuat_pada DESC
      LIMIT 3
    ");
    $stmt_rel->bind_param("i", $id);
  }
  $stmt_rel->execute();
  $res_rel = $stmt_rel->get_result();
  if ($res_rel) {
    while ($row = $res_rel->fetch_assoc()) {
      $related[] = $row;
    }
  }
  $stmt_rel->close();
}

$hero_image = '../img/hero-yogyakarta.jpg';
if (!$not_found && !empty($destinasi['gambar_sampul_url'])) {
  $hero_image = $destinasi['gambar_sampul_url'];
}

$kategori_label = $destinasi ? ($destinasi['kategori'] ?? 'Destinasi') : 'Destinasi';
$kota_label = $destinasi ? trim((string)($destinasi['kota'] ?? '')) : '';
$jam_label = $destinasi ? trim((string)($destinasi['jam_operasional'] ?? '')) : '';
$kontak_label = $destinasi ? trim((string)($destinasi['nomor_kontak'] ?? '')) : '';
$alamat_label = $destinasi ? trim((string)($destinasi['alamat'] ?? '')) : '';
$harga_label = $destinasi ? format_harga($destinasi['harga_tiket'] ?? null) : 'Belum tersedia';

$kota_display = $kota_label !== '' ? $kota_label : 'Yogyakarta';
$jam_display = $jam_label !== '' ? $jam_label : 'Belum tersedia';
$kontak_display = $kontak_label !== '' ? $kontak_label : 'Belum tersedia';
$alamat_display = $alamat_label !== '' ? $alamat_label : 'Belum tersedia';
if ($alamat_label !== '' && $kota_label !== '' && stripos($alamat_label, $kota_label) === false) {
  $alamat_display = $alamat_label . ", " . $kota_label;
} elseif ($alamat_label === '' && $kota_label !== '') {
  $alamat_display = $kota_label;
}

$deskripsi_display = $destinasi && trim((string)($destinasi['deskripsi'] ?? '')) !== ''
  ? $destinasi['deskripsi']
  : 'Deskripsi belum tersedia.';

$has_map = $destinasi
  && is_numeric($destinasi['latitude'])
  && is_numeric($destinasi['longitude']);
$map_lat = $has_map ? (float)$destinasi['latitude'] : null;
$map_lng = $has_map ? (float)$destinasi['longitude'] : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $not_found ? 'Destinasi Tidak Ditemukan - JogjaVerse' : h($destinasi['nama']) . ' - JogjaVerse' ?></title>

  <!-- CSS Libraries -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/leaflet.css">

  <style>
    /* 1. VARIABLE WARNA */
    :root {
        --primary-color: #2D1B20; /* Dark Maroon */
        --secondary-color: #C69C6D; /* Gold */
    }
    
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #FDFBF7;
    }

    h1, h2, h3, .navbar-brand, .font-serif {
        font-family: 'Playfair Display', serif;
    }

    /* 2. NAVBAR (BESAR & SAMA PERSIS EVENT) */
    .navbar {
        transition: all 0.4s ease;
        padding: 1.75rem 0; /* Padding Besar */
    }
    .navbar-brand { font-size: 2rem; }
    .nav-link {
        font-size: 1.4rem;
        margin: 0 15px;
        font-weight: 500;
        position: relative;
    }
    .btn-login-custom {
        padding: 14px 36px;
        font-size: 1.25rem;
        font-weight: 600;
        border-radius: 50px;
    }
    .nav-link::after {
        content: ''; position: absolute; width: 0; height: 3px; bottom: 0px; left: 50%;
        background-color: var(--secondary-color); transition: all 0.3s ease; transform: translateX(-50%);
    }
    .nav-link:hover::after, .nav-link.active::after { width: 80%; }

    /* 3. HEADER BACKGROUND */
    #background {
        background-color: var(--primary-color);
        background-size: cover;
        background-position: center;
        height: 420px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 108px; /* Jarak Navbar Fixed */
    }

    /* 4. CARD STYLE KHUSUS DESTINASI (TETAP DIPERTAHANKAN) */
    .card-destinasi {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
        background: #fff;
    }
    .card-destinasi:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    /* 5. TOMBOL DETAIL ANIMATED */
    .link-gold-animated {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;       
        color: var(--primary-color);         
        text-decoration: none;
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;      
        transition: color 0.3s ease;
        padding-bottom: 2px;
    }
    .link-gold-animated i { font-size: 1rem; transition: transform 0.3s ease; }
    .link-gold-animated:hover { color: var(--secondary-color); }
    .link-gold-animated:hover i { transform: translateX(4px); }
    .link-gold-animated::after {
        content: ''; position: absolute; width: 0; height: 2px; bottom: 0px; left: 50%;
        background-color: var(--secondary-color); transition: all 0.3s ease; transform: translateX(-50%);
    }
    .link-gold-animated:hover::after { width: 100%; }
  </style>
</head>
<body>

<!-- NAVBAR (Layout Besar) -->
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-dark" href="landingpageclean.php">
        Jogja<span style="color: var(--secondary-color);">Verse.</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto text-center">
        <li class="nav-item"><a class="nav-link fw-bold text-dark active" href="destinasiLainnya.php">Destinasi</a></li>
        <li class="nav-item"><a class="nav-link fw-bold text-dark" href="eventLainnya.php">Event&Atraksi</a></li>
        <li class="nav-item"><a class="nav-link fw-bold text-dark" href="kulinerlainnya.php">Kuliner</a></li>
      </ul>
      <?php
        if (!empty($_SESSION['login']) && $_SESSION['login'] === true) {
          $displayName = htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']);
          $avatarPath = '/public/user/img/default_avatar.png';
          echo '<a href="/public/dashboard_user.php" class="d-flex align-items-center text-decoration-none text-dark">';
          echo '<img src="'.$avatarPath.'" alt="Profile" style="width:38px;height:38px;border-radius:50%;object-fit:cover;margin-right:8px;">';
          echo '<span class="fw-medium d-none d-md-inline">'. $displayName .'</span>';
          echo '</a>';
        } else {
          echo '<a href="#login" class="btn btn-outline-dark btn-login-custom">Login</a>';
        }
        ?>
    </div>
  </div>
</nav>

<?php if ($not_found): ?>
  <!-- HEADER BACKGROUND -->
  <section id="background" style="background: linear-gradient(rgba(45, 27, 32, 0.7), rgba(45, 27, 32, 0.7)), url('../img/hero-yogyakarta.jpg');">
    <div class="text-center">
      <h1 class="fw-bold text-white display-4 mb-2">Destinasi Tidak Ditemukan</h1>
      <p class="text-white-50 fs-5">Data destinasi tidak tersedia atau belum dipublikasikan.</p>
    </div>
  </section>

  <section class="py-5">
    <div class="container text-center">
      <p class="text-muted mb-4">Coba kembali ke daftar destinasi untuk melihat pilihan lain.</p>
      <a href="destinasiLainnya.php" class="btn btn-outline-dark rounded-pill px-4">Kembali ke Destinasi</a>
    </div>
  </section>
<?php else: ?>
  <!-- HEADER BACKGROUND -->
  <section id="background" style="background: linear-gradient(rgba(45, 27, 32, 0.65), rgba(45, 27, 32, 0.65)), url('<?= h($hero_image) ?>');">
    <div class="text-center">
      <h1 class="fw-bold text-white display-4 mb-2"><?= h($destinasi['nama']) ?></h1>
      <p class="text-white-50 fs-5"><?= h($kota_display) ?></p>
    </div>
  </section>

  <!-- BREADCRUMB -->
  <section class="py-4">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="landingpageclean.php" class="text-decoration-none text-dark">Home</a></li>
          <li class="breadcrumb-item"><a href="destinasiLainnya.php" class="text-decoration-none text-dark">Destinasi</a></li>
          <li class="breadcrumb-item active" aria-current="page"><?= h($destinasi['nama']) ?></li>
        </ol>
      </nav>
    </div>
  </section>

  <!-- CONTENT SECTION -->
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
                <?= h($kota_display) ?>
              </span>
              <span class="d-inline-flex align-items-center gap-2 bg-white border rounded-pill px-3 py-2 shadow-sm small">
                <i class="bi bi-clock text-warning"></i>
                <?= h($jam_display) ?>
              </span>
              <span class="d-inline-flex align-items-center gap-2 bg-white border rounded-pill px-3 py-2 shadow-sm small">
                <i class="bi bi-ticket-perforated text-warning"></i>
                <?= h($harga_label) ?>
              </span>
              <span class="d-inline-flex align-items-center gap-2 bg-white border rounded-pill px-3 py-2 shadow-sm small">
                <i class="bi bi-telephone text-warning"></i>
                <?= h($kontak_display) ?>
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
              <span class="text-muted small"><?= h($alamat_display) ?></span>
            </div>
            <?php if ($has_map): ?>
              <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow-sm">
                <div id="map"></div>
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
                <li class="mb-2"><i class="bi bi-geo-alt me-2 text-warning"></i><?= h($alamat_display) ?></li>
                <li class="mb-2"><i class="bi bi-clock me-2 text-warning"></i><?= h($jam_display) ?></li>
                <li class="mb-2"><i class="bi bi-ticket-perforated me-2 text-warning"></i><?= h($harga_label) ?></li>
                <li class="mb-2"><i class="bi bi-telephone me-2 text-warning"></i><?= h($kontak_display) ?></li>
                <li><i class="bi bi-tag me-2 text-warning"></i><?= h($kategori_label) ?></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- DESTINASI LAINNYA -->
  <section class="py-5">
    <div class="container">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <h2 class="fw-bold font-serif mb-0">Destinasi Lainnya</h2>
        <a href="destinasiLainnya.php" class="link-gold-animated">Lihat Semua <i class="bi bi-arrow-right"></i></a>
      </div>

      <div class="row g-4">
        <?php if (!empty($related)): ?>
          <?php foreach ($related as $row): 
            $img_url = !empty($row['gambar']) ? $row['gambar'] : 'https://placehold.co/600x400?text=Destinasi';
          ?>
          <div class="col-sm-6 col-lg-4">
            <div class="card card-destinasi h-100">
              <div class="position-relative overflow-hidden" style="height: 220px;">
                <img src="<?= h($img_url) ?>"
                     class="w-100 h-100 object-fit-cover"
                     alt="<?= h($row['nama_destinasi']) ?>"
                     onerror="this.src='https://placehold.co/600x400?text=No+Image'">

                <span class="position-absolute top-0 start-0 m-3 px-3 py-1 bg-white rounded-pill fw-bold shadow-sm text-dark"
                      style="font-size:0.7rem;">
                  <?= h($row['kategori']) ?>
                </span>
              </div>

              <div class="card-body p-4 d-flex flex-column">
                <h5 class="fw-bold mb-2 fs-5 font-serif text-truncate">
                  <?= h($row['nama_destinasi']) ?>
                </h5>
                <p class="text-muted mb-3 flex-grow-1 small" style="line-height:1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  <?= h($row['deskripsi_singkat']) ?>
                </p>
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                  <span class="text-muted small">
                    <i class="bi bi-clock me-1 text-warning"></i>
                    <?= h($row['estimasi_waktu']) ?>
                  </span>
                  <a href="detailDestinasi.php?id=<?= h($row['id']) ?>" class="link-gold-animated">
                     Detail <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center py-4">
            <div class="text-muted">Belum ada destinasi lain di kategori ini.</div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<!-- FOOTER -->
<footer class="footer-custom text-light pt-5" style="background-color: #321B1F;">
  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-4">
        <div class="d-flex align-items-center mb-3">
          <h5 class="mb-0 fw-bold font-serif">Jogja<span style="color: var(--secondary-color);">Verse.</span></h5>
        </div>
        <p class="small text-light opacity-75">
          Sistem pariwisata cerdas terintegrasi untuk mendukung pengelolaan
          destinasi yang efektif dan pengalaman wisatawan yang tak terlupakan.
        </p>
        <ul class="list-unstyled small opacity-75">
          <li class="mb-2"><i class="bi bi-geo-alt me-2 text-warning"></i> Jl. Malioboro No. 1, Yogyakarta</li>
          <li class="mb-2"><i class="bi bi-envelope me-2 text-warning"></i> info@wisatajogja.go.id</li>
          <li><i class="bi bi-telephone me-2 text-warning"></i> (0274) 123456</li>
        </ul>
      </div>
      <div class="col-lg-2 col-6">
        <h6 class="fw-bold mb-3">Wisata</h6>
        <ul class="list-unstyled small opacity-75">
          <li><a href="destinasiLainnya.php" class="text-white text-decoration-none">Destinasi</a></li>
          <li><a href="eventLainnya.php" class="text-white text-decoration-none">Event</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-6">
        <h6 class="fw-bold mb-3">Layanan</h6>
        <ul class="list-unstyled small opacity-75">
            <li><a href="#" class="text-white text-decoration-none">Reservasi Tiket</a></li>
            <li><a href="#" class="text-white text-decoration-none">Panduan</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-6">
        <h6 class="fw-bold mb-3">Tentang</h6>
        <ul class="list-unstyled small opacity-75">
            <li><a href="#" class="text-white text-decoration-none">Tentang Kami</a></li>
            <li><a href="#" class="text-white text-decoration-none">Kebijakan Privasi</a></li>
        </ul>
      </div>
    </div>
    <hr class="border-light opacity-25 my-4">
    <div class="text-center pb-4 small opacity-50">
      &copy; 2025 JogjaVerse. Disponsori oleh Pemerintah Kota Yogyakarta
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!$not_found && $has_map): ?>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const lat = <?= json_encode($map_lat) ?>;
      const lng = <?= json_encode($map_lng) ?>;
      const map = L.map('map').setView([lat, lng], 14);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
      }).addTo(map);
      L.marker([lat, lng]).addTo(map);
    });
  </script>
<?php endif; ?>
</body>
</html>
