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

if (empty($_SESSION['login']) || $_SESSION['login'] !== true) {
  header("Location: /public/login.php");
  exit();
}

$id_reservasi = filter_input(INPUT_GET, 'id_reservasi', FILTER_VALIDATE_INT);
if (!$id_reservasi) {
  header("Location: eventLainnya.php");
  exit();
}

$id_pengguna = (int)($_SESSION['id_pengguna'] ?? 0);

$stmtCol = $koneksi->prepare("
  SELECT 1
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'pembayaran'
    AND column_name = 'kode_transaksi'
  LIMIT 1
");
$stmtCol->execute();
$hasKodeTransaksi = (bool)$stmtCol->get_result()->fetch_assoc();
$stmtCol->close();

$selectKode = $hasKodeTransaksi ? "p.kode_transaksi" : "NULL AS kode_transaksi";
$stmt = $koneksi->prepare("
  SELECT r.*, e.judul, e.lokasi, e.kuota,
         p.id_pembayaran, p.status AS status_pembayaran, p.jumlah AS jumlah_bayar,
         p.metode, p.dibayar_pada, $selectKode
  FROM reservasi_event r
  JOIN event e ON r.id_event = e.id_event
  JOIN pembayaran p ON p.id_reservasi = r.id_reservasi
  WHERE r.id_reservasi = ? AND r.id_pengguna = ?
  LIMIT 1
");
$stmt->bind_param("ii", $id_reservasi, $id_pengguna);
$stmt->execute();
$res = $stmt->get_result();
$data = $res ? $res->fetch_assoc() : null;
$stmt->close();

$not_found = !$data;
if ($not_found) {
  http_response_code(404);
}

$expired = false;
if (!$not_found && $data['status'] === 'PENDING' && !empty($data['kedaluwarsa_pada'])) {
  $expire_ts = strtotime($data['kedaluwarsa_pada']);
  if ($expire_ts && $expire_ts < time() && $data['status_pembayaran'] !== 'SUDAH_BAYAR') {
    $expired = true;
    $stmtExp = $koneksi->prepare("UPDATE reservasi_event SET status = 'KADALUARSA' WHERE id_reservasi = ? AND status = 'PENDING'");
    $stmtExp->bind_param("i", $id_reservasi);
    $stmtExp->execute();
    $stmtExp->close();

    $stmtPay = $koneksi->prepare("UPDATE pembayaran SET status = 'GAGAL' WHERE id_reservasi = ? AND status = 'BELUM_BAYAR'");
    $stmtPay->bind_param("i", $id_reservasi);
    $stmtPay->execute();
    $stmtPay->close();

    $data['status'] = 'KADALUARSA';
    $data['status_pembayaran'] = 'GAGAL';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$not_found && !$expired) {
  if ($data['status_pembayaran'] !== 'SUDAH_BAYAR') {
    $koneksi->begin_transaction();
    try {
      $stmtBayar = $koneksi->prepare("UPDATE pembayaran SET status = 'SUDAH_BAYAR', dibayar_pada = NOW() WHERE id_reservasi = ?");
      $stmtBayar->bind_param("i", $id_reservasi);
      $stmtBayar->execute();
      $stmtBayar->close();

      $stmtRes = $koneksi->prepare("UPDATE reservasi_event SET status = 'DIKONFIRMASI' WHERE id_reservasi = ?");
      $stmtRes->bind_param("i", $id_reservasi);
      $stmtRes->execute();
      $stmtRes->close();

      if (isset($data['kuota']) && is_numeric($data['kuota']) && (int)$data['kuota'] > 0) {
        $stmtKuota = $koneksi->prepare("UPDATE event SET kuota = GREATEST(kuota - ?, 0) WHERE id_event = ?");
        $stmtKuota->bind_param("ii", $data['jumlah_tiket'], $data['id_event']);
        $stmtKuota->execute();
        $stmtKuota->close();
      }

      $koneksi->commit();
      header("Location: suksesPembayaranEvent.php?id_reservasi=" . $id_reservasi);
      exit();
    } catch (Throwable $e) {
      $koneksi->rollback();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pembayaran Event - JogjaVerse</title>

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
        background: rgba(45, 27, 32, 0.95);
    }
    .payment-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }
  </style>
</head>
<body>

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
    </div>
  </div>
</nav>

<section class="pt-5 mt-5 pb-5">
  <div class="container">
    <div class="mb-4">
      <h2 class="fw-bold font-serif">Pembayaran Event</h2>
      <p class="text-muted">Selesaikan pembayaran sebelum waktu kedaluwarsa.</p>
    </div>

    <?php if ($not_found): ?>
      <div class="alert alert-warning">Data reservasi tidak ditemukan.</div>
      <a href="eventLainnya.php" class="btn btn-outline-dark rounded-pill px-4">Kembali ke Event</a>
    <?php else: ?>
      <?php if ($expired): ?>
        <div class="alert alert-danger">Pembayaran kedaluwarsa. Silakan lakukan reservasi ulang.</div>
        <a href="eventLainnya.php" class="btn btn-outline-dark rounded-pill px-4">Kembali ke Event</a>
      <?php else: ?>
        <div class="row g-4">
          <div class="col-lg-7">
            <div class="card payment-card p-4">
              <h4 class="fw-bold font-serif mb-3"><?= h($data['judul']) ?></h4>
              <p class="text-muted mb-1">Jumlah tiket: <b><?= h($data['jumlah_tiket']) ?></b></p>
              <p class="text-muted mb-1">Total harga: <b><?= format_harga($data['total_harga']) ?></b></p>
              <p class="text-muted mb-3">Kedaluwarsa pada: <b><?= h($data['kedaluwarsa_pada'] ?? '-') ?></b></p>

              <form method="POST">
                <div class="mb-3">
                  <label class="form-label">Metode Pembayaran (Simulasi)</label>
                  <div class="d-grid gap-2">
                    <label class="border rounded-3 p-3 d-flex align-items-center gap-2">
                      <input type="radio" name="metode" checked>
                      QRIS
                    </label>
                    <label class="border rounded-3 p-3 d-flex align-items-center gap-2">
                      <input type="radio" name="metode">
                      Virtual Account
                    </label>
                    <label class="border rounded-3 p-3 d-flex align-items-center gap-2">
                      <input type="radio" name="metode">
                      Kartu Kredit
                    </label>
                  </div>
                </div>
                <button class="btn btn-gold px-4" type="submit">Bayar Sekarang (Simulasi)</button>
              </form>
            </div>
          </div>

          <div class="col-lg-5">
            <div class="card payment-card p-4">
              <h5 class="fw-bold font-serif mb-3">Detail Pembayaran</h5>
              <div class="d-flex justify-content-between mb-2">
                <span>Kode Transaksi</span>
                <span><?= h($data['kode_transaksi'] ?? '-') ?></span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>Status Reservasi</span>
                <span><?= h($data['status']) ?></span>
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span>Status Pembayaran</span>
                <span><?= h($data['status_pembayaran']) ?></span>
              </div>
              <div class="d-flex justify-content-between">
                <span>Total</span>
                <span class="fw-bold"><?= format_harga($data['total_harga']) ?></span>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

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
