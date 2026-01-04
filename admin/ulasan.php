<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/_crud_helper.php";
wajib_admin();

$flash = $_SESSION['flash'] ?? "";
unset($_SESSION['flash']);

$allowed_status = ['tampil', 'sembunyi'];
$allowed_filters = ['all', 'tampil', 'sembunyi'];
$filter_raw = $_GET['status'] ?? 'sembunyi';
if ($filter_raw === '' || $filter_raw === 'all' || $filter_raw === 'semua') {
  $filter_status = 'all';
} elseif (in_array($filter_raw, $allowed_filters, true)) {
  $filter_status = $filter_raw;
} else {
  $filter_status = 'sembunyi';
}

$q = trim($_GET['q'] ?? "");

function table_exists(mysqli $koneksi, string $table): bool {
  $stmt = $koneksi->prepare("
    SELECT COUNT(*) as total
    FROM information_schema.tables
    WHERE table_schema = DATABASE()
      AND table_name = ?
  ");
  if (!$stmt) {
    return false;
  }
  $stmt->bind_param("s", $table);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return ((int)($row['total'] ?? 0)) > 0;
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

function pick_column(mysqli $koneksi, string $table, array $candidates): ?string {
  foreach ($candidates as $col) {
    if (table_has_column($koneksi, $table, $col)) {
      return $col;
    }
  }
  return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id_ulasan'])) {
  $id_ulasan = (int)$_POST['id_ulasan'];
  $action = $_POST['action'];
  $new_status = $action === 'approve' ? 'tampil' : 'sembunyi';

  if ($id_ulasan > 0 && in_array($new_status, $allowed_status, true)) {
    $stmt = $koneksi->prepare("UPDATE ulasan SET status = ? WHERE id_ulasan = ? LIMIT 1");
    $stmt->bind_param("si", $new_status, $id_ulasan);
    $stmt->execute();
    $stmt->close();
    $_SESSION['flash'] = "Status ulasan berhasil diperbarui.";
  }
  $qs = http_build_query(['status' => $filter_status, 'q' => $q]);
  header("Location: " . BASE_URL . "/admin/ulasan.php" . ($qs ? "?" . $qs : ""));
  exit();
}

$metric = [
  'total' => 0,
  'pending' => 0,
  'tampil' => 0,
  'avg' => null,
];
$stmt = $koneksi->prepare("
  SELECT
    COUNT(*) as total,
    SUM(status = 'sembunyi') as pending,
    SUM(status = 'tampil') as tampil,
    AVG(CASE WHEN status = 'tampil' THEN rating END) as rata
  FROM ulasan
  WHERE jenis_target IN ('destinasi','event','kuliner')
");
if ($stmt) {
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $metric['total'] = (int)($row['total'] ?? 0);
  $metric['pending'] = (int)($row['pending'] ?? 0);
  $metric['tampil'] = (int)($row['tampil'] ?? 0);
  $metric['avg'] = $row['rata'] ?? null;
  $stmt->close();
}

$has_destinasi = table_exists($koneksi, 'destinasi') && table_has_column($koneksi, 'destinasi', 'id_destinasi');
$has_event = table_exists($koneksi, 'event') && table_has_column($koneksi, 'event', 'id_event');
$has_kuliner = table_exists($koneksi, 'kuliner') && table_has_column($koneksi, 'kuliner', 'id_kuliner');

$destinasi_name_col = $has_destinasi ? pick_column($koneksi, 'destinasi', ['nama', 'nama_destinasi']) : null;
$event_name_col = $has_event ? pick_column($koneksi, 'event', ['judul', 'nama_event', 'nama']) : null;
$kuliner_name_col = $has_kuliner ? pick_column($koneksi, 'kuliner', ['nama', 'nama_kuliner']) : null;

$destinasi_select = $destinasi_name_col ? "d.`$destinasi_name_col` AS nama_destinasi" : "NULL AS nama_destinasi";
$event_select = $event_name_col ? "e.`$event_name_col` AS nama_event" : "NULL AS nama_event";
$kuliner_select = $kuliner_name_col ? "k.`$kuliner_name_col` AS nama_kuliner" : "NULL AS nama_kuliner";

$join_destinasi = $has_destinasi ? "LEFT JOIN destinasi d ON uls.jenis_target = 'destinasi' AND uls.id_target = d.id_destinasi" : "";
$join_event = $has_event ? "LEFT JOIN event e ON uls.jenis_target = 'event' AND uls.id_target = e.id_event" : "";
$join_kuliner = $has_kuliner ? "LEFT JOIN kuliner k ON uls.jenis_target = 'kuliner' AND uls.id_target = k.id_kuliner" : "";

$sql = "
  SELECT
    uls.id_ulasan,
    uls.jenis_target,
    uls.id_target,
    uls.rating,
    uls.komentar,
    uls.status,
    uls.dibuat_pada,
    p.nama_lengkap,
    p.username,
    $destinasi_select,
    $event_select,
    $kuliner_select
  FROM ulasan uls
  LEFT JOIN pengguna p ON uls.id_pengguna = p.id_pengguna
  $join_destinasi
  $join_event
  $join_kuliner
  WHERE uls.jenis_target IN ('destinasi','event','kuliner')
";
$types = "";
$params = [];
if ($filter_status !== 'all') {
  $sql .= " AND uls.status = ?";
  $types .= "s";
  $params[] = $filter_status;
}
if ($q !== "") {
  $like = "%" . $q . "%";
  $search_parts = ["uls.komentar LIKE ?", "p.nama_lengkap LIKE ?", "p.username LIKE ?"];
  $types .= "sss";
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  if ($destinasi_name_col && $has_destinasi) {
    $search_parts[] = "d.`$destinasi_name_col` LIKE ?";
    $types .= "s";
    $params[] = $like;
  }
  if ($event_name_col && $has_event) {
    $search_parts[] = "e.`$event_name_col` LIKE ?";
    $types .= "s";
    $params[] = $like;
  }
  if ($kuliner_name_col && $has_kuliner) {
    $search_parts[] = "k.`$kuliner_name_col` LIKE ?";
    $types .= "s";
    $params[] = $like;
  }
  $sql .= " AND (" . implode(" OR ", $search_parts) . ")";
}
$sql .= " ORDER BY uls.dibuat_pada DESC";

$stmt = $koneksi->prepare($sql);
if ($types !== "") {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$data = $stmt->get_result();
?>

<?php require_once __DIR__ . "/partials/header.php"; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin_crud.css">

<?php if ($flash): ?><div class="notice"><?= h($flash) ?></div><?php endif; ?>

<div class="metric-cards">
  <div class="metric-card">
    <div class="label">Total Ulasan</div>
    <div class="value"><?= h($metric['total']) ?></div>
    <div class="muted">Destinasi, event, kuliner</div>
  </div>
  <div class="metric-card">
    <div class="label">Pending</div>
    <div class="value"><?= h($metric['pending']) ?></div>
    <div class="muted">Status sembunyi</div>
  </div>
  <div class="metric-card">
    <div class="label">Tampil</div>
    <div class="value"><?= h($metric['tampil']) ?></div>
    <div class="muted">Sudah disetujui</div>
  </div>
  <div class="metric-card">
    <div class="label">Rata-rata Rating</div>
    <div class="value"><?= $metric['avg'] !== null ? h(number_format((float)$metric['avg'], 2)) : "-" ?></div>
    <div class="muted">Hanya ulasan tampil</div>
  </div>
</div>

<div class="card">
  <div class="toolbar">
    <div>
      <h1 class="page-title" style="margin:0">Moderasi Ulasan</h1>
      <p class="page-sub">Setujui atau sembunyikan ulasan sebelum tampil</p>
    </div>
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <select name="status">
        <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>Semua</option>
        <option value="sembunyi" <?= $filter_status === 'sembunyi' ? 'selected' : '' ?>>Pending (Sembunyi)</option>
        <option value="tampil" <?= $filter_status === 'tampil' ? 'selected' : '' ?>>Tampil (Disetujui)</option>
      </select>
      <input class="search" type="text" name="q" placeholder="Cari komentar/nama..." value="<?= h($q) ?>">
      <button class="btn secondary" type="submit">Filter</button>
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
          <th>Komentar</th>
          <th>Waktu</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($data && $data->num_rows > 0): ?>
          <?php while($row = $data->fetch_assoc()): ?>
            <?php
              $nama_user = $row['nama_lengkap'] ?? $row['username'] ?? '-';
              $target_nama = $row['nama_destinasi'] ?? $row['nama_event'] ?? $row['nama_kuliner'] ?? '';
              if ($target_nama === '' || $target_nama === null) {
                $target_nama = '(ID: ' . ($row['id_target'] ?? '-') . ')';
              }
              $jenis = $row['jenis_target'] ?? '-';
              $status = $row['status'] ?? '-';
            ?>
            <tr>
              <td><?= h($row['id_ulasan']) ?></td>
              <td><?= h($nama_user) ?></td>
              <td><?= h($target_nama) ?></td>
              <td><?= h($jenis) ?></td>
              <td><?= h($row['rating'] ?? '-') ?></td>
              <td><?= h($status) ?></td>
              <td style="max-width:320px"><small><?= h($row['komentar'] ?? '-') ?></small></td>
              <td><small><?= h($row['dibuat_pada'] ?? '-') ?></small></td>
              <td class="actions">
                <?php if ($status === 'sembunyi'): ?>
                  <form method="POST">
                    <input type="hidden" name="id_ulasan" value="<?= h($row['id_ulasan']) ?>">
                    <input type="hidden" name="action" value="approve">
                    <button class="btn-sm primary" type="submit">Setujui</button>
                  </form>
                <?php else: ?>
                  <form method="POST">
                    <input type="hidden" name="id_ulasan" value="<?= h($row['id_ulasan']) ?>">
                    <input type="hidden" name="action" value="reject">
                    <button class="btn-sm danger" type="submit">Sembunyikan</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="9"><small>Belum ada ulasan sesuai filter.</small></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
<!--
Manual test:
1) Buka /admin/ulasan.php => tabel menampilkan semua ulasan.
2) Pilih filter “Tampil (Disetujui)” => hanya tampil.
3) Pilih filter “Pending (Sembunyi)” => hanya sembunyi.
4) Klik tombol “Sembunyikan” pada ulasan tampil => status berubah, stats & tabel update.
5) Cari kata di komentar/target => hasil terfilter.
-->
