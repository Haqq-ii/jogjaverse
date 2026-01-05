<?php
require_once __DIR__ . "/../../../config/koneksi.php";
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

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
$hasKulinerKategoriTable = table_exists($koneksi, 'kuliner_kategori');
$hasKulinerKategoriCol = table_has_column($koneksi, 'kuliner', 'kuliner_kategori_id');
$kategori_ready = $hasKulinerKategoriTable && $hasKulinerKategoriCol;
if (!$kategori_ready) {
    $kategori_id = 0;
}

// C. Query Dasar (Base Query)
// Menggunakan LEFT JOIN agar data tetap muncul meski kategori tidak valid/kosong
$where_clause = "WHERE k.status = 'publish'";

if ($kategori_ready && $kategori_id > 0) {
    $where_clause .= " AND k.kuliner_kategori_id = $kategori_id";
}
$join_kategori = $kategori_ready ? "LEFT JOIN kuliner_kategori kk ON k.kuliner_kategori_id = kk.id" : "";
$select_kategori = $kategori_ready ? "kk.nama AS kategori" : "NULL AS kategori";

// D. Hitung Total Data
$query_count = "
    SELECT COUNT(*) as total 
    FROM kuliner k
    $join_kategori
    $where_clause
";
$result_count = mysqli_query($koneksi, $query_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_data = $row_count['total'];
$total_pages = ceil($total_data / $limit);

// E. Ambil Data Kuliner
$query_data = "
    SELECT 
        k.id_kuliner,
        k.nama,
        k.deskripsi,
        $select_kategori,
        k.gambar_sampul_url
    FROM kuliner k
    $join_kategori
    $where_clause
    ORDER BY k.dibuat_pada DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($koneksi, $query_data);

if (!$result) {
    die('Query error: ' . mysqli_error($koneksi));
}

// F. Ambil Daftar Kategori untuk Dropdown
$kategori_options = [];
$kategori_message = '';
if ($kategori_ready) {
    $query_kategori = "SELECT id, nama FROM kuliner_kategori ORDER BY nama ASC";
    $result_kategori = mysqli_query($koneksi, $query_kategori);
    if ($result_kategori) {
        while ($row = mysqli_fetch_assoc($result_kategori)) {
            $kategori_options[] = $row;
        }
    } else {
        $kategori_message = 'Gagal memuat kategori.';
    }
    if (empty($kategori_options) && $kategori_message === '') {
        $kategori_message = 'Kategori belum tersedia.';
    }
} else {
    if (!$hasKulinerKategoriCol) {
        $kategori_message = 'Kolom kuliner_kategori_id belum tersedia. Jalankan migrasi.';
    } elseif (!$hasKulinerKategoriTable) {
        $kategori_message = 'Tabel kuliner_kategori belum tersedia. Jalankan migrasi.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wisata Kuliner - JogjaVerse</title>

  <!-- CSS Libraries -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../css/kulinerLainnya.css">
  <link rel="stylesheet" href="../css/navbar_solid.css">
  <style>
   
  </style>
</head>
<body class="navbar-solid-page">

<!-- NAVBAR -->
<?php include __DIR__ . '/partials/navbar_solid.php'; ?>

<!-- HEADER BACKGROUND -->
<section id="background">
  <div class="text-center" data-aos="fade-up">
    <h1 class="fw-bold text-white display-4 mb-2">Wisata Kuliner</h1>
    <p class="text-white-50 fs-5">Nikmati Cita Rasa Otentik Khas Yogyakarta</p>
  </div>
</section>

<!-- CONTENT SECTION -->
<section class="py-5">
  <div class="container" data-aos="fade-up">

      <!-- FILTER & INFO BAR -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 pb-3 border-bottom gap-3">
          <div class="text-muted" style="font-size: 1rem;">
              Menampilkan <strong><?= mysqli_num_rows($result) ?></strong> dari <strong><?= $total_data ?></strong> kuliner
          </div>

          <!-- Dropdown Filter -->
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle rounded-pill px-4 text-dark border-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-funnel me-2"></i><?= ($kategori_ready && $kategori_id > 0) ? 'Kategori Terpilih' : 'Semua Kuliner' ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2">
                <li>
                    <a class="dropdown-item rounded-2 <?= ($kategori_id == 0) ? 'active' : '' ?>" href="kulinerLainnya.php">
                        Semua Kuliner
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <?php if (!empty($kategori_options)): ?>
                    <?php foreach ($kategori_options as $kat): ?>
                        <li>
                            <a class="dropdown-item rounded-2 <?= ($kategori_id == (int)$kat['id']) ? 'active' : '' ?>"
                               href="?kategori=<?= (int)$kat['id'] ?>">
                               <?= htmlspecialchars($kat['nama']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <?php if ($kategori_message !== ''): ?>
              <div class="text-muted small mt-2"><?= htmlspecialchars($kategori_message) ?></div>
            <?php endif; ?>
          </div>
      </div>

      <!-- CARDS GRID -->
      <div class="row g-4">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $img_url = !empty($row['gambar_sampul_url']) ? $row['gambar_sampul_url'] : 'https://placehold.co/600x400?text=Kuliner';
                $kategoriLabel = trim((string)($row['kategori'] ?? ''));
                $kategoriLabel = $kategoriLabel !== '' ? $kategoriLabel : '-';
            ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
              <!-- CARD KULINER -->
              <div class="card card-kuliner h-100">
                
                <!-- Gambar (Tinggi 220px) -->
                <div class="position-relative overflow-hidden" style="height: 220px;">
                  <img src="<?= htmlspecialchars($img_url) ?>"
                       class="w-100 h-100 object-fit-cover"
                       alt="<?= htmlspecialchars($row['nama']) ?>"
                       onerror="this.src='https://placehold.co/600x400?text=No+Image'">
                </div>

                <!-- Padding Body p-4 -->
                <div class="card-body p-4 d-flex flex-column">
                  
                  <!-- Kategori Badge -->
                  <div class="mb-2">
                      <span class="badge bg-light text-dark border border-secondary border-opacity-25 rounded-pill px-3" style="font-size: 0.75rem;">
                          <?= htmlspecialchars($kategoriLabel) ?>
                      </span>
                  </div>

                  <!-- Judul -->
                  <h5 class="fw-bold mb-2 fs-5 font-serif text-dark">
                    <?= htmlspecialchars($row['nama']) ?>
                  </h5>

                  <!-- Deskripsi -->
                  <p class="text-muted mb-3 flex-grow-1" style="line-height:1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?= htmlspecialchars(substr(strip_tags($row['deskripsi']), 0, 100)) ?>...
                  </p>

                  <div class="border-top pt-3 mt-auto">
                    <div class="text-center">
                        <a href="detailKuliner.php?id=<?= $row['id_kuliner'] ?>" class="link-gold-animated">
                           Lihat Menu <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                  </div>

                </div>

              </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted fs-5">Tidak ada kuliner ditemukan untuk kategori ini.</div>
                <a href="kulinerLainnya.php" class="btn btn-outline-dark mt-3 rounded-pill">Lihat Semua</a>
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
