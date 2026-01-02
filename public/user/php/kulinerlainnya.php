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

  <style>
    /* 1. VARIABLE WARNA */
    :root {
        --primary-color: #2D1B20; /* Dark Maroon */
        --secondary-color: #C69C6D; /* Gold */
    }
    
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #FDFBF7;
    }

    h1, h2, h3, .navbar-brand, .font-serif {
        font-family: 'Playfair Display', serif;
    }

    /* 2. NAVBAR (Style Besar & Mewah) */
    .navbar {
        transition: all 0.4s ease;
        padding: 1.75rem 0; /* Padding Besar */
    }
    .navbar-brand { font-size: 2rem; }
    .nav-link {
        font-size: 1.4rem;
        margin: 0 15px;
        font-weight: 500;
        position: relative;
    }
    .btn-login-custom {
        padding: 14px 36px;
        font-size: 1.25rem;
        font-weight: 600;
        border-radius: 50px;
    }
    .nav-link::after {
        content: ''; position: absolute; width: 0; height: 3px; bottom: 0px; left: 50%;
        background-color: var(--secondary-color); transition: all 0.3s ease; transform: translateX(-50%);
    }
    .nav-link:hover::after, .nav-link.active::after { width: 80%; }

    /* 3. HEADER BACKGROUND (Kuliner Theme) */
    #background {
        background: linear-gradient(rgba(45, 27, 32, 0.7), rgba(45, 27, 32, 0.7)), url('https://images.unsplash.com/photo-1555126634-323283e090fa?q=80&w=1920&auto=format&fit=crop'); 
        background-color: var(--primary-color);
        background-size: cover;
        background-position: center;
        height: 350px; 
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 108px; /* Jarak Navbar Fixed */
    }

    /* 4. CARD STYLE KHUSUS KULINER (Updated Font Sizes) */
    .card-kuliner {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
        background: #fff;
    }
    .card-kuliner:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    
    /* Penyesuaian Font Content Card agar tidak kekecilan */
    .card-body h5 {
        font-size: 1.25rem; /* fs-5 equivalent, diperjelas */
        line-height: 1.4;
    }
    .card-body p {
        font-size: 0.95rem; /* Lebih besar dari small (0.875rem) */
    }
    .card-meta {
        font-size: 0.9rem; /* Ukuran info lokasi & harga */
    }

    /* 5. TOMBOL DETAIL ANIMATED */
    .link-gold-animated {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;       
        color: var(--primary-color);         
        text-decoration: none;
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem; /* Sedikit diperbesar */
        transition: color 0.3s ease;
        padding-bottom: 2px;
    }
    .link-gold-animated i { font-size: 1.1rem; transition: transform 0.3s ease; }
    .link-gold-animated:hover { color: var(--secondary-color); }
    .link-gold-animated:hover i { transform: translateX(4px); }
    .link-gold-animated::after {
        content: ''; position: absolute; width: 0; height: 2px; bottom: 0px; left: 50%;
        background-color: var(--secondary-color); transition: all 0.3s ease; transform: translateX(-50%);
    }
    .link-gold-animated:hover::after { width: 100%; }

    /* 6. PAGINATION */
    .pagination .page-link {
        color: var(--primary-color);
        border: 1px solid #dee2e6;
        margin: 0 3px;
        border-radius: 5px;
    }
    .pagination .page-link:hover {
        background-color: #f8f9fa;
        color: var(--secondary-color);
        border-color: var(--secondary-color);
    }
    .pagination .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: #fff;
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-dark" href="landingpageclena.php">
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
          echo '<a href="/public/dashboard_user.php" class="d-flex align-items-center text-decoration-none text-dark">';
          echo '<img src="'.$avatarPath.'" alt="Profile" style="width:38px;height:38px;border-radius:50%;object-fit:cover;margin-right:8px;">';
          echo '<span class="fw-medium d-none d-md-inline">'. $displayName .'</span>';
          echo '</a>';
        } else {
          echo '<a href="#login" class="btn btn-outline-dark btn-login-custom">Login</a>';
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

<!-- FOOTER -->
<footer class="footer-custom text-light pt-5" style="background-color: #321B1F;">
  <div class="container">
    <div class="row gy-4">
      <div class="col-lg-4">
        <div class="d-flex align-items-center mb-3">
          <h5 class="mb-0 fw-bold font-serif">Jogja<span style="color: var(--secondary-color);">Verse.</span></h5>
        </div>
        <p class="small text-light opacity-75">
          Sistem pariwisata cerdas terintegrasi untuk mendukung pengelolaan
          destinasi yang efektif dan pengalaman wisatawan yang tak terlupakan.
        </p>
        <ul class="list-unstyled small opacity-75">
          <li class="mb-2"><i class="bi bi-geo-alt me-2 text-warning"></i> Jl. Malioboro No. 1, Yogyakarta</li>
          <li class="mb-2"><i class="bi bi-envelope me-2 text-warning"></i> info@wisatajogja.go.id</li>
          <li><i class="bi bi-telephone me-2 text-warning"></i> (0274) 123456</li>
        </ul>
      </div>
      <div class="col-lg-2 col-6">
        <h6 class="fw-bold mb-3">Wisata</h6>
        <ul class="list-unstyled small opacity-75">
          <li><a href="destinasiLainnya.php" class="text-white text-decoration-none">Destinasi</a></li>
          <li><a href="eventLainnya.php" class="text-white text-decoration-none">Event</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-6">
        <h6 class="fw-bold mb-3">Layanan</h6>
        <ul class="list-unstyled small opacity-75">
            <li><a href="#" class="text-white text-decoration-none">Reservasi Tiket</a></li>
            <li><a href="#" class="text-white text-decoration-none">Panduan</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-6">
        <h6 class="fw-bold mb-3">Tentang</h6>
        <ul class="list-unstyled small opacity-75">
            <li><a href="#" class="text-white text-decoration-none">Tentang Kami</a></li>
            <li><a href="#" class="text-white text-decoration-none">Kebijakan Privasi</a></li>
        </ul>
      </div>
    </div>
    <hr class="border-light opacity-25 my-4">
    <div class="text-center pb-4 small opacity-50">
      &copy; 2025 JogjaVerse. Disponsori oleh Pemerintah Kota Yogyakarta
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>