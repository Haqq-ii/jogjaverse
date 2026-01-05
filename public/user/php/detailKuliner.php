<?php
require_once __DIR__ . "/../../../config/koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function h($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function table_exists(mysqli $koneksi, string $table): bool {
  $stmt = $koneksi->prepare("
    SELECT COUNT(*) as total
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = ?
  ");
  if (!$stmt) {
    return false;
  }
  $stmt->bind_param("s", $table);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return ((int)($row['total'] ?? 0)) > 0;
}

function table_has_column(mysqli $koneksi, string $table, string $column): bool {
  $stmt = $koneksi->prepare("
    SELECT COUNT(*) as total
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = ?
      AND column_name = ?
  ");
  if (!$stmt) {
    return false;
  }
  $stmt->bind_param("ss", $table, $column);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return ((int)($row['total'] ?? 0)) > 0;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
  $id = filter_input(INPUT_GET, 'id_kuliner', FILTER_VALIDATE_INT);
}
$kuliner = null;
$ulasan_error = '';
$ulasan_success = '';
$hasKulinerKategoriTable = table_exists($koneksi, 'kuliner_kategori');
$hasKulinerKategoriCol = table_has_column($koneksi, 'kuliner', 'kuliner_kategori_id');
$kategoriSelect = 'NULL AS kategori';
$kategoriJoin = '';
if ($hasKulinerKategoriTable && $hasKulinerKategoriCol) {
  $kategoriSelect = 'kk.nama AS kategori';
  $kategoriJoin = 'LEFT JOIN kuliner_kategori kk ON kln.kuliner_kategori_id = kk.id';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_ulasan') {
  if (empty($_SESSION['login']) || $_SESSION['login'] !== true) {
    $ulasan_error = 'Silakan login untuk menulis ulasan.';
  } elseif (!$id) {
    $ulasan_error = 'Kuliner tidak ditemukan.';
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
        $jenis_target = 'kuliner';
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
    SELECT kln.*, $kategoriSelect
    FROM kuliner kln
    $kategoriJoin
    WHERE kln.id_kuliner = ? AND kln.status = 'publish'
    LIMIT 1
  ");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res) {
    $kuliner = $res->fetch_assoc();
  }
  $stmt->close();
}

$not_found = !$id || !$kuliner;
if ($not_found) {
  http_response_code(404);
}

$ulasan_list = [];
$ulasan_avg = null;
$ulasan_total = 0;
if (!$not_found) {
  $jenis_target = 'kuliner';
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

$detailGallery = [];
if (!$not_found) {
  $stmtGal = $koneksi->prepare("SELECT gambar_url, keterangan FROM galeri WHERE jenis_target = 'kuliner' AND id_target = ? ORDER BY urutan ASC, id_galeri ASC");
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
if (!$not_found && !empty($kuliner['gambar_sampul_url'])) {
  $hero_image = $kuliner['gambar_sampul_url'];
}

$kategori_label = $kuliner ? ($kuliner['kategori'] ?? 'Kuliner') : 'Kuliner';

$deskripsi_display = $kuliner && trim((string)($kuliner['deskripsi'] ?? '')) !== ''
  ? $kuliner['deskripsi']
  : 'Belum ada deskripsi.';

$desc_plain = trim(strip_tags((string)$deskripsi_display));
$is_long_desc = strlen($desc_plain) > 600;
$detail_class = $is_long_desc ? 'is-long' : 'is-short';

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $not_found ? 'Kuliner Tidak Ditemukan - JogjaVerse' : h($kuliner['nama']) . ' - JogjaVerse' ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style2.css">

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
    .hero-kuliner {
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
    .detail-wrapper {
        display: flex;
        gap: 24px;
        align-items: stretch;
    }
    .detail-col {
        flex: 1 1 0;
        min-width: 0;
    }
    .detail-card {
        height: 100%;
    }
    .media-column {
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .media-main {
        flex: 0 0 auto;
        min-height: 320px;
    }
    .detail-wrapper.is-long .media-main {
        min-height: 420px;
    }
    .media-main img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 16px;
        display: block;
    }
    .media-gallery {
        margin-top: 16px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        align-content: start;
    }
    .detail-wrapper.is-long .media-gallery {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        flex: 1 1 auto;
    }
    .detail-wrapper.is-short .media-gallery {
        max-height: 260px;
        overflow: auto;
        flex: 0 0 auto;
    }
    .media-gallery img {
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        border-radius: 12px;
        cursor: pointer;
        display: block;
    }
    .media-gallery.is-empty {
        display: block;
    }
    @media (max-width: 991.98px) {
        .detail-wrapper {
            flex-direction: column;
        }
        .detail-card {
            height: auto;
        }
        .detail-wrapper.is-short .media-gallery {
            max-height: none;
        }
    }
  </style>
</head>
<body class="navbar-overlay">

<!-- 2. STRUKTUR HTML -->
<?php include __DIR__ . '/includes/navbar.php'; ?>

<?php if ($not_found): ?>
  <div class="hero-kuliner" style="background-image: url('../img/hero-yogyakarta.jpg');">
    <div class="hero-overlay">
      <div class="text-center">
        <h1 class="fw-bold display-4 mb-2">Kuliner Tidak Ditemukan</h1>
        <p class="text-white-50 fs-5">Data kuliner tidak tersedia atau belum dipublikasikan.</p>
      </div>
    </div>
  </div>

  <section class="py-5">
    <div class="container text-center">
      <p class="text-muted mb-4">Coba kembali ke daftar kuliner untuk melihat pilihan lain.</p>
      <a href="kulinerLainnya.php" class="btn btn-outline-dark rounded-pill px-4">Kembali ke Kuliner</a>
    </div>
  </section>
<?php else: ?>
  <div class="hero-kuliner" style="background-image: url('<?= h($hero_image) ?>');">
    <div class="hero-overlay">
      <div class="text-center">
        <h1 class="fw-bold display-4 mb-2"><?= h($kuliner['nama']) ?></h1>
      </div>
    </div>
  </div>

  <section class="py-4">
    <div class="container">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="landingpageclean.php" class="text-decoration-none text-dark">Home</a></li>
          <li class="breadcrumb-item"><a href="kulinerLainnya.php" class="text-decoration-none text-dark">Kuliner</a></li>
          <li class="breadcrumb-item active" aria-current="page"><?= h($kuliner['nama']) ?></li>
        </ol>
      </nav>
    </div>
  </section>

  <section class="pb-5">
    <div class="container">
      <div class="mb-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="d-inline-flex align-items-center gap-2 bg-white border rounded-pill px-3 py-2 shadow-sm small">
            <i class="bi bi-tag text-warning"></i>
            <?= h($kategori_label) ?>
          </span>
        </div>
      </div>

      <div class="detail-wrapper <?= $detail_class ?>">
        <div class="detail-col desc-column">
          <div class="detail-card bg-white rounded-4 p-4 shadow-sm">
            <div class="text-muted" style="line-height:1.8;">
              <?= nl2br(h($deskripsi_display)) ?>
            </div>
          </div>
        </div>

        <div class="detail-col">
          <div class="detail-card media-column bg-white rounded-4 p-4 shadow-sm">
            <div class="media-main">
              <img src="<?= h($hero_image) ?>" alt="<?= h($kuliner['nama']) ?>">
            </div>
            <?php if (!empty($detailGallery)): ?>
              <div class="media-gallery">
                <?php foreach ($detailGallery as $img): ?>
                  <img src="<?= h($img['gambar_url']) ?>" alt="<?= h($img['keterangan'] ?? $kuliner['nama']) ?>" loading="lazy">
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="media-gallery is-empty">
                <small class="text-muted">Belum ada gambar tambahan.</small>
              </div>
            <?php endif; ?>
          </div>
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
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
