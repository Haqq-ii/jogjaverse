<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/_crud_helper.php";
wajib_admin();

function safe_count_table(mysqli $koneksi, string $table): int {
  $stmt = $koneksi->prepare("SELECT COUNT(*) as total FROM `$table`");
  if (!$stmt) return 0;
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  return (int)($row['total'] ?? 0);
}

function recent_rows(mysqli $koneksi, string $table, int $limit = 6): array {
  [$cols, $pk] = describe_table($koneksi, $table);
  if (!$pk) return [];
  $colNames = array_map(fn($c) => $c['Field'], $cols);
  $orderCol = guess_time_col($colNames) ?: $pk;

  $sql = "SELECT * FROM `$table` ORDER BY `$orderCol` DESC LIMIT ?";
  $stmt = $koneksi->prepare($sql);
  if (!$stmt) return [];
  $stmt->bind_param("i", $limit);
  $stmt->execute();
  return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$kunjungan = fetch_trend($koneksi, "kunjungan");

[$kCols] = describe_table($koneksi, "kunjungan");
$kColNames = array_map(fn($c) => $c['Field'], $kCols);
$colJenis = pick_col($kColNames, ["jenis_halaman", "jenis", "tipe"]);
$jenisSummary = [];
if ($colJenis) {
  $sql = "SELECT `$colJenis` as jenis, COUNT(*) as total FROM `kunjungan` GROUP BY `$colJenis` ORDER BY total DESC LIMIT 6";
  $stmt = $koneksi->prepare($sql);
  if ($stmt) {
    $stmt->execute();
    $jenisSummary = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }
}

$totalDestinasi = safe_count_table($koneksi, "destinasi");
$totalEvent     = safe_count_table($koneksi, "event");
$totalKuliner   = safe_count_table($koneksi, "kuliner");
$totalAkun      = safe_count_table($koneksi, "pengguna");
$totalUlasan    = safe_count_table($koneksi, "ulasan");
$totalPelaporan = safe_count_table($koneksi, "pelaporan");

$recentVisits  = $kunjungan['recent'] ?? [];
$recentAccounts = recent_rows($koneksi, "pengguna", 6);
?>

<?php require_once __DIR__ . "/partials/header.php"; ?>

<div class="metric-cards">
  <div class="metric-card">
    <div class="label">Total Kunjungan</div>
    <div class="value" id="stat-total-kunjungan"><?= h($kunjungan['total']) ?></div>
    <div class="muted">Data real-time dari tabel kunjungan</div>
  </div>
  <div class="metric-card">
    <div class="label">Hari Ini</div>
    <div class="value" id="stat-today-kunjungan"><?= h($kunjungan['today']) ?></div>
    <div class="muted">Pengunjung hari ini</div>
  </div>
  <div class="metric-card">
    <div class="label">Konten Wisata</div>
    <div class="value"><?= h($totalDestinasi + $totalEvent + $totalKuliner) ?></div>
    <div class="muted">Destinasi, event, kuliner</div>
  </div>
  <div class="metric-card">
    <div class="label">Akun Pengguna</div>
    <div class="value"><?= h($totalAkun) ?></div>
    <div class="muted">Admin & wisatawan terdaftar</div>
  </div>
</div>

<div class="card chart-grid">
  <div>
    <div class="toprow">
      <h2 style="margin:0">Tren 7 Hari</h2>
      <span class="pill">Kunjungan</span>
    </div>
    <table class="table-minimal">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Jumlah</th>
        </tr>
      </thead>
      <tbody id="trend-body">
        <?php if (!empty($kunjungan['per_hari'])): ?>
          <?php foreach ($kunjungan['per_hari'] as $row): ?>
            <tr>
              <td><?= h($row['tgl']) ?></td>
              <td><b><?= h($row['total']) ?></b></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="2"><small>Belum ada data kunjungan / kolom waktu tidak ditemukan.</small></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div>
    <div class="toprow">
      <h2 style="margin:0">Distribusi Halaman</h2>
      <span class="pill">Jenis halaman</span>
    </div>
    <ul class="list-simple" id="jenis-list">
      <?php if (count($jenisSummary) > 0): ?>
        <?php foreach ($jenisSummary as $j): ?>
          <li style="display:flex;justify-content:space-between;align-items:center">
            <span><?= h($j['jenis'] ?? '-') ?></span>
            <b><?= h($j['total'] ?? 0) ?></b>
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li><small>Belum ada data / kolom jenis_halaman tidak ditemukan.</small></li>
      <?php endif; ?>
    </ul>
  </div>
</div>

<div class="card">
  <div class="toprow">
    <h2 style="margin:0">Tren 7 Hari (Klik Detail Destinasi)</h2>
    <span class="pill">Destinasi</span>
  </div>
  <div class="chart-grid">
    <div>
      <canvas id="trend-destinasi-chart" height="140"></canvas>
    </div>
    <div>
      <table class="table-minimal">
        <thead>
          <tr>
            <th>Top Destinasi</th>
            <th>Jumlah</th>
          </tr>
        </thead>
        <tbody id="trend-destinasi-top">
          <tr><td colspan="2"><small>Memuat data...</small></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card two-col">
  <div>
    <div class="toprow">
      <h2 style="margin:0">Kunjungan Terbaru</h2>
      <a class="btn secondary" href="<?= BASE_URL ?>/public/track_view.php" target="_blank">Lihat tracker</a>
    </div>
    <table class="table-minimal">
      <thead>
        <tr>
          <th>Halaman</th>
          <th>ID Target</th>
          <th>Waktu</th>
        </tr>
      </thead>
      <tbody id="recent-body">
        <?php if (count($recentVisits) > 0): ?>
          <?php foreach ($recentVisits as $rv): ?>
            <tr>
              <td><?= h($rv[$colJenis] ?? ($rv['jenis_halaman'] ?? $rv['jenis'] ?? '-')) ?></td>
              <td><?= h($rv['id_target'] ?? '-') ?></td>
              <td>
                <?php
                  $colNames = array_keys($rv);
                  $timeCol = guess_time_col($colNames);
                  echo h($timeCol ? ($rv[$timeCol] ?? '-') : '-');
                ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="3"><small>Belum ada data kunjungan.</small></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div>
    <div class="toprow">
      <h2 style="margin:0">Akun Terbaru</h2>
      <a class="btn secondary" href="<?= BASE_URL ?>/admin/akun.php">Lihat semua</a>
    </div>
    <table class="table-minimal">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Peran</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (count($recentAccounts) > 0): ?>
          <?php foreach ($recentAccounts as $acc): ?>
            <tr>
              <td><?= h($acc['nama_lengkap'] ?? $acc['username'] ?? 'User') ?></td>
              <td><span class="pill"><?= h($acc['peran'] ?? '-') ?></span></td>
              <td>
                <?php $aktif = ($acc['status_aktif'] ?? null) == 1; ?>
                <span class="status-dot">
                  <span class="<?= $aktif ? '' : 'gray' ?>"></span>
                  <?= $aktif ? "Aktif" : "Non-aktif" ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="3"><small>Belum ada akun.</small></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const apiUrl = "<?= BASE_URL ?>/admin/api/trend_7hari.php";
    const chartEl = document.getElementById('trend-destinasi-chart');
    const topBody = document.getElementById('trend-destinasi-top');
    if (!chartEl || !topBody) return;

    let chartInstance = null;

    const renderTop = (items = []) => {
      topBody.innerHTML = '';
      if (!Array.isArray(items) || items.length === 0) {
        topBody.innerHTML = '<tr><td colspan="2"><small>Belum ada data trending.</small></td></tr>';
        return;
      }
      items.forEach((row) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${row.nama ?? '-'}</td><td><b>${row.count ?? 0}</b></td>`;
        topBody.appendChild(tr);
      });
    };

    const renderChart = (daily = []) => {
      const labels = daily.map((d) => d.date);
      const values = daily.map((d) => d.count);
      if (chartInstance) {
        chartInstance.data.labels = labels;
        chartInstance.data.datasets[0].data = values;
        chartInstance.update();
        return;
      }
      chartInstance = new Chart(chartEl, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Klik Detail',
            data: values,
            borderColor: '#4A2A2A',
            backgroundColor: 'rgba(74, 42, 42, 0.15)',
            tension: 0.3,
            fill: true,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
          }
        }
      });
    };

    fetch(apiUrl, { cache: 'no-cache' })
      .then((res) => res.ok ? res.json() : null)
      .then((data) => {
        if (!data) return;
        renderChart(data.daily || []);
        renderTop(data.top || []);
      })
      .catch(() => {
        renderTop([]);
      });
  });
</script>

<script>
  // Polling sederhana untuk menyegarkan data kunjungan tanpa reload
  document.addEventListener('DOMContentLoaded', () => {
    const apiUrl = "<?= BASE_URL ?>/admin/api_kunjungan.php";
    const totalEl = document.getElementById('stat-total-kunjungan');
    const todayEl = document.getElementById('stat-today-kunjungan');
    const trendBody = document.getElementById('trend-body');
    const jenisList = document.getElementById('jenis-list');
    const recentBody = document.getElementById('recent-body');
    const fallbackJenisCol = <?= $colJenis ? "'" . h($colJenis) . "'" : "null" ?>;
    const fallbackTimeCol = <?= ($timeCol = guess_time_col($kColNames)) ? "'" . h($timeCol) . "'" : "null" ?>;

    const esc = (val) => {
      const div = document.createElement('div');
      div.textContent = val ?? '';
      return div.innerHTML;
    };

    const pickField = (row, fields, defaultVal = '-') => {
      for (const f of fields) {
        if (!f) continue;
        const v = row[f];
        if (v !== undefined && v !== null && v !== '') return v;
      }
      return defaultVal;
    };

    const renderTrend = (perHari = []) => {
      if (!trendBody) return;
      trendBody.innerHTML = '';
      if (!Array.isArray(perHari) || perHari.length === 0) {
        trendBody.innerHTML = '<tr><td colspan="2"><small>Belum ada data kunjungan / kolom waktu tidak ditemukan.</small></td></tr>';
        return;
      }
      perHari.forEach((row) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${esc(row.tgl ?? '-')}</td><td><b>${esc(row.total ?? 0)}</b></td>`;
        trendBody.appendChild(tr);
      });
    };

    const renderJenis = (items = []) => {
      if (!jenisList) return;
      jenisList.innerHTML = '';
      if (!Array.isArray(items) || items.length === 0) {
        jenisList.innerHTML = '<li><small>Belum ada data / kolom jenis_halaman tidak ditemukan.</small></li>';
        return;
      }
      items.forEach((item) => {
        const li = document.createElement('li');
        li.style.display = "flex";
        li.style.justifyContent = "space-between";
        li.style.alignItems = "center";
        li.innerHTML = `<span>${esc(item.jenis ?? '-')}</span><b>${esc(item.total ?? 0)}</b>`;
        jenisList.appendChild(li);
      });
    };

    const renderRecent = (rows = [], meta = {}) => {
      if (!recentBody) return;
      const jenisCol = meta.jenis_col || fallbackJenisCol || 'jenis_halaman';
      const timeCol = meta.time_col || fallbackTimeCol;
      recentBody.innerHTML = '';

      if (!Array.isArray(rows) || rows.length === 0) {
        recentBody.innerHTML = '<tr><td colspan="3"><small>Belum ada data kunjungan.</small></td></tr>';
        return;
      }

      rows.forEach((row) => {
        const jenis = pickField(row, [jenisCol, 'jenis_halaman', 'jenis', 'tipe']);
        const target = pickField(row, ['id_target']);
        const waktu = timeCol ? pickField(row, [timeCol]) : '-';
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${esc(jenis)}</td><td>${esc(target)}</td><td><small>${esc(waktu)}</small></td>`;
        recentBody.appendChild(tr);
      });
    };

    const applyData = (payload) => {
      if (!payload) return;
      if (totalEl && payload.total !== undefined) totalEl.textContent = payload.total;
      if (todayEl && payload.today !== undefined) todayEl.textContent = payload.today;
      renderTrend(payload.per_hari);
      renderJenis(payload.jenis);
      renderRecent(payload.recent, payload.meta || {});
    };

    const fetchData = () => {
      fetch(apiUrl, { cache: 'no-cache' })
        .then((r) => r.ok ? r.json() : null)
        .then((json) => {
          if (!json || !json.ok) return;
          applyData(json.data);
        })
        .catch(() => {
          // diamkan saja agar tidak mengganggu UI
        });
    };

    // load pertama dan polling tiap 10 detik
    fetchData();
    setInterval(fetchData, 10000);
  });
</script>

<?php require_once __DIR__ . "/partials/footer.php"; ?>
