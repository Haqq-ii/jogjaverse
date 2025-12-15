<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/_crud_helper.php";
wajib_admin();

$table = "pelaporan";

[$cols, $pk] = describe_table($koneksi, $table);
if (!$pk) die("PK tidak ditemukan di tabel $table");

$colNames  = array_map(fn($c) => $c['Field'], $cols);
$colUser   = pick_col($colNames, ["id_pengguna", "pelapor_id", "user_id"]);
$colTarget = pick_col($colNames, ["id_target", "target_id", "id_destinasi", "id_event"]);
$colJenis  = pick_col($colNames, ["jenis", "jenis_laporan", "tipe", "kategori"]);
$colStatus = pick_col($colNames, ["status", "status_laporan", "status_pelaporan"]);
$colIsi    = pick_col($colNames, ["deskripsi", "keterangan", "pesan", "isi", "detail"]);
$colTime   = guess_time_col($colNames);

$q = trim($_GET['q'] ?? "");
$data = fetch_rows($koneksi, $table, $pk, $q, $cols);

$totalLaporan = 0;
$pending = 0;
$resolved = 0;
$stmt = $koneksi->prepare("SELECT COUNT(*) as total FROM `$table`");
if ($stmt) {
  $stmt->execute();
  $totalLaporan = (int)$stmt->get_result()->fetch_assoc()['total'];
}
if ($colStatus) {
  $sql = "
    SELECT 
      SUM(CASE WHEN LOWER(`$colStatus`) IN ('pending','open','baru','dalam_proses') THEN 1 ELSE 0 END) as pending,
      SUM(CASE WHEN LOWER(`$colStatus`) IN ('selesai','done','resolved','closed') THEN 1 ELSE 0 END) as resolved
    FROM `$table`
  ";
  $stmt = $koneksi->prepare($sql);
  if ($stmt) {
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $pending = (int)($r['pending'] ?? 0);
    $resolved = (int)($r['resolved'] ?? 0);
  }
}
?>

<?php require_once __DIR__ . "/partials/header.php"; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin_crud.css">

<div class="metric-cards">
  <div class="metric-card">
    <div class="label">Total Pelaporan</div>
    <div class="value"><?= h($totalLaporan) ?></div>
    <div class="muted">Semua laporan masuk</div>
  </div>
  <div class="metric-card">
    <div class="label">Pending</div>
    <div class="value"><?= h($pending) ?></div>
    <div class="muted"><?= $colStatus ? "Status pending/open" : "Kolom status tidak ada" ?></div>
  </div>
  <div class="metric-card">
    <div class="label">Terselesaikan</div>
    <div class="value"><?= h($resolved) ?></div>
    <div class="muted"><?= $colStatus ? "Status selesai/resolved" : "Kolom status tidak ada" ?></div>
  </div>
</div>

<div class="card">
  <div class="toolbar">
    <div>
      <h1 class="page-title" style="margin:0">Pelaporan Pengguna</h1>
      <p class="page-sub">Monitor laporan & isu dari wisatawan</p>
    </div>
    <form method="GET" style="display:flex;gap:10px;align-items:center">
      <input class="search" type="text" name="q" placeholder="Cari isi laporan..." value="<?= h($q) ?>">
      <button class="btn secondary" type="submit">Cari</button>
      <a class="btn secondary" href="<?= BASE_URL ?>/admin/pelaporan.php">Reset</a>
    </form>
  </div>

  <div class="table-wrap">
    <table class="table2">
      <thead>
        <tr>
          <th>ID</th>
          <th>Pelapor</th>
          <th>Target</th>
          <th>Jenis</th>
          <th>Status</th>
          <th>Isi</th>
          <?php if ($colTime): ?><th>Waktu</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $data->fetch_assoc()): ?>
          <tr>
            <td><?= h($row[$pk]) ?></td>
            <td><?= h($colUser ? ($row[$colUser] ?? '-') : '-') ?></td>
            <td><?= h($colTarget ? ($row[$colTarget] ?? '-') : '-') ?></td>
            <td><?= h($colJenis ? ($row[$colJenis] ?? '-') : '-') ?></td>
            <td><?= h($colStatus ? ($row[$colStatus] ?? '-') : '-') ?></td>
            <td style="max-width:320px"><small><?= h($colIsi ? ($row[$colIsi] ?? '-') : '-') ?></small></td>
            <?php if ($colTime): ?>
              <td><small><?= h($row[$colTime] ?? '-') ?></small></td>
            <?php endif; ?>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
