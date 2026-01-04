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
$where_clause = "WHERE d.status = 'publish' AND k.tipe = 'destinasi'";

if ($kategori_id > 0) {
    $where_clause .= " AND d.id_kategori = $kategori_id";
}

// D. Hitung Total Data
$query_count = "
    SELECT COUNT(*) as total 
    FROM destinasi d
    JOIN kategori k ON d.id_kategori = k.id_kategori
    $where_clause
";
$result_count = mysqli_query($koneksi, $query_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_data = $row_count['total'];
$total_pages = ceil($total_data / $limit);

// E. Ambil Data Destinasi
$query_data = "
    SELECT 
        d.id_destinasi AS id,
        d.nama AS nama_destinasi,
        d.deskripsi AS deskripsi_singkat,
        k.nama AS kategori,
        d.gambar_sampul_url AS gambar,
        d.jam_operasional AS estimasi_waktu
    FROM destinasi d
    JOIN kategori k ON d.id_kategori = k.id_kategori
    $where_clause
    ORDER BY d.dibuat_pada DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($koneksi, $query_data);

if (!$result) {
    die('Query error: ' . mysqli_error($koneksi));
}

// F. Ambil Daftar Kategori untuk Dropdown
$query_kategori = "SELECT * FROM kategori WHERE tipe = 'destinasi' ORDER BY nama ASC";
$result_kategori = mysqli_query($koneksi, $query_kategori);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Semua Destinasi - JogjaVerse</title>

  <!-- CSS Libraries -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../css/destinasiLainnya.css">
</head>

<body class="navbar-solid">

<!-- NAVBAR (Layout Besar) -->
<?php include __DIR__ . '/includes/navbar.php'; ?>

<!-- HEADER BACKGROUND -->
<section id="background">
  <div class="text-center" data-aos="fade-up">
    <h1 class="fw-bold text-white display-4 mb-2">Jelajah Destinasi</h1>
    <p class="text-white-50 fs-5">Temukan keindahan tersembunyi di Yogyakarta</p>
  </div>
</section>

<!-- CONTENT SECTION -->
<section class="py-5">
  <div class="container" data-aos="fade-up">

      <!-- FILTER & INFO BAR (DISAMAKAN STRUKTURNYA DENGAN EVENT) -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 pb-3 border-bottom gap-3">
          <div class="text-muted">
              Menampilkan <strong><?= mysqli_num_rows($result) ?></strong> dari <strong><?= $total_data ?></strong> destinasi
          </div>

          <!-- Dropdown Filter -->
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle rounded-pill px-4 text-dark border-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-funnel me-2"></i><?= ($kategori_id > 0) ? 'Kategori Terpilih' : 'Semua Kategori' ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2">
                <li>
                    <a class="dropdown-item rounded-2 <?= ($kategori_id == 0) ? 'active' : '' ?>" href="destinasiLainnya.php">
                        Semua Kategori
                    </a>
                </li>
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

      <!-- CARDS GRID -->
      <div class="row g-4">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $img_url = !empty($row['gambar']) ? $row['gambar'] : 'https://placehold.co/600x400?text=Destinasi';
            ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
              <!-- CARD DESTINASI (UKURAN GAMBAR & PADDING DISAMAKAN) -->
              <div class="card card-destinasi h-100">
                
                <!-- Gambar (Tinggi disamakan: 220px) -->
                <div class="position-relative overflow-hidden" style="height: 220px;">
                  <img src="<?= htmlspecialchars($img_url) ?>"
                       class="w-100 h-100 object-fit-cover"
                       alt="<?= htmlspecialchars($row['nama_destinasi']) ?>"
                       onerror="this.src='https://placehold.co/600x400?text=No+Image'">

                  <!-- Badge Kategori -->
                  <span class="position-absolute top-0 start-0 m-3 px-3 py-1 bg-white rounded-pill fw-bold shadow-sm text-dark"
                        style="font-size:0.7rem;">
                    <?= htmlspecialchars($row['kategori']) ?>
                  </span>
                </div>

                <!-- Padding Body disamakan: p-4 -->
                <div class="card-body p-4 d-flex flex-column">
                  <!-- Font Size Judul disamakan: fs-5 -->
                  <h5 class="fw-bold mb-2 fs-5 font-serif text-truncate">
                    <?= htmlspecialchars($row['nama_destinasi']) ?>
                  </h5>

                  <p class="text-muted mb-3 flex-grow-1 small" style="line-height:1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?= htmlspecialchars($row['deskripsi_singkat']) ?>
                  </p>

                  <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <span class="text-muted small">
                      <i class="bi bi-clock me-1 text-warning"></i>
                      <?= htmlspecialchars($row['estimasi_waktu']) ?>
                    </span>

                    <!-- TOMBOL DETAIL -->
                    <a href="detailDestinasi.php?id=<?= $row['id'] ?>" class="link-gold-animated">
                       Detail <i class="bi bi-arrow-right"></i>
                    </a>
                  </div>
                </div>

              </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted fs-5">Tidak ada destinasi ditemukan untuk kategori ini.</div>
                <a href="destinasiLainnya.php" class="btn btn-outline-dark mt-3 rounded-pill">Lihat Semua</a>
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

<?php include __DIR__ . '/partials/jove_widget.php'; ?>
<link rel="stylesheet" href="../css/jove.css">
<script src="../js/jove.js"></script>
</body>
</html>
