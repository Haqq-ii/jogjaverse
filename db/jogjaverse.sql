-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Jan 03, 2026 at 04:09 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jogjaverse`
--

-- --------------------------------------------------------

--
-- Table structure for table `atraksi`
--

CREATE TABLE `atraksi` (
  `id_atraksi` bigint UNSIGNED NOT NULL,
  `id_destinasi` bigint UNSIGNED DEFAULT NULL,
  `id_kategori` bigint UNSIGNED DEFAULT NULL,
  `nama` varchar(160) NOT NULL,
  `deskripsi` text,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `hari_buka` varchar(80) DEFAULT NULL,
  `gambar_sampul_url` varchar(255) DEFAULT NULL,
  `status` enum('draft','publish','arsip') NOT NULL DEFAULT 'draft',
  `dibuat_oleh` bigint UNSIGNED DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `atraksi`
--

INSERT INTO `atraksi` (`id_atraksi`, `id_destinasi`, `id_kategori`, `nama`, `deskripsi`, `jam_mulai`, `jam_selesai`, `hari_buka`, `gambar_sampul_url`, `status`, `dibuat_oleh`, `dibuat_pada`, `diubah_pada`) VALUES
(2, 11, 1, 'Tari Topeng', 'adalah pokoknya', '09:00:00', '17:01:00', 'Senin - Jumat', '/assets/uploads/atr_69592c40b09d73.91339865.jpg', 'publish', 8, '2026-01-03 14:48:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `destinasi`
--

CREATE TABLE `destinasi` (
  `id_destinasi` bigint UNSIGNED NOT NULL,
  `id_kategori` bigint UNSIGNED DEFAULT NULL,
  `nama` varchar(160) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `deskripsi` text,
  `alamat` varchar(255) DEFAULT NULL,
  `kota` varchar(80) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `jam_operasional` varchar(120) DEFAULT NULL,
  `harga_tiket` int UNSIGNED DEFAULT '0',
  `nomor_kontak` varchar(30) DEFAULT NULL,
  `gambar_sampul_url` varchar(255) DEFAULT NULL,
  `status` enum('draft','publish','arsip') NOT NULL DEFAULT 'draft',
  `dibuat_oleh` bigint UNSIGNED DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `destinasi`
--

INSERT INTO `destinasi` (`id_destinasi`, `id_kategori`, `nama`, `slug`, `deskripsi`, `alamat`, `kota`, `latitude`, `longitude`, `jam_operasional`, `harga_tiket`, `nomor_kontak`, `gambar_sampul_url`, `status`, `dibuat_oleh`, `dibuat_pada`, `diubah_pada`) VALUES
(7, 2, 'Heha Sky View', 'dest_693f08c738b2d4.35672802.jpg', 'lorem ipsum dolor sit amet', NULL, NULL, -7.7956000, 110.3695000, '9:00 - 21:00', NULL, NULL, '/assets/uploads/dest_694eef2eec2ec2.01692125.jpg', 'publish', NULL, '2025-12-18 17:51:55', '2025-12-26 20:25:19'),
(9, 1, 'Pantai Drini', '.', 'Pantai yang indah dengan suarah tabrakn ombak ', NULL, 'Sleman', -7.7956000, 110.3695000, '7:00 - 16:00', NULL, NULL, '/assets/uploads/dest_694eefe2878625.49001356.jpg', 'publish', NULL, '2025-12-26 20:28:18', '2026-01-02 13:29:03'),
(11, 1, 'Candi Prambanan', 'candi_prambanan', 'Candi Prambanan merupakan salah satu peninggalan sejarah dan kebudayaan terbesar di Indonesia yang menjadi simbol kejayaan peradaban Hindu di Pulau Jawa. Kompleks candi ini terletak di Kecamatan Prambanan, Kabupaten Sleman, Daerah Istimewa Yogyakarta, meskipun sebagian wilayahnya juga masuk ke dalam Kabupaten Klaten, Jawa Tengah. Dengan arsitektur yang megah dan nilai sejarah yang tinggi, Candi Prambanan telah ditetapkan sebagai Situs Warisan Dunia oleh UNESCO pada tahun 1991. Keberadaan candi ini tidak hanya mencerminkan keindahan seni bangunan masa lampau, tetapi juga menggambarkan kemajuan teknologi, kepercayaan, serta kehidupan sosial masyarakat Jawa Kuno.\r\nCandi Prambanan dibangun pada abad ke-9 Masehi, pada masa pemerintahan Dinasti Sanjaya yang menganut agama Hindu. Candi ini didedikasikan untuk Trimurti, yaitu tiga dewa utama dalam ajaran Hindu, yakni Dewa Brahma sebagai pencipta, Dewa Wisnu sebagai pemelihara, dan Dewa Siwa sebagai pelebur. Dari ketiga dewa tersebut, Dewa Siwa menempati posisi paling utama, yang tercermin dari Candi Siwa sebagai bangunan tertinggi dan terbesar di kompleks Prambanan, dengan ketinggian mencapai sekitar 47 meter. Hal ini menunjukkan bahwa aliran Hindu Siwaisme memiliki pengaruh yang sangat kuat pada masa itu.\r\nSecara keseluruhan, kompleks Candi Prambanan terdiri atas ratusan bangunan candi, meskipun tidak semuanya masih berdiri utuh hingga saat ini. Kompleks ini tersusun dalam tiga zona utama, yaitu zona luar, zona tengah, dan zona inti. Zona inti merupakan bagian terpenting yang berisi delapan candi utama dan delapan candi pendamping. Di antara candi utama tersebut, tiga candi besar didedikasikan untuk Dewa Siwa, Brahma, dan Wisnu, serta tiga candi wahana yang masing-masing diperuntukkan bagi kendaraan dewa, yaitu Nandi (lembu) untuk Siwa, Angsa untuk Brahma, dan Garuda untuk Wisnu.\r\nKeindahan Candi Prambanan tidak hanya terlihat dari kemegahan bangunannya, tetapi juga dari relief-relief yang terukir di dinding candi. Relief tersebut menceritakan kisah epik Ramayana dan Krishnayana, yang dipahat dengan sangat detail dan artistik. Relief Ramayana yang terdapat pada Candi Siwa dan Candi Brahma menggambarkan perjalanan hidup Rama, Sinta, dan Rahwana, serta nilai-nilai moral seperti kesetiaan, keberanian, dan pengorbanan. Relief-relief ini tidak hanya berfungsi sebagai hiasan, tetapi juga sebagai media pendidikan dan penyebaran ajaran agama Hindu kepada masyarakat pada masa itu.\r\nArsitektur Candi Prambanan mencerminkan konsep kosmologi Hindu, di mana bangunan candi dianggap sebagai representasi Gunung Meru, tempat bersemayamnya para dewa. Bentuk bangunan yang menjulang tinggi dengan struktur berundak melambangkan tingkatan alam semesta, mulai dari dunia manusia hingga alam para dewa. Setiap bagian candi dirancang dengan perhitungan yang matang, baik dari segi proporsi, orientasi, maupun simbolisme religius. Hal ini menunjukkan tingkat pengetahuan dan keterampilan yang tinggi dari para arsitek dan pemahat pada masa Jawa Kuno.\r\nSeiring berjalannya waktu, Candi Prambanan sempat mengalami masa kemunduran dan kerusakan, terutama akibat perpindahan pusat kekuasaan ke Jawa Timur serta bencana alam seperti gempa bumi dan letusan gunung berapi. Pada abad ke-16, kompleks candi ini mulai ditinggalkan dan tertutup oleh semak belukar. Upaya pemugaran baru dilakukan secara serius pada abad ke-20 oleh pemerintah Hindia Belanda dan dilanjutkan oleh pemerintah Indonesia setelah kemerdekaan. Proses restorasi ini memerlukan waktu yang panjang dan ketelitian tinggi untuk menjaga keaslian struktur bangunan.\r\nSaat ini, Candi Prambanan tidak hanya berfungsi sebagai situs sejarah dan tempat ibadah umat Hindu, tetapi juga sebagai destinasi wisata budaya yang menarik wisatawan domestik maupun mancanegara. Berbagai kegiatan budaya sering diselenggarakan di kawasan ini, salah satunya adalah Sendratari Ramayana Prambanan, sebuah pertunjukan seni yang menggabungkan tari, musik, dan drama dengan latar belakang Candi Prambanan yang megah. Pertunjukan ini menjadi daya tarik tersendiri karena mampu menghidupkan kembali kisah Ramayana dalam bentuk seni pertunjukan yang memukau.\r\nDengan segala keindahan, nilai sejarah, dan makna filosofis yang dimilikinya, Candi Prambanan merupakan warisan budaya yang sangat berharga bagi bangsa Indonesia. Keberadaan candi ini menjadi bukti nyata bahwa nenek moyang bangsa Indonesia memiliki peradaban yang maju, kreatif, dan berlandaskan nilai-nilai spiritual yang kuat. Oleh karena itu, pelestarian dan perawatan Candi Prambanan merupakan tanggung jawab bersama agar warisan budaya ini dapat terus dinikmati dan dipelajari oleh generasi mendatang.\r\n', 'Jl. Raya Solo - Yogyakarta No.16, Kranggan, Bokoharjo, Kecamatan Prambanan, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55571', 'Sleman', -7.7520370, 110.4914550, '08:00-16:00', 50, '+62 811 2688 000', '/assets/uploads/dest_695924f477ec29.72712864.jpg', 'publish', 8, '2026-01-03 14:17:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `id_event` bigint UNSIGNED NOT NULL,
  `id_destinasi` bigint UNSIGNED DEFAULT NULL,
  `id_kategori` bigint UNSIGNED DEFAULT NULL,
  `judul` varchar(180) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `deskripsi` text,
  `lokasi` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `mulai_pada` datetime NOT NULL,
  `selesai_pada` datetime NOT NULL,
  `kuota` int UNSIGNED DEFAULT '0',
  `harga` int UNSIGNED DEFAULT '0',
  `gambar_sampul_url` varchar(255) DEFAULT NULL,
  `status` enum('draft','publish','dibatalkan','arsip') NOT NULL DEFAULT 'draft',
  `dibuat_oleh` bigint UNSIGNED DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`id_event`, `id_destinasi`, `id_kategori`, `judul`, `slug`, `deskripsi`, `lokasi`, `latitude`, `longitude`, `mulai_pada`, `selesai_pada`, `kuota`, `harga`, `gambar_sampul_url`, `status`, `dibuat_oleh`, `dibuat_pada`, `diubah_pada`) VALUES
(2, NULL, NULL, 'Konser ', '.', NULL, 'Prambanan', NULL, NULL, '2026-01-02 03:02:00', '2026-01-02 03:02:00', 200, 75, '/assets/uploads/evt_6957b4a30ce3e8.54306731.png', 'publish', NULL, '2026-01-01 20:03:24', '2026-01-02 12:05:55'),
(4, NULL, NULL, 'Teater', '-', NULL, 'Malioboro', NULL, NULL, '2026-01-02 14:50:00', '2026-01-02 21:50:00', NULL, NULL, NULL, 'publish', NULL, '2026-01-02 07:51:15', '2026-01-02 11:20:41'),
(7, NULL, NULL, 'Pagelaran Wayang', '---', NULL, 'Alun-Alun Utara', NULL, NULL, '2026-01-03 19:07:00', '2026-01-03 22:07:00', NULL, NULL, NULL, 'publish', NULL, '2026-01-02 12:07:50', NULL),
(8, 11, 1, 'Wayang Kulit', 'wayang_kulit', 'faefafcecveaffevavrdvrvae', 'Jln Nglanjaran', NULL, NULL, '2026-01-03 21:48:00', '2026-01-03 23:00:00', 99, 150000, '/assets/uploads/evt_69593a884593b6.93284992.png', 'publish', 8, '2026-01-03 15:49:28', '2026-01-03 16:06:16');

-- --------------------------------------------------------

--
-- Table structure for table `galeri`
--

CREATE TABLE `galeri` (
  `id_galeri` bigint UNSIGNED NOT NULL,
  `jenis_target` enum('destinasi','event','atraksi','kuliner') NOT NULL,
  `id_target` bigint UNSIGNED NOT NULL,
  `gambar_url` varchar(255) NOT NULL,
  `keterangan` varchar(180) DEFAULT NULL,
  `urutan` int UNSIGNED NOT NULL DEFAULT '0',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `galeri`
--

INSERT INTO `galeri` (`id_galeri`, `jenis_target`, `id_target`, `gambar_url`, `keterangan`, `urutan`, `dibuat_pada`) VALUES
(1, 'destinasi', 11, '/assets/uploads/detail/dest_detail_695924f489b6e3.25063079.jpg', NULL, 1, '2026-01-03 14:17:24'),
(2, 'destinasi', 11, '/assets/uploads/detail/dest_detail_695924f49701d7.15856130.jpg', NULL, 2, '2026-01-03 14:17:24'),
(3, 'destinasi', 11, '/assets/uploads/detail/dest_detail_695924f4a02048.75763247.jpg', NULL, 3, '2026-01-03 14:17:24'),
(4, 'atraksi', 2, '/assets/uploads/detail/atr_detail_69592c40bd07a5.16727415.jpg', NULL, 1, '2026-01-03 14:48:32'),
(5, 'event', 8, '/assets/uploads/detail/evt_detail_69593a88517a13.88091287.png', NULL, 1, '2026-01-03 15:49:28'),
(6, 'event', 8, '/assets/uploads/detail/evt_detail_69593a885cea90.02199455.png', NULL, 2, '2026-01-03 15:49:28');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` bigint UNSIGNED NOT NULL,
  `nama` varchar(80) NOT NULL,
  `tipe` enum('destinasi','event','atraksi','kuliner') NOT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama`, `tipe`, `dibuat_pada`) VALUES
(1, 'Sejarah', 'destinasi', '2025-12-15 14:52:05'),
(2, 'Pegunungan', 'destinasi', '2025-12-15 14:52:05');

-- --------------------------------------------------------

--
-- Table structure for table `kuliner`
--

CREATE TABLE `kuliner` (
  `id_kuliner` bigint UNSIGNED NOT NULL,
  `id_destinasi` bigint UNSIGNED DEFAULT NULL,
  `id_kategori` bigint UNSIGNED DEFAULT NULL,
  `nama` varchar(160) NOT NULL,
  `deskripsi` text,
  `alamat` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `rentang_harga` varchar(40) DEFAULT NULL,
  `jam_operasional` varchar(120) DEFAULT NULL,
  `nomor_kontak` varchar(30) DEFAULT NULL,
  `gambar_sampul_url` varchar(255) DEFAULT NULL,
  `status` enum('draft','publish','arsip') NOT NULL DEFAULT 'draft',
  `dibuat_oleh` bigint UNSIGNED DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kuliner`
--

INSERT INTO `kuliner` (`id_kuliner`, `id_destinasi`, `id_kategori`, `nama`, `deskripsi`, `alamat`, `latitude`, `longitude`, `rentang_harga`, `jam_operasional`, `nomor_kontak`, `gambar_sampul_url`, `status`, `dibuat_oleh`, `dibuat_pada`, `diubah_pada`) VALUES
(1, NULL, NULL, 'Gudeg Bu Yuyun', 'Gudeg enak', 'Yogyakarta', NULL, NULL, '25.000', NULL, NULL, '/assets/uploads/kul_695774de70cd97.21269305.jpg', 'publish', NULL, '2026-01-02 07:33:50', '2026-01-02 11:46:38'),
(2, NULL, 1, 'Bakpia Patok', 'Bakpia kukus', 'Malioboro', NULL, NULL, '20.000 ', NULL, NULL, '/assets/uploads/kul_6957a9e8b4a3c8.71126961.jpg', 'publish', NULL, '2026-01-02 11:20:08', '2026-01-02 11:49:35'),
(3, NULL, NULL, 'Lupis mba setimen', 'Lupis', 'Klaten', NULL, NULL, '15.000', NULL, NULL, '/assets/uploads/kul_6957b564bf8679.52669425.jpg', 'publish', NULL, '2026-01-02 12:09:08', '2026-01-02 12:10:18');

-- --------------------------------------------------------

--
-- Table structure for table `kunjungan`
--

CREATE TABLE `kunjungan` (
  `id_kunjungan` bigint UNSIGNED NOT NULL,
  `id_pengguna` bigint UNSIGNED DEFAULT NULL,
  `jenis_halaman` enum('destinasi','event','atraksi','kuliner','beranda') NOT NULL,
  `id_target` bigint UNSIGNED DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip_hash` char(64) DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kunjungan`
--

INSERT INTO `kunjungan` (`id_kunjungan`, `id_pengguna`, `jenis_halaman`, `id_target`, `user_agent`, `ip_hash`, `dibuat_pada`) VALUES
(1, 1, 'beranda', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2025-12-14 19:18:56');

-- --------------------------------------------------------

--
-- Table structure for table `log_admin`
--

CREATE TABLE `log_admin` (
  `id_log` bigint UNSIGNED NOT NULL,
  `id_admin` bigint UNSIGNED NOT NULL,
  `aksi` varchar(60) NOT NULL,
  `jenis_target` enum('destinasi','event','atraksi','kuliner','reservasi_event') NOT NULL,
  `id_target` bigint UNSIGNED NOT NULL,
  `detail` text,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pelaporan`
--

CREATE TABLE `pelaporan` (
  `id_pelaporan` bigint UNSIGNED NOT NULL,
  `id_pengguna` bigint UNSIGNED DEFAULT NULL,
  `jenis_target` enum('destinasi','event','atraksi','kuliner') NOT NULL,
  `id_target` bigint UNSIGNED NOT NULL,
  `judul` varchar(120) NOT NULL,
  `deskripsi` text NOT NULL,
  `status` enum('baru','diproses','selesai','ditolak') NOT NULL DEFAULT 'baru',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` bigint UNSIGNED NOT NULL,
  `id_reservasi` bigint UNSIGNED NOT NULL,
  `metode` enum('transfer_manual','gateway') NOT NULL DEFAULT 'transfer_manual',
  `jumlah` int UNSIGNED NOT NULL DEFAULT '0',
  `status` enum('BELUM_BAYAR','SUDAH_BAYAR','GAGAL','REFUND') NOT NULL DEFAULT 'BELUM_BAYAR',
  `bukti_bayar_url` varchar(255) DEFAULT NULL,
  `dibayar_pada` datetime DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_reservasi`, `metode`, `jumlah`, `status`, `bukti_bayar_url`, `dibayar_pada`, `dibuat_pada`) VALUES
(1, 6, 'gateway', 150000, 'BELUM_BAYAR', NULL, NULL, '2026-01-03 16:00:56'),
(2, 7, 'gateway', 150000, 'BELUM_BAYAR', NULL, NULL, '2026-01-03 16:01:25'),
(3, 8, 'gateway', 150000, 'SUDAH_BAYAR', NULL, '2026-01-03 16:06:16', '2026-01-03 16:06:11');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` bigint UNSIGNED NOT NULL,
  `nama_lengkap` varchar(120) NOT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `kata_sandi_hash` varchar(255) NOT NULL,
  `peran` enum('admin','user') NOT NULL DEFAULT 'user',
  `nomor_hp` varchar(30) DEFAULT NULL,
  `foto_profil_url` varchar(255) DEFAULT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `nama_lengkap`, `username`, `email`, `kata_sandi_hash`, `peran`, `nomor_hp`, `foto_profil_url`, `status_aktif`, `dibuat_pada`, `diubah_pada`) VALUES
(8, 'Administrator', 'admin', 'admin@jogjaverse.com', '0192023a7bbd73250516f069df18b500', 'admin', '081234567890', NULL, 1, '2025-12-16 15:17:22', '2026-01-03 13:26:52'),
(9, 'User Example', 'user', 'user@jogjaverse.com', '6ad14ba9986e3615423dfca256d04e3f', 'user', '081234567891', NULL, 1, '2025-12-16 15:17:22', '2026-01-03 13:25:19'),
(10, 'adminn', 'adminn', 'uadyi@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'admin', '09123456789', NULL, 1, '2025-12-18 17:42:59', '2026-01-03 02:09:02'),
(11, 'userrr', 'userrr', 'user@113.com', '96e79218965eb72c92a549dd5a330112', 'user', '0887982747214', NULL, 1, '2025-12-26 18:35:30', '2025-12-26 18:35:58');

-- --------------------------------------------------------

--
-- Table structure for table `relasi_tag`
--

CREATE TABLE `relasi_tag` (
  `id_relasi_tag` bigint UNSIGNED NOT NULL,
  `id_tag` bigint UNSIGNED NOT NULL,
  `jenis_target` enum('destinasi','event','atraksi','kuliner') NOT NULL,
  `id_target` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservasi_event`
--

CREATE TABLE `reservasi_event` (
  `id_reservasi` bigint UNSIGNED NOT NULL,
  `id_event` bigint UNSIGNED NOT NULL,
  `id_pengguna` bigint UNSIGNED NOT NULL,
  `jumlah_tiket` int UNSIGNED NOT NULL DEFAULT '1',
  `harga_satuan` int UNSIGNED NOT NULL DEFAULT '0',
  `total_harga` int UNSIGNED NOT NULL DEFAULT '0',
  `status` enum('PENDING','DIKONFIRMASI','DIBATALKAN','KADALUARSA') NOT NULL DEFAULT 'PENDING',
  `kedaluwarsa_pada` datetime DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reservasi_event`
--

INSERT INTO `reservasi_event` (`id_reservasi`, `id_event`, `id_pengguna`, `jumlah_tiket`, `harga_satuan`, `total_harga`, `status`, `kedaluwarsa_pada`, `catatan`, `dibuat_pada`, `diubah_pada`) VALUES
(6, 8, 8, 1, 150000, 150000, 'PENDING', '2026-01-03 23:15:56', '', '2026-01-03 16:00:56', NULL),
(7, 8, 8, 1, 150000, 150000, 'PENDING', '2026-01-03 23:16:25', '', '2026-01-03 16:01:25', NULL),
(8, 8, 8, 1, 150000, 150000, 'DIKONFIRMASI', '2026-01-03 23:21:11', '', '2026-01-03 16:06:11', '2026-01-03 16:06:16');

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE `tag` (
  `id_tag` bigint UNSIGNED NOT NULL,
  `nama` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tiket_event`
--

CREATE TABLE `tiket_event` (
  `id_tiket` bigint UNSIGNED NOT NULL,
  `id_reservasi` bigint UNSIGNED NOT NULL,
  `kode_tiket` varchar(80) NOT NULL,
  `sudah_dipakai` tinyint(1) NOT NULL DEFAULT '0',
  `dipakai_pada` datetime DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tiket_event`
--

INSERT INTO `tiket_event` (`id_tiket`, `id_reservasi`, `kode_tiket`, `sudah_dipakai`, `dipakai_pada`, `dibuat_pada`) VALUES
(1, 6, 'EVT-6-884357-1', 0, NULL, '2026-01-03 16:00:56'),
(2, 7, 'EVT-7-F1FE1F-1', 0, NULL, '2026-01-03 16:01:25'),
(3, 8, 'EVT-8-4A3096-1', 0, NULL, '2026-01-03 16:06:11');

-- --------------------------------------------------------

--
-- Table structure for table `ulasan`
--

CREATE TABLE `ulasan` (
  `id_ulasan` bigint UNSIGNED NOT NULL,
  `id_pengguna` bigint UNSIGNED NOT NULL,
  `jenis_target` enum('destinasi','event','atraksi','kuliner') NOT NULL,
  `id_target` bigint UNSIGNED NOT NULL,
  `rating` tinyint UNSIGNED NOT NULL,
  `komentar` text,
  `status` enum('tampil','sembunyi') NOT NULL DEFAULT 'tampil',
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `atraksi`
--
ALTER TABLE `atraksi`
  ADD PRIMARY KEY (`id_atraksi`),
  ADD KEY `fk_atr_kategori` (`id_kategori`),
  ADD KEY `fk_atr_dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `idx_atr_destinasi` (`id_destinasi`),
  ADD KEY `idx_atr_status` (`status`);

--
-- Indexes for table `destinasi`
--
ALTER TABLE `destinasi`
  ADD PRIMARY KEY (`id_destinasi`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_dest_kategori` (`id_kategori`),
  ADD KEY `fk_dest_dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `idx_dest_status` (`status`),
  ADD KEY `idx_dest_lokasi` (`kota`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id_event`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_evt_destinasi` (`id_destinasi`),
  ADD KEY `fk_evt_kategori` (`id_kategori`),
  ADD KEY `fk_evt_dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `idx_evt_waktu` (`mulai_pada`,`selesai_pada`),
  ADD KEY `idx_evt_status` (`status`);

--
-- Indexes for table `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id_galeri`),
  ADD KEY `idx_gal_target` (`jenis_target`,`id_target`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indexes for table `kuliner`
--
ALTER TABLE `kuliner`
  ADD PRIMARY KEY (`id_kuliner`),
  ADD KEY `fk_kul_kategori` (`id_kategori`),
  ADD KEY `fk_kul_dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `idx_kul_destinasi` (`id_destinasi`),
  ADD KEY `idx_kul_status` (`status`);

--
-- Indexes for table `kunjungan`
--
ALTER TABLE `kunjungan`
  ADD PRIMARY KEY (`id_kunjungan`),
  ADD KEY `idx_kunjungan_waktu` (`dibuat_pada`),
  ADD KEY `idx_kunjungan_target` (`jenis_halaman`,`id_target`);

--
-- Indexes for table `log_admin`
--
ALTER TABLE `log_admin`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_log_target` (`jenis_target`,`id_target`),
  ADD KEY `idx_log_admin` (`id_admin`);

--
-- Indexes for table `pelaporan`
--
ALTER TABLE `pelaporan`
  ADD PRIMARY KEY (`id_pelaporan`),
  ADD KEY `idx_pelaporan_status` (`status`),
  ADD KEY `idx_pelaporan_target` (`jenis_target`,`id_target`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `id_reservasi` (`id_reservasi`),
  ADD KEY `idx_bayar_status` (`status`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `relasi_tag`
--
ALTER TABLE `relasi_tag`
  ADD PRIMARY KEY (`id_relasi_tag`),
  ADD UNIQUE KEY `uq_tag_target` (`id_tag`,`jenis_target`,`id_target`),
  ADD KEY `idx_reltag_target` (`jenis_target`,`id_target`);

--
-- Indexes for table `reservasi_event`
--
ALTER TABLE `reservasi_event`
  ADD PRIMARY KEY (`id_reservasi`),
  ADD KEY `idx_res_event` (`id_event`),
  ADD KEY `idx_res_pengguna` (`id_pengguna`),
  ADD KEY `idx_res_status` (`status`);

--
-- Indexes for table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`id_tag`),
  ADD UNIQUE KEY `nama` (`nama`);

--
-- Indexes for table `tiket_event`
--
ALTER TABLE `tiket_event`
  ADD PRIMARY KEY (`id_tiket`),
  ADD UNIQUE KEY `kode_tiket` (`kode_tiket`),
  ADD KEY `idx_tiket_res` (`id_reservasi`);

--
-- Indexes for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD PRIMARY KEY (`id_ulasan`),
  ADD KEY `idx_ulasan_target` (`jenis_target`,`id_target`),
  ADD KEY `idx_ulasan_pengguna` (`id_pengguna`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `atraksi`
--
ALTER TABLE `atraksi`
  MODIFY `id_atraksi` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `destinasi`
--
ALTER TABLE `destinasi`
  MODIFY `id_destinasi` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id_event` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id_galeri` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kuliner`
--
ALTER TABLE `kuliner`
  MODIFY `id_kuliner` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kunjungan`
--
ALTER TABLE `kunjungan`
  MODIFY `id_kunjungan` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `log_admin`
--
ALTER TABLE `log_admin`
  MODIFY `id_log` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelaporan`
--
ALTER TABLE `pelaporan`
  MODIFY `id_pelaporan` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `relasi_tag`
--
ALTER TABLE `relasi_tag`
  MODIFY `id_relasi_tag` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservasi_event`
--
ALTER TABLE `reservasi_event`
  MODIFY `id_reservasi` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
  MODIFY `id_tag` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiket_event`
--
ALTER TABLE `tiket_event`
  MODIFY `id_tiket` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `id_ulasan` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `atraksi`
--
ALTER TABLE `atraksi`
  ADD CONSTRAINT `fk_atr_destinasi` FOREIGN KEY (`id_destinasi`) REFERENCES `destinasi` (`id_destinasi`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_atr_dibuat_oleh` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_atr_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `destinasi`
--
ALTER TABLE `destinasi`
  ADD CONSTRAINT `fk_dest_dibuat_oleh` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dest_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `fk_evt_destinasi` FOREIGN KEY (`id_destinasi`) REFERENCES `destinasi` (`id_destinasi`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evt_dibuat_oleh` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evt_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `kuliner`
--
ALTER TABLE `kuliner`
  ADD CONSTRAINT `fk_kul_destinasi` FOREIGN KEY (`id_destinasi`) REFERENCES `destinasi` (`id_destinasi`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kul_dibuat_oleh` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kul_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `log_admin`
--
ALTER TABLE `log_admin`
  ADD CONSTRAINT `fk_log_admin` FOREIGN KEY (`id_admin`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `fk_bayar_res` FOREIGN KEY (`id_reservasi`) REFERENCES `reservasi_event` (`id_reservasi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `relasi_tag`
--
ALTER TABLE `relasi_tag`
  ADD CONSTRAINT `fk_reltag_tag` FOREIGN KEY (`id_tag`) REFERENCES `tag` (`id_tag`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reservasi_event`
--
ALTER TABLE `reservasi_event`
  ADD CONSTRAINT `fk_res_evt` FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_res_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tiket_event`
--
ALTER TABLE `tiket_event`
  ADD CONSTRAINT `fk_tiket_res` FOREIGN KEY (`id_reservasi`) REFERENCES `reservasi_event` (`id_reservasi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD CONSTRAINT `fk_ulasan_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
