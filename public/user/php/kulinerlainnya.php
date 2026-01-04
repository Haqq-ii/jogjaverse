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
// Menggunakan LEFT JOIN agar data tetap muncul meski kategori tidak valid/kosong
$where_clause = "WHERE k.status = 'publish'";

if ($kategori_id > 0) {
    $where_clause .= " AND k.id_kategori = $kategori_id";
}

// D. Hitung Total Data
$query_count = "
    SELECT COUNT(*) as total 
    FROM kuliner k
    LEFT JOIN kategori c ON k.id_kategori = c.id_kategori
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
        c.nama AS kategori,
        k.gambar_sampul_url,
        k.alamat,
        k.rentang_harga
    FROM kuliner k
    LEFT JOIN kategori c ON k.id_kategori = c.id_kategori
    $where_clause
    ORDER BY k.dibuat_pada DESC
    LIMIT $limit OFFSET $offset
";
$result = mysqli_query($koneksi, $query_data);

if (!$result) {
    die('Query error: ' . mysqli_error($koneksi));
}

// F. Ambil Daftar Kategori untuk Dropdown
$query_kategori = "SELECT * FROM kategori WHERE tipe = 'kuliner' ORDER BY nama ASC";
$result_kategori = mysqli_query($koneksi, $query_kategori);
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
  <style>
   
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-dark" href="landingpageclean.php">
        Jogja<span style="color: var(--secondary-color);">Verse.</span>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto text-center">
        <li class="nav-item"><a class="nav-link fw-bold text-dark" href="destinasiLainnya.php">Destinasi</a></li>
        <li class="nav-item"><a class="nav-link fw-bold text-dark" href="eventLainnya.php">Event&Atraksi</a></li>
        <li class="nav-item"><a class="nav-link fw-bold text-dark active" href="kulinerlainnya.php">Kuliner</a></li>
      </ul>
      <?php
        if (!empty($_SESSION['login']) && $_SESSION['login'] === true) {
          $displayName = htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username']);
          $avatarPath = '/public/user/img/default_avatar.png';
          echo '<a href="/public/user.php" class="d-flex align-items-center text-decoration-none text-dark">';
          echo '<img src="'.$avatarPath.'" alt="Profile" style="width:38px;height:38px;border-radius:50%;object-fit:cover;margin-right:8px;">';
          echo '<span class="fw-medium d-none d-md-inline">'. $displayName .'</span>';
          echo '</a>';
        } else {
          echo '<a href="/public/login.php" class="btn btn-outline-dark btn-login-custom">Login</a>';
        }
        ?>
    </div>
  </div>
</nav>

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
          <div class="text-muted" style="font-size: 1rem;"> <!-- Hapus small -->
              Menampilkan <strong><?= mysqli_num_rows($result) ?></strong> dari <strong><?= $total_data ?></strong> kuliner
          </div>

          <!-- Dropdown Filter -->
          <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle rounded-pill px-4 text-dark border-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-funnel me-2"></i><?= ($kategori_id > 0) ? 'Kategori Terpilih' : 'Semua Kuliner' ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2">
                <li>
                    <a class="dropdown-item rounded-2 <?= ($kategori_id == 0) ? 'active' : '' ?>" href="kulinerLainnya.php">
                        Semua Kuliner
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
                $img_url = !empty($row['gambar_sampul_url']) ? $row['gambar_sampul_url'] : 'https://placehold.co/600x400?text=Kuliner';
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
                          <?= htmlspecialchars($row['kategori'] ?? 'Umum') ?>
                      </span>
                  </div>

                  <!-- Judul (Hapus text-truncate agar nama panjang terlihat penuh, ukuran fs-5) -->
                  <h5 class="fw-bold mb-2 fs-5 font-serif text-dark">
                    <?= htmlspecialchars($row['nama']) ?>
                  </h5>

                  <!-- Deskripsi (Hapus class small, pakai font-size CSS) -->
                  <p class="text-muted mb-3 flex-grow-1" style="line-height:1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?= htmlspecialchars(substr(strip_tags($row['deskripsi']), 0, 100)) ?>...
                  </p>

                  <div class="border-top pt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3 card-meta">
                        <!-- Lokasi -->
                        <span class="text-muted d-flex align-items-center" style="max-width: 60%;">
                            <i class="bi bi-geo-alt me-1 text-warning"></i> 
                            <span class="text-truncate"><?= htmlspecialchars($row['alamat']) ?></span>
                        </span>
                        <!-- Harga -->
                        <span class="fw-bold text-dark">
                            Rp <?= number_format($row['rentang_harga'], 0, ',', '.') ?>
                        </span>
                    </div>

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

<!-- Footer (Ditambahkan di sini agar satu file style) -->
<footer class="footer-custom pt-5 mt-5">
  <div class="container">
    <div class="row gy-4">

      <!-- Brand & Info -->
      <div class="col-lg-4 pe-lg-5">
        <div class="d-flex align-items-center mb-3">
          <h5 class="mb-0 fw-bold footer-brand">
             Jogja<span style="color: #C69C6D;">Verse.</span>
          </h5>
        </div>
        <p class="small text-light opacity-75 mb-4">
          Platform pariwisata digital terlengkap untuk menjelajahi keistimewaan Yogyakarta, dari destinasi budaya hingga kuliner legendaris.
        </p>
        <ul class="list-unstyled small opacity-75">
          <li class="mb-2 d-flex align-items-start">
            <i class="bi bi-geo-alt-fill icon-gold me-2 mt-1"></i>
            <span>Jl. Malioboro No. 1, Yogyakarta 55271</span>
          </li>
          <li class="mb-2 d-flex align-items-center">
            <i class="bi bi-envelope-fill icon-gold me-2"></i>
            <span>halo@jogjaverse.id</span>
          </li>
          <li class="d-flex align-items-center">
            <i class="bi bi-telephone-fill icon-gold me-2"></i>
            <span>(0274) 123456</span>
          </li>
        </ul>
      </div>

      <!-- Links: Wisata -->
      <div class="col-lg-2 col-6">
        <h6 class="fw-bold mb-3 text-white">Jelajah</h6>
        <ul class="list-unstyled footer-link">
          <li><a href="#destinasi">Destinasi Populer</a></li>
          <li><a href="#event">Kalender Event</a></li>
          <li><a href="#kuliner">Kuliner Khas</a></li>
          <li><a href="#">Virtual Tour</a></li>
        </ul>
      </div>

      <!-- Links: Layanan -->
      <div class="col-lg-3 col-6">
        <h6 class="fw-bold mb-3 text-white">Layanan</h6>
        <ul class="list-unstyled footer-link">
          <li><a href="#">Pusat Bantuan</a></li>
          <li><a href="#">Panduan Perjalanan</a></li>
          <li><a href="#">Kerjasama Mitra</a></li>
          <li><a href="#">Kontak Kami</a></li>
        </ul>
      </div>

      <!-- Links: Tentang -->
      <div class="col-lg-3 col-6">
        <h6 class="fw-bold mb-3 text-white">Tentang</h6>
        <ul class="list-unstyled footer-link">
          <li><a href="#">Tentang JogjaVerse</a></li>
          <li><a href="#">Kebijakan Privasi</a></li>
          <li><a href="#">Syarat & Ketentuan</a></li>
          <li><a href="#">Karir</a></li>
        </ul>
      </div>
    </div>

    <hr class="border-light opacity-10 my-4">

    <!-- Bottom Footer -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pb-4 gap-3">
      <small class="opacity-50">
        &copy; 2025 JogjaVerse. Disponsori oleh Pemerintah Kota Yogyakarta.
      </small>

      <div class="d-flex gap-2">
        <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
        <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
        <a href="#" class="social-icon"><i class="bi bi-twitter-x"></i></a>
        <a href="#" class="social-icon"><i class="bi bi-youtube"></i></a>
      </div>
    </div>

  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>