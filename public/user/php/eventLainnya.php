<?php
require_once __DIR__ . "/../../../config/koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// -----------------------------------------------------------
// 1. LOGIKA FILTER & PAGINATION
// -----------------------------------------------------------

// A. Konfigurasi Pagination
$limit = 12; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;

// B. Tangkap Filter Kategori
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

// C. Query Dasar (Base Query)
// Asumsi tabel bernama 'event' dan memiliki relasi ke tabel 'kategori'
// Kita filter tipe kategori khusus untuk event/atraksi jika perlu, atau ambil semua
$where_clause = "WHERE e.status = 'publish'";

// Jika ingin memisahkan event dan atraksi berdasarkan tipe kategori, tambahkan kondisi ini:
// $where_clause .= " AND k.tipe IN ('event', 'atraksi')"; 

if ($kategori_id > 0) {
    $where_clause .= " AND e.id_kategori = $kategori_id";
}

// D. Hitung Total Data
$query_count = "
    SELECT COUNT(*) as total 
    FROM event e
    LEFT JOIN kategori k ON e.id_kategori = k.id_kategori
    $where_clause
";
$result_count = mysqli_query($koneksi, $query_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_data = $row_count['total'];
$total_pages = ceil($total_data / $limit);

// E. Ambil Data Event
// Kolom disesuaikan untuk kebutuhan Event: Tanggal, Lokasi, Harga
$query_data = "
    SELECT 
        e.id_event,
        e.judul AS nama_event,
        e.deskripsi AS deskripsi_singkat,
        k.nama AS kategori,
        e.gambar_sampul_url,
        e.mulai_pada,    -- Pastikan kolom ini ada di DB (atau 'mulai_pada')
        e.lokasi,
        e.harga       -- Pastikan kolom ini ada di DB
    FROM event e
    LEFT JOIN kategori k ON e.id_kategori = k.id_kategori
    $where_clause
    ORDER BY e.mulai_pada ASC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($koneksi, $query_data);

// Error Handling Sederhana
if (!$result) {
    // Fallback jika tabel/kolom tidak sesuai (untuk debugging)
    die('Query error: ' . mysqli_error($koneksi));
}

// F. Ambil Daftar Kategori untuk Dropdown (Tipe Event/Atraksi)
$query_kategori = "SELECT * FROM kategori WHERE tipe IN ('event', 'atraksi') ORDER BY nama ASC";
$result_kategori = mysqli_query($koneksi, $query_kategori);

// Helper Function: Format Tanggal Indonesia
function formatTanggal($date) {
    if (empty($date)) return ['tgl' => '??', 'bln' => '???'];
    $timestamp = strtotime($date);
    $bulan = array (
        1 =>   'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
    );
    return [
        'hari' => date('d', $timestamp),
        'bulan' => $bulan[(int)date('m', $timestamp)],
        'tahun' => date('Y', $timestamp),
        'full' => date('d F Y', $timestamp)
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event & Atraksi - JogjaVerse</title>

  <!-- CSS Libraries -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/eventLainnya.css">

  <style>
    
  </style>
</head>
<body class="navbar-solid">

<!-- NAVBAR -->
<?php include __DIR__ . '/includes/navbar.php'; ?>

<!-- HEADER BACKGROUND -->
<section id="background">
  <div class="text-center" data-aos="fade-up">
    <h1 class="fw-bold text-white display-4 mb-2">Event & Atraksi</h1>
    <p class="text-white-50 fs-5">Saksikan kemeriahan budaya dan hiburan di Yogyakarta</p>
  </div>
</section>

<!-- CONTENT SECTION -->
<section class="py-5">
  <div class="container" data-aos="fade-up">

      <!-- FILTER BAR -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 pb-3 border-bottom gap-3">
          <div class="text-muted">
              Menampilkan <strong><?= mysqli_num_rows($result) ?></strong> event mendatang
          </div>

          <!-- Dropdown Filter -->
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle rounded-pill px-4 text-dark border-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-funnel me-2"></i><?= ($kategori_id > 0) ? 'Kategori Terpilih' : 'Semua Jenis Event' ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2">
                <li><a class="dropdown-item rounded-2 <?= ($kategori_id == 0) ? 'active' : '' ?>" href="eventLainnya.php">Semua Event</a></li>
                <li><hr class="dropdown-divider"></li>
                <?php while($kat = mysqli_fetch_assoc($result_kategori)): ?>
                    <li>
                        <a class="dropdown-item rounded-2 <?= ($kategori_id == $kat['id_kategori']) ? 'active' : '' ?>" 
                           href="?kategori=<?= $kat['id_kategori'] ?>">
                           <?= htmlspecialchars($kat['nama']) ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
          </div>
      </div>

      <!-- EVENT CARDS GRID -->
      <div class="row g-4">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $date_info = formatTanggal($row['mulai_pada'] ?? '');
                $img_url = !empty($row['gambar_sampul_url']) ? $row['gambar_sampul_url'] : 'https://placehold.co/600x400?text=Event+Jogja';
                $kategori_label = trim((string)($row['kategori'] ?? ''));
                $kategori_label = $kategori_label !== '' ? $kategori_label : 'Event';
                $judul = $row['nama_event'] ?? 'Event';
                $deskripsi = trim((string)($row['deskripsi_singkat'] ?? ''));
                $deskripsi = $deskripsi !== '' ? $deskripsi : 'Belum ada deskripsi.';
                $lokasi = trim((string)($row['lokasi'] ?? ''));
                $lokasi = $lokasi !== '' ? $lokasi : '-';
                $tanggal = trim((string)($date_info['full'] ?? ''));
                $tanggal = $tanggal !== '' ? $tanggal : '-';
            ?>
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
              <div class="card card-event h-100">
                
                <div class="position-relative overflow-hidden card-media">
                  <img src="<?= htmlspecialchars($img_url, ENT_QUOTES, 'UTF-8') ?>"
                       class="w-100 h-100 object-fit-cover"
                       alt="<?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>"
                       onerror="this.src='https://placehold.co/600x400?text=No+Image'">

                  <span class="position-absolute top-0 start-0 m-3 px-3 py-1 bg-white rounded-pill fw-bold shadow-sm text-dark"
                        style="font-size:0.7rem;">
                    <?= htmlspecialchars($kategori_label, ENT_QUOTES, 'UTF-8') ?>
                  </span>
                </div>

                <div class="card-body p-4 d-flex flex-column">
                  <h5 class="fw-bold mb-2 fs-5 font-serif text-dark">
                    <?= htmlspecialchars($judul, ENT_QUOTES, 'UTF-8') ?>
                  </h5>

                  <p class="text-muted mb-3 flex-grow-1 small"
                     style="line-height:1.5; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;">
                    <?= htmlspecialchars($deskripsi, ENT_QUOTES, 'UTF-8') ?>
                  </p>

                  <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <div class="text-muted small">
                      <div class="d-flex align-items-center">
                        <i class="bi bi-geo-alt me-1 text-warning"></i>
                        <span class="text-truncate"><?= htmlspecialchars($lokasi, ENT_QUOTES, 'UTF-8') ?></span>
                      </div>
                      <div class="d-flex align-items-center">
                        <i class="bi bi-calendar-event me-1 text-warning"></i>
                        <span><?= htmlspecialchars($tanggal, ENT_QUOTES, 'UTF-8') ?></span>
                      </div>
                    </div>

                    <a href="detailEvent.php?id=<?= (int)$row['id_event'] ?>" class="link-gold-animated">
                       Detail <i class="bi bi-arrow-right"></i>
                    </a>
                  </div>
                </div>

              </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-calendar-x display-1 text-muted opacity-25"></i>
                <div class="text-muted fs-5 mt-3">Belum ada event atau atraksi yang tersedia.</div>
                <a href="eventLainnya.php" class="btn btn-outline-dark mt-3 rounded-pill">Reset Filter</a>
            </div>
        <?php endif; ?>
      </div>

      <!-- PAGINATION -->
      <?php if ($total_pages > 1): ?>
      <div class="mt-5 d-flex justify-content-center">
        <nav aria-label="Page navigation">
          <ul class="pagination">
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page - 1 ?>&kategori=<?= $kategori_id ?>">
                <i class="bi bi-chevron-left"></i>
              </a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&kategori=<?= $kategori_id ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $page + 1 ?>&kategori=<?= $kategori_id ?>">
                <i class="bi bi-chevron-right"></i>
              </a>
            </li>
          </ul>
        </nav>
      </div>
      <?php endif; ?>

  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
