<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/_crud_helper.php";
wajib_admin();

$flash = $_SESSION['flash'] ?? "";
unset($_SESSION['flash']);

$allowed_status = ['all', 'baru', 'diproses', 'selesai', 'ditolak'];
$filter_status = $_GET['status'] ?? 'baru';
if (!in_array($filter_status, $allowed_status, true)) {
  $filter_status = 'baru';
}

$q = trim($_GET['q'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
  $id_pelaporan = (int)($_POST['id_pelaporan'] ?? 0);
  $status_baru = $_POST['status'] ?? '';
  if ($id_pelaporan > 0 && in_array($status_baru, $allowed_status, true) && $status_baru !== 'all') {
    $stmt = $koneksi->prepare("UPDATE pelaporan SET status = ? WHERE id_pelaporan = ? LIMIT 1");
    if ($stmt) {
      $stmt->bind_param("si", $status_baru, $id_pelaporan);
      $stmt->execute();
      $stmt->close();
      $_SESSION['flash'] = "Status pelaporan berhasil diperbarui.";
    }
  }
  $qs = http_build_query(['status' => $filter_status, 'q' => $q]);
  header("Location: " . BASE_URL . "/admin/pelaporan.php" . ($qs ? "?" . $qs : ""));
  exit();
}

$metric = [
  'total' => 0,
  'baru' => 0,
  'diproses' => 0,
  'selesai' => 0,
  'ditolak' => 0,
];
$stmt = $koneksi->prepare("
  SELECT
    COUNT(*) as total,
    SUM(status = 'baru') as baru,
    SUM(status = 'diproses') as diproses,
    SUM(status = 'selesai') as selesai,
    SUM(status = 'ditolak') as ditolak
  FROM pelaporan
");
if ($stmt) {
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $metric['total'] = (int)($row['total'] ?? 0);
  $metric['baru'] = (int)($row['baru'] ?? 0);
  $metric['diproses'] = (int)($row['diproses'] ?? 0);
  $metric['selesai'] = (int)($row['selesai'] ?? 0);
  $metric['ditolak'] = (int)($row['ditolak'] ?? 0);
  $stmt->close();
}

$sql = "
  SELECT
    p.id_pelaporan,
    p.jenis_target,
    p.id_target,
    p.judul,
    p.deskripsi,
    p.status,
    p.dibuat_pada,
    u.nama_lengkap,
    u.username
  FROM pelaporan p
  LEFT JOIN pengguna u ON p.id_pengguna = u.id_pengguna
  WHERE 1=1
";
$types = "";
$params = [];
if ($filter_status !== 'all') {
  $sql .= " AND p.status = ?";
  $types .= "s";
  $params[] = $filter_status;
}
if ($q !== '') {
  $like = "%" . $q . "%";
  $sql .= " AND (p.judul LIKE ? OR p.deskripsi LIKE ? OR u.nama_lengkap LIKE ? OR u.username LIKE ?)";
  $types .= "ssss";
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
}
$sql .= " ORDER BY p.dibuat_pada DESC";

$stmt = $koneksi->prepare($sql);
if ($types !== '') {
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
    <div class="label">Total Pelaporan</div>
    <div class="value"><?= h($metric['total']) ?></div>
    <div class="muted">Semua laporan</div>
  </div>
  <div class="metric-card">
    <div class="label">Baru</div>
    <div class="value"><?= h($metric['baru']) ?></div>
    <div class="muted">Status baru</div>
  </div>
  <div class="metric-card">
    <div class="label">Diproses</div>
    <div class="value"><?= h($metric['diproses']) ?></div>
    <div class="muted">Dalam penanganan</div>
  </div>
  <div class="metric-card">
    <div class="label">Selesai</div>
    <div class="value"><?= h($metric['selesai']) ?></div>
    <div class="muted">Tuntas</div>
  </div>
  <div class="metric-card">
    <div class="label">Ditolak</div>
    <div class="value"><?= h($metric['ditolak']) ?></div>
    <div class="muted">Tidak valid</div>
  </div>
</div>

<div class="card">
  <div class="toolbar">
    <div>
      <h1 class="page-title" style="margin:0">Pelaporan Pengguna</h1>
      <p class="page-sub">Kelola laporan masuk dari pengguna</p>
    </div>
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
      <select name="status">
        <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>Semua</option>
        <option value="baru" <?= $filter_status === 'baru' ? 'selected' : '' ?>>Baru</option>
        <option value="diproses" <?= $filter_status === 'diproses' ? 'selected' : '' ?>>Diproses</option>
        <option value="selesai" <?= $filter_status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
        <option value="ditolak" <?= $filter_status === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
      </select>
      <input class="search" type="text" name="q" placeholder="Cari judul/username..." value="<?= h($q) ?>">
      <button class="btn secondary" type="submit">Filter</button>
      <a class="btn secondary" href="<?= BASE_URL ?>/admin/pelaporan.php">Reset</a>
    </form>
  </div>

  <div class="table-wrap">
    <table class="table2">
      <thead>
        <tr>
          <th>ID</th>
          <th>Pengguna</th>
          <th>Target</th>
          <th>Judul</th>
          <th>Deskripsi</th>
          <th>Status</th>
          <th>Waktu</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($data && $data->num_rows > 0): ?>
          <?php while ($row = $data->fetch_assoc()): ?>
            <?php
              $nama_user = $row['nama_lengkap'] ?? $row['username'] ?? '-';
              $target = ($row['jenis_target'] ?? '-') . ' #' . ($row['id_target'] ?? '-');
              $status = $row['status'] ?? '-';
            ?>
            <tr>
              <td><?= h($row['id_pelaporan']) ?></td>
              <td><?= h($nama_user) ?></td>
              <td><?= h($target) ?></td>
              <td><?= h($row['judul'] ?? '-') ?></td>
              <td style="max-width:320px"><small><?= h($row['deskripsi'] ?? '-') ?></small></td>
              <td><?= h($status) ?></td>
              <td><small><?= h($row['dibuat_pada'] ?? '-') ?></small></td>
              <td class="actions">
                <form method="POST" style="display:flex;gap:6px;align-items:center;">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="id_pelaporan" value="<?= h($row['id_pelaporan']) ?>">
                  <select name="status">
                    <option value="baru" <?= $status === 'baru' ? 'selected' : '' ?>>Baru</option>
                    <option value="diproses" <?= $status === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                    <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="ditolak" <?= $status === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                  </select>
                  <button class="btn-sm primary" type="submit">Update</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8"><small>Belum ada pelaporan sesuai filter.</small></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
