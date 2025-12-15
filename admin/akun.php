<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/_crud_helper.php";
wajib_admin();

function count_pengguna(mysqli $koneksi, string $where = "", string $types = "", array $params = []): int {
  $sql = "SELECT COUNT(*) as total FROM pengguna " . $where;
  $stmt = $koneksi->prepare($sql);
  if (!$stmt) return 0;
  if ($params) {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  return (int)($row['total'] ?? 0);
}

[$cols, $pk] = describe_table($koneksi, "pengguna");
if (!$pk) die("Tabel pengguna belum siap. Silakan buat tabel pengguna terlebih dahulu.");
$colNames = array_map(fn($c) => $c['Field'], $cols);
$orderCol = guess_time_col($colNames) ?: $pk;

$totalUser   = count_pengguna($koneksi);
$totalAktif  = count_pengguna($koneksi, "WHERE status_aktif = 1");
$totalAdmin  = count_pengguna($koneksi, "WHERE peran = 'admin'");
$totalNonAktif = $totalUser - $totalAktif;

$q = trim($_GET['q'] ?? "");
$params = [];
$types = "";
$sql = "SELECT id_pengguna, nama_lengkap, username, email, peran, status_aktif";
if ($orderCol) $sql .= ", `$orderCol`";
$sql .= " FROM pengguna";

if ($q !== "") {
  $sql .= " WHERE nama_lengkap LIKE ? OR email LIKE ? OR username LIKE ?";
  $like = "%" . $q . "%";
  $params = [$like, $like, $like];
  $types = "sss";
}

if ($orderCol) {
  $sql .= " ORDER BY `$orderCol` DESC";
}

$stmt = $koneksi->prepare($sql);
if ($params) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();
?>

<?php require_once __DIR__ . "/partials/header.php"; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin_crud.css">

<div class="metric-cards">
  <div class="metric-card">
    <div class="label">Total Akun</div>
    <div class="value"><?= h($totalUser) ?></div>
    <div class="muted">Semua pengguna terdaftar</div>
  </div>
  <div class="metric-card">
    <div class="label">Aktif</div>
    <div class="value"><?= h($totalAktif) ?></div>
    <div class="muted">Status aktif = 1</div>
  </div>
  <div class="metric-card">
    <div class="label">Admin</div>
    <div class="value"><?= h($totalAdmin) ?></div>
    <div class="muted">Peran admin</div>
  </div>
  <div class="metric-card">
    <div class="label">Non-aktif</div>
    <div class="value"><?= h($totalNonAktif) ?></div>
    <div class="muted">Perlu verifikasi</div>
  </div>
</div>

<div class="card">
  <div class="toolbar">
    <div>
      <h1 class="page-title" style="margin:0">Daftar Akun Pengguna</h1>
      <p class="page-sub">Pantau jumlah akun & status aktivasi</p>
    </div>
    <form method="GET" style="display:flex;gap:10px;align-items:center">
      <input class="search" type="text" name="q" placeholder="Cari nama/email/username..." value="<?= h($q) ?>">
      <button class="btn secondary" type="submit">Cari</button>
      <a class="btn secondary" href="<?= BASE_URL ?>/admin/akun.php">Reset</a>
    </form>
  </div>

  <div class="table-wrap">
    <table class="table2">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Email</th>
          <th>Username</th>
          <th>Peran</th>
          <th>Status</th>
          <?php if ($orderCol): ?><th>Waktu</th><?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $users->fetch_assoc()): ?>
          <?php $aktif = ($row['status_aktif'] ?? null) == 1; ?>
          <tr>
            <td><?= h($row['id_pengguna']) ?></td>
            <td><b><?= h($row['nama_lengkap'] ?? '-') ?></b></td>
            <td><?= h($row['email'] ?? '-') ?></td>
            <td><?= h($row['username'] ?? '-') ?></td>
            <td><span class="pill"><?= h($row['peran'] ?? '-') ?></span></td>
            <td>
              <span class="status-dot">
                <span class="<?= $aktif ? '' : 'gray' ?>"></span>
                <?= $aktif ? "Aktif" : "Non-aktif" ?>
              </span>
            </td>
            <?php if ($orderCol): ?>
              <td><small><?= h($row[$orderCol] ?? '-') ?></small></td>
            <?php endif; ?>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
