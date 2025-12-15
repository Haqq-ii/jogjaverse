<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/_crud_helper.php";
wajib_admin();

$table = "kuliner";

[$cols, $pk] = describe_table($koneksi, $table);
if (!$pk) die("PK tidak ditemukan di tabel $table");

$colNames = array_map(fn($c) => $c['Field'], $cols);

$colNama     = pick_col($colNames, ["nama_kuliner", "nama", "judul"]);
$colLok      = pick_col($colNames, ["alamat", "lokasi", "kecamatan", "kota", "kabupaten"]);
$colHarga    = pick_col($colNames, ["rentang_harga", "harga", "harga_rata", "harga_mulai"]);
$colFoto     = pick_col($colNames, ["gambar_sampul_url","foto_url", "gambar_url", "thumbnail", "image_url", "cover"]);

$flash = $_SESSION['flash'] ?? "";
unset($_SESSION['flash']);

$q = trim($_GET['q'] ?? "");

// handle delete
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];
  $stmt = $koneksi->prepare("DELETE FROM `$table` WHERE `$pk` = ? LIMIT 1");
  $stmt->bind_param("s", $id);
  $stmt->execute();
  $_SESSION['flash'] = "Data berhasil dihapus.";
  header("Location: " . BASE_URL . "/admin/kuliner.php");
  exit();
}

// handle save (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? "";

$skip = [$pk, "dibuat_pada", "diubah_pada"];
$payload = [];
foreach ($cols as $c) {
  $f = $c['Field'];
  if (in_array($f, $skip, true)) continue;
  if (isset($_POST[$f])) $payload[$f] = $_POST[$f] === "" ? null : $_POST[$f];
}
if (array_key_exists("status", $payload) && $payload["status"] === null) {
  $payload["status"] = "draft";
}

// upload gambar sampul
if (isset($_FILES['gambar_sampul_url']) && is_uploaded_file($_FILES['gambar_sampul_url']['tmp_name'])) {
  $uploadDir = __DIR__ . '/../assets/uploads';
  if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
  }
  $ext = pathinfo($_FILES['gambar_sampul_url']['name'], PATHINFO_EXTENSION);
  $safeName = uniqid('kul_', true) . "." . strtolower($ext);
  $targetPath = $uploadDir . '/' . $safeName;
  if (move_uploaded_file($_FILES['gambar_sampul_url']['tmp_name'], $targetPath)) {
    $payload['gambar_sampul_url'] = BASE_URL . "/assets/uploads/" . $safeName;
  }
}

  if ($id !== "") {
    $set = [];
    $types = "";
    $vals = [];
    foreach ($payload as $k => $v) {
      $set[] = "`$k` = ?";
      $types .= "s";
      $vals[] = $v;
    }
    $types .= "s";
    $vals[] = $id;

    $sql = "UPDATE `$table` SET " . implode(", ", $set) . " WHERE `$pk` = ? LIMIT 1";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param($types, ...$vals);
    $stmt->execute();

    $_SESSION['flash'] = "Data berhasil diupdate.";
  } else {
    $colsIns = array_keys($payload);
    $ph = array_fill(0, count($colsIns), "?");
    $types = str_repeat("s", count($colsIns));
    $vals = array_values($payload);

    $sql = "INSERT INTO `$table` (`" . implode("`,`", $colsIns) . "`) VALUES (" . implode(",", $ph) . ")";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param($types, ...$vals);
    $stmt->execute();

    $_SESSION['flash'] = "Data berhasil ditambahkan.";
  }

  header("Location: " . BASE_URL . "/admin/kuliner.php");
  exit();
}

// edit fetch
$edit = null;
if (isset($_GET['edit'])) {
  $id = $_GET['edit'];
  $stmt = $koneksi->prepare("SELECT * FROM `$table` WHERE `$pk` = ? LIMIT 1");
  $stmt->bind_param("s", $id);
  $stmt->execute();
  $edit = $stmt->get_result()->fetch_assoc();
}

$data = fetch_rows($koneksi, $table, $pk, $q, $cols);
$destinasiOptions = fetch_options_by_table(
  $koneksi,
  ["destinasi"],
  ["nama_destinasi", "nama", "judul"]
);
$kategoriOptions = fetch_options_by_table(
  $koneksi,
  ["kategori", "kategori_kuliner", "kategori_destinasi"],
  ["nama", "nama_kategori", "judul", "label"]
);
$penggunaOptions = fetch_options_by_table(
  $koneksi,
  ["pengguna"],
  ["nama_lengkap", "username", "email", "nama"]
);
?>

<?php require_once __DIR__ . "/partials/header.php"; ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin_crud.css">

<div class="card">
  <div>
    <h1 class="page-title">Kelola Kuliner</h1>
    <p class="page-sub">Tambah / edit kuliner khas Jogja</p>
  </div>

  <?php if ($flash): ?><div class="notice"><?= h($flash) ?></div><?php endif; ?>

  <div class="toolbar">
    <div class="left">
      <button class="btn" onclick="openModal()">+ Tambah Kuliner</button>
    </div>
    <form method="GET" style="display:flex;gap:10px;align-items:center">
      <input class="search" type="text" name="q" placeholder="Cari..." value="<?= h($q) ?>">
      <button class="btn secondary" type="submit">Cari</button>
      <a class="btn secondary" href="<?= BASE_URL ?>/admin/kuliner.php">Reset</a>
    </form>
  </div>

  <div class="table-wrap">
    <table class="table2">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Alamat</th>
          <th>Rentang Harga</th>
          <th>Status</th>
          <th>Foto</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php while($row = $data->fetch_assoc()): ?>
        <tr>
          <td><?= h($row[$pk]) ?></td>
          <td><b><?= h($colNama ? ($row[$colNama] ?? '-') : '-') ?></b></td>
          <td><?= h($colLok ? ($row[$colLok] ?? '-') : '-') ?></td>
          <td><?= h($colHarga ? ($row[$colHarga] ?? '-') : '-') ?></td>
          <td><span class="pill"><?= h($row['status'] ?? '-') ?></span></td>
          <td>
            <?php if ($colFoto && !empty($row[$colFoto])): ?>
              <a href="<?= h($row[$colFoto]) ?>" target="_blank">Lihat</a>
            <?php else: ?>
              <small>-</small>
            <?php endif; ?>
          </td>
          <td class="actions">
            <a class="btn-sm gray" href="<?= BASE_URL ?>/admin/kuliner.php?edit=<?= h($row[$pk]) ?>">Edit</a>
            <a class="btn-sm danger" onclick="return confirm('Hapus kuliner ini?')" href="<?= BASE_URL ?>/admin/kuliner.php?hapus=<?= h($row[$pk]) ?>">Hapus</a>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL FORM -->
<div class="modal" id="modal">
  <div class="modal-card">
    <div class="modal-head">
      <h3><?= $edit ? "Edit Kuliner" : "Tambah Kuliner" ?></h3>
      <button class="close" onclick="closeModal()">Tutup</button>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <?php if ($edit): ?>
        <input type="hidden" name="id" value="<?= h($edit[$pk]) ?>">
      <?php endif; ?>

      <?php $statusVal = $edit['status'] ?? 'draft'; ?>
      <div class="form-grid">
        <div>
          <label>Destinasi</label>
          <?php
            $selectedDestinasi = $edit['id_destinasi'] ?? '';
            $destinasiHasSelected = $selectedDestinasi !== '' && in_array(
              (string)$selectedDestinasi,
              array_map(fn($o) => (string)$o['id'], $destinasiOptions),
              true
            );
          ?>
          <?php if (count($destinasiOptions) > 0): ?>
            <select name="id_destinasi">
              <option value="">-- Pilih destinasi --</option>
              <?php foreach ($destinasiOptions as $opt): ?>
                <option value="<?= h($opt['id']) ?>" <?= ((string)$selectedDestinasi === (string)$opt['id']) ? 'selected' : '' ?>>
                  <?= h($opt['label']) ?>
                </option>
              <?php endforeach; ?>
              <?php if ($selectedDestinasi !== "" && !$destinasiHasSelected): ?>
                <option value="<?= h($selectedDestinasi) ?>" selected>ID: <?= h($selectedDestinasi) ?> (tidak ditemukan)</option>
              <?php endif; ?>
            </select>
          <?php else: ?>
            <input type="number" name="id_destinasi" value="<?= h($selectedDestinasi) ?>" placeholder="Belum ada destinasi, isi ID">
          <?php endif; ?>
        </div>
        <div>
          <label>Kategori</label>
          <?php
            $selectedKategori = $edit['id_kategori'] ?? '';
            $kategoriHasSelected = $selectedKategori !== '' && in_array(
              (string)$selectedKategori,
              array_map(fn($o) => (string)$o['id'], $kategoriOptions),
              true
            );
          ?>
          <?php if (count($kategoriOptions) > 0): ?>
            <select name="id_kategori">
              <option value="">-- Pilih kategori --</option>
              <?php foreach ($kategoriOptions as $opt): ?>
                <option value="<?= h($opt['id']) ?>" <?= ((string)$selectedKategori === (string)$opt['id']) ? 'selected' : '' ?>>
                  <?= h($opt['label']) ?>
                </option>
              <?php endforeach; ?>
              <?php if ($selectedKategori !== "" && !$kategoriHasSelected): ?>
                <option value="<?= h($selectedKategori) ?>" selected>ID: <?= h($selectedKategori) ?> (tidak ditemukan)</option>
              <?php endif; ?>
            </select>
          <?php else: ?>
            <input type="number" name="id_kategori" value="<?= h($selectedKategori) ?>" placeholder="Belum ada kategori, isi ID">
          <?php endif; ?>
        </div>
        <div class="full">
          <label>nama *</label>
          <input type="text" name="nama" required value="<?= h($edit['nama'] ?? '') ?>">
        </div>
        <div class="full">
          <label>deskripsi</label>
          <textarea name="deskripsi"><?= h($edit['deskripsi'] ?? '') ?></textarea>
        </div>
        <div class="full">
          <label>alamat</label>
          <input type="text" name="alamat" value="<?= h($edit['alamat'] ?? '') ?>">
        </div>
        <div>
          <label>latitude</label>
          <input type="number" step="0.000001" name="latitude" value="<?= h($edit['latitude'] ?? '') ?>">
        </div>
        <div>
          <label>longitude</label>
          <input type="number" step="0.000001" name="longitude" value="<?= h($edit['longitude'] ?? '') ?>">
        </div>
        <div>
          <label>rentang_harga</label>
          <input type="text" name="rentang_harga" value="<?= h($edit['rentang_harga'] ?? '') ?>">
        </div>
        <div>
          <label>jam_operasional</label>
          <input type="text" name="jam_operasional" value="<?= h($edit['jam_operasional'] ?? '') ?>">
        </div>
        <div>
          <label>nomor_kontak</label>
          <input type="text" name="nomor_kontak" value="<?= h($edit['nomor_kontak'] ?? '') ?>">
        </div>
        <div class="full">
          <label>gambar_sampul_url</label>
          <?php if (!empty($edit['gambar_sampul_url'])): ?>
            <div style="margin-bottom:6px"><a href="<?= h($edit['gambar_sampul_url']) ?>" target="_blank">Lihat gambar saat ini</a></div>
          <?php endif; ?>
          <input type="file" accept="image/*" name="gambar_sampul_url">
        </div>
        <div>
          <label>status</label>
          <select name="status">
            <option value="draft" <?= $statusVal === 'draft' ? 'selected' : '' ?>>draft</option>
            <option value="publish" <?= $statusVal === 'publish' ? 'selected' : '' ?>>publish</option>
            <option value="arsip" <?= $statusVal === 'arsip' ? 'selected' : '' ?>>arsip</option>
          </select>
        </div>
        <div>
          <label>Dibuat oleh</label>
          <?php
            $selectedPembuat = $edit['dibuat_oleh'] ?? '';
            $penggunaHasSelected = $selectedPembuat !== '' && in_array(
              (string)$selectedPembuat,
              array_map(fn($o) => (string)$o['id'], $penggunaOptions),
              true
            );
          ?>
          <?php if (count($penggunaOptions) > 0): ?>
            <select name="dibuat_oleh">
              <option value="">-- Pilih pengguna --</option>
              <?php foreach ($penggunaOptions as $opt): ?>
                <option value="<?= h($opt['id']) ?>" <?= ((string)$selectedPembuat === (string)$opt['id']) ? 'selected' : '' ?>>
                  <?= h($opt['label']) ?>
                </option>
              <?php endforeach; ?>
              <?php if ($selectedPembuat !== "" && !$penggunaHasSelected): ?>
                <option value="<?= h($selectedPembuat) ?>" selected>ID: <?= h($selectedPembuat) ?> (tidak ditemukan)</option>
              <?php endif; ?>
            </select>
          <?php else: ?>
            <input type="number" name="dibuat_oleh" value="<?= h($selectedPembuat) ?>" placeholder="Masukkan ID pembuat">
          <?php endif; ?>
        </div>
        <div>
          <label>dibuat_pada</label>
          <input type="text" value="<?= h($edit['dibuat_pada'] ?? '') ?>" disabled>
        </div>
        <div>
          <label>diubah_pada</label>
          <input type="text" value="<?= h($edit['diubah_pada'] ?? '') ?>" disabled>
        </div>
      </div>

      <div style="margin-top:12px;display:flex;gap:10px;justify-content:flex-end">
        <button class="btn" type="submit">Simpan</button>
        <button class="btn secondary" type="button" onclick="closeModal()">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
  const modal = document.getElementById('modal');
  function openModal(){ modal.classList.add('show'); }
  function closeModal(){ modal.classList.remove('show'); }
  <?php if ($edit): ?>
    openModal();
  <?php endif; ?>
</script>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
