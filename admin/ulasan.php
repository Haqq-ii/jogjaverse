<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/_crud_helper.php";
wajib_admin();

$table = "ulasan";

[$cols, $pk] = describe_table($koneksi, $table);
if (!$pk) die("PK tidak ditemukan di tabel $table");

$colNames  = array_map(fn($c) => $c['Field'], $cols);
$colUser   = pick_col($colNames, ["id_pengguna", "pengguna_id", "user_id"]);
$colTarget = pick_col($colNames, ["id_destinasi", "id_event", "id_target", "target_id"]);
$colJenis  = pick_col($colNames, ["jenis", "jenis_target", "tipe"]);
$colRating = pick_col($colNames, ["rating", "nilai"]);
$colStatus = pick_col($colNames, ["status", "status_ulasan"]);
$colIsi    = pick_col($colNames, ["komentar", "isi", "ulasan", "review", "pesan"]);
$colTime   = guess_time_col($colNames);

$q = trim($_GET['q'] ?? "");
$data = fetch_rows($koneksi, $table, $pk, $q, $cols);

$totalUlasan = 0;
$avgRating = null;
$stmt = $koneksi->prepare("SELECT COUNT(*) as total FROM `$table`");
if ($stmt) {
  $stmt->execute();
  $totalUlasan = (int)$stmt->get_result()->fetch_assoc()['total'];
}
if ($colRating) {
  $stmt = $koneksi->prepare("SELECT AVG(`$colRating`) as rata FROM `$table`");
  if ($stmt) {
    $stmt->execute();
    $avgRating = $stmt->get_result()->fetch_assoc()['rata'] ?? null;
  }
}
?>

<?php require_once __DIR__ . "/partials/header.php"; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin_crud.css">

<div class="metric-cards">
  <div class="metric-card">
    <div class="label">Total Ulasan</div>
    <div class="value"><?= h($totalUlasan) ?></div>
    <div class="muted">Semua ulasan pengguna</div>
  </div>
  <div class="metric-card">
    <div class="label">Rata-rata Rating</div>
    <div class="value"><?= $avgRating !== null ? h(number_format((float)$avgRating, 2)) : "-" ?></div>
    <div class="muted"><?= $colRating ? "Berdasarkan kolom rating" : "Kolom rating tidak ditemukan" ?></div>
  </div>
</div>

<div class="card">
  <div class="toolbar">
    <div>
      <h1 class="page-title" style="margin:0">Ulasan Pengguna</h1>
      <p class="page-sub">Lihat umpan balik terbaru dari wisatawan</p>
    </div>
    <form method="GET" style="display:flex;gap:10px;align-items:center">
      <input class="search" type="text" name="q" placeholder="Cari isi ulasan..." value="<?= h($q) ?>">
      <button class="btn secondary" type="submit">Cari</button>
      <a class="btn secondary" href="<?= BASE_URL ?>/admin/ulasan.php">Reset</a>
    </form>
  </div>

  <div class="table-wrap">
    <table class="table2">
      <thead>
        <tr>
          <th>ID</th>
          <th>Pengguna</th>
          <th>Target</th>
          <th>Jenis</th>
          <th>Rating</th>
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
            <td><?= h($colRating ? ($row[$colRating] ?? '-') : '-') ?></td>
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
