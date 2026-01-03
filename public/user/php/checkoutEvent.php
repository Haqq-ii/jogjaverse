<?php
require_once __DIR__ . "/../../../config/koneksi.php";
date_default_timezone_set('Asia/Jakarta');
 
$app_debug = false;
if (defined('APP_DEBUG')) {
  $app_debug = (bool)APP_DEBUG;
} else {
  $env_debug = getenv('APP_DEBUG');
  if ($env_debug !== false) {
    $app_debug = in_array(strtolower((string)$env_debug), ['1', 'true', 'yes', 'on'], true);
  }
}
if ($app_debug) {
  mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
} else {
  mysqli_report(MYSQLI_REPORT_OFF);
}
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

function log_error($msg, array $context = []) {
  $logDir = __DIR__ . "/../../../storage/logs";
  if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
  }
  $logFile = $logDir . "/reservasi.log";
  $payload = [
    'time' => date('Y-m-d H:i:s'),
    'message' => $msg,
    'context' => $context,
  ];
  file_put_contents($logFile, json_encode($payload, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND);
}

$session_user_id = 0;
if (isset($_SESSION['user']['id_pengguna'])) {
  $session_user_id = (int)$_SESSION['user']['id_pengguna'];
} elseif (isset($_SESSION['id_pengguna'])) {
  $session_user_id = (int)$_SESSION['id_pengguna'];
} elseif (isset($_SESSION['user_id'])) {
  $session_user_id = (int)$_SESSION['user_id'];
}

if (empty($_SESSION['login']) || $_SESSION['login'] !== true || $session_user_id <= 0) {
  header("Location: /public/login.php");
  exit();
}

$id_event = filter_input(INPUT_GET, 'id_event', FILTER_VALIDATE_INT);
if (!$id_event) {
  header("Location: eventLainnya.php");
  exit();
}

$event = null;
$stmt = $koneksi->prepare("
  SELECT e.*, k.nama AS kategori
  FROM event e
  LEFT JOIN kategori k ON e.id_kategori = k.id_kategori AND k.tipe = 'event'
  WHERE e.id_event = ? AND e.status = 'publish'
  LIMIT 1
");
$stmt->bind_param("i", $id_event);
$stmt->execute();
$res = $stmt->get_result();
if ($res) {
  $event = $res->fetch_assoc();
}
$stmt->close();

if (!$event) {
  http_response_code(404);
  $not_found = true;
} else {
  $not_found = false;
}

$kuota_tersisa = null;
if (!$not_found && isset($event['kuota']) && is_numeric($event['kuota']) && (int)$event['kuota'] > 0) {
  $stmtKuota = $koneksi->prepare("
    SELECT COALESCE(SUM(jumlah_tiket), 0) AS terpakai
    FROM reservasi_event
    WHERE id_event = ?
      AND status IN ('PENDING','DIKONFIRMASI')
      AND (status != 'PENDING' OR kedaluwarsa_pada IS NULL OR kedaluwarsa_pada > NOW())
  ");
  $stmtKuota->bind_param("i", $id_event);
  $stmtKuota->execute();
  $rowKuota = $stmtKuota->get_result()->fetch_assoc();
  $stmtKuota->close();

  $terpakai = (int)($rowKuota['terpakai'] ?? 0);
  $kuota_tersisa = max(0, (int)$event['kuota'] - $terpakai);
}

$errors = [];
$success = "";
$jumlah_tiket = (int)($_POST['jumlah_tiket'] ?? 1);
if ($jumlah_tiket < 1) {
  $jumlah_tiket = 1;
}
$catatan = trim($_POST['catatan'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$not_found) {
  if ($kuota_tersisa !== null && $jumlah_tiket > $kuota_tersisa) {
    $errors[] = "Jumlah tiket melebihi kuota tersisa.";
  }

  if (empty($errors)) {
    $harga_satuan = (int)($event['harga'] ?? 0);
    $total_harga = $harga_satuan * $jumlah_tiket;
    $id_pengguna = $session_user_id;

    $stmtUser = $koneksi->prepare("SELECT 1 FROM pengguna WHERE id_pengguna = ? LIMIT 1");
    $stmtUser->bind_param("i", $id_pengguna);
    $stmtUser->execute();
    $userExists = $stmtUser->get_result()->fetch_assoc();
    $stmtUser->close();

    if (!$userExists) {
      $errors[] = "Akun tidak ditemukan. Silakan login ulang.";
      log_error("Akun tidak ditemukan saat checkout.", [
        'id_pengguna' => $id_pengguna,
        'id_event' => $id_event,
      ]);
    } else {
      $koneksi->begin_transaction();
      try {
        $status_reservasi = 'PENDING';
        $kedaluwarsa_pada = date('Y-m-d H:i:s', time() + (15 * 60));

        $stmtRes = $koneksi->prepare("
          INSERT INTO reservasi_event
            (id_event, id_pengguna, jumlah_tiket, harga_satuan, total_harga, status, kedaluwarsa_pada, catatan)
          VALUES
            (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtRes->bind_param("iiiiisss", $id_event, $id_pengguna, $jumlah_tiket, $harga_satuan, $total_harga, $status_reservasi, $kedaluwarsa_pada, $catatan);
        if (!$stmtRes->execute()) {
          log_error("Gagal insert reservasi_event.", [
            'stmt_error' => $stmtRes->error,
            'db_error' => $koneksi->error,
            'id_event' => $id_event,
            'id_pengguna' => $id_pengguna,
            'jumlah_tiket' => $jumlah_tiket,
            'harga_satuan' => $harga_satuan,
            'total_harga' => $total_harga,
          ]);
          throw new Exception("Insert reservasi_event gagal.");
        }
        $id_reservasi = $koneksi->insert_id;
        $stmtRes->close();

        $kode_transaksi = 'PAY-' . $id_reservasi . '-' . strtoupper(bin2hex(random_bytes(3)));
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

        if ($hasKodeTransaksi) {
          $stmtBayar = $koneksi->prepare("
            INSERT INTO pembayaran (id_reservasi, metode, jumlah, status, kode_transaksi)
            VALUES (?, 'gateway', ?, 'BELUM_BAYAR', ?)
          ");
          $stmtBayar->bind_param("iis", $id_reservasi, $total_harga, $kode_transaksi);
        } else {
          $stmtBayar = $koneksi->prepare("
            INSERT INTO pembayaran (id_reservasi, metode, jumlah, status)
            VALUES (?, 'gateway', ?, 'BELUM_BAYAR')
          ");
          $stmtBayar->bind_param("ii", $id_reservasi, $total_harga);
        }
        if (!$stmtBayar->execute()) {
          log_error("Gagal insert pembayaran.", [
            'stmt_error' => $stmtBayar->error,
            'db_error' => $koneksi->error,
            'id_reservasi' => $id_reservasi,
            'total_harga' => $total_harga,
          ]);
          throw new Exception("Insert pembayaran gagal.");
        }
        $stmtBayar->close();

        $stmtTiket = $koneksi->prepare("
          INSERT INTO tiket_event (id_reservasi, kode_tiket, sudah_dipakai)
          VALUES (?, ?, 0)
        ");
        for ($i = 0; $i < $jumlah_tiket; $i++) {
          $tries = 0;
          $inserted = false;
          while ($tries < 3 && !$inserted) {
            $kode_tiket = 'EVT-' . $id_reservasi . '-' . strtoupper(bin2hex(random_bytes(3))) . '-' . ($i + 1);
            $stmtTiket->bind_param("is", $id_reservasi, $kode_tiket);
            $ok = $stmtTiket->execute();
            if ($ok) {
              $inserted = true;
            } else {
              if ((int)$stmtTiket->errno !== 1062) {
                log_error("Gagal insert tiket_event.", [
                  'stmt_error' => $stmtTiket->error,
                  'db_error' => $koneksi->error,
                  'id_reservasi' => $id_reservasi,
                  'kode_tiket' => $kode_tiket,
                ]);
                break;
              }
              $tries++;
            }
          }
          if (!$inserted) {
            log_error("Gagal membuat kode tiket.", [
              'id_reservasi' => $id_reservasi,
              'index' => $i + 1,
              'stmt_error' => $stmtTiket->error,
              'db_error' => $koneksi->error,
            ]);
            throw new Exception("Gagal membuat kode tiket.");
          }
        }
        $stmtTiket->close();

        $koneksi->commit();
        header("Location: pembayaranEvent.php?id_reservasi=" . $id_reservasi);
        exit();
      } catch (Throwable $e) {
        $koneksi->rollback();
        log_error("Checkout gagal.", [
          'error' => $e->getMessage(),
          'db_error' => $koneksi->error,
          'id_event' => $id_event,
          'id_pengguna' => $id_pengguna,
          'jumlah_tiket' => $jumlah_tiket,
          'harga_satuan' => $harga_satuan,
          'total_harga' => $total_harga,
        ]);
        $errors[] = $app_debug ? "Gagal menyimpan reservasi: " . $e->getMessage() : "Gagal menyimpan reservasi. Coba lagi.";
      }
    }
  }
}

$harga_satuan_display = $event ? format_harga($event['harga'] ?? null) : 'Belum tersedia';
$total_estimasi = $event ? ((int)($event['harga'] ?? 0) * $jumlah_tiket) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout Event - JogjaVerse</title>

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
    .checkout-card {
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

<section class="pt-5 mt-5 pb-5">
  <div class="container">
    <div class="mb-4">
      <h2 class="fw-bold font-serif">Checkout Event</h2>
      <p class="text-muted">Lengkapi data reservasi tiket sebelum lanjut ke pembayaran.</p>
    </div>

    <?php if ($not_found): ?>
      <div class="alert alert-warning">Data event tidak ditemukan atau belum dipublikasikan.</div>
      <a href="eventLainnya.php" class="btn btn-outline-dark rounded-pill px-4">Kembali ke Event</a>
    <?php else: ?>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $err): ?>
            <div><?= h($err) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="row g-4">
        <div class="col-lg-7">
          <div class="card checkout-card p-4">
            <h4 class="fw-bold font-serif mb-3"><?= h($event['judul']) ?></h4>
            <div class="text-muted mb-2"><i class="bi bi-geo-alt text-warning me-2"></i><?= h($event['lokasi'] ?? 'Yogyakarta') ?></div>
            <div class="text-muted mb-4"><i class="bi bi-calendar-event text-warning me-2"></i><?= h($event['mulai_pada'] ?? '') ?></div>

            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Jumlah Tiket</label>
                <input type="number" class="form-control" name="jumlah_tiket" min="1"
                  value="<?= h($jumlah_tiket) ?>"
                  <?= $kuota_tersisa !== null ? 'max="' . h($kuota_tersisa) . '"' : '' ?>
                  <?= ($kuota_tersisa !== null && $kuota_tersisa <= 0) ? 'disabled' : '' ?>
                  required>
                <?php if ($kuota_tersisa !== null): ?>
                  <small class="text-muted">Kuota tersisa: <?= h($kuota_tersisa) ?> tiket</small>
                <?php endif; ?>
              </div>
              <div class="mb-3">
                <label class="form-label">Catatan (opsional)</label>
                <input type="text" class="form-control" name="catatan" value="<?= h($catatan) ?>">
              </div>
              <button class="btn btn-gold px-4" type="submit" <?= ($kuota_tersisa !== null && $kuota_tersisa <= 0) ? 'disabled' : '' ?>>
                Lanjut Pembayaran
              </button>
              <a href="detailEvent.php?id=<?= h($id_event) ?>" class="btn btn-outline-dark ms-2">Batal</a>
            </form>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="card checkout-card p-4">
            <h5 class="fw-bold font-serif mb-3">Ringkasan Harga</h5>
            <div class="d-flex justify-content-between mb-2">
              <span>Harga Satuan</span>
              <span><?= h($harga_satuan_display) ?></span>
            </div>
            <div class="d-flex justify-content-between mb-3">
              <span>Jumlah Tiket</span>
              <span><?= h($jumlah_tiket) ?></span>
            </div>
            <hr>
            <div class="d-flex justify-content-between fw-bold">
              <span>Total Harga</span>
              <span><?= format_harga($total_estimasi) ?></span>
            </div>
          </div>
        </div>
      </div>
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
