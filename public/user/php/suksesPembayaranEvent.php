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
  SELECT r.*, e.judul, e.id_event, p.status AS status_pembayaran, $selectKode
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

$tickets = [];
if (!$not_found) {
  $stmtTiket = $koneksi->prepare("SELECT kode_tiket, sudah_dipakai, dipakai_pada FROM tiket_event WHERE id_reservasi = ? ORDER BY id_tiket ASC");
  $stmtTiket->bind_param("i", $id_reservasi);
  $stmtTiket->execute();
  $resTiket = $stmtTiket->get_result();
  if ($resTiket) {
    while ($row = $resTiket->fetch_assoc()) {
      $tickets[] = $row;
    }
  }
  $stmtTiket->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sukses Pembayaran - JogjaVerse</title>

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
    .success-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }
    .ticket-code {
        border: 1px dashed #C69C6D;
        border-radius: 10px;
        padding: 10px 12px;
        font-family: monospace;
        background: #fff;
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<section class="pt-5 mt-5 pb-5">
  <div class="container">
    <?php if ($not_found): ?>
      <div class="alert alert-warning">Data reservasi tidak ditemukan.</div>
      <a href="eventLainnya.php" class="btn btn-outline-dark rounded-pill px-4">Kembali ke Event</a>
    <?php else: ?>
      <div class="card success-card p-4 mb-4">
        <h2 class="fw-bold font-serif mb-2">Pembayaran Berhasil</h2>
        <p class="text-muted mb-3">Reservasi tiket event Anda sudah dikonfirmasi.</p>
        <div class="d-flex flex-wrap gap-3">
          <div>
            <div class="text-muted small">Event</div>
            <div class="fw-bold"><?= h($data['judul']) ?></div>
          </div>
          <div>
            <div class="text-muted small">Total Bayar</div>
            <div class="fw-bold"><?= format_harga($data['total_harga']) ?></div>
          </div>
          <div>
            <div class="text-muted small">Kode Transaksi</div>
            <div class="fw-bold"><?= h($data['kode_transaksi'] ?? '-') ?></div>
          </div>
        </div>
      </div>

      <div class="card success-card p-4 mb-4">
        <h5 class="fw-bold font-serif mb-3">Kode Tiket</h5>
        <?php if (!empty($tickets)): ?>
          <div class="d-grid gap-2">
            <?php foreach ($tickets as $t): ?>
              <div class="ticket-code"><?= h($t['kode_tiket']) ?></div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-muted">Tiket belum tersedia.</div>
        <?php endif; ?>
      </div>

      <div class="d-flex flex-wrap gap-2">
        <a href="detailEvent.php?id=<?= h($data['id_event']) ?>" class="btn btn-gold px-4">Lihat Detail Event</a>
        <a href="eventLainnya.php" class="btn btn-outline-dark">Kembali ke Event</a>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
