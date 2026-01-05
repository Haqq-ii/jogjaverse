<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!function_exists('h')) {
  function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
  }
}

$current_user = null;
if (!empty($_SESSION['login']) && $_SESSION['login'] === true) {
  $user_id = (int)($_SESSION['id_pengguna'] ?? 0);
  if ($user_id > 0) {
    $stmtUser = $koneksi->prepare("SELECT id_pengguna, nama_lengkap, username, foto_profil_url FROM pengguna WHERE id_pengguna = ? LIMIT 1");
    if ($stmtUser) {
      $stmtUser->bind_param("i", $user_id);
      $stmtUser->execute();
      $current_user = $stmtUser->get_result()->fetch_assoc();
      $stmtUser->close();
      if ($current_user) {
        $_SESSION['nama_lengkap'] = $current_user['nama_lengkap'] ?? ($_SESSION['nama_lengkap'] ?? '');
        $_SESSION['username'] = $current_user['username'] ?? ($_SESSION['username'] ?? '');
        $_SESSION['foto_profil_url'] = $current_user['foto_profil_url'] ?? '';
      }
    }
  }
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

$ulasan_terbaru = [];
$res = $koneksi->query("
  SELECT u.rating, u.komentar, u.jenis_target, u.dibuat_pada,
         p.nama_lengkap,
         d.nama AS nama_destinasi,
         e.judul AS nama_event,
         k.nama AS nama_kuliner
  FROM ulasan u
  LEFT JOIN pengguna p ON u.id_pengguna = p.id_pengguna
  LEFT JOIN destinasi d ON u.jenis_target = 'destinasi' AND u.id_target = d.id_destinasi
  LEFT JOIN event e ON u.jenis_target = 'event' AND u.id_target = e.id_event
  LEFT JOIN kuliner k ON u.jenis_target = 'kuliner' AND u.id_target = k.id_kuliner
  WHERE u.status = 'tampil' AND u.jenis_target IN ('destinasi','event','kuliner')
  ORDER BY u.dibuat_pada DESC
  LIMIT 6
");
if ($res) {
    while ($row = $res->fetch_assoc()) {
      $ulasan_terbaru[] = $row;
    }
}

require_once __DIR__ . '/helpers/trend.php';
$trend_data = jv_get_trend_7hari($koneksi);
$trend_top = $trend_data['top'] ?? [];

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
    <link rel="stylesheet" href="../css/landingpage.css">
    <title>JogjaVerse</title>


</head>
<body class="navbar-overlay landingpage">

<!-- 2. STRUKTUR HTML -->
<?php include __DIR__ . '/includes/navbar.php'; ?>


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
    
    <h2 class="fw-bold mb-3 text-center" style="color: #321B1F; font-size: clamp(2rem, 3.5vw, 3.rem);">Tempat Destinasi <span style="color: #EEB32B;">Unggulan</span></h2>
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

<!-- Trending Minggu Ini -->
<section class="py-5">
  <div class="container" style="background-color:#F9F7F5; border-radius: 20px; padding: 2rem;" data-aos="fade-up">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
      <h2 class="fw-bold mb-0 text-center" style="color: #321B1F; font-size: clamp(2rem, 3.5vw, 3.rem);">Trending Minggu Ini</h2>
      <span class="text-muted small">Berdasarkan data mingguan</span>
    </div>

    <div class="row g-4 justify-content-center mx-auto">
      <?php if (!empty($trend_top)): ?>
        <?php foreach ($trend_top as $t): ?>
          <?php
            $trend_img = !empty($t['gambar']) ? $t['gambar'] : 'https://placehold.co/600x400?text=Destinasi';
            $trend_jam = trim((string)($t['jam_operasional'] ?? ''));
            $trend_jam = $trend_jam !== '' ? $trend_jam : '-';
          ?>
          <div class="col-6 col-sm-6 col-md-4 col-lg-3" data-aos="zoom-in" data-aos-delay="200">
            <div class="card border-0 shadow rounded-4 overflow-hidden h-100">
              <div class="position-relative card-image-wrapper">
                <img src="<?= h($trend_img) ?>"
                     class="card-img-top object-fit-cover"
                     alt="<?= h($t['nama'] ?? 'Destinasi') ?>"
                     style="height: 200px;"
                     onerror="this.src='https://placehold.co/600x400?text=Destinasi'">

                <div class="card-gradient-overlay"></div>

                <span class="position-absolute top-0 start-0 m-2 py-1 px-2
                             bg-white text-dark rounded-pill fw-bold shadow-sm"
                      style="font-size: 0.6rem;">
                  Trending
                </span>

                <div class="position-absolute bottom-0 start-0 m-2 text-white z-2
                            d-flex align-items-center"
                     style="font-size: 0.75rem;">
                  <i class="bi bi-graph-up-arrow text-warning me-1"></i>
                  <span class="fw-bold me-1"><?= (int)($t['count'] ?? 0) ?></span>
                  <span class="opacity-75">klik</span>
                </div>
              </div>

              <div class="card-body p-3 d-flex flex-column">
                <h5 class="card-title fw-bold mb-1 font-serif fs-6">
                  <?= h($t['nama'] ?? 'Destinasi') ?>
                </h5>

                <div class="d-flex align-items-center justify-content-between mt-auto">
                  <div class="d-flex align-items-center text-muted"
                       style="font-size: 0.7rem;">
                    <i class="bi bi-clock me-1"></i>
                    <span><?= h($trend_jam) ?></span>
                  </div>
                  <a href="detailDestinasi.php?id=<?= (int)($t['id'] ?? 0) ?>"
                     class="link-detail-animated"
                     style="font-size: 0.75rem;">
                    Detail <i class="bi bi-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center py-4">
          <div class="text-muted">Belum ada data trending minggu ini.</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Even dan Atraksi -->
<section class="py-5">
  <h2 class="fw-bold mb-3 text-center" style="color: #321B1F; font-size: clamp(2rem, 3.5vw, 3.rem);">Event & <span style="color: #4A1B22;">Atraksi</span></h2>
  <p class="text-center mb-5" style="color:#846267; font-size: clamp(1.1rem, 1.5vw, 1.5rem); ">
    Ikuti berbagai event budaya, festival, dan atraksi menarik yang diselenggarakan di Kota 
    <br>Yogyakarta
  </p>
    
  <div class="container" data-aos="fade-up">

    <div class="row g-3">
    
      <!-- LEFT COLUMN: BIG CARD (Event Pertama/Unggulan) -->
      <div class="col-lg-6">
        <?php if(isset($event[0])): $mainEvent = $event[0]; ?>
        <?php
          $event_id = $mainEvent['id_event'] ?? null;
          $event_href = $event_id ? "detailEvent.php?id_event=" . $event_id : "eventLainnya.php";
        ?>
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
              <a href="<?= $event_href ?>" class="btn btn-sm fw-bold rounded-pill px-4 py-2 w-100 w-md-auto shadow" 
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
            $event_id = $e['id_event'] ?? null;
            $event_href = $event_id ? "detailEvent.php?id_event=" . $event_id : "eventLainnya.php";
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
              
              <!-- UPDATED BUTTON: Link Detail dengan Animasi Garis -->
              <a href="<?= $event_href ?>" class="link-detail-animated" style="font-size: 0.75rem;">
                  Detail <i class="bi bi-arrow-right"></i>
              </a>

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
    
    <h2 class="fw-bold mb-3 text-center" style="color: #321B1F; font-size: clamp(2rem, 3.5vw, 3.rem);">Kuliner  <span style="color: #EEB32B; font-size: clamp(2rem, 3.5vw, 3.rem)">Yogyakarta</span></h2>
    <p class="text-center mb-5" style="color:#846267; font-size: clamp(1.1rem, 1.5vw, 1.5rem);">
      Temukan destinasi wisata terbaik di Yogyakarta dengan informasi lengkap, ulasan <br>
      pengunjung, dan pemesanan tiket online
    </p>

    <div class="row g-4 justify-content-center mx-auto">

      <!-- Kuliner Dynamic Loop -->
    <?php foreach ($kuliner as $k): ?>
    <?php
      $kuliner_id = $k['id_kuliner'] ?? null;
      $kuliner_href = $kuliner_id ? "detailKuliner.php?id_kuliner=" . $kuliner_id : "kulinerLainnya.php";
      $kategori = $k['kategori'] ?? 'Kuliner';
      $nama = $k['nama'] ?? 'Kuliner';
      $alamat = trim((string)($k['alamat'] ?? ''));
      $gambar = $k['gambar_sampul_url'] ?? '';
    ?>
    <div class="col-6 col-md-4 col-lg-3">
      <a href="<?= $kuliner_href ?>" class="card border-0 shadow rounded-4 overflow-hidden h-100 text-decoration-none text-dark">

        <!-- Menggunakan 'gambar_url' sesuai table -->
        <img src="<?= $gambar ?>" class="card-img-top" style="height:200px; object-fit:cover;" alt="<?= $nama ?>">

        <div class="card-body">
          <span class="badge bg-light text-dark small"><?= $kategori ?></span>

          <h6 class="fw-bold mt-2"><?= $nama ?></h6>

          <!-- Keterangan dikosongkan agar tampilan tetap rapi -->
          <p class="text-warning small">
            &nbsp;
          </p>

          <?php if ($alamat !== ''): ?>
            <div class="small">
              <span><i class="bi bi-geo-alt"></i> <?= $alamat ?></span>
            </div>
          <?php endif; ?>

        </div>
      </a>
    </div>
    <?php endforeach; ?>

      <div class="detail-destinasi">
            <a href="kulinerLainnya.php" class="link-gold-animated">
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


<!-- Ulasan -->
<section class="py-5" style="background-color: #FDFBF7;">
  <div class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
      <h2 class="fw-bold mb-0" style="color: #321B1F;">Ulasan Pengunjung</h2>
      <span class="text-muted small">Hanya ulasan yang sudah disetujui</span>
    </div>

    <?php if (empty($ulasan_terbaru)): ?>
      <div class="text-muted">Belum ada ulasan yang ditampilkan.</div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($ulasan_terbaru as $u): ?>
          <?php
            $target_nama = $u['nama_destinasi'] ?? $u['nama_event'] ?? $u['nama_kuliner'] ?? '-';
            $nama_user = $u['nama_lengkap'] ?? 'Pengunjung';
          ?>
          <div class="col-md-6 col-lg-4">
            <div class="bg-white rounded-4 p-4 shadow-sm h-100">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="fw-bold"><?= htmlspecialchars($nama_user, ENT_QUOTES, 'UTF-8') ?></div>
                <small class="text-muted"><?= htmlspecialchars($u['dibuat_pada'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small>
              </div>
              <div class="text-warning small mb-2">Rating: <?= htmlspecialchars($u['rating'] ?? '-', ENT_QUOTES, 'UTF-8') ?>/5</div>
              <div class="text-muted mb-2"><?= nl2br(htmlspecialchars($u['komentar'] ?? '', ENT_QUOTES, 'UTF-8')) ?></div>
              <div class="small text-muted">Pada: <?= htmlspecialchars($target_nama, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>
        <?php endforeach; ?>
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

