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
if (!$id) {
  $id = filter_input(INPUT_GET, 'id_destinasi', FILTER_VALIDATE_INT);
}
$destinasi = null;
$ulasan_error = '';
$ulasan_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_ulasan') {
  if (empty($_SESSION['login']) || $_SESSION['login'] !== true) {
    $ulasan_error = 'Silakan login untuk menulis ulasan.';
  } elseif (!$id) {
    $ulasan_error = 'Destinasi tidak ditemukan.';
  } else {
    $rating = (int)($_POST['rating'] ?? 0);
    $komentar = trim((string)($_POST['komentar'] ?? ''));
    if ($rating < 1 || $rating > 5) {
      $ulasan_error = 'Rating harus diisi 1 sampai 5.';
    } elseif (strlen($komentar) > 500) {
      $ulasan_error = 'Komentar maksimal 500 karakter.';
    } else {
      $id_pengguna = (int)($_SESSION['id_pengguna'] ?? 0);
      if ($id_pengguna <= 0) {
        $ulasan_error = 'Akun tidak valid.';
      } else {
        $jenis_target = 'destinasi';
        $komentar = $komentar === '' ? null : $komentar;
        $stmtUlas = $koneksi->prepare("
          INSERT INTO ulasan (id_pengguna, jenis_target, id_target, rating, komentar, status, dibuat_pada)
          VALUES (?, ?, ?, ?, ?, 'sembunyi', NOW())
        ");
        $stmtUlas->bind_param("isiis", $id_pengguna, $jenis_target, $id, $rating, $komentar);
        if ($stmtUlas->execute()) {
          $ulasan_success = 'Ulasan berhasil dikirim dan menunggu moderasi.';
        } else {
          $ulasan_error = 'Gagal mengirim ulasan.';
        }
        $stmtUlas->close();
      }
    }
  }
}

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

$ulasan_list = [];
$ulasan_avg = null;
$ulasan_total = 0;
if (!$not_found) {
  $jenis_target = 'destinasi';
  $stmtU = $koneksi->prepare("
    SELECT u.rating, u.komentar, u.dibuat_pada, p.nama_lengkap
    FROM ulasan u
    LEFT JOIN pengguna p ON u.id_pengguna = p.id_pengguna
    WHERE u.jenis_target = ? AND u.id_target = ? AND u.status = 'tampil'
    ORDER BY u.dibuat_pada DESC
  ");
  $stmtU->bind_param("si", $jenis_target, $id);
  $stmtU->execute();
  $resU = $stmtU->get_result();
  if ($resU) {
    while ($row = $resU->fetch_assoc()) {
      $ulasan_list[] = $row;
    }
  }
  $stmtU->close();

  $stmtAvg = $koneksi->prepare("
    SELECT AVG(rating) as rata, COUNT(*) as total
    FROM ulasan
    WHERE jenis_target = ? AND id_target = ? AND status = 'tampil'
  ");
  $stmtAvg->bind_param("si", $jenis_target, $id);
  $stmtAvg->execute();
  $avgRow = $stmtAvg->get_result()->fetch_assoc();
  $ulasan_avg = $avgRow['rata'] ?? null;
  $ulasan_total = (int)($avgRow['total'] ?? 0);
  $stmtAvg->close();
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

$detailGallery = [];
if (!$not_found) {
  $stmtGal = $koneksi->prepare("SELECT gambar_url, keterangan FROM galeri WHERE jenis_target = 'destinasi' AND id_target = ? ORDER BY urutan ASC, id_galeri ASC");
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

$lat_raw = $destinasi['latitude'] ?? '';
$lng_raw = $destinasi['longitude'] ?? '';
$has_map = $destinasi
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
  <title><?= $not_found ? 'Destinasi Tidak Ditemukan - JogjaVerse' : h($destinasi['nama']) . ' - JogjaVerse' ?></title>

  <!-- CSS Libraries -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style2.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
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
    /* 3. HERO & NAVBAR */
    .navbar {
        background: transparent;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1050;
    }
    .hero-destinasi {
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
    #mapDestinasi {
        width: 100%;
        height: 280px;
        border-radius: 16px;
        position: relative;
        z-index: 1;
    }
    .leaflet-container {
        z-index: 1;
    }
  </style>

</head>
<body>

<!-- 2. STRUKTUR HTML -->
<?php include __DIR__ . '/includes/navbar.php'; ?>


<?php if ($not_found): ?>
  <!-- HERO DESTINASI -->
  <div class="hero-destinasi" style="background-image: url('../img/hero-yogyakarta.jpg');">
    <div class="hero-overlay">
      <div class="text-center">
        <h1 class="fw-bold display-4 mb-2">Destinasi Tidak Ditemukan</h1>
        <p class="text-white-50 fs-5">Data destinasi tidak tersedia atau belum dipublikasikan.</p>
      </div>
    </div>
  </div>

  <section class="py-5">
    <div class="container text-center">
      <p class="text-muted mb-4">Coba kembali ke daftar destinasi untuk melihat pilihan lain.</p>
      <a href="destinasiLainnya.php" class="btn btn-outline-dark rounded-pill px-4">Kembali ke Destinasi</a>
    </div>
  </section>
<?php else: ?>
  <!-- HERO DESTINASI -->
  <div class="hero-destinasi" style="background-image: url('<?= h($hero_image) ?>');">
    <div class="hero-overlay">
      <div class="text-center">
        <h1 class="fw-bold display-4 mb-2"><?= h($destinasi['nama']) ?></h1>
        <p class="text-white-50 fs-5"><?= h($kota_display) ?></p>
      </div>
    </div>
  </div>

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
              <div class="rounded-4 overflow-hidden shadow-sm">
                <div id="mapDestinasi"></div>
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

          <section class="detail-gambar">
            <h5 class="fw-bold font-serif mb-3">Detail Gambar</h5>
            <?php if (!empty($detailGallery)): ?>
              <?php foreach ($detailGallery as $img): ?>
                <img src="<?= h($img['gambar_url']) ?>" alt="<?= h($img['keterangan'] ?? $destinasi['nama']) ?>" loading="lazy">
              <?php endforeach; ?>
            <?php else: ?>
              <small class="text-muted">Belum ada gambar detail.</small>
            <?php endif; ?>
          </section>
        </div>
      </div>
    </div>
  </section>

  <!-- ULASAN -->
  <section class="py-5">
    <div class="container">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <h2 class="fw-bold font-serif mb-0">Ulasan Pengunjung</h2>
        <?php if ($ulasan_total > 0 && $ulasan_avg !== null): ?>
          <span class="text-muted small">Rata-rata: <?= h(number_format((float)$ulasan_avg, 1)) ?>/5 (<?= h($ulasan_total) ?> ulasan)</span>
        <?php endif; ?>
      </div>

      <?php if ($ulasan_success): ?>
        <div class="alert alert-success"><?= h($ulasan_success) ?></div>
      <?php endif; ?>
      <?php if ($ulasan_error): ?>
        <div class="alert alert-danger"><?= h($ulasan_error) ?></div>
      <?php endif; ?>

      <?php if (!empty($_SESSION['login']) && $_SESSION['login'] === true): ?>
        <div class="bg-white rounded-4 p-4 shadow-sm mb-4">
          <h5 class="fw-bold font-serif mb-3">Tulis Ulasan</h5>
          <form method="POST">
            <input type="hidden" name="action" value="submit_ulasan">
            <div class="row g-3">
              <div class="col-md-3">
                <label class="form-label small fw-bold">Rating</label>
                <select name="rating" class="form-select" required>
                  <option value="">Pilih</option>
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>"><?= $i ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="col-md-9">
                <label class="form-label small fw-bold">Komentar</label>
                <textarea name="komentar" class="form-control" rows="3" maxlength="500" placeholder="Tulis komentar (maks 500 karakter)"></textarea>
              </div>
            </div>
            <button class="btn btn-gold mt-3" type="submit">Kirim Ulasan</button>
          </form>
        </div>
      <?php else: ?>
        <div class="alert alert-warning">
          Silakan <a href="/public/login.php" class="text-decoration-none">login</a> untuk menulis ulasan.
        </div>
      <?php endif; ?>

      <?php if (empty($ulasan_list)): ?>
        <div class="text-muted">Belum ada ulasan yang ditampilkan.</div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($ulasan_list as $u): ?>
            <div class="col-md-6">
              <div class="bg-white rounded-4 p-4 shadow-sm h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div class="fw-bold"><?= h($u['nama_lengkap'] ?? 'Pengguna') ?></div>
                  <small class="text-muted"><?= h($u['dibuat_pada'] ?? '-') ?></small>
                </div>
                <div class="text-warning small mb-2">Rating: <?= h($u['rating'] ?? '-') ?>/5</div>
                <div class="text-muted"><?= nl2br(h($u['komentar'] ?? '')) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
<?php if (!$not_found && $has_map): ?>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const lat = parseFloat(<?= json_encode($map_lat) ?>);
      const lng = parseFloat(<?= json_encode($map_lng) ?>);
      const mapEl = document.getElementById('mapDestinasi');
      if (!mapEl || Number.isNaN(lat) || Number.isNaN(lng)) {
        return;
      }
      if (window._mapDestinasi) {
        return;
      }
      window._mapDestinasi = L.map('mapDestinasi').setView([lat, lng], 14);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
      }).addTo(window._mapDestinasi);
      L.marker([lat, lng]).addTo(window._mapDestinasi);
    });
  </script>
<?php endif; ?>

</body>
</html>




