<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../admin/_crud_helper.php";
wajib_admin();

$limit = 50;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
$page = $page && $page > 0 ? $page : 1;
$offset = ($page - 1) * $limit;

$rows = [];
$error = '';
[$cols, $pk] = describe_table($koneksi, 'kunjungan');
$colNames = array_map(fn($c) => $c['Field'], $cols);
$timeCol = guess_time_col($colNames);
$orderCol = $timeCol ?: ($pk ?: null);

$colHalaman = pick_col($colNames, ['halaman', 'jenis_halaman', 'jenis', 'tipe']);
$colTargetType = pick_col($colNames, ['target_type', 'jenis_target']);
$colTargetId = pick_col($colNames, ['target_id', 'id_target']);
$colUser = pick_col($colNames, ['user_id', 'id_pengguna']);
$colSession = pick_col($colNames, ['session_id']);
$colUA = pick_col($colNames, ['user_agent']);
$colIP = pick_col($colNames, ['ip_hash']);

$columns = [];
if ($timeCol) $columns[] = ['label' => 'Waktu', 'key' => $timeCol];
if ($colHalaman) $columns[] = ['label' => 'Halaman', 'key' => $colHalaman];
if ($colTargetType) $columns[] = ['label' => 'Tipe Target', 'key' => $colTargetType];
if ($colTargetId) $columns[] = ['label' => 'ID Target', 'key' => $colTargetId];
if ($colUser) $columns[] = ['label' => 'User', 'key' => $colUser];
if ($colSession) $columns[] = ['label' => 'Session', 'key' => $colSession];
if ($colUA) $columns[] = ['label' => 'User Agent', 'key' => $colUA];
if ($colIP) $columns[] = ['label' => 'IP Hash', 'key' => $colIP];

if (count($cols) === 0) {
  $error = 'Tabel kunjungan belum tersedia.';
} else {
  $sql = "SELECT * FROM `kunjungan`";
  if ($orderCol) {
    $sql .= " ORDER BY `" . $orderCol . "` DESC";
  }
  $sql .= " LIMIT ? OFFSET ?";
  $stmt = $koneksi->prepare($sql);
  if ($stmt) {
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
  } else {
    $error = 'Gagal mengambil data tracker.';
  }
}

function jv_short($value, $max = 80) {
  $value = (string)$value;
  if (strlen($value) <= $max) return $value;
  return substr($value, 0, $max - 3) . '...';
}
?>

<?php require_once __DIR__ . "/../admin/partials/header.php"; ?>

<div class="card">
  <div class="toprow">
    <h2 style="margin:0">Tracker Kunjungan</h2>
    <span class="pill">kunjungan</span>
  </div>
  <div class="small">Menampilkan <?= h($limit) ?> data terbaru.</div>

  <?php if ($error !== ''): ?>
    <div class="error" style="margin-top:12px;"><?= h($error) ?></div>
  <?php endif; ?>

  <table class="table-minimal" style="margin-top:12px;">
    <thead>
      <tr>
        <?php if (count($columns) > 0): ?>
          <?php foreach ($columns as $col): ?>
            <th><?= h($col['label']) ?></th>
          <?php endforeach; ?>
        <?php else: ?>
          <th>Data</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php if (count($rows) > 0 && count($columns) > 0): ?>
        <?php foreach ($rows as $row): ?>
          <tr>
            <?php foreach ($columns as $col): ?>
              <?php $val = $row[$col['key']] ?? '-'; ?>
              <td><?= h(jv_short($val)) ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      <?php elseif (count($rows) > 0): ?>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><?= h(json_encode($row)) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td><small>Belum ada data kunjungan.</small></td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="wrap" style="margin-top:12px;">
    <?php if ($page > 1): ?>
      <a class="btn secondary" href="?page=<?= $page - 1 ?>">Sebelumnya</a>
    <?php endif; ?>
    <span class="pill">Halaman <?= h($page) ?></span>
    <?php if (count($rows) === $limit): ?>
      <a class="btn secondary" href="?page=<?= $page + 1 ?>">Berikutnya</a>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . "/../admin/partials/footer.php"; ?>