<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (empty($_SESSION['login']) || $_SESSION['login'] !== true) {
  $redirect_to = '/public/user/php/pelaporan.php';
  header("Location: " . BASE_URL . "/public/login.php?redirect_to=" . urlencode($redirect_to));
  exit();
}

if (!function_exists('h')) {
  function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
  }
}

$user_id = (int)($_SESSION['id_pengguna'] ?? 0);
$current_user = null;
if ($user_id > 0) {
  $stmtUser = $koneksi->prepare("SELECT id_pengguna, nama_lengkap, username, foto_profil_url FROM pengguna WHERE id_pengguna = ? LIMIT 1");
  if ($stmtUser) {
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $current_user = $stmtUser->get_result()->fetch_assoc();
    $stmtUser->close();
  }
}

$flash_success = $_SESSION['flash_pelaporan_success'] ?? '';
$flash_error = $_SESSION['flash_pelaporan_error'] ?? '';
unset($_SESSION['flash_pelaporan_success'], $_SESSION['flash_pelaporan_error']);

$destinasi_options = [];
$event_options = [];
$kuliner_options = [];

$res = $koneksi->query("SELECT id_destinasi, nama FROM destinasi ORDER BY nama LIMIT 200");
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $destinasi_options[] = $row;
  }
}
$res = $koneksi->query("SELECT id_event, judul FROM event ORDER BY judul LIMIT 200");
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $event_options[] = $row;
  }
}
$res = $koneksi->query("SELECT id_kuliner, nama FROM kuliner ORDER BY nama LIMIT 200");
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $kuliner_options[] = $row;
  }
}

$riwayat = [];
$stmt = $koneksi->prepare("
  SELECT p.*, d.nama AS nama_destinasi, e.judul AS nama_event, k.nama AS nama_kuliner
  FROM pelaporan p
  LEFT JOIN destinasi d ON p.jenis_target = 'destinasi' AND p.id_target = d.id_destinasi
  LEFT JOIN event e ON p.jenis_target = 'event' AND p.id_target = e.id_event
  LEFT JOIN kuliner k ON p.jenis_target = 'kuliner' AND p.id_target = k.id_kuliner
  WHERE p.id_pengguna = ? AND p.jenis_target IN ('destinasi','event','kuliner')
  ORDER BY p.dibuat_pada DESC
");
if ($stmt) {
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($res) {
    while ($row = $res->fetch_assoc()) {
      $riwayat[] = $row;
    }
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pelaporan - JogjaVerse</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style2.css">
  <style>
    body { background: #F7F5F2; }
    .page-wrap { max-width: 1100px; margin: 0 auto; padding: 110px 20px 40px; }
    .card-soft { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    .table-simple { width: 100%; border-collapse: collapse; }
    .table-simple th, .table-simple td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; font-size: 14px; }
    .table-simple th { background: #F7F5F2; color: #4D2832; font-weight: 700; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/navbar.php'; ?>

  <div class="page-wrap">
    <div class="card-soft mb-4">
      <h1 class="h4 fw-bold mb-1" style="color:#4D2832;">Pelaporan</h1>
      <p class="text-muted mb-3">Laporkan masalah atau konten yang tidak sesuai.</p>

      <?php if ($flash_success): ?>
        <div class="alert alert-success"><?= h($flash_success) ?></div>
      <?php endif; ?>
      <?php if ($flash_error): ?>
        <div class="alert alert-danger"><?= h($flash_error) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/public/user/php/proses_pelaporan.php" class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Jenis Target</label>
          <select name="jenis_target" class="form-select" required>
            <option value="destinasi">Destinasi</option>
            <option value="event">Event</option>
            <option value="kuliner">Kuliner</option>
          </select>
        </div>
        <div class="col-md-8">
          <label class="form-label">Target</label>
          <?php if (!empty($destinasi_options) || !empty($event_options) || !empty($kuliner_options)): ?>
            <select name="id_target" class="form-select" required>
              <?php if (!empty($destinasi_options)): ?>
                <optgroup label="Destinasi">
                  <?php foreach ($destinasi_options as $d): ?>
                    <option value="<?= h($d['id_destinasi']) ?>">#<?= h($d['id_destinasi']) ?> - <?= h($d['nama']) ?></option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
              <?php if (!empty($event_options)): ?>
                <optgroup label="Event">
                  <?php foreach ($event_options as $e): ?>
                    <option value="<?= h($e['id_event']) ?>">#<?= h($e['id_event']) ?> - <?= h($e['judul']) ?></option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
              <?php if (!empty($kuliner_options)): ?>
                <optgroup label="Kuliner">
                  <?php foreach ($kuliner_options as $k): ?>
                    <option value="<?= h($k['id_kuliner']) ?>">#<?= h($k['id_kuliner']) ?> - <?= h($k['nama']) ?></option>
                  <?php endforeach; ?>
                </optgroup>
              <?php endif; ?>
            </select>
          <?php else: ?>
            <input type="number" name="id_target" class="form-control" placeholder="Masukkan ID target" required>
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Judul</label>
          <input type="text" name="judul" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Deskripsi</label>
          <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-dark">Kirim Laporan</button>
        </div>
      </form>
    </div>

    <div class="card-soft">
      <h2 class="h5 fw-bold mb-3" style="color:#4D2832;">Riwayat Pelaporan</h2>
      <?php if (empty($riwayat)): ?>
        <div class="text-muted">Belum ada pelaporan.</div>
      <?php else: ?>
        <table class="table-simple">
          <thead>
            <tr>
              <th>Target</th>
              <th>Judul</th>
              <th>Status</th>
              <th>Waktu</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($riwayat as $row): ?>
              <?php
                $target_nama = $row['nama_destinasi'] ?? $row['nama_event'] ?? $row['nama_kuliner'] ?? ('#' . ($row['id_target'] ?? '-'));
              ?>
              <tr>
                <td><?= h(($row['jenis_target'] ?? '-') . ' - ' . $target_nama) ?></td>
                <td><?= h($row['judul'] ?? '-') ?></td>
                <td><?= h($row['status'] ?? '-') ?></td>
                <td><?= h($row['dibuat_pada'] ?? '-') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
