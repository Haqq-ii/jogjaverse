<?php
require_once __DIR__ . '/../../../config/koneksi.php';


/* 3 DESTINASI POPULER */
$destinasi = [];
$res = $koneksi->query("
  SELECT * FROM destinasi
  WHERE status = 'publish'
  ORDER BY dibuat_pada DESC
  LIMIT 3
");

while ($row = $res->fetch_assoc()) {
  $destinasi[] = $row;
}

/* 4 EVENT POPULER */
$event = [];
$res = $koneksi->query("
  SELECT * FROM event
  WHERE status = 'publish'
  ORDER BY kuota DESC
  LIMIT 4
");

while ($row = $res->fetch_assoc()) {
  $event[] = $row;
}


/* 3 KULINER POPULER */
$kuliner = [];
$res = $koneksi->query("
  SELECT * FROM kuliner
  WHERE status = 'publish'
  ORDER BY dibuat_pada DESC
  LIMIT 3
");

while ($row = $res->fetch_assoc()) {
  $kuliner[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- AOS CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/style.css">
    <title>JogjaVerse</title>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-transparent fixed-top" data-aos="fade-down">
  <div class="container">
    
    <a class="navbar-brand fw-bold" href="#" >JogjaVerse</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center" style="font-size: 1.3rem;">
        <li class="nav-item" style="font-size: 1.2rem;">
          <a class="nav-link active fw-bold" href="../html/destinasiLainnya.php">Destinasi</a>
        </li>
        <li class="nav-item" style="font-size: 1.2rem;">
          <a class="nav-link fw-bold" href="#atraksi">Atraksi</a>
        </li>
        <li class="nav-item" style="font-size: 1.2rem;">
          <a class="nav-link fw bold" href="#event">Event</a>
        </li>
        <li class="nav-item fw-bold" style="font-size: 1.2rem;">
          <a class="nav-link" href="#event">Kuliner</a>
        </li>
      </ul>

      <div class="d-flex justify-content-center">
        <a href="/public/login.php" class="btn btn-outline-light px-3 py-1 rounded-3 fw-medium">Login</a>
      </div>

    </div>
  </div>
</nav>

<!-- JUMBOTRON -->
<section
  id="background"
  class="container-fluid p-5 d-flex flex-column justify-content-center align-items-center text-center text-white min-vh-100"
  data-aos="fade-up"
>

  <h1 class="fw-bold mb-3" style=" font-size: clamp(2.5rem, 8vw, 8rem); letter-spacing: 1rem;">
    <span class="text-gold">YOGYAKARTA</span>
  </h1>
  <p class="mb-4 fw-bold" style="font-size: clamp(1rem, 3vw, 2.5rem); letter-spacing: 1rem;">
    BUDAYA DALAM SETIAP LANGKAH
  </p>

</section>

<!-- Deskripsi -->
<section class="deskripsi d-flex justify-content-center align-items-center text-center" data-aos="fade-up">
<!-- style="background-color: #F1EFEC; padding: 2rem ;"  -->
  <div class="desk">
      <h2 class="title mb-2 fw-bold" style="font-size: clamp(2rem, 3.5vw, 3.5rem);">SELAMAT DATANG DI YOGYAKARTA</h2>
      <p class="sub-title" style="font-size: clamp(1.1rem, 2vw, 2rem);">
        Dikenal sebagai daerah budaya dan pendidikan yang sarat sejarah dan <br> kearifan lokal
      </p>
  </div>
</section>


<!-- IMAGE + TEXT -->
<section class="container my-5">
  <div class="row align-items-start">

    <!-- KIRI: CARD BERTINGKAT -->
    <div class="col-md-4" data-aos="fade-right">
      <div class="row g-3 justify-content-center">
        <div class="col-6" data-aos="zoom-out" data-aos-delay="400"><div class="card rounded-3 overflow-hidden opacity-100"><img src="../img/Rama.jpg" class="img-fluid" alt=""></div></div>
        <div class="col-6" data-aos="zoom-out" data-aos-delay="500"><div class="card rounded-3 overflow-hidden opacity-100"><img src="../img/pantai.jpg" class="img-fluid" alt=""></div></div>
        <div class="col-5" data-aos="zoom-out" data-aos-delay="600"><div class="card rounded-3 overflow-hidden opacity-75 scale-down"><img src="../img/Tugu.jpg" class="img-fluid" alt=""></div></div>
        <div class="col-5" data-aos="zoom-out" data-aos-delay="700"><div class="card rounded-3 overflow-hidden opacity-50 scale-down"><img src="../img/Borombur.jpg" class="img-fluid" alt=""></div></div>
      </div>
    </div>

    <!-- KANAN: TEKS -->
    <div class="col-md-8 ps-md-4" data-aos="fade-left" style="font-size: clamp(1rem, 1.3vw, 1.3rem);">
      <p style="color: #846267;">
        Yogyakarta, atau sering disebut Jogja, adalah kota budaya yang kaya akan sejarah dan tradisi.
         Terletak di pulau Jawa bagian selatan, kota ini menjadi pusat pendidikan, seni, dan pariwisata
          yang populer di Indonesia. Dengan keraton sebagai simbol kebudayaan Jawa, serta candi-candi
           bersejarah seperti Prambanan dan Borobudur di sekitarnya, 
        Yogyakarta memadukan keindahan arsitektur klasik dengan kehidupan modern yang dinamis.
      </p>
      <p style="color: #846267;">
        Selain warisan budayanya, Yogyakarta juga dikenal dengan kreativitas masyarakatnya, pasar seni, kuliner khas, dan suasana kota 
        yang hangat dan ramah bagi wisatawan. Kota ini menghadirkan pengalaman unik antara belajar sejarah, menikmati alam, dan merasakan
         kehidupan urban yang tetap kental nuansa tradisionalnya. 
        Setiap sudut kota mengajak pengunjung untuk menjelajahi cerita, seni, dan kearifan lokal yang membuat Yogyakarta tak terlupakan.
      </p>
    </div>

  </div>
</section>


<!-- CARD SECTION -->
<section class="py-5">
  <div class="container" style="background-color:#F9F7F5; border-radius: 20px; padding: 2rem;" data-aos="fade-up">
    
    <h2 class="fw-bold mb-3 text-center" style="color: #321B1F; font-size: clamp(2rem, 3.5vw, 3.5rem);">Tempat Destinasi <span style="color: #EEB32B;">Unggulan</span></h2>
    <p class="text-center mb-5" style="color:#846267; font-size: clamp(1.1rem, 1.5vw, 1.5rem);">
      Temukan destinasi wisata terbaik di Yogyakarta dengan informasi lengkap, ulasan <br>
      pengunjung, dan pemesanan tiket online
    </p>

    <div class="row g-4 justify-content-center mx-auto">

      <!-- Card 1 -->
<?php foreach ($destinasi as $d): ?>
<div class="col-6 col-sm-6 col-md-4 col-lg-3"
     data-aos="zoom-in"
     data-aos-delay="200">

  <div class="card border-0 shadow rounded-4 overflow-hidden h-100">

    <div class="position-relative card-image-wrapper">
      <img src="<?= $d['gambar_sampul_url'] ?>"
           class="card-img-top object-fit-cover"
           alt="<?= $d['nama'] ?>"
           style="height: 200px;">

      <div class="card-gradient-overlay"></div>

      <span class="position-absolute top-0 start-0 m-2 py-1 px-2
                   bg-white text-dark rounded-pill fw-bold shadow-sm"
            style="font-size: 0.6rem;">
        Destinasi
      </span>

      <div class="position-absolute bottom-0 start-0 m-2 text-white z-2
                  d-flex align-items-center"
           style="font-size: 0.75rem;">
        <i class="bi bi-star-fill text-warning me-1"></i>
        <span class="fw-bold me-1">4.8</span>
        <span class="opacity-75">(2.4k)</span>
      </div>
    </div>

    <div class="card-body p-3 d-flex flex-column">

      <h5 class="card-title fw-bold mb-1 font-serif fs-6">
        <?= $d['nama'] ?>
      </h5>

      <p class="text-muted mb-3 flex-grow-1"
         style="font-size: 0.7rem; line-height: 1.4;">
        <?= $d['deskripsi'] ?? '' ?>
      </p>

      <div class="d-flex align-items-center justify-content-between mt-auto">

        <div class="d-flex align-items-center text-muted"
             style="font-size: 0.7rem;">
          <i class="bi bi-clock me-1"></i>
            <span>
              <?= $d['jam_operasional'] ?? '-' ?>
            </span>
        </div>

        <a href="#"
           class="text-decoration-none fw-bold text-dark
                  icon-link icon-link-hover"
           style="font-size: 0.75rem;">
          Detail <i class="bi bi-arrow-right"></i>
        </a>

      </div>
    </div>

  </div>
</div>
<?php endforeach; ?>

      <div class="detail-destinasi">
          
          <a href="destinasiLainnya.php" class=""><i class="bi bi-geo-alt">Lihat Destinasi Lainnya</i></a>  
      </div>

    </div>
  </div>
</section>
<!-- Akhir Card Section -->

<!-- Even dan Atraksi-->
 <section class="py-5">
    <h2 class="fw-bold mb-3 text-center" style="color: #321B1F; font-size: clamp(2rem, 3.5vw, 3.5rem);">Event & <span style="color: #4A1B22;">Atraksi</span></h2>
    <p class="text-center mb-5" style="color:#846267; font-size: clamp(1.1rem, 1.5vw, 1.5rem); ">
      Ikuti berbagai event budaya, festival, dan atraksi menarik yang diselenggarakan di Kota 
      <br>Yogyakarta
    </p>
    
  <div class="container" data-aos="fade-up">

  <div class="row g-3">
    
    <div class="col-lg-6">
      <div class="card h-100 border-0 rounded-4 p-3 p-lg-4 text-white position-relative overflow-hidden shadow-lg">
        
        <img src="../img/candi.jpg" 
             class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" 
             alt="Latar Belakang">

        <div class="position-absolute top-0 start-0 w-100 h-100" 
             style="background: linear-gradient(0deg, rgba(74, 27, 34, 0.95) 0%, rgba(74, 27, 34, 0.6) 100%); z-index: 1;">
        </div>

        <div class="position-relative h-100 d-flex flex-column justify-content-center" style="z-index: 2;">
          
          <span class="position-absolute top-0 end-0 badge text-dark fw-bold px-3 py-1 rounded-pill shadow-sm" 
                style="background-color: #EEB32B; font-size: 0.75rem;">
            Event Unggulan
          </span>

          <div class="mb-2">
            <span class="px-2 py-1 rounded-pill text-white border border-light border-opacity-25" 
                  style="font-size: 0.7rem; background: rgba(255,255,255,0.2); backdrop-filter: blur(4px);">
              Seni & Budaya
            </span>
          </div>

          <h3 class="fs-2 fw-bold font-serif mb-3">Festival Kesenian Yogyakarta</h3>

          <div class="row g-2 mb-4 text-white-50" style="font-size: 0.85rem;">
            <div class="col-6 d-flex align-items-center">
              <i class="bi bi-calendar-event me-2 text-white"></i> <span class="text-white">15 – 22 Juni 2025</span>
            </div>
            <div class="col-6 d-flex align-items-center">
              <i class="bi bi-geo-alt me-2 text-white"></i> <span class="text-white">Benteng Vredeburg</span>
            </div>
            <div class="col-6 d-flex align-items-center">
              <i class="bi bi-clock me-2 text-white"></i> <span class="text-white">18:00 WIB</span>
            </div>
            <div class="col-6 d-flex align-items-center">
              <i class="bi bi-people me-2 text-white"></i> <span class="text-white">5k+ pengunjung</span>
            </div>
          </div>

          <div>
            <a href="#" class="btn btn-sm fw-bold rounded-pill px-4 py-2 w-100 w-md-auto shadow" 
               style="background-color: #EEB32B; color: #321B1F;">
              Pesan Tiket <i class="bi bi-arrow-right ms-2"></i>
            </a>
          </div>

        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="d-flex flex-column gap-2 h-100">

        <div class="card border-0 rounded-4 p-3 shadow-sm card-event-hover" style="background-color: #F9F7F5;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
              <span class="badge border border-danger border-opacity-10 rounded-pill px-2 fw-normal" style="font-size: 0.7rem; background-color: #EBE1E1; color: #6F202D;">
                Festival
              </span>
              <span class="ms-2 small" style="color: #846267; font-size: 0.8rem;">28 – 30 Juli 2025</span>
            </div>
            <a href="#" class="text-dark"><i class="bi bi-arrow-right"></i></a>
          </div>
          <h6 class="fw-bold font-serif mb-1 fs-5" style="color: #4A1B22;">Jogja Java Carnival</h6>
          <div class="text-muted d-flex gap-3" style="font-size: 0.8rem;">
            <span style="color: #846267;"><i class="bi bi-geo-alt me-1"></i> Malioboro</span>
            <span style="color: #846267;"><i class="bi bi-clock me-1"></i> 19:00 WIB</span>
          </div>
        </div>

        <div class="card border-0 rounded-4 p-3 shadow-sm card-event-hover" style="background-color: #F9F7F5;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
              <span class="badge border border-danger border-opacity-10 rounded-pill px-2 fw-normal" style="font-size: 0.7rem; background-color: #EBE1E1; color: #6F202D;">
                Tradisi
              </span>
              <span class="ms-2 small" style="color: #846267; font-size: 0.8rem;">5 – 12 Agustus 2025</span>
            </div>
            <a href="#" class="text-dark"><i class="bi bi-arrow-right"></i></a>
          </div>
          <h6 class="fw-bold font-serif mb-1 fs-5" style="color: #4A1B22;">Sekaten</h6>
          <div class="text-muted d-flex gap-3" style="font-size: 0.8rem;">
            <span style="color: #846267;"><i class="bi bi-geo-alt me-1"></i> Alun-Alun Utara</span>
            <span style="color: #846267;"><i class="bi bi-clock me-1"></i> Sepanjang Hari</span>
          </div>
        </div>

        <div class="card border-0 rounded-4 p-3 shadow-sm card-event-hover" style="background-color: #F9F7F5;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
              <span class="badge border border-danger border-opacity-10 rounded-pill px-2 fw-normal" style="font-size: 0.7rem; background-color: #EBE1E1; color:#6F202D;">
                Budaya
              </span>
              <span class="ms-2 small" style="color: #846267; font-size: 0.8rem;">
                Setiap Sabtu
              </span>
            </div>
            <a href="#" class="text-dark"><i class="bi bi-arrow-right"></i></a>
          </div>
          <h6 class="fw-bold font-serif mb-1 fs-5" style="color: #4A1B22;">Pagelaran Wayang</h6>
          <div class="text-muted d-flex gap-3" style="font-size: 0.8rem;">
            <span style="color: #846267;"><i class="bi bi-geo-alt me-1" ></i> Keraton Yogya</span>
            <span style="color: #846267;"><i class="bi bi-clock me-1"></i> 20:00 WIB</span>
          </div>
        </div>

      </div>
    </div>

  </div>

    <div class="detail-destinasi"> 
        <a href="#" class="">
            <i class="bi bi-calendar3">Lihat Semua Event</i>
        </a>  
    </div>

      <!-- <div class="text-center mt-5">
    <a href="#" class="btn btn-sm btn-outline-dark rounded-3 px-4 py-2" style="border-color: #4A1B22; color: #4A1B22;">
      <i class="bi bi-calendar3 me-2"></i> Lihat Semua Event
    </a>
  </div> -->

</div>

 </section>
 <!-- akhir Even dan Atraksi -->


  <!-- Kuliner -->
 <section class="py-5 mt-5">
  <div class="container" style="background-color:#F9F7F5; border-radius: 20px; padding: 2rem;" data-aos="fade-up">
    
    <h2 class="fw-bold mb-3 text-center" style="color: #321B1F; font-size: clamp(2rem, 3.5vw, 3.rem);">Kuliner  <span style="color: #EEB32B; font-size: clamp(2rem, 3.5vw, 3.rem)">Yogyakarta</span></h2>
    <p class="text-center mb-5" style="color:#846267; font-size: clamp(1.1rem, 1.5vw, 1.5rem);">
      Temukan destinasi wisata terbaik di Yogyakarta dengan informasi lengkap, ulasan <br>
      pengunjung, dan pemesanan tiket online
    </p>

    <div class="row g-4 justify-content-center mx-auto">

      <!-- Card 1 -->
    <?php foreach ($kuliner as $k): ?>
<div class="col-6 col-md-4 col-lg-3">
  <div class="card border-0 shadow rounded-4 overflow-hidden h-100">

    <img src="<?= $k['gambar_sampul_url'] ?>" class="card-img-top" style="height:200px; object-fit:cover;">

    <div class="card-body">
      <span class="badge bg-light text-dark small"><?= $k['kategori'] ?></span>

      <h6 class="fw-bold mt-2"><?= $k['nama'] ?></h6>

      <p class="text-warning small"><?= $k['menu_unggulan'] ?></p>

      <div class="d-flex justify-content-between small">
        <span><i class="bi bi-geo-alt"></i> <?= $k['kota'] ?></span>
        <span class="fw-bold">
          Rp <?= number_format($k['harga_min']) ?>
        </span>
      </div>

    </div>
  </div>
</div>
<?php endforeach; ?>

      <div class="detail-destinasi">
          
          <a href="#" class=""><i class="bi bi-fork-knife">Jelahai Semua Kuliner</i></a>  
      </div>

    </div>
  </div>
</section>
<!-- Akhir Card Section -->




<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<!-- AOS JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
<script>
  AOS.init({
    duration: 1000, // durasi animasi
    once: true    // animasi akan muncul setiap scroll
  });
</script>

</body>
</html>
