<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
wajib_admin();

if (!function_exists('h')) {
  function h($val) {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
  }
}

$flash = $_SESSION['flash'] ?? "";
unset($_SESSION['flash']);

$allowedStatus = ['PENDING', 'DIKONFIRMASI', 'DIBATALKAN', 'KADALUARSA'];
$filter = $_GET['status'] ?? '';
if (!in_array($filter, $allowedStatus, true)) {
  $filter = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
  $id_reservasi = (int)($_POST['id_reservasi'] ?? 0);
  $status_baru = $_POST['status'] ?? '';

  if ($id_reservasi > 0 && in_array($status_baru, $allowedStatus, true)) {
    $koneksi->begin_transaction();
    try {
      $stmt = $koneksi->prepare("UPDATE reservasi_event SET status = ? WHERE id_reservasi = ?");
      $stmt->bind_param("si", $status_baru, $id_reservasi);
      $stmt->execute();
      $stmt->close();

      if ($status_baru === 'DIBATALKAN') {
        $stmtPay = $koneksi->prepare("SELECT status FROM pembayaran WHERE id_reservasi = ? LIMIT 1");
        $stmtPay->bind_param("i", $id_reservasi);
        $stmtPay->execute();
        $rowPay = $stmtPay->get_result()->fetch_assoc();
        $stmtPay->close();

        $statusPay = ($rowPay && $rowPay['status'] === 'SUDAH_BAYAR') ? 'REFUND' : 'GAGAL';
        $stmtUp = $koneksi->prepare("UPDATE pembayaran SET status = ? WHERE id_reservasi = ?");
        $stmtUp->bind_param("si", $statusPay, $id_reservasi);
        $stmtUp->execute();
        $stmtUp->close();
      }

      $koneksi->commit();
      $_SESSION['flash'] = "Status reservasi berhasil diperbarui.";
    } catch (Throwable $e) {
      $koneksi->rollback();
      $_SESSION['flash'] = "Gagal memperbarui status.";
    }
  }
  header("Location: " . BASE_URL . "/admin/reservasi_event.php");
  exit();
}

$sql = "
  SELECT r.id_reservasi, r.jumlah_tiket, r.total_harga, r.status AS status_reservasi, r.dibuat_pada,
         e.judul AS nama_event,
         u.nama_lengkap, u.username,
         p.status AS status_pembayaran
  FROM reservasi_event r
  JOIN event e ON r.id_event = e.id_event
  LEFT JOIN pengguna u ON r.id_pengguna = u.id_pengguna
  LEFT JOIN pembayaran p ON r.id_reservasi = p.id_reservasi
";
$types = "";
$params = [];
if ($filter !== '') {
  $sql .= " WHERE r.status = ?";
  $types .= "s";
  $params[] = $filter;
}
$sql .= " ORDER BY r.dibuat_pada DESC";

$stmt = $koneksi->prepare($sql);
if ($types !== '') {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$data = $stmt->get_result();

$detail = null;
$detailTickets = [];
if (isset($_GET['detail'])) {
  $id_detail = (int)$_GET['detail'];
  $stmtDetail = $koneksi->prepare("
    SELECT r.*, e.judul AS nama_event, u.nama_lengkap, u.username,
           p.status AS status_pembayaran, p.metode, p.jumlah, p.kode_transaksi, p.dibayar_pada
    FROM reservasi_event r
    JOIN event e ON r.id_event = e.id_event
    LEFT JOIN pengguna u ON r.id_pengguna = u.id_pengguna
    LEFT JOIN pembayaran p ON r.id_reservasi = p.id_reservasi
    WHERE r.id_reservasi = ?
    LIMIT 1
  ");
  $stmtDetail->bind_param("i", $id_detail);
  $stmtDetail->execute();
  $detail = $stmtDetail->get_result()->fetch_assoc();
  $stmtDetail->close();

  if ($detail) {
    $stmtTiket = $koneksi->prepare("SELECT kode_tiket, sudah_dipakai, dipakai_pada FROM tiket_event WHERE id_reservasi = ? ORDER BY id_tiket ASC");
    $stmtTiket->bind_param("i", $id_detail);
    $stmtTiket->execute();
    $resTiket = $stmtTiket->get_result();
    if ($resTiket) {
      while ($row = $resTiket->fetch_assoc()) {
        $detailTickets[] = $row;
      }
    }
    $stmtTiket->close();
  }
}
?>

<?php require_once __DIR__ . "/partials/header.php"; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin_crud.css">

<div class="card">
  <div>
    <h1 class="page-title">Reservasi Event</h1>
    <p class="page-sub">Kelola data reservasi tiket event dari pengguna</p>
  </div>

  <?php if ($flash): ?><div class="notice"><?= h($flash) ?></div><?php endif; ?>

  <div class="toolbar">
    <div class="left">
      <form method="GET" style="display:flex;gap:10px;align-items:center">
        <select name="status">
          <option value="">Semua Status</option>
          <?php foreach ($allowedStatus as $st): ?>
            <option value="<?= h($st) ?>" <?= $filter === $st ? 'selected' : '' ?>><?= h($st) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn secondary" type="submit">Filter</button>
        <a class="btn secondary" href="<?= BASE_URL ?>/admin/reservasi_event.php">Reset</a>
      </form>
    </div>
  </div>

  <div class="table-wrap">
    <table class="table2">
      <thead>
        <tr>
          <th>ID</th>
          <th>Event</th>
          <th>Pengguna</th>
          <th>Jumlah</th>
          <th>Total</th>
          <th>Status</th>
          <th>Pembayaran</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php while($row = $data->fetch_assoc()): ?>
        <tr>
          <td><?= h($row['id_reservasi']) ?></td>
          <td><?= h($row['nama_event'] ?? '-') ?></td>
          <td><?= h($row['nama_lengkap'] ?? $row['username'] ?? '-') ?></td>
          <td><?= h($row['jumlah_tiket'] ?? '-') ?></td>
          <td><?= h(number_format((int)($row['total_harga'] ?? 0), 0, ',', '.')) ?></td>
          <td><span class="pill"><?= h($row['status_reservasi'] ?? '-') ?></span></td>
          <td><span class="pill"><?= h($row['status_pembayaran'] ?? '-') ?></span></td>
          <td class="actions">
            <a class="btn-sm gray" href="<?= BASE_URL ?>/admin/reservasi_event.php?detail=<?= h($row['id_reservasi']) ?>">Detail</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($detail): ?>
  <div class="card" style="margin-top:16px;">
    <h3 style="margin-top:0;">Detail Reservasi #<?= h($detail['id_reservasi']) ?></h3>
    <div class="form-grid">
      <div>
        <label>Event</label>
        <input type="text" value="<?= h($detail['nama_event'] ?? '-') ?>" disabled>
      </div>
      <div>
        <label>Pengguna</label>
        <input type="text" value="<?= h($detail['nama_lengkap'] ?? $detail['username'] ?? '-') ?>" disabled>
      </div>
      <div>
        <label>Jumlah Tiket</label>
        <input type="text" value="<?= h($detail['jumlah_tiket'] ?? '-') ?>" disabled>
      </div>
      <div>
        <label>Total Harga</label>
        <input type="text" value="<?= h(number_format((int)($detail['total_harga'] ?? 0), 0, ',', '.')) ?>" disabled>
      </div>
      <div>
        <label>Status Reservasi</label>
        <input type="text" value="<?= h($detail['status'] ?? '-') ?>" disabled>
      </div>
      <div>
        <label>Status Pembayaran</label>
        <input type="text" value="<?= h($detail['status_pembayaran'] ?? '-') ?>" disabled>
      </div>
      <div>
        <label>Metode</label>
        <input type="text" value="<?= h($detail['metode'] ?? '-') ?>" disabled>
      </div>
      <div>
        <label>Kode Transaksi</label>
        <input type="text" value="<?= h($detail['kode_transaksi'] ?? '-') ?>" disabled>
      </div>
      <div>
        <label>Dibayar Pada</label>
        <input type="text" value="<?= h($detail['dibayar_pada'] ?? '-') ?>" disabled>
      </div>
      <div>
        <label>Catatan</label>
        <input type="text" value="<?= h($detail['catatan'] ?? '-') ?>" disabled>
      </div>
    </div>

    <div style="margin-top:12px;">
      <form method="POST" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id_reservasi" value="<?= h($detail['id_reservasi']) ?>">
        <label>Ubah Status</label>
        <select name="status">
          <?php foreach ($allowedStatus as $st): ?>
            <option value="<?= h($st) ?>" <?= ($detail['status'] ?? '') === $st ? 'selected' : '' ?>><?= h($st) ?></option>
          <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">Simpan</button>
      </form>
    </div>

    <div style="margin-top:12px;">
      <h4 style="margin-bottom:8px;">Kode Tiket</h4>
      <?php if (!empty($detailTickets)): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px;">
          <?php foreach ($detailTickets as $t): ?>
            <div style="border:1px dashed #ddd;border-radius:8px;padding:8px;">
              <div><b><?= h($t['kode_tiket']) ?></b></div>
              <small>Status: <?= ($t['sudah_dipakai'] ?? 0) ? 'Dipakai' : 'Belum Dipakai' ?></small>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="text-muted">Belum ada tiket.</div>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
