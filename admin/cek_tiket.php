<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
wajib_admin();

function h($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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

$has_checkin = table_has_column($koneksi, 'reservasi_event', 'status_checkin');
$has_checkin_time = table_has_column($koneksi, 'reservasi_event', 'checkin_pada');

$message = '';
$info = null;
$is_valid = false;
$kode_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'checkin') {
  $id_reservasi = (int)($_POST['id_reservasi'] ?? 0);
  if ($has_checkin && $id_reservasi > 0) {
    $stmt = $koneksi->prepare("UPDATE reservasi_event SET status_checkin = 'sudah', checkin_pada = NOW() WHERE id_reservasi = ? AND status_checkin = 'belum'");
    $stmt->bind_param("i", $id_reservasi);
    $stmt->execute();
    $stmt->close();
    $message = 'Check-in berhasil diperbarui.';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'search') {
  $kode_input = trim((string)($_POST['kode'] ?? ''));
  $id_reservasi = 0;

  if ($kode_input === '') {
    $message = 'Masukkan kode tiket atau ID reservasi.';
  } else {
    if (ctype_digit($kode_input)) {
      $id_reservasi = (int)$kode_input;
    } elseif (preg_match('/JGV-(\d+)-/i', $kode_input, $m)) {
      $id_reservasi = (int)$m[1];
    }

    if ($id_reservasi <= 0) {
      $message = 'Format kode tiket tidak valid.';
    } else {
      $select = "
        r.id_reservasi, r.status, r.jumlah_tiket, r.total_harga, r.dibuat_pada,
        e.judul, e.mulai_pada, e.lokasi,
        p.nama_lengkap
      ";
      if ($has_checkin) {
        $select .= ", r.status_checkin";
      }
      if ($has_checkin_time) {
        $select .= ", r.checkin_pada";
      }
      $sql = "
        SELECT $select
        FROM reservasi_event r
        LEFT JOIN event e ON r.id_event = e.id_event
        LEFT JOIN pengguna p ON r.id_pengguna = p.id_pengguna
        WHERE r.id_reservasi = ?
        LIMIT 1
      ";
      $stmt = $koneksi->prepare($sql);
      $stmt->bind_param("i", $id_reservasi);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($res) {
        $info = $res->fetch_assoc();
      }
      $stmt->close();

      if (!$info) {
        $message = 'Tiket tidak ditemukan.';
      } else {
        $status = strtoupper((string)($info['status'] ?? ''));
        $is_valid = in_array($status, ['DIKONFIRMASI', 'LUNAS', 'BERHASIL', 'SUDAH_BAYAR'], true);
      }
    }
  }
}
?>
<?php require_once __DIR__ . "/partials/header.php"; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin_crud.css">

<div class="card">
  <h1 class="page-title">Cek Tiket Event</h1>
  <p class="page-sub">Masukkan kode tiket atau ID reservasi untuk verifikasi.</p>

  <?php if ($message): ?>
    <div class="notice"><?= h($message) ?></div>
  <?php endif; ?>

  <form method="POST" class="form-grid" style="margin-top:12px;">
    <input type="hidden" name="action" value="search">
    <div class="full">
      <label>Kode Tiket / ID Reservasi</label>
      <input type="text" name="kode" placeholder="Contoh: JGV-123-ABCDEF atau 123" value="<?= h($kode_input) ?>">
    </div>
    <div class="full">
      <button class="btn" type="submit">Verifikasi</button>
    </div>
  </form>
</div>

<?php if ($info): ?>
  <div class="card">
    <div class="toprow">
      <h2 style="margin:0">Hasil Verifikasi</h2>
      <span class="pill"><?= $is_valid ? 'VALID' : 'TIDAK VALID' ?></span>
    </div>
    <div class="table-wrap">
      <table class="table2">
        <tbody>
          <tr><th>ID Reservasi</th><td><?= h($info['id_reservasi'] ?? '-') ?></td></tr>
          <tr><th>Nama Event</th><td><?= h($info['judul'] ?? '-') ?></td></tr>
          <tr><th>Waktu Event</th><td><?= h($info['mulai_pada'] ?? '-') ?></td></tr>
          <tr><th>Lokasi</th><td><?= h($info['lokasi'] ?? '-') ?></td></tr>
          <tr><th>Nama Pemilik</th><td><?= h($info['nama_lengkap'] ?? '-') ?></td></tr>
          <tr><th>Jumlah Tiket</th><td><?= h($info['jumlah_tiket'] ?? '-') ?></td></tr>
          <tr><th>Status Reservasi</th><td><?= h($info['status'] ?? '-') ?></td></tr>
          <tr><th>Waktu Dibuat</th><td><?= h($info['dibuat_pada'] ?? '-') ?></td></tr>
          <?php if ($has_checkin): ?>
            <tr><th>Status Check-in</th><td><?= h($info['status_checkin'] ?? '-') ?></td></tr>
          <?php endif; ?>
          <?php if ($has_checkin_time): ?>
            <tr><th>Check-in Pada</th><td><?= h($info['checkin_pada'] ?? '-') ?></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if ($has_checkin && $is_valid): ?>
      <?php if (($info['status_checkin'] ?? '') === 'sudah'): ?>
        <div class="notice">Tiket sudah digunakan.</div>
      <?php else: ?>
        <form method="POST" style="margin-top:10px;">
          <input type="hidden" name="action" value="checkin">
          <input type="hidden" name="id_reservasi" value="<?= h($info['id_reservasi'] ?? 0) ?>">
          <button class="btn" type="submit">Check-in</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
