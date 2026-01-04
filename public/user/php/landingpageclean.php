<?php
require_once __DIR__ . '/../../../config/koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/* 3 DESTINASI POPULER (Dibiarkan Sesuai Request) */
$destinasi = [];
$res = $koneksi->query("
  SELECT * FROM destinasi
  WHERE status = 'publish'
  ORDER BY dibuat_pada DESC
  LIMIT 3
");

if ($res) {
    while ($row = $res->fetch_assoc()) {
      $destinasi[] = $row;
    }
}

/* 4 EVENT POPULER (Disambungkan ke DB) */
// Mengambil 4 event teratas berdasarkan kuota (atau tanggal)
$event = [];
$res = $koneksi->query("
  SELECT e.*, k.nama AS kategori
  FROM event e
  LEFT JOIN kategori k ON e.id_kategori = k.id_kategori
  WHERE e.status = 'publish'
  ORDER BY e.kuota DESC
  LIMIT 4
");

if ($res) {
    while ($row = $res->fetch_assoc()) {
      $event[] = $row;
    }
}


/* 3 KULINER POPULER (Disambungkan ke DB) */
// Menggunakan created_at sesuai screenshot table kuliner
$kuliner = [];
$res = $koneksi->query("
  SELECT * FROM kuliner
  WHERE status = 'publish'
  ORDER BY dibuat_pada DESC
  LIMIT 3
");

if ($res) {
    while ($row = $res->fetch_assoc()) {
      $kuliner[] = $row;
    }
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

    
  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- AOS CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

    <link rel="stylesheet" href="../css/style2.css">
    <title>JogjaVerse</title>

  <style>
    
  </style>
</head>
<body>

<!-- 2. STRUKTUR HTML -->
<nav class="navbar navbar-expand-lg fixed-top navbar-dark">
  <div class="container">
    
    <!-- Logo Brand -->
    <a class="navbar-brand fw-bold" href="#">
        Jogja<span style="color: #C69C6D;">Verse.</span>
    </a>

    <!-- Tombol Toggle Mobile -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center">
        <li class="nav-item">
          <!-- Menggunakan link sesuai request awal Anda tapi dengan style baru -->
          <a class="nav-link" href="destinasiLainnya.php">Destinasi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="eventLainnya.php">Event & Atraksi</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="kulinerlainnya.php">Kuliner</a>
        </li>
      </ul>

      <!-- Tombol Login -->
  <div class="d-flex justify-content-center">
    <?php
    if (!empty($_SESSION['login']) && $_SESSION['login'] === true) {
      $displayName = htmlspecialchars($_SESSION['nama_lengkap'] ?? ($_SESSION['username'] ?? 'User'));
      $avatarPath = '/public/user/img/default_avatar.png';

      echo '<a href="/public/user.php" class="d-flex align-items-center text-decoration-none">';

      // UPDATE: Ukuran gambar diubah jadi 35px agar pas dengan navbar kecil
      echo '<img src="' . $avatarPath . '" alt="Profile" style="width:35px; height:35px; border-radius:50%; object-fit:cover; margin-right:8px;">';

      echo '<span class="text-white fw-medium d-none d-md-inline" style="font-size: 0.95rem;">' . $displayName . '</span>';
      echo '</a>';
    } else {
      echo '<a href="/public/login.php" class="btn btn-gold px-4">Login</a>';
    }
    ?>
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

      <!-- Card Loop -->
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

      <!-- Deskripsi dihapus sesuai snippet asli Anda yang kosong di bagian ini -->

      <div class="d-flex align-items-center justify-content-between mt-auto">

        <div class="d-flex align-items-center text-muted"
             style="font-size: 0.7rem;">
          <i class="bi bi-clock me-1"></i>
            <span>
              <?= $d['jam_operasional'] ?? '-' ?>
            </span>
        </div>

        <!-- PERUBAHAN DI SINI: Menggunakan class link-detail-animated -->
        <a href="#"
           class="link-detail-animated"
           style="font-size: 0.75rem;">
          Detail <i class="bi bi-arrow-right"></i>
        </a>

      </div>
    </div>

  </div>
</div>
<?php endforeach; ?>

        <div class="detail-destinasi">
            <a href="destinasiLainnya.php" class="link-gold-animated">
                <i class="bi bi-geo-alt"></i> Lihat Destinasi Lainnya
            </a> 
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
    
    <!-- LEFT COLUMN: BIG CARD (Event Pertama/Unggulan) -->
    <div class="col-lg-6">
      <?php if(isset($event[0])): $mainEvent = $event[0]; ?>
      <div class="card h-100 border-0 rounded-4 p-3 p-lg-4 text-white position-relative overflow-hidden shadow-lg">
        
        <img src="<?= $mainEvent['gambar_sampul_url'] ?>" 
             class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover" 
             alt="<?= $mainEvent['judul'] ?>">

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
              <?= $mainEvent['kategori'] ?? 'Event' ?>
            </span>
          </div>

          <h3 class="fs-2 fw-bold font-serif mb-3"><?= $mainEvent['judul'] ?></h3>

          <div class="row g-2 mb-4 text-white-50" style="font-size: 0.85rem;">
            <div class="col-6 d-flex align-items-center">
              <i class="bi bi-calendar-event me-2 text-white"></i> 
              <span class="text-white">
                <?= date('d M', strtotime($mainEvent['mulai_pada'])) ?> – <?= date('d M Y', strtotime($mainEvent['selesai_pada'])) ?>
              </span>
            </div>
            <div class="col-6 d-flex align-items-center">
              <i class="bi bi-geo-alt me-2 text-white"></i> <span class="text-white"><?= $mainEvent['lokasi'] ?></span>
            </div>
            <div class="col-6 d-flex align-items-center">
              <i class="bi bi-clock me-2 text-white"></i> 
              <span class="text-white"><?= date('H:i', strtotime($mainEvent['mulai_pada'])) ?> WIB</span>
            </div>
            <div class="col-6 d-flex align-items-center">
              <i class="bi bi-people me-2 text-white"></i> 
              <span class="text-white"><?= $mainEvent['kuota'] ?? 'Unlimited' ?> kuota</span>
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
      <?php endif; ?>
    </div>

    <!-- RIGHT COLUMN: LIST (Event 2, 3, 4) -->
    <div class="col-lg-6">
      <div class="d-flex flex-column gap-2 h-100">

        <?php 
        // Loop dari index 1 sampai 3 (3 item)
        $listEvents = array_slice($event, 1, 3);
        foreach($listEvents as $e): 
        ?>
        <div class="card border-0 rounded-4 p-3 shadow-sm card-event-hover" style="background-color: #F9F7F5;">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
              <span class="badge border border-danger border-opacity-10 rounded-pill px-2 fw-normal" style="font-size: 0.7rem; background-color: #EBE1E1; color: #6F202D;">
                <?= $e['kategori'] ?? 'Event' ?>
              </span>
              <span class="ms-2 small" style="color: #846267; font-size: 0.8rem;">
                <?= date('d M Y', strtotime($e['mulai_pada'])) ?>
              </span>
            </div>
            <a href="#" class="text-dark"><i class="bi bi-arrow-right"></i></a>
          </div>
          <h6 class="fw-bold font-serif mb-1 fs-5" style="color: #4A1B22;"><?= $e['judul'] ?></h6>
          <div class="text-muted d-flex gap-3" style="font-size: 0.8rem;">
            <span style="color: #846267;"><i class="bi bi-geo-alt me-1"></i> <?= $e['lokasi'] ?></span>
            <span style="color: #846267;"><i class="bi bi-clock me-1"></i> <?= date('H:i', strtotime($e['mulai_pada'])) ?> WIB</span>
          </div>
        </div>
        <?php endforeach; ?>

      </div>
    </div>

  </div>

        <div class="detail-destinasi">
            <a href="eventLainnya.php" class="link-gold-animated">
                <i class="bi bi-geo-alt"></i> Lihat Semua Event
            </a> 
        </div>
</div>

 </section>
 <!-- akhir Even dan Atraksi -->


  <!-- Kuliner -->
 <section class="py-5 mt-5">
  <div class="container" style="background-color:#F9F7F5; border-radius: 20px; padding: 2rem;" data-aos="fade-up">
    
        <h2 class="fw-bold mb-3 text-center" style="color: #321B1F; font-size: clamp(2rem, 3.5vw, 3.5rem);">Kuliner<span style="color: #EEB32B;">Yogyakarta</span></h2>
    <p class="text-center mb-5" style="color:#846267; font-size: clamp(1.1rem, 1.5vw, 1.5rem);">
      Temukan destinasi wisata terbaik di Yogyakarta dengan informasi lengkap, ulasan <br>
      pengunjung, dan pemesanan tiket online
    </p>

    <div class="row g-4 justify-content-center mx-auto">

      <!-- Kuliner Dynamic Loop -->
    <?php foreach ($kuliner as $k): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <div class="card border-0 shadow rounded-4 overflow-hidden h-100">

        <!-- Menggunakan 'gambar_url' sesuai table -->
        <img src="<?= $k['gambar_sampul_url'] ?>" class="card-img-top" style="height:200px; object-fit:cover;">

        <div class="card-body">
          <span class="badge bg-light text-dark small"><?= $k['kategori'] ?></span>

          <h6 class="fw-bold mt-2"><?= $k['nama'] ?></h6>

          <!-- Menggunakan 'deskripsi' yang dipotong karena tidak ada kolom 'menu_unggulan' di table -->
          <p class="text-warning small">
            <?= substr(strip_tags($k['deskripsi']), 0, 40)  ?>
          </p>

          <div class="d-flex justify-content-between small">
            <!-- Menggunakan 'lokasi' dan 'harga_min' sesuai table -->
            <span><i class="bi bi-geo-alt"></i> <?= $k['alamat'] ?></span>
            <span class="fw-bold">
              Rp <?= number_format($k['rentang_harga'], 0, ',', '.') ?>
            </span>
          </div>

        </div>
      </div>
    </div>
    <?php endforeach; ?>

      <div class="detail-destinasi">
          <a href="kulinerlainnya.php" class="link-gold-animated">
            <i class="bi bi-fork-knife"></i> Lihat Semua Kuliner
          </a> 
      </div>

    </div>
  </div>
</section>
<!-- Akhir Card Section -->

<!-- Section Peta / Maps (Ditambahkan Baru) -->
<section class="py-5 mt-3" style="background-color: #FDFBF7;">
    <div class="container py-5">
        <div class="row align-items-center gx-lg-5">
            <!-- Text Content -->
            <div class="col-lg-5 mb-5 mb-lg-0">
                <h2 class="display-5 fw-bold mb-4" style="font-family: 'Playfair Display', serif; color: #2D1B20;">
                    Navigasi Mudah ke <span style="color: #C69C6D;">Setiap Destinasi</span>
                </h2>
                <p class="lead text-muted mb-5" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                    Temukan rute tercepat menuju event budaya, festival, dan atraksi menarik di jantung Kota Yogyakarta.
                </p>

                <!-- Fitur Navigasi -->
                <div class="d-flex align-items-start mb-4">
                    <div class="me-3 rounded-circle d-flex align-items-center justify-content-center" 
                         style="width: 50px; height: 50px; background-color: rgba(198, 156, 109, 0.1); color: #C69C6D;">
                        <i class="bi bi-compass fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1" style="color: #2D1B20;">Navigasi Real-time</h5>
                        <p class="text-muted small mb-0">Panduan arah akurat dengan informasi lalu lintas terkini.</p>
                    </div>
                </div>

                <a href="#" class="btn btn-gold shadow-sm mt-2">
                    Buka Peta Digital <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>

            <!-- Map Display -->
            <div class="col-lg-7">
                <div class="card border-0 rounded-4 shadow-lg overflow-hidden position-relative">
                     <!-- Browser Header Cosmetic -->
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                        <div class="d-flex gap-2 me-3">
                            <div class="rounded-circle bg-danger opacity-75" style="width: 10px; height: 10px;"></div>
                            <div class="rounded-circle bg-warning opacity-75" style="width: 10px; height: 10px;"></div>
                            <div class="rounded-circle bg-success opacity-75" style="width: 10px; height: 10px;"></div>
                        </div>
                        <div class="bg-light rounded-pill px-3 py-1 small text-muted flex-grow-1 text-truncate">
                            <i class="bi bi-lock-fill me-1 opacity-50"></i> maps.jogjaverse.id
                        </div>
                    </div>

                    <!-- Map Iframe -->
                    <div class="position-relative">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3952.972324962283!2d110.3658!3d-7.7956!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e7a5787bd5b6c5d%3A0x6a1c5195156f0899!2sYogyakarta!5e0!3m2!1sen!2sid!4v1600000000000!5m2!1sen!2sid" 
                            width="100%" height="450" style="border:0; display: block;" 
                            allowfullscreen="" loading="lazy">
                        </iframe>
                        
                        <!-- Floating Info Card -->
                        <div class="position-absolute bottom-0 start-50 translate-middle-x mb-4 w-75">
                            <div class="card border-0 shadow-sm rounded-4 p-3" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(5px);">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 1px;">Jelajahi</small>
                                        <span class="fw-bold text-dark fs-5">500+ Destinasi</span>
                                    </div>
                                    <div class="d-flex ps-3">
                                        <!-- Mock Avatar Users -->
                                        <div class="rounded-circle bg-secondary border border-2 border-white overflow-hidden d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px; margin-right: -10px; z-index: 3;">
                                            <i class="bi bi-person-fill text-white small"></i>
                                        </div>
                                         <div class="rounded-circle bg-secondary border border-2 border-white overflow-hidden d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px; margin-right: -10px; z-index: 2;">
                                            <i class="bi bi-person-fill text-white small"></i>
                                        </div>
                                        <div class="rounded-circle bg-primary text-white border border-2 border-white d-flex align-items-center justify-content-center shadow-sm" style="width: 35px; height: 35px; z-index: 1; background-color: #C69C6D !important;">
                                            <i class="bi bi-plus small"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

<!-- 3. JAVASCRIPT (Untuk Efek Scroll) -->
<script>
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
</script>

</body>
</html>

