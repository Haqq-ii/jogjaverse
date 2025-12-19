<?php
require_once __DIR__ . "/../../../config/koneksi.php";

/*
  AMBIL DATA PUBLIK DESTINASI + NAMA KATEGORI
*/
$query = "
  SELECT
    d.id_destinasi,
    d.nama              AS nama_destinasi,
    d.deskripsi         AS deskripsi_singkat,
    k.nama              AS kategori,
    d.gambar_sampul_url AS gambar,
    d.jam_operasional   AS estimasi_waktu
  FROM destinasi d
  JOIN kategori k
       ON d.id_kategori = k.id_kategori
  WHERE d.status = 'publish'
    AND k.tipe = 'destinasi'
  ORDER BY d.dibuat_pada DESC
";

$result = mysqli_query($koneksi, $query);

if (!$result) {
  die('Query error: ' . mysqli_error($koneksi));
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../css/destinasiLainnya.css">

  <title>JogjaVerse</title>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm p-3">
  <div class="container">
    <a class="navbar-brand fw-bold text-dark" href="../php/langingPage.php" style="font-size: 1.3rem;">JogjaVerse</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto text-center" style="font-size: 1.3rem;">
        <li class="nav-item"><a class="nav-link fw-bold text-dark" href="#">Destinasi</a></li>
        <li class="nav-item"><a class="nav-link fw-bold text-dark" href="#atraksi">Atraksi</a></li>
        <li class="nav-item"><a class="nav-link fw-bold text-dark" href="#event">Event</a></li>
        <li class="nav-item"><a class="nav-link fw-bold text-dark" href="#kuliner">Kuliner</a></li>
      </ul>
      <a href="#login" class="btn btn-outline-dark rounded-3">Login</a>
    </div>
  </div>
</nav>

<section id="background">
  <h1 class="fw-bold" style="font-size: 4rem;">
    <span class="text-white">Destinasi</span>
  </h1>
</section>



<!-- CARD SECTION -->
<section class="py-5" >
  <div class="container-fluid px-4" data-aos="fade-up">

        <!-- dropdown -->
        <div class="dropdown d-flex justify-content-end" style="margin-right: 3.9rem;">
            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Kategori Destinasi
            </a>

            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Alam</a></li>
                <li><a class="dropdown-item" href="#">Sejarah</a></li>
                <li><a class="dropdown-item" href="#">Budaya</a></li>
            </ul>
        </div>


    <!-- Wrapper background -->
    <div style="padding:3rem">

      <!-- Cards -->
      <div class="row g-4 justify-content-center mx-0">

<?php while ($row = mysqli_fetch_assoc($result)): ?>
<div class="col-6 col-sm-6 col-md-4 col-lg-3">
  <div class="card border-0 shadow rounded-4 overflow-hidden h-100">

    <div class="position-relative">
      <img src="<?= htmlspecialchars($row['gambar']) ?>"
           class="card-img-top object-fit-cover"
           style="height:200px;"
           alt="<?= htmlspecialchars($row['nama_destinasi']) ?>">

      <!-- BADGE KATEGORI -->
      <span class="position-absolute top-0 start-0 m-2 px-2 py-1 bg-white rounded-pill fw-bold shadow-sm"
            style="font-size:0.6rem;">
        <?= htmlspecialchars($row['kategori']) ?>
      </span>
    </div>

    <div class="card-body p-3 d-flex flex-column">
      <h5 class="fw-bold mb-1 fs-6">
        <?= htmlspecialchars($row['nama_destinasi']) ?>
      </h5>

      <p class="text-muted mb-3 flex-grow-1"
         style="font-size:0.7rem; line-height:1.4;">
        <?= htmlspecialchars($row['deskripsi_singkat']) ?>
      </p>

      <div class="d-flex justify-content-between align-items-center">
        <span class="text-muted" style="font-size:0.7rem;">
          <i class="bi bi-clock me-1"></i>
          <?= htmlspecialchars($row['estimasi_waktu']) ?>
        </span>

        <a href="detailDestinasi.php?id=<?= $row['id_destinasi'] ?>"
           class="fw-bold text-dark text-decoration-none"
           style="font-size:0.75rem;">
          Detail <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>

  </div>
</div>
<?php endwhile; ?>




      </div>
    </div>
  </div>
</section>
<!-- END CARD SECTION -->

<!-- footer -->
 <footer class="footer-custom text-light pt-5" style="background-color: #321B1F;">
  <div class="container">

    <div class="row gy-4">

      <!-- Brand & Contact -->
      <div class="col-lg-4">
        <div class="d-flex align-items-center mb-3">
          <div class="logo-icon me-2">
            <i class="bi bi-geo-alt-fill"></i>
          </div>
          <h5 class="mb-0 fw-bold">WisataJogja</h5>
        </div>

        <p class="small text-light opacity-75">
          Sistem pariwisata cerdas terintegrasi untuk mendukung pengelolaan
          destinasi yang efektif dan pengalaman wisatawan yang tak terlupakan.
        </p>

        <ul class="list-unstyled small">
          <li class="mb-2">
            <i class="bi bi-geo-alt me-2"></i>
            Jl. Malioboro No. 1, Yogyakarta 55271
          </li>
          <li class="mb-2">
            <i class="bi bi-envelope me-2"></i>
            info@wisatajogja.go.id
          </li>
          <li>
            <i class="bi bi-telephone me-2"></i>
            (0274) 123456
          </li>
        </ul>
      </div>

      <!-- Wisata -->
      <div class="col-lg-2 col-6">
        <h6 class="fw-semibold mb-3">Wisata</h6>
        <ul class="list-unstyled footer-link">
          <li><a href="#">Destinasi</a></li>
          <li><a href="#">Event</a></li>
          <li><a href="#">Kuliner</a></li>
          <li><a href="#">Peta Interaktif</a></li>
        </ul>
      </div>

      <!-- Layanan -->
      <div class="col-lg-3 col-6">
        <h6 class="fw-semibold mb-3">Layanan</h6>
        <ul class="list-unstyled footer-link">
          <li><a href="#">Reservasi Tiket</a></li>
          <li><a href="#">Panduan Wisata</a></li>
          <li><a href="#">FAQ</a></li>
          <li><a href="#">Hubungi Kami</a></li>
        </ul>
      </div>

      <!-- Tentang -->
      <div class="col-lg-3 col-6">
        <h6 class="fw-semibold mb-3">Tentang</h6>
        <ul class="list-unstyled footer-link">
          <li><a href="#">Tentang Kami</a></li>
          <li><a href="#">Kebijakan Privasi</a></li>
          <li><a href="#">Syarat & Ketentuan</a></li>
          <li><a href="#">Karir</a></li>
        </ul>
      </div>

    </div>

    <hr class="border-light opacity-25 my-4">

    <!-- Bottom -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center pb-4 gap-3">
      <small class="opacity-75">
        © 2025 WisataJogja. Disponsori oleh Pemerintah Kota Yogyakarta
      </small>

      <div class="d-flex gap-3">
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
