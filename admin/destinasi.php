<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/../config/upload.php";
require_once __DIR__ . "/_crud_helper.php";
wajib_admin();

$table = "destinasi";
$galeriTable = "galeri";
$galeriJenis = "destinasi";
$detailPrefix = "dest_detail_";

[$cols, $pk] = describe_table($koneksi, $table);
if (!$pk) die("PK tidak ditemukan di tabel $table");

$colNames = array_map(fn($c)=>$c['Field'], $cols);

// kolom umum untuk destinasi (kalau ada)
$colNama  = pick_col($colNames, ["nama_destinasi","nama","judul","nama_tempat"]);
$colLok   = pick_col($colNames, ["lokasi","alamat","kecamatan","kabupaten","kota"]);
$colDesk  = pick_col($colNames, ["deskripsi","keterangan","ringkasan"]);
$colFoto  = pick_col($colNames, ["gambar_sampul_url","foto_url","gambar_url","thumbnail","image_url"]);
$colLat   = pick_col($colNames, ["latitude","lat"]);
$colLng   = pick_col($colNames, ["longitude","lng","lon"]);

$flash = $_SESSION['flash'] ?? "";
unset($_SESSION['flash']);

$q = trim($_GET['q'] ?? "");

// handle delete
if (isset($_GET['hapus'])) {
  $id = $_GET['hapus'];

  $stmtGal = $koneksi->prepare("SELECT gambar_url FROM `$galeriTable` WHERE jenis_target = ? AND id_target = ?");
  if ($stmtGal) {
    $stmtGal->bind_param("ss", $galeriJenis, $id);
    $stmtGal->execute();
    $resGal = $stmtGal->get_result();
    if ($resGal) {
      while ($rowGal = $resGal->fetch_assoc()) {
        delete_uploaded_image($rowGal['gambar_url'] ?? '');
      }
    }
    $stmtGal->close();
  }
  $stmtDelGal = $koneksi->prepare("DELETE FROM `$galeriTable` WHERE jenis_target = ? AND id_target = ?");
  if ($stmtDelGal) {
    $stmtDelGal->bind_param("ss", $galeriJenis, $id);
    $stmtDelGal->execute();
    $stmtDelGal->close();
  }

  $stmt = $koneksi->prepare("DELETE FROM `$table` WHERE `$pk` = ? LIMIT 1");
  $stmt->bind_param("s", $id);
  $stmt->execute();
  $_SESSION['flash'] = "Data berhasil dihapus.";
  header("Location: " . BASE_URL . "/admin/destinasi.php");
  exit();
}


// handle save (add/edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? "";

  // ambil field yang ada di tabel, tapi jangan pk autoincrement atau timestamp auto
  $skip = [$pk, "dibuat_pada", "diubah_pada", "website_url"];
  $payload = [];
  foreach ($cols as $c) {
    $f = $c['Field'];
    if (in_array($f, $skip, true)) continue;
    if (isset($_POST[$f])) $payload[$f] = $_POST[$f] === "" ? null : $_POST[$f];
  }
  if (array_key_exists("status", $payload) && $payload["status"] === null) {
    $payload["status"] = "draft";
  }
  if (table_has_column($koneksi, $table, 'slug')) {
    $nameSource = '';
    foreach (['nama', 'nama_destinasi', 'judul', 'nama_tempat', 'title'] as $key) {
      if (isset($_POST[$key]) && trim((string)$_POST[$key]) !== '') {
        $nameSource = trim((string)$_POST[$key]);
        break;
      }
    }
    if ($nameSource !== '') {
      $baseSlug = slugify($nameSource);
      $payload['slug'] = generate_unique_slug(
        $koneksi,
        $table,
        'slug',
        $baseSlug,
        $id !== "" ? (string)$id : null,
        $pk
      );
    }
  }

  // upload gambar
  if (isset($_FILES['gambar_sampul_url']) && is_uploaded_file($_FILES['gambar_sampul_url']['tmp_name'])) {
    $uploadDir = __DIR__ . '/../assets/uploads';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }
    $ext = pathinfo($_FILES['gambar_sampul_url']['name'], PATHINFO_EXTENSION);
    $safeName = uniqid('dest_', true) . "." . strtolower($ext);
    $targetPath = $uploadDir . '/' . $safeName;
    if (move_uploaded_file($_FILES['gambar_sampul_url']['tmp_name'], $targetPath)) {
      $payload['gambar_sampul_url'] = BASE_URL . "/assets/uploads/" . $safeName;
    }
  }
$refId = "";
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
  $refId = (string)$id;
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
  $refId = (string)$koneksi->insert_id;
}

if ($refId !== "" && !empty($_FILES['detail_gambar']) && isset($_FILES['detail_gambar']['name']) && is_array($_FILES['detail_gambar']['name'])) {
  $hasDetail = false;
  foreach ($_FILES['detail_gambar']['name'] as $n) {
    if ($n !== '') {
      $hasDetail = true;
      break;
    }
  }
  if ($hasDetail) {
    $urutStart = 0;
    $stmtUrut = $koneksi->prepare("SELECT COALESCE(MAX(urutan), 0) AS max_urut FROM `$galeriTable` WHERE jenis_target = ? AND id_target = ?");
    if ($stmtUrut) {
      $stmtUrut->bind_param("ss", $galeriJenis, $refId);
      $stmtUrut->execute();
      $rowUrut = $stmtUrut->get_result()->fetch_assoc();
      $urutStart = (int)($rowUrut['max_urut'] ?? 0);
      $stmtUrut->close();
    }

    $stmtIns = $koneksi->prepare("INSERT INTO `$galeriTable` (jenis_target, id_target, gambar_url, urutan) VALUES (?, ?, ?, ?)");
    if ($stmtIns) {
      $pos = 0;
      $total = count($_FILES['detail_gambar']['name']);
      for ($i = 0; $i < $total; $i++) {
        if (($_FILES['detail_gambar']['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
          continue;
        }
        $file = [
          'name' => $_FILES['detail_gambar']['name'][$i] ?? '',
          'tmp_name' => $_FILES['detail_gambar']['tmp_name'][$i] ?? '',
          'error' => $_FILES['detail_gambar']['error'][$i] ?? UPLOAD_ERR_NO_FILE,
        ];
        $url = upload_image($file, $detailPrefix);
        if (!$url) {
          continue;
        }
        $urutan = $urutStart + $pos + 1;
        $stmtIns->bind_param("sssi", $galeriJenis, $refId, $url, $urutan);
        $stmtIns->execute();
        $pos++;
      }
      $stmtIns->close();
    }
  }
}

header("Location: " . BASE_URL . "/admin/destinasi.php");
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

$detailImages = [];
if ($edit) {
  $stmtGal = $koneksi->prepare("SELECT id_galeri, gambar_url, keterangan, urutan FROM `$galeriTable` WHERE jenis_target = ? AND id_target = ? ORDER BY urutan ASC, id_galeri ASC");
  if ($stmtGal) {
    $stmtGal->bind_param("ss", $galeriJenis, $edit[$pk]);
    $stmtGal->execute();
    $resGal = $stmtGal->get_result();
    if ($resGal) {
      while ($rowGal = $resGal->fetch_assoc()) {
        $detailImages[] = $rowGal;
      }
    }
    $stmtGal->close();
  }
}


$data = fetch_rows($koneksi, $table, $pk, $q, $cols);
$kategoriOptions = fetch_options_by_table(
  $koneksi,
  ["kategori", "kategori_destinasi"],
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
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/leaflet.css">
<style>
  .map-box {
    width: 100%;
    height: 320px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    margin: 10px 0 14px;
  }
  .map-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 6px;
  }
  .map-actions button {
    padding: 8px 10px;
    border-radius: 10px;
    border: 1px solid #e4e6f0;
    background: #fff;
    cursor: pointer;
  }
  .search-location {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #e4e6f0;
  }
  .search-results {
    display: none;
    border: 1px solid #e4e6f0;
    border-radius: 10px;
    margin-top: 6px;
    background: #fff;
    max-height: 180px;
    overflow: auto;
  }
  .search-result-item {
    width: 100%;
    text-align: left;
    padding: 8px 10px;
    border: 0;
    background: #fff;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
  }
  .search-result-item:last-child {
    border-bottom: 0;
  }
  .search-result-item:hover {
    background: #f6f7fb;
  }
  .search-hint {
    font-size: 12px;
    color: #777;
    margin-top: 4px;
  }
</style>

<div class="card">
  <div>
    <h1 class="page-title">Kelola Destinasi</h1>
    <p class="page-sub">Tambah / edit destinasi yang akan tampil di website wisatawan</p>
  </div>

  <?php if ($flash): ?><div class="notice"><?= h($flash) ?></div><?php endif; ?>

  <div class="toolbar">
    <div class="left">
      <button class="btn" onclick="openModal()">+ Tambah Destinasi</button>
    </div>
    <form method="GET" style="display:flex;gap:10px;align-items:center">
      <input class="search" type="text" name="q" placeholder="Cari..." value="<?= h($q) ?>">
      <button class="btn secondary" type="submit">Cari</button>
      <a class="btn secondary" href="<?= BASE_URL ?>/admin/destinasi.php">Reset</a>
    </form>
  </div>

  <div class="table-wrap">
    <table class="table2">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nama</th>
          <th>Kota</th>
          <th>Status</th>
          <th>Harga</th>
          <th>Foto</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php while($row = $data->fetch_assoc()): ?>
        <tr>
          <td><?= h($row[$pk]) ?></td>
          <td><b><?= h($colNama ? ($row[$colNama] ?? '-') : '-') ?></b></td>
          <td><?= h($row['kota'] ?? ($colLok ? ($row[$colLok] ?? '-') : '-')) ?></td>
          <td><span class="pill"><?= h($row['status'] ?? '-') ?></span></td>
          <td><?= h(isset($row['harga_tiket']) ? number_format((float)$row['harga_tiket'], 0, ',', '.') : '-') ?></td>
          <td>
            <?php if ($colFoto && !empty($row[$colFoto])): ?>
              <a href="<?= h($row[$colFoto]) ?>" target="_blank">Lihat</a>
            <?php else: ?>
              <small>-</small>
            <?php endif; ?>
          </td>
          <td class="actions">
            <a class="btn-sm gray" href="<?= BASE_URL ?>/admin/destinasi.php?edit=<?= h($row[$pk]) ?>">Edit</a>
            <a class="btn-sm danger" onclick="return confirm('Hapus destinasi ini?')" href="<?= BASE_URL ?>/admin/destinasi.php?hapus=<?= h($row[$pk]) ?>">Hapus</a>
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
      <h3><?= $edit ? "Edit Destinasi" : "Tambah Destinasi" ?></h3>
      <button class="close" onclick="closeModal()">Tutup</button>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <?php if ($edit): ?>
        <input type="hidden" name="id" value="<?= h($edit[$pk]) ?>">
      <?php endif; ?>

      <div class="form-grid">
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
        <div>
          <label>nama *</label>
          <input type="text" name="nama" required value="<?= h($edit['nama'] ?? '') ?>">
        </div>
        <div>
          <label>kota</label>
          <input type="text" name="kota" value="<?= h($edit['kota'] ?? '') ?>">
        </div>
        <div>
          <label>alamat</label>
          <input type="text" name="alamat" value="<?= h($edit['alamat'] ?? '') ?>">
        </div>
        <div>
          <label>latitude</label>
          <input id="latitude" readonly type="number" step="0.000001" name="latitude" value="<?= h($edit['latitude'] ?? '') ?>">
        </div>
        <div>
          <label>longitude</label>
          <input id="longitude" readonly type="number" step="0.000001" name="longitude" value="<?= h($edit['longitude'] ?? '') ?>">
        </div>
        <div>
          <label>jam_operasional</label>
          <input type="text" name="jam_operasional" value="<?= h($edit['jam_operasional'] ?? '') ?>">
        </div>
        <div>
          <label>harga_tiket</label>
          <input type="number" name="harga_tiket" value="<?= h($edit['harga_tiket'] ?? '') ?>">
        </div>
        <div>
          <label>nomor_kontak</label>
          <input type="text" name="nomor_kontak" value="<?= h($edit['nomor_kontak'] ?? '') ?>">
        </div>
        <div class="full">
          <label>deskripsi</label>
          <textarea name="deskripsi"><?= h($edit['deskripsi'] ?? '') ?></textarea>
        </div>

        <div class="full">
          <div class="map-actions">
            <strong>Pilih lokasi di peta</strong>
            <button type="button" onclick="useMyLocation()">Gunakan Lokasi Saya</button>
            <small class="small">Klik peta untuk menempatkan marker</small>
          </div>
          <input type="text" id="searchLocation" class="form-control search-location" placeholder="Cari lokasi (contoh: Malioboro)">
          <div id="searchResults" class="search-results" aria-live="polite"></div>
          <div class="search-hint">Pilih hasil untuk memindahkan marker.</div>
          <div id="map" class="map-box"></div>
        </div>

        <div class="full">
          <label>gambar_sampul_url</label>
          <?php if (!empty($edit['gambar_sampul_url'])): ?>
            <div style="margin-bottom:6px"><a href="<?= h($edit['gambar_sampul_url']) ?>" target="_blank">Lihat gambar saat ini</a></div>
          <?php endif; ?>
          <input type="file" accept="image/*" name="gambar_sampul_url">
        </div>
        <div class="full">
          <label>detail_gambar (bisa lebih dari 1)</label>
          <input type="file" name="detail_gambar[]" accept="image/*" multiple>
          <?php if (!empty($detailImages)): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:10px;margin-top:10px;">
              <?php foreach ($detailImages as $img): ?>
                <div style="border:1px solid #eee;padding:6px;border-radius:10px;text-align:center;">
                  <img src="<?= h($img['gambar_url']) ?>" alt="detail" style="width:100%;height:90px;object-fit:cover;border-radius:8px;">
                  <a class="btn-sm danger" style="margin-top:6px;display:inline-block;" onclick="return confirm('Hapus gambar ini?')" href="<?= BASE_URL ?>/admin/delete_detail_gambar.php?id_galeri=<?= h($img['id_galeri']) ?>&redirect=destinasi&ref_id=<?= h($edit[$pk]) ?>">Hapus</a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
        <div>
          <label>status</label>
          <?php $status = $edit['status'] ?? 'draft'; ?>
          <select name="status">
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>draft</option>
            <option value="publish" <?= $status === 'publish' ? 'selected' : '' ?>>publish</option>
            <option value="arsip" <?= $status === 'arsip' ? 'selected' : '' ?>>arsip</option>
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

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  const modal = document.getElementById('modal');
  let map, marker;
  let mapInited = false;

  function openModal(){
    modal.classList.add('show');
    ensureMap();
  }
  function closeModal(){ modal.classList.remove('show'); }

  function ensureMap() {
    if (typeof L === 'undefined') {
      const mapEl = document.getElementById('map');
      if (mapEl) mapEl.innerHTML = "<div style='padding:12px;color:#b00;'>Leaflet JS gagal dimuat. Pastikan koneksi internet atau cek blokir CDN.</div>";
      return;
    }
    if (!mapInited) {
      initMap();
      mapInited = true;
    }
    // tunggu modal render supaya ukuran div terhitung
    setTimeout(() => { if (map) map.invalidateSize(); }, 150);
  }

  // LEAFLET MAP
  const defaultLat = parseFloat("<?= isset($edit['latitude']) && $edit['latitude'] !== null && $edit['latitude'] !== '' ? $edit['latitude'] : '-7.7956' ?>");
  const defaultLng = parseFloat("<?= isset($edit['longitude']) && $edit['longitude'] !== null && $edit['longitude'] !== '' ? $edit['longitude'] : '110.3695' ?>");

  function initMap() {
    const mapEl = document.getElementById('map');
    if (!mapEl) return;

    map = L.map(mapEl).setView([defaultLat, defaultLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    if (latInput.value && lngInput.value) {
      marker = L.marker([parseFloat(latInput.value), parseFloat(lngInput.value)]).addTo(map);
    } else {
      marker = L.marker([defaultLat, defaultLng]).addTo(map);
      updateLatLng(defaultLat, defaultLng);
    }

    map.on('click', (e) => {
      const { lat, lng } = e.latlng;
      marker.setLatLng([lat, lng]);
      updateLatLng(lat, lng);
    });
  }

  function updateLatLng(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
  }

  function useMyLocation() {
    if (!navigator.geolocation) {
      alert('Geolocation tidak didukung browser ini.');
      return;
    }
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        map.setView([lat, lng], 15);
        marker.setLatLng([lat, lng]);
        updateLatLng(lat, lng);
      },
      () => alert('Gagal mengambil lokasi. Pastikan izin lokasi aktif.')
    );
  }

  const searchInput = document.getElementById('searchLocation');
  const searchResults = document.getElementById('searchResults');
  let searchTimer = null;

  async function searchLocation(query) {
    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`;
    const res = await fetch(url);
    if (!res.ok) return [];
    return await res.json();
  }

  function clearSearchResults() {
    if (!searchResults) return;
    searchResults.innerHTML = '';
    searchResults.style.display = 'none';
  }

  function renderSearchResults(items) {
    if (!searchResults) return;
    searchResults.innerHTML = '';
    if (!items || items.length === 0) {
      const empty = document.createElement('div');
      empty.className = 'search-result-item';
      empty.textContent = 'Lokasi tidak ditemukan.';
      searchResults.appendChild(empty);
      searchResults.style.display = 'block';
      return;
    }
    items.slice(0, 6).forEach((item) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'search-result-item';
      btn.textContent = item.display_name || 'Lokasi';
      btn.addEventListener('click', () => {
        ensureMap();
        const lat = parseFloat(item.lat);
        const lon = parseFloat(item.lon);
        if (!map || !marker || Number.isNaN(lat) || Number.isNaN(lon)) return;
        map.setView([lat, lon], 15);
        marker.setLatLng([lat, lon]);
        updateLatLng(lat, lon);
        if (searchInput) searchInput.value = item.display_name || '';
        clearSearchResults();
      });
      searchResults.appendChild(btn);
    });
    searchResults.style.display = 'block';
  }

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const query = searchInput.value.trim();
      clearTimeout(searchTimer);
      if (query.length < 3) {
        clearSearchResults();
        return;
      }
      searchTimer = setTimeout(async () => {
        const items = await searchLocation(query);
        renderSearchResults(items);
      }, 350);
    });
  }

  document.addEventListener('click', (e) => {
    if (!searchResults || !searchInput) return;
    if (e.target === searchInput || searchResults.contains(e.target)) return;
    clearSearchResults();
  });

  document.addEventListener('DOMContentLoaded', () => {
    <?php if ($edit): ?>
      openModal(); // kalau mode edit, modal otomatis kebuka
    <?php endif; ?>
  });
</script>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
