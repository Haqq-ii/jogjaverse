<?php
require_once __DIR__ . "/../../../config/config.php";
require_once __DIR__ . "/../../../config/koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function h($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function load_env(string $path): array {
  if (!is_readable($path)) {
    return [];
  }
  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if (!$lines) {
    return [];
  }
  $data = [];
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) {
      continue;
    }
    $pos = strpos($line, '=');
    if ($pos === false) {
      continue;
    }
    $key = trim(substr($line, 0, $pos));
    $val = trim(substr($line, $pos + 1));
    if ($val !== '' && $val[0] === '"' && str_ends_with($val, '"')) {
      $val = substr($val, 1, -1);
    } elseif ($val !== '' && $val[0] === "'" && str_ends_with($val, "'")) {
      $val = substr($val, 1, -1);
    }
    $data[$key] = $val;
  }
  return $data;
}

if (empty($_SESSION['login']) || $_SESSION['login'] !== true) {
  header("Location: " . BASE_URL . "/public/login.php");
  exit();
}

$user_id = (int)($_SESSION['id_pengguna'] ?? 0);
if ($user_id <= 0) {
  header("Location: " . BASE_URL . "/public/login.php");
  exit();
}

$id_reservasi = filter_input(INPUT_GET, 'id_reservasi', FILTER_VALIDATE_INT);
$not_found = true;
$not_ready = false;
$ticket = null;

if ($id_reservasi) {
  $stmt = $koneksi->prepare("
    SELECT r.id_reservasi, r.id_pengguna, r.jumlah_tiket, r.total_harga, r.status, r.dibuat_pada,
           e.judul, e.mulai_pada, e.selesai_pada, e.lokasi,
           p.nama_lengkap
    FROM reservasi_event r
    LEFT JOIN event e ON r.id_event = e.id_event
    LEFT JOIN pengguna p ON r.id_pengguna = p.id_pengguna
    WHERE r.id_reservasi = ?
    LIMIT 1
  ");
  $stmt->bind_param("i", $id_reservasi);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res) {
    $ticket = $res->fetch_assoc();
  }
  $stmt->close();
}

if ($ticket && (int)($ticket['id_pengguna'] ?? 0) === $user_id) {
  $status = strtoupper((string)($ticket['status'] ?? ''));
  $allowed = ['DIKONFIRMASI', 'LUNAS', 'BERHASIL', 'SUDAH_BAYAR'];
  if (in_array($status, $allowed, true)) {
    $not_found = false;
  } else {
    $not_ready = true;
  }
}

$env = load_env(__DIR__ . "/../../../.env");
$qr_base_url = trim($env['QR_API_BASE_URL'] ?? 'https://api.qrserver.com/v1/create-qr-code/');
$qr_size = trim($env['QR_API_SIZE'] ?? '220x220');
$qr_api_key = trim($env['QR_API_KEY'] ?? '');

$kode_tiket = '';
$qr_url = '';
$qr_img_src = '';
$qr_width = 220;
$qr_height = 220;
if (!$not_found) {
  $hash = strtoupper(substr(hash('sha256', $id_reservasi . '|' . $user_id . '|' . ($ticket['dibuat_pada'] ?? '')), 0, 6));
  $kode_tiket = 'JGV-' . $id_reservasi . '-' . $hash;
  $payload = "JOGJAVERSE|TIKET|" . $kode_tiket . "|RES|" . $id_reservasi;

  if (preg_match('/(\d+)x(\d+)/', $qr_size, $m)) {
    $qr_width = (int)$m[1];
    $qr_height = (int)$m[2];
  }

  $params = [
    'size' => $qr_size,
    'data' => $payload,
  ];
  if ($qr_api_key !== '') {
    $params['apikey'] = $qr_api_key;
  }

  $base = rtrim($qr_base_url);
  $base = rtrim($base, '?');
  $separator = (strpos($base, '?') === false) ? '?' : '&';
  $qr_url = $base . $separator . http_build_query($params);

  // Debug manual: echo $qr_url;
  $qr_img_src = $qr_api_key !== '' ? ("tiketEvent.php?id_reservasi=" . $id_reservasi . "&qr=1") : $qr_url;
}

if (isset($_GET['qr'])) {
  if ($not_found || $not_ready || $qr_url === '') {
    http_response_code(404);
    exit();
  }
  $img = @file_get_contents($qr_url);
  if ($img === false) {
    http_response_code(502);
    exit();
  }
  header("Content-Type: image/png");
  echo $img;
  exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tiket Event - JogjaVerse</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/style2.css">
  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: #FDFBF7;
      color: #321B1F;
      padding-top: 0;
    }
    .ticket-page {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 16px;
    }
    .ticket-wrap {
      max-width: 720px;
      width: 100%;
      margin: 0 auto;
      padding: 0;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .ticket-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      border: 1px solid rgba(0, 0, 0, 0.06);
      width: 100%;
    }
    .ticket-header {
      background: #4A1B22;
      color: #fff;
      padding: 20px 24px;
    }
    .ticket-header h1 {
      font-family: 'Playfair Display', serif;
      font-size: 24px;
      margin: 0;
    }
    .ticket-body {
      padding: 24px;
    }
    .ticket-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }
    .ticket-item small {
      display: block;
      color: #846267;
      font-size: 12px;
      margin-bottom: 4px;
    }
    .ticket-item span {
      font-weight: 600;
    }
    .ticket-code {
      font-size: 18px;
      font-weight: 700;
      color: #4A1B22;
    }
    .ticket-footer {
      padding: 20px 24px;
      border-top: 1px dashed #E2D8D6;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
    }
    .qr-box {
      width: 220px;
      height: 220px;
      border: 1px solid #E2D8D6;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fff;
    }
    .ticket-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 16px;
      justify-content: center;
      width: 100%;
    }
    @page {
      margin: 0;
    }
    @media print {
      .no-print { display: none !important; }
      body { background: #fff; margin: 0; }
      .ticket-page { padding: 0; min-height: auto; }
      .ticket-wrap { margin: 0; max-width: none; }
      .ticket-card { box-shadow: none; border: 1px solid #ddd; }
    }
  </style>
</head>
<body>
  <main class="ticket-page">
    <div class="ticket-wrap">
    <?php if ($not_found): ?>
      <div class="ticket-card">
        <div class="ticket-header">
          <h1>Tiket Tidak Ditemukan</h1>
        </div>
        <div class="ticket-body">
          <p class="text-muted mb-0">Data tiket tidak tersedia atau bukan milik Anda.</p>
        </div>
      </div>
      <div class="ticket-actions no-print">
        <a href="/public/user.php?tab=bookings" class="btn btn-outline-dark">Kembali</a>
      </div>
    <?php elseif ($not_ready): ?>
      <div class="ticket-card">
        <div class="ticket-header">
          <h1>Tiket Belum Tersedia</h1>
        </div>
        <div class="ticket-body">
          <p class="text-muted mb-0">Reservasi Anda masih menunggu konfirmasi.</p>
        </div>
      </div>
      <div class="ticket-actions no-print">
        <a href="/public/user.php?tab=bookings" class="btn btn-outline-dark">Kembali</a>
      </div>
    <?php else: ?>
      <div class="ticket-card">
        <div class="ticket-header">
          <h1>Tiket Event JogjaVerse</h1>
        </div>
        <div class="ticket-body">
          <div class="ticket-grid">
            <div class="ticket-item">
              <small>Nama Event</small>
              <span><?= h($ticket['judul'] ?? '-') ?></span>
            </div>
            <div class="ticket-item">
              <small>Tanggal & Jam</small>
              <span><?= h($ticket['mulai_pada'] ?? '-') ?></span>
            </div>
            <div class="ticket-item">
              <small>Lokasi</small>
              <span><?= h($ticket['lokasi'] ?? '-') ?></span>
            </div>
            <div class="ticket-item">
              <small>Nama Pemilik</small>
              <span><?= h($ticket['nama_lengkap'] ?? '-') ?></span>
            </div>
            <div class="ticket-item">
              <small>ID Reservasi</small>
              <span><?= h($ticket['id_reservasi'] ?? '-') ?></span>
            </div>
            <div class="ticket-item">
              <small>Jumlah Tiket</small>
              <span><?= h($ticket['jumlah_tiket'] ?? '-') ?></span>
            </div>
            <div class="ticket-item">
              <small>Total Pembayaran</small>
              <span>Rp <?= h(number_format((int)($ticket['total_harga'] ?? 0), 0, ',', '.')) ?></span>
            </div>
            <div class="ticket-item">
              <small>Status</small>
              <span><?= h($ticket['status'] ?? '-') ?></span>
            </div>
            <div class="ticket-item">
              <small>Waktu Dibuat</small>
              <span><?= h($ticket['dibuat_pada'] ?? '-') ?></span>
            </div>
            <div class="ticket-item">
              <small>Kode Tiket</small>
              <span class="ticket-code"><?= h($kode_tiket) ?></span>
            </div>
          </div>
        </div>
        <div class="ticket-footer">
          <div class="qr-box">
            <img src="<?= h($qr_img_src) ?>"
                 alt="QR Tiket"
                 width="<?= h($qr_width) ?>"
                 height="<?= h($qr_height) ?>"
                 onerror="this.style.display='none'; var el=document.getElementById('qr-error'); if(el){el.style.display='block';}">
          </div>
          <small id="qr-error" class="text-danger" style="display:none;">QR gagal dimuat, silakan refresh halaman.</small>
          <small class="text-muted">Tunjukkan QR ini saat check-in.</small>
        </div>
      </div>
      <div class="ticket-actions no-print">
        <button class="btn btn-gold" type="button" onclick="window.print()">Download / Print</button>
        <a href="/public/user.php?tab=bookings" class="btn btn-outline-dark">Kembali</a>
      </div>
    <?php endif; ?>
    </div>
  </main>
</body>
</html>
