-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Jan 05, 2026 at 02:11 PM
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
(2, NULL, 1, 'Tari Topeng', 'adalah pokoknya', '09:00:00', '17:01:00', 'Senin - Jumat', '/assets/uploads/atr_69592c40b09d73.91339865.jpg', 'publish', 8, '2026-01-03 14:48:32', NULL);

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
(12, 1, 'Museum Ullen Sentalu', 'museum-ullen-sentalu', 'Museum Ullen Sentalu di Yogyakarta adalah pusat pelestarian budaya dan sejarah bangsawan Mataram, termasuk Kesultanan Yogyakarta dan Kasunanan Surakarta. Terletak di lereng Gunung Merapi, museum ini menawarkan pengalaman unik dengan desain semi-terbuka dan suasana yang tenang. Koleksi utamanya meliputi lukisan tokoh bangsawan, batik klasik, sastra, dan surat pribadi yang menggambarkan kehidupan para bangsawan. Dengan tur berpemandu dan pendekatan naratif, museum ini menyajikan sejarah dan budaya Jawa secara mendalam, menjadikannya destinasi penting bagi wisatawan dan pelajar yang ingin memahami kearifan lokal.', 'Jl. Boyong No.KM 25, Kaliurang, Hargobinangun, Kec. Pakem, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55582', 'Sleman', -7.5976480, 110.4233960, '08.30-15.00', 50, '+62 813-2891-8828', '/assets/uploads/dest_695b46e4216031.07444462.png', 'publish', 8, '2026-01-05 05:06:44', NULL),
(13, 1, 'Taman Sari (Water Castle)', 'taman-sari-water-castle', 'Taman Sari Yogyakarta adalah kompleks bersejarah yang dibangun pada abad ke-18 oleh Sri Sultan Hamengku Buwono I sebagai taman kerajaan untuk rekreasi, meditasi, dan pertahanan. Terletak dekat dengan Keraton Yogyakarta, Taman Sari menggabungkan unsur budaya Jawa, Portugis, dan Tiongkok dalam arsitekturnya, menghasilkan desain yang unik. Dikenal dengan Umbul Pasiraman, kolam pemandian untuk Sultan dan keluarganya, serta Sumur Gumuling, tempat meditasi dengan desain simbolik. Taman ini juga memiliki terowongan bawah tanah sebagai jalur pertahanan. Walaupun sebagian besar kompleks rusak, restorasi terus dilakukan untuk menjaga kelestariannya, menjadikannya destinasi wisata budaya yang penting, mencerminkan filosofi kehidupan dan harmoni antara kekuasaan duniawi dan spiritual.', 'Jl. Taman Sari No.42, Patehan, Kecamatan Kraton, Kota Yogyakarta, Daerah Istimewa Yogyakarta 55133', 'Yogyakarta', -7.8100320, 110.3591530, '09.00-15.00', 25, '08122791278', '/assets/uploads/dest_695b47c50d8252.36785121.png', 'publish', 8, '2026-01-05 05:10:29', '2026-01-05 05:15:46'),
(14, 1, 'Benteng Vredeburg', 'benteng-vredeburg', 'Benteng Vredeburg adalah bangunan bersejarah yang terletak di pusat Kota Yogyakarta, tepatnya di kawasan Malioboro. Dibangun pada tahun 1760 oleh pemerintah kolonial Belanda, benteng ini awalnya berfungsi sebagai alat pengawasan dan pertahanan. Kini, Benteng Vredeburg berfungsi sebagai museum yang menyimpan diorama dan koleksi sejarah perjuangan Indonesia, mulai dari masa penjajahan Belanda hingga kemerdekaan. Museum ini juga menjadi pusat kegiatan budaya dan edukasi sejarah. Dengan arsitektur militer Eropa abad ke-18 yang masih terjaga, Benteng Vredeburg memberikan wawasan mendalam tentang sejarah Indonesia dan menjadi destinasi wisata penting yang mudah dijangkau wisatawan.', 'Jl. Margo Mulyo No.6, Ngupasan, Kecamatan Gondomanan, Kota Yogyakarta, Daerah Istimewa Yogyakarta 55122, Indonesia', 'Yogyakarta', -7.8003250, 110.3663350, '08.00-20.00', 3, '(0274) 586934', '/assets/uploads/dest_695b48d3055de0.35075499.png', 'publish', 8, '2026-01-05 05:14:59', '2026-01-05 05:15:50'),
(15, 2, 'Pantai Parangtritis', 'pantai-parangtritis', 'Pantai Parangtritis, terletak sekitar 27 km selatan Yogyakarta, adalah destinasi wisata alam yang terkenal dengan pantai berpasir hitam dan ombak besar. Selain keindahan alamnya, pantai ini kaya akan nilai budaya dan spiritual, terkait erat dengan legenda Nyi Roro Kidul, penguasa Laut Selatan. Dikenal juga dengan gumuk pasir yang langka, Parangtritis menjadi tempat menarik untuk sandboarding dan aktivitas alam lainnya. Tradisi upacara Labuhan yang diadakan Keraton Yogyakarta menambah dimensi sakral tempat ini. Pengunjung dapat menikmati matahari terbenam yang indah, serta menikmati kuliner pesisir dan aktivitas tradisional seperti menaiki delman. Meskipun indah, pantai ini juga memiliki ombak yang berbahaya, sehingga kewaspadaan sangat diperlukan.', 'Pantai Parangtritis Mancingan RT 07, Pantai, Parangtritis, Kecamatan Kretek, Kabupaten Bantul, Daerah Istimewa Yogyakarta 55772, Indonesia', 'Bantul', -8.0272830, 110.3370080, '-', 15, '+62 813-6478-0043 ', '/assets/uploads/dest_695b499fe44d32.98240902.png', 'publish', 8, '2026-01-05 05:18:23', '2026-01-05 05:37:55'),
(16, 2, 'Obelix Hiils', 'obelix-hiils', 'Obelix Hills adalah destinasi wisata modern di kawasan perbukitan Prambanan, Yogyakarta, yang menawarkan pemandangan alam spektakuler dan fasilitas hiburan menarik. Terletak di dataran tinggi, Obelix Hills menyuguhkan pemandangan 360 derajat dari hamparan perbukitan, persawahan, dan Kota Yogyakarta. Dikenal dengan spot foto tematik dan area panorama terbuka, tempat ini sangat populer untuk menikmati sunset yang memukau. Selain itu, Obelix Hills juga dilengkapi dengan kafe, restoran, dan fasilitas lainnya, serta sering mengadakan acara hiburan. Dengan pengelolaan profesional dan suasana yang nyaman, Obelix Hills menjadi destinasi favorit bagi wisatawan yang mencari keindahan alam dan pengalaman wisata yang dinamis.', 'Klumprit, Jl. Rakai Panangkaran Blok I & 2, Klumprit II, Wukirharjo, Kec. Prambanan, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55572', 'Sleman', -7.8066470, 110.5198040, '11.00-19.00', 30, '08112948833', '/assets/uploads/dest_695b4b1ec117a0.42183795.png', 'publish', 8, '2026-01-05 05:24:46', '2026-01-05 05:26:01'),
(17, NULL, 'Hutam Pinus Pengger dan Hutan Pinus Mangunan', 'hutam-pinus-pengger-dan-hutan-pinus-mangunan', 'Hutan Pinus Pengger dan Hutan Pinus Mangunan merupakan dua destinasi wisata alam yang terletak di kawasan perbukitan Dlingo, Kabupaten Bantul, Daerah Istimewa Yogyakarta. Kedua tempat ini dikenal luas karena menawarkan suasana alam yang sejuk, hamparan pepohonan pinus yang menjulang tinggi, serta pemandangan alam dari ketinggian yang memukau. Keberadaan hutan pinus ini menjadi alternatif wisata alam yang menenangkan, jauh dari hiruk-pikuk perkotaan, sekaligus mencerminkan keberhasilan pengelolaan lingkungan berbasis pariwisata berkelanjutan.', 'Jl. Dlingo-Patuk, Sendangsari, Terong, Kec. Dlingo, Kabupaten Bantul, Daerah Istimewa Yogyakarta 55783', 'Bantul', -7.9228660, 110.4341080, '08.00-21.00', 7, '082135753988', '/assets/uploads/dest_695b4c456fa268.29564669.png', 'publish', 8, '2026-01-05 05:28:54', '2026-01-05 05:29:41'),
(18, 1, 'Keraton Yogyakarta', 'keraton-yogyakarta', 'Keraton Yogyakarta Hadiningrat, didirikan oleh Sri Sultan Hamengku Buwono I pada tahun 1755, adalah pusat kebudayaan dan simbol kekuasaan Kesultanan Yogyakarta. Arsitektur keraton mencerminkan filosofi kehidupan Jawa, dengan tata letak yang menghubungkan alam, manusia, dan spiritual. Keraton terdiri dari bangunan utama seperti Kedhaton dan Bangsal Kencono, yang menjadi tempat tinggal Sultan dan pusat upacara. Sebagai pusat pelestarian budaya, Keraton Yogyakarta juga menjaga seni tradisional seperti tari Bedhaya, wayang kulit, dan gamelan. Selain itu, keraton memiliki museum yang menyimpan koleksi bersejarah. Selain sebagai simbol kebudayaan, keraton juga memainkan peran penting dalam sejarah Indonesia, terutama selama masa kemerdekaan. Hingga kini, Keraton Yogyakarta tetap menjadi lambang keluhuran budaya Jawa yang terus berkembang.', 'Jl. Rotowijayan No.1, Panembahan, Kecamatan Kraton, Kota Yogyakarta, Daerah Istimewa Yogyakarta 55131', 'Yogyakarta', -7.8075970, 110.3638700, '08.30-14.30', 25, '(0274) 376795', '/assets/uploads/dest_695b4cd54a3136.62287274.png', 'publish', 8, '2026-01-05 05:32:05', NULL),
(19, 2, 'Heha Sky View', 'heha-sky-view', 'HeHa Sky View adalah destinasi wisata modern yang terletak di perbukitan Patuk, Kabupaten Gunungkidul, Yogyakarta. Menawarkan pemandangan spektakuler dari ketinggian, tempat ini populer untuk menikmati panorama alam, terutama sunset dan pemandangan malam kota Yogyakarta. Dengan konsep wisata terpadu, HeHa Sky View memiliki spot foto ikonik seperti Sky Glass, Sky Bridge, dan balon udara, serta berbagai instalasi artistik. Selain itu, tempat ini juga menawarkan wisata kuliner dengan restoran terbuka yang menyajikan hidangan lokal dan internasional. Dilengkapi dengan fasilitas lengkap, HeHa Sky View cocok untuk keluarga, pasangan, dan komunitas yang ingin bersantai, berfoto, atau menikmati suasana senja. Dengan pengelolaan yang baik, destinasi ini juga berdampak positif bagi ekonomi lokal.', 'Jl. Dlingo-Patuk No.2, Patuk, Bukit, Kec. Patuk, Kabupaten Gunungkidul, Daerah Istimewa Yogyakarta 55862', 'Gunung Kidul', -7.8484810, 110.4785860, '10.00-21.00', 30, '02744357999', '/assets/uploads/dest_695b4d6d6da8a2.30047443.png', 'publish', 8, '2026-01-05 05:34:37', NULL),
(20, NULL, 'Alun-Alun Kidul', 'alun-alun-kidul', 'Alun-Alun Kidul (Alkid) Yogyakarta adalah ruang publik ikonik yang terletak di selatan Keraton Yogyakarta, dengan nilai sejarah, budaya, dan sosial yang kuat. Dulu digunakan untuk latihan prajurit dan upacara kerajaan, alun-alun ini kini dikenal dengan tradisi masangin, yaitu berjalan melewati dua pohon beringin besar dengan mata tertutup sebagai simbol introspeksi diri. Pada malam hari, kawasan ini menjadi pusat hiburan dengan wahana rekreasi seperti odong-odong dan permainan anak-anak, serta pedagang kaki lima yang menjajakan kuliner khas Yogyakarta. Selain sebagai tempat wisata, Alun-Alun Kidul juga berfungsi sebagai ruang sosial dan kegiatan budaya, menciptakan suasana hangat dan dinamis yang mencerminkan kehidupan masyarakat Yogyakarta.', 'Jl. Alun Alun Kidul, Patehan, Kecamatan Kraton, Kota Yogyakarta, Daerah Istimewa Yogyakarta 55133', 'Yogyakarta', -7.7956000, 110.3695000, NULL, NULL, NULL, '/assets/uploads/dest_695b4deb31bab4.35643112.png', 'publish', 8, '2026-01-05 05:36:43', '2026-01-05 05:38:13'),
(21, 1, 'Malioboro', 'malioboro', 'Jalan Malioboro adalah ikon utama Kota Yogyakarta yang menghubungkan Tugu Yogyakarta dan Keraton Yogyakarta, penuh dengan nilai sejarah, budaya, dan kehidupan masyarakat. Sebagai pusat aktivitas ekonomi, budaya, dan pariwisata, jalan ini dipenuhi pedagang kaki lima yang menjual berbagai barang, seperti batik, kerajinan tangan, dan kuliner khas Yogyakarta. Malioboro juga menjadi ruang ekspresi seni, dengan para seniman jalanan yang menampilkan karya mereka di trotoar. Dalam beberapa tahun terakhir, kawasan ini mengalami revitalisasi untuk meningkatkan kenyamanan pengunjung, tetap mempertahankan identitas budaya sekaligus beradaptasi dengan modernitas. Pada malam hari, Malioboro menyuguhkan suasana yang romantis dengan lampu jalan, angkringan, dan musik, menjadikannya pusat pertemuan berbagai kalangan. Sebagai saksi sejarah perjuangan Indonesia, Malioboro tetap menjadi simbol hidup dan dinamis Yogyakarta.', 'Jalan Malioboro, Kota Yogyakarta, Daerah Istimewa Yogyakarta 55271, Indonesia', 'Yogyakarta', -7.7956000, 110.3695000, '-', NULL, NULL, '/assets/uploads/dest_695b4ee217b8c1.80417962.png', 'publish', 8, '2026-01-05 05:40:50', '2026-01-05 10:02:11'),
(22, 1, 'Candi Prambanan', 'candi-prambanan', 'Candi Prambanan adalah kompleks candi Hindu terbesar di Indonesia yang terletak di Kecamatan Prambanan, sekitar 17 kilometer sebelah timur Kota Yogyakarta. Candi ini dibangun pada abad ke-9 Masehi pada masa pemerintahan Dinasti Sanjaya dari Kerajaan Mataram Kuno. Prambanan didedikasikan untuk Trimurti, yaitu tiga dewa utama dalam ajaran Hindu: Brahma sebagai dewa pencipta, Wisnu sebagai dewa pemelihara, dan Siwa sebagai dewa pelebur. Candi Siwa merupakan bangunan utama dan tertinggi dengan ketinggian sekitar 47 meter, menjadikannya pusat dari keseluruhan kompleks.\r\nArsitektur Candi Prambanan mencerminkan keindahan dan kecanggihan seni bangunan Hindu klasik di Jawa. Struktur candinya ramping dan menjulang tinggi, dihiasi dengan relief-relief halus yang menceritakan kisah epik Ramayana dan Krishnayana. Relief tersebut tidak hanya memiliki nilai estetika, tetapi juga berfungsi sebagai media penyampaian ajaran moral dan keagamaan kepada masyarakat pada masa itu. Tata letak kompleks candi disusun secara simetris, melambangkan keseimbangan kosmos menurut kepercayaan Hindu.\r\nSelain sebagai tempat ibadah, Candi Prambanan juga memiliki nilai sejarah dan budaya yang sangat penting. Kompleks ini sempat mengalami kerusakan parah akibat gempa bumi dan faktor alam, namun telah direstorasi secara bertahap oleh pemerintah dan para ahli. Pada tahun 1991, Candi Prambanan ditetapkan sebagai Situs Warisan Dunia oleh UNESCO. Hingga kini, Prambanan menjadi destinasi wisata unggulan sekaligus pusat kegiatan budaya, seperti pertunjukan Sendratari Ramayana, yang terus memperkenalkan kekayaan warisan budaya Indonesia kepada dunia.', 'Jl. Raya Solo - Yogyakarta No.16, Kranggan, Bokoharjo, Kecamatan Prambanan, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55571', 'Yogyakarta', -7.7522270, 110.4915330, '06:00-16:30', 50000, '+62 811 2688 000', '/assets/uploads/dest_695bba9d3aa5e8.48061003.png', 'publish', 8, '2026-01-05 13:20:29', NULL);

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
(10, NULL, 1, 'Wayang kulit', 'wayang-kulit', 'Wayang kulit adalah seni pertunjukan tradisional yang sangat penting di Yogyakarta, berfungsi tidak hanya sebagai hiburan, tetapi juga sebagai media pendidikan dan pelestarian nilai budaya Jawa. Wayang kulit terbuat dari kulit kerbau yang dipahat dan dicat, dimainkan oleh dalang yang mengisi suara tokoh dan mengendalikan cerita, dengan iringan gamelan Jawa. Cerita yang diangkat umumnya berasal dari Mahabharata dan Ramayana, dengan tokoh-tokoh seperti Arjuna, Bima, dan Semar yang menyampaikan nilai kehidupan. Wayang kulit Yogyakarta dikenal dengan pementasan yang halus dan filosofis, mencerminkan keseimbangan antara manusia, alam, dan Tuhan. Pertunjukan wayang kulit sering diadakan dalam upacara adat, festival budaya, dan acara penting lainnya. UNESCO telah mengakui wayang kulit sebagai Warisan Budaya Takbenda Dunia, dan Yogyakarta menjadi pusat pelestariannya. Menyaksikan pertunjukan wayang kulit di Yogyakarta menawarkan pengalaman budaya yang mendalam dan penuh makna.', NULL, NULL, NULL, '2026-01-05 12:43:00', '2026-01-05 12:43:00', NULL, NULL, '/assets/uploads/evt_695b4fb5104989.34240308.png', 'publish', 8, '2026-01-05 05:44:21', '2026-01-05 11:55:33'),
(11, NULL, NULL, 'Upacara Labuhan Merapi', 'upacara-labuhan-merapi', 'Upacara Labuhan Merapi adalah tradisi adat dan spiritual yang dipelopori oleh Keraton Ngayogyakarta Hadiningrat untuk mempersembahkan sesaji kepada Tuhan, leluhur, dan kekuatan alam, terutama Gunung Merapi, dengan tujuan memohon keselamatan, keberkahan, dan keseimbangan hidup. Ritual ini digelar setiap tahun pada tanggal 30 Rejeb dalam kalender Jawa, sehari setelah peringatan Tingalan Dalem Jumenengan Dalem, hari kenaikan tahta Sultan Hamengku Buwono X. Dilaksanakan di lereng Gunung Merapi, ritual dimulai dengan arak-arakan persembahan (uborampe) dari Keraton menuju Bangsal Srimanganti di hutan lereng Merapi. Selain pemberian sesaji, prosesi ini mencakup macapat, doa bersama, kenduri, dan pertunjukan wayang kulit, menjadikannya sebuah festival budaya. Upacara yang dimulai pagi hari ini dipenuhi dengan kesakralan dan dihormati oleh ratusan peserta, baik masyarakat lokal maupun wisatawan.', 'Merapi', NULL, NULL, '2026-01-06 12:45:00', '2026-01-06 12:45:00', NULL, NULL, '/assets/uploads/evt_695b5003e1f8d8.41346681.png', 'publish', 8, '2026-01-05 05:45:39', NULL),
(12, NULL, NULL, 'Gamelan Jawa', 'gamelan-jawa', 'Gamelan Jawa di Pendopo Keraton Ngayogyakarta Hadiningrat adalah ekspresi budaya karawitan tradisional yang ikonik dan bersejarah di Yogyakarta. Gamelan, yang terdiri dari alat musik logam seperti gong, kenong, bonang, saron, dan kendang, menghasilkan harmoni khas Jawa yang mendalam dan penuh makna. Di Keraton, gamelan tidak hanya berfungsi sebagai musik pengiring, tetapi juga mencerminkan nilai spiritual dan filosofi hidup Jawa, sering digunakan untuk mengiringi tarian klasik, upacara kerajaan, dan pertunjukan wayang kulit.\r\n\r\nDi Pendopo Keraton, gamelan dimainkan oleh abdi dalem atau kelompok karawitan yang terlatih, membawa suasana yang menggetarkan batin. Keraton Yogyakarta memiliki koleksi gamelan pusaka dengan nilai sejarah tinggi, yang hanya dimainkan pada acara-acara khusus. Pertunjukan gamelan menjadi bagian penting dari kegiatan budaya harian di keraton, memberikan pengalaman budaya otentik bagi wisatawan dan memperkenalkan filosofi serta estetika kehidupan masyarakat Jawa.', NULL, NULL, NULL, '2026-01-07 12:46:00', '2026-01-07 12:46:00', NULL, NULL, '/assets/uploads/evt_695b504815b9b1.68396510.png', 'publish', 8, '2026-01-05 05:46:48', NULL),
(13, NULL, NULL, 'Festival Sekaten', 'festival-sekaten', 'Festival Sekaten adalah tradisi budaya dan keagamaan yang telah berlangsung ratusan tahun di Yogyakarta, dimulai pada masa Kesultanan Mataram Islam. Festival ini diselenggarakan untuk memperingati Maulid Nabi Muhammad SAW dan sebagai sarana dakwah Islam yang dikemas dalam budaya Jawa. Sekaten, yang nama nya berasal dari kata syahadatain (dua kalimat syahadat), dimulai dengan gamelan untuk menarik perhatian masyarakat agar mendengarkan dakwah.', NULL, NULL, NULL, '2026-01-08 12:47:00', '2026-01-08 12:47:00', NULL, NULL, '/assets/uploads/evt_695b5092b06e84.87205575.png', 'publish', 8, '2026-01-05 05:48:02', NULL),
(14, NULL, NULL, 'Batik Tulis Yogyakarta', 'batik-tulis-yogyakarta', 'Kampung Batik Giriloyo adalah pusat batik tulis tradisional yang terletak di Wukirsari, Imogiri, Bantul, Yogyakarta. Kampung ini telah menjadi bagian dari budaya Kerajaan Mataram sejak abad ke-17, dengan pengrajin yang masih mempertahankan teknik membatik tulis manual menggunakan canting dan lilin malam. Batik Giriloyo terkenal dengan warna alami seperti soga (cokelat) dan bahan pewarna alami lainnya, menciptakan palet warna lembut khas batik Jawa klasik. Motif batiknya, seperti Parang dan Sidomukti, mengandung filosofi dan nilai budaya yang mendalam.', 'Kampung Batik Giriloyo', NULL, NULL, '2026-01-09 12:48:00', '2026-01-09 12:48:00', NULL, NULL, '/assets/uploads/evt_695b50e727e852.17208106.png', 'publish', 8, '2026-01-05 05:49:27', NULL),
(15, 22, 3, 'Sintha Obong Prambanan', 'sintha-obong-prambanan', 'Shinta Obong Prambanan merupakan salah satu bagian paling terkenal dan sakral dari kisah **Sendratari Ramayana** yang dipentaskan di kompleks Candi Prambanan, Yogyakarta. Pertunjukan ini mengangkat episode ketika Dewi Shinta harus membuktikan kesucian dan kesetiaannya kepada Rama setelah berhasil diselamatkan dari penculikan Rahwana, raja Alengka. Adegan “Shinta Obong” menggambarkan momen ketika Dewi Shinta dengan penuh keteguhan hati rela membakar dirinya di atas api suci sebagai bentuk ujian kebenaran.\r\n\r\nDalam pementasannya, Shinta Obong ditampilkan melalui tarian klasik Jawa yang anggun, penuh makna, dan sarat simbolisme. Gerak tari yang lembut mencerminkan kesucian, ketulusan, serta kekuatan batin Dewi Shinta. Api yang menyala di tengah panggung menjadi simbol ujian moral dan spiritual, sementara iringan gamelan serta tata cahaya dramatis memperkuat suasana haru dan sakral. Menurut kisah Ramayana, Dewi Shinta tidak terbakar oleh api karena kemurnian hatinya, sehingga membuktikan bahwa ia tetap setia dan suci.\r\n\r\nShinta Obong Prambanan tidak hanya menjadi tontonan seni, tetapi juga sarana pelestarian nilai budaya dan filosofi Jawa. Kisah ini mengajarkan tentang kejujuran, pengorbanan, kesetiaan, serta keberanian mempertahankan kebenaran. Hingga kini, Shinta Obong menjadi salah satu daya tarik utama pertunjukan Sendratari Ramayana Prambanan yang memikat wisatawan lokal maupun mancanegara.', 'Candi Prambanan', NULL, NULL, '2026-01-09 19:30:00', '2026-01-09 21:00:00', 44, 150000, '/assets/uploads/evt_695bbd02c091c7.76915047.jpg', 'publish', 8, '2026-01-05 13:30:42', '2026-01-05 13:46:17');

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
(4, 'atraksi', 2, '/assets/uploads/detail/atr_detail_69592c40bd07a5.16727415.jpg', NULL, 1, '2026-01-03 14:48:32'),
(17, 'kuliner', 7, '/assets/uploads/detail/kul_detail_695b4277d0c878.43830659.png', NULL, 2, '2026-01-05 04:47:51'),
(28, 'kuliner', 8, '/assets/uploads/detail/kul_detail_695b433e1cb6b6.14180838.png', NULL, 1, '2026-01-05 04:51:10'),
(29, 'kuliner', 8, '/assets/uploads/detail/kul_detail_695b433e209291.13804364.png', NULL, 2, '2026-01-05 04:51:10'),
(30, 'kuliner', 8, '/assets/uploads/detail/kul_detail_695b433e23ac81.71203895.png', NULL, 3, '2026-01-05 04:51:10'),
(31, 'kuliner', 9, '/assets/uploads/detail/kul_detail_695b436e4c7799.55153231.png', NULL, 1, '2026-01-05 04:51:58'),
(32, 'kuliner', 9, '/assets/uploads/detail/kul_detail_695b436e4ecc54.86565343.png', NULL, 2, '2026-01-05 04:51:58'),
(33, 'kuliner', 9, '/assets/uploads/detail/kul_detail_695b436e51af58.57139016.png', NULL, 3, '2026-01-05 04:51:58'),
(34, 'kuliner', 10, '/assets/uploads/detail/kul_detail_695b43bd5c3bb4.41368388.png', NULL, 1, '2026-01-05 04:53:17'),
(35, 'kuliner', 10, '/assets/uploads/detail/kul_detail_695b43bd60a598.12934561.png', NULL, 2, '2026-01-05 04:53:17'),
(36, 'kuliner', 10, '/assets/uploads/detail/kul_detail_695b43bd64d435.02682671.png', NULL, 3, '2026-01-05 04:53:17'),
(37, 'kuliner', 11, '/assets/uploads/detail/kul_detail_695b43f6a381c3.33313190.png', NULL, 1, '2026-01-05 04:54:14'),
(38, 'kuliner', 11, '/assets/uploads/detail/kul_detail_695b43f6a60532.45609884.png', NULL, 2, '2026-01-05 04:54:14'),
(39, 'kuliner', 11, '/assets/uploads/detail/kul_detail_695b43f6a9d813.95131280.png', NULL, 3, '2026-01-05 04:54:14'),
(40, 'kuliner', 12, '/assets/uploads/detail/kul_detail_695b442e1af777.35579719.png', NULL, 1, '2026-01-05 04:55:10'),
(41, 'kuliner', 12, '/assets/uploads/detail/kul_detail_695b442e1eb237.04586329.png', NULL, 2, '2026-01-05 04:55:10'),
(42, 'kuliner', 12, '/assets/uploads/detail/kul_detail_695b442e225698.84619522.png', NULL, 3, '2026-01-05 04:55:10'),
(43, 'kuliner', 13, '/assets/uploads/detail/kul_detail_695b4462dab587.73654633.png', NULL, 1, '2026-01-05 04:56:02'),
(44, 'kuliner', 13, '/assets/uploads/detail/kul_detail_695b4462dd26c2.22549015.png', NULL, 2, '2026-01-05 04:56:02'),
(45, 'kuliner', 13, '/assets/uploads/detail/kul_detail_695b4462e0eac5.50079872.png', NULL, 3, '2026-01-05 04:56:02'),
(46, 'kuliner', 14, '/assets/uploads/detail/kul_detail_695b449b646cd5.94162614.png', NULL, 1, '2026-01-05 04:56:59'),
(47, 'kuliner', 14, '/assets/uploads/detail/kul_detail_695b449b688214.85589288.png', NULL, 2, '2026-01-05 04:56:59'),
(48, 'kuliner', 14, '/assets/uploads/detail/kul_detail_695b449b6c5e02.83440413.png', NULL, 3, '2026-01-05 04:56:59'),
(49, 'kuliner', 15, '/assets/uploads/detail/kul_detail_695b44ca725436.69940017.png', NULL, 1, '2026-01-05 04:57:46'),
(50, 'kuliner', 15, '/assets/uploads/detail/kul_detail_695b44ca753156.15791619.png', NULL, 2, '2026-01-05 04:57:46'),
(51, 'kuliner', 15, '/assets/uploads/detail/kul_detail_695b44ca778a49.37782306.png', NULL, 3, '2026-01-05 04:57:46'),
(53, 'kuliner', 16, '/assets/uploads/detail/kul_detail_695b4502b51556.26841856.png', NULL, 2, '2026-01-05 04:58:42'),
(54, 'kuliner', 16, '/assets/uploads/detail/kul_detail_695b4502b8d931.81070593.png', NULL, 3, '2026-01-05 04:58:42'),
(55, 'kuliner', 17, '/assets/uploads/detail/kul_detail_695b4532143cd8.28716666.png', NULL, 1, '2026-01-05 04:59:30'),
(57, 'kuliner', 17, '/assets/uploads/detail/kul_detail_695b45321a6f12.98156670.png', NULL, 3, '2026-01-05 04:59:30'),
(58, 'destinasi', 12, '/assets/uploads/detail/dest_detail_695b46e4243309.51386502.png', NULL, 1, '2026-01-05 05:06:44'),
(59, 'destinasi', 12, '/assets/uploads/detail/dest_detail_695b46e4277197.84101994.png', NULL, 2, '2026-01-05 05:06:44'),
(60, 'destinasi', 12, '/assets/uploads/detail/dest_detail_695b46e42a2820.73564634.png', NULL, 3, '2026-01-05 05:06:44'),
(61, 'destinasi', 13, '/assets/uploads/detail/dest_detail_695b47c510e0c2.00725941.png', NULL, 1, '2026-01-05 05:10:29'),
(62, 'destinasi', 13, '/assets/uploads/detail/dest_detail_695b47c51442f6.86991664.png', NULL, 2, '2026-01-05 05:10:29'),
(63, 'destinasi', 13, '/assets/uploads/detail/dest_detail_695b47c51780f4.50274950.png', NULL, 3, '2026-01-05 05:10:29'),
(64, 'destinasi', 14, '/assets/uploads/detail/dest_detail_695b48d311ee36.67068138.png', NULL, 1, '2026-01-05 05:14:59'),
(65, 'destinasi', 14, '/assets/uploads/detail/dest_detail_695b48d316bdc4.42149569.png', NULL, 2, '2026-01-05 05:14:59'),
(66, 'destinasi', 14, '/assets/uploads/detail/dest_detail_695b48d31bba89.88836629.png', NULL, 3, '2026-01-05 05:14:59'),
(67, 'destinasi', 15, '/assets/uploads/detail/dest_detail_695b499fe7f489.08293395.png', NULL, 1, '2026-01-05 05:18:23'),
(68, 'destinasi', 15, '/assets/uploads/detail/dest_detail_695b499feb9714.35958186.png', NULL, 2, '2026-01-05 05:18:23'),
(69, 'destinasi', 15, '/assets/uploads/detail/dest_detail_695b499fef8241.59129911.png', NULL, 3, '2026-01-05 05:18:23'),
(70, 'destinasi', 16, '/assets/uploads/detail/dest_detail_695b4b1ec52f55.83387338.png', NULL, 1, '2026-01-05 05:24:46'),
(71, 'destinasi', 16, '/assets/uploads/detail/dest_detail_695b4b1ec96e27.64735009.png', NULL, 2, '2026-01-05 05:24:46'),
(72, 'destinasi', 16, '/assets/uploads/detail/dest_detail_695b4b1eccc204.92035376.png', NULL, 3, '2026-01-05 05:24:46'),
(73, 'destinasi', 17, '/assets/uploads/detail/dest_detail_695b4c45745df8.55879026.png', NULL, 1, '2026-01-05 05:29:41'),
(74, 'destinasi', 17, '/assets/uploads/detail/dest_detail_695b4c457a1724.71196899.png', NULL, 2, '2026-01-05 05:29:41'),
(75, 'destinasi', 17, '/assets/uploads/detail/dest_detail_695b4c457cb106.71738482.png', NULL, 3, '2026-01-05 05:29:41'),
(76, 'destinasi', 18, '/assets/uploads/detail/dest_detail_695b4cd54fb8e0.84521503.png', NULL, 1, '2026-01-05 05:32:05'),
(77, 'destinasi', 18, '/assets/uploads/detail/dest_detail_695b4cd5543c76.39154473.png', NULL, 2, '2026-01-05 05:32:05'),
(78, 'destinasi', 18, '/assets/uploads/detail/dest_detail_695b4cd5583f65.47003432.png', NULL, 3, '2026-01-05 05:32:05'),
(79, 'destinasi', 19, '/assets/uploads/detail/dest_detail_695b4d6d7258f8.84782342.png', NULL, 1, '2026-01-05 05:34:37'),
(80, 'destinasi', 19, '/assets/uploads/detail/dest_detail_695b4d6d7573e3.73339104.png', NULL, 2, '2026-01-05 05:34:37'),
(81, 'destinasi', 19, '/assets/uploads/detail/dest_detail_695b4d6d784485.98534722.png', NULL, 3, '2026-01-05 05:34:37'),
(82, 'destinasi', 20, '/assets/uploads/detail/dest_detail_695b4deb3572b2.87955212.png', NULL, 1, '2026-01-05 05:36:43'),
(83, 'destinasi', 20, '/assets/uploads/detail/dest_detail_695b4deb38edc5.34375840.png', NULL, 2, '2026-01-05 05:36:43'),
(84, 'destinasi', 20, '/assets/uploads/detail/dest_detail_695b4deb3bafc5.25408019.png', NULL, 3, '2026-01-05 05:36:43'),
(85, 'destinasi', 21, '/assets/uploads/detail/dest_detail_695b4ee21b3409.95626533.png', NULL, 1, '2026-01-05 05:40:50'),
(86, 'destinasi', 21, '/assets/uploads/detail/dest_detail_695b4ee21f7352.40009946.png', NULL, 2, '2026-01-05 05:40:50'),
(87, 'destinasi', 21, '/assets/uploads/detail/dest_detail_695b4ee2239eb1.93932890.png', NULL, 3, '2026-01-05 05:40:50'),
(88, 'event', 10, '/assets/uploads/detail/evt_detail_695b4fb514a714.17493840.png', NULL, 1, '2026-01-05 05:44:21'),
(89, 'event', 10, '/assets/uploads/detail/evt_detail_695b4fb517f657.91844062.png', NULL, 2, '2026-01-05 05:44:21'),
(90, 'event', 10, '/assets/uploads/detail/evt_detail_695b4fb51a5e71.96746243.png', NULL, 3, '2026-01-05 05:44:21'),
(91, 'event', 11, '/assets/uploads/detail/evt_detail_695b5003e66085.48507803.png', NULL, 1, '2026-01-05 05:45:39'),
(92, 'event', 11, '/assets/uploads/detail/evt_detail_695b5003e9e3d2.85362335.png', NULL, 2, '2026-01-05 05:45:39'),
(93, 'event', 11, '/assets/uploads/detail/evt_detail_695b5003ecbbb0.95532103.png', NULL, 3, '2026-01-05 05:45:39'),
(94, 'event', 12, '/assets/uploads/detail/evt_detail_695b50481a3110.39501079.png', NULL, 1, '2026-01-05 05:46:48'),
(95, 'event', 12, '/assets/uploads/detail/evt_detail_695b50481d99c3.85877651.png', NULL, 2, '2026-01-05 05:46:48'),
(96, 'event', 12, '/assets/uploads/detail/evt_detail_695b5048200811.33155595.png', NULL, 3, '2026-01-05 05:46:48'),
(97, 'event', 13, '/assets/uploads/detail/evt_detail_695b5092b479a9.84439192.png', NULL, 1, '2026-01-05 05:48:02'),
(98, 'event', 13, '/assets/uploads/detail/evt_detail_695b5092b82851.81493205.png', NULL, 2, '2026-01-05 05:48:02'),
(99, 'event', 13, '/assets/uploads/detail/evt_detail_695b5092bc14e2.77464261.png', NULL, 3, '2026-01-05 05:48:02'),
(100, 'event', 14, '/assets/uploads/detail/evt_detail_695b50e72c94d1.76494579.png', NULL, 1, '2026-01-05 05:49:27'),
(101, 'event', 14, '/assets/uploads/detail/evt_detail_695b50e731c1b0.56403478.png', NULL, 2, '2026-01-05 05:49:27'),
(102, 'event', 14, '/assets/uploads/detail/evt_detail_695b50e7364d06.84497321.png', NULL, 3, '2026-01-05 05:49:27'),
(103, 'destinasi', 22, '/assets/uploads/detail/dest_detail_695bba9d5c5f89.33774452.jpg', NULL, 1, '2026-01-05 13:20:29');

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
(2, 'Pegunungan', 'destinasi', '2025-12-15 14:52:05'),
(3, 'Budaya', 'event', '2026-01-05 13:06:45');

-- --------------------------------------------------------

--
-- Table structure for table `kuliner`
--

CREATE TABLE `kuliner` (
  `id_kuliner` bigint UNSIGNED NOT NULL,
  `kuliner_kategori_id` bigint UNSIGNED DEFAULT NULL,
  `nama` varchar(160) NOT NULL,
  `deskripsi` text,
  `gambar_sampul_url` varchar(255) DEFAULT NULL,
  `status` enum('draft','publish','arsip') NOT NULL DEFAULT 'draft',
  `dibuat_oleh` bigint UNSIGNED DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `diubah_pada` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kuliner`
--

INSERT INTO `kuliner` (`id_kuliner`, `kuliner_kategori_id`, `nama`, `deskripsi`, `gambar_sampul_url`, `status`, `dibuat_oleh`, `dibuat_pada`, `diubah_pada`) VALUES
(7, 1, 'Gudeg Yu Djum', 'ChatGPT said:\r\nGudeg Yu Djum adalah ikon kuliner Yogyakarta yang terkenal karena cita rasa khas, kualitas konsisten, dan sejarah panjang sejak 1950-an. Gudegnya termasuk gudeg kering berbahan nangka muda yang dimasak lama dengan santan dan rempah hingga meresap, menghasilkan rasa manis-gurih yang lembut serta lebih tahan lama. Keistimewaannya terletak pada pemasakan dengan kayu bakar yang memberi aroma khas, ditambah lauk pelengkap seperti ayam kampung, telur pindang, tahu-tempe, krecek pedas, dan sambal goreng. Selain dinikmati di tempat, Gudeg Yu Djum juga populer sebagai oleh-oleh dalam kemasan besek atau kaleng, tanpa meninggalkan resep tradisional yang diwariskan turun-temurun.', '/assets/uploads/kul_695b4277c5bb34.46509997.png', 'publish', 8, '2026-01-05 04:47:51', NULL),
(8, 1, 'Sate Ratu', 'Sate Ratu adalah destinasi kuliner populer di Yogyakarta yang terkenal dengan sate ayam, kambing, dan mixed berkualitas tinggi. Satenya dibakar menggunakan arang sehingga aromanya khas, dagingnya empuk, dan bumbunya meresap dengan rasa manis-gurih seimbang. Disajikan dengan saus kacang tradisional serta sambal yang bisa disesuaikan tingkat pedasnya. Suasana warung sederhana dan hangat, bahan baku terjaga, serta pelayanan ramah, menjadikannya rekomendasi wajib bagi wisatawan maupun warga lokal.', '/assets/uploads/kul_695b433e17aae8.42783555.png', 'publish', 8, '2026-01-05 04:48:44', '2026-01-05 04:51:10'),
(9, 1, 'Sate Klatak Pak Pong', 'Sate Klathak Pak Pong adalah salah satu destinasi kuliner legendaris di Yogyakarta, khususnya bagi pecinta olahan kambing yang ingin mencicipi sate khas yang berbeda dari kebanyakan sate di Indonesia. Sate klathak sendiri merupakan variasi sate kambing yang unik karena cara memasaknya, bumbunya, dan sensasi rasa yang dihasilkan — yang semuanya mencerminkan tradisi kuliner khas Jogja selatan.', '/assets/uploads/kul_695b436e49e054.98308106.png', 'publish', 8, '2026-01-05 04:51:58', NULL),
(10, 1, 'Oseng Mercon Bu Narti', 'Oseng Mercon Bu Narti adalah kuliner legendaris Yogyakarta yang terkenal dengan sensasi pedas ekstrem “mercon”. Hidangan ini berupa tumisan cepat daging (sapi/ayam/paru) dengan cabe rawit, bawang, dan rempah, menghasilkan rasa gurih–pedas dengan sedikit manis. Ciri khasnya ada pada racikan bumbu turun-temurun yang pedasnya tajam namun tetap seimbang. Warungnya sederhana, ramah, dan terjangkau, sehingga jadi tujuan favorit pecinta pedas yang ingin merasakan tantangan kuliner khas Jogja.', '/assets/uploads/kul_695b43bd585f50.84149800.png', 'publish', 8, '2026-01-05 04:53:17', NULL),
(11, 1, 'Nasi Teri pojok Gejayan', 'Nasi Teri Pojok Gejayan adalah salah satu kuliner legendaris di Yogyakarta yang sudah menjadi favorit masyarakat lokal, mahasiswa, hingga wisatawan yang berburu makanan malam atau sahur ketika Ramadan. Warung ini dikenal sebagai spot kuliner malam yang ramah di kantong, penuh rasa, dan sarat dengan nuansa lokal Jogja.', '/assets/uploads/kul_695b43f69fcfe9.98367123.png', 'publish', 8, '2026-01-05 04:54:14', NULL),
(12, 1, 'Mangut Lele Mbah Marto', 'Mangut Lele Mbah Marto adalah kuliner legendaris Yogyakarta yang terkenal dengan mangut lele asap: lele diasapi lalu dimasak dengan kuah santan dan rempah khas Jawa, menghasilkan rasa gurih, pedas, dan kaya aroma. Prosesnya masih tradisional dengan resep turun-temurun, disajikan dengan nasi hangat serta pendamping seperti lalapan dan sayur. Suasana warung sederhana dan hangat, sehingga pengalaman makan terasa autentik dan sering direkomendasikan sebagai destinasi wajib kuliner Jogja.', '/assets/uploads/kul_695b442e1629d9.64285196.png', 'publish', 8, '2026-01-05 04:55:10', NULL),
(13, 4, 'Kopi Jos Lik Man', 'Kopi Jos Lik Man adalah ikon kuliner malam Yogyakarta dengan penyajian unik: kopi hitam panas diberi arang membara ke dalam gelas hingga terdengar bunyi “jos”. Teknik ini memberi sensasi khas dengan aroma kuat, rasa lebih bold dan sedikit berasap, cocok dinikmati malam hari. Warungnya mempertahankan racikan tradisional memakai kopi lokal, suasananya sederhana dan ramah, serta populer sebagai tempat nongkrong dan tujuan wisata kuliner otentik Jogja.', '/assets/uploads/kul_695b4462d3e3a9.80879524.png', 'publish', 8, '2026-01-05 04:56:02', NULL),
(14, 5, 'Jadah Tempe Mbah Carik', 'Jadah Tempe Mbah Carik adalah kuliner tradisional khas Yogyakarta yang terkenal dengan perpaduan jadah ketan yang lembut dan tempe goreng berbumbu yang gurih, kadang dilengkapi sambal untuk menambah rasa pedas. Resepnya diwariskan turun-temurun dengan bahan berkualitas—ketan pilihan dan tempe segar—sehingga cita rasanya tetap autentik. Suasananya sederhana dan hangat, membuatnya populer sebagai camilan atau santapan ringan yang sering direkomendasikan wisatawan.', '/assets/uploads/kul_695b449b608349.20175628.png', 'publish', 8, '2026-01-05 04:56:59', NULL),
(15, 1, 'Hoouse Of Ramintem', 'House of Raminten adalah ikon wisata kuliner Yogyakarta yang memadukan makanan tradisional Jawa, budaya lokal, dan hiburan dalam satu pengalaman. Restoran ini dikenal dengan suasana Jawa yang hangat, dekorasi khas seperti ukiran kayu, lampu temaram, serta ornamen unik bernuansa Yogyakarta yang membuat pengunjung merasakan pengalaman makan yang autentik dan berbeda dari restoran pada umumnya.', '/assets/uploads/kul_695b44ca6d1641.58427654.png', 'publish', 8, '2026-01-05 04:57:46', NULL),
(16, 5, 'Bakpia Pathok', 'Bakpia Pathok adalah ikon oleh-oleh khas Yogyakarta yang berasal dari kawasan Pathok. Kue bulat kecil ini dikenal dengan kulit tipis lembut dan isian manis seperti kacang hijau, serta varian modern seperti keju, cokelat, durian, hingga green tea. Berawal dari pengaruh kue Tionghoa pada awal abad ke-20, bakpia kemudian diadaptasi sesuai selera lokal dan kini menjadi buruan wisatawan karena rasanya konsisten, mudah dibawa, dan tersedia luas di toko serta kios oleh-oleh di Yogyakarta.', '/assets/uploads/kul_695b4502acee27.10233797.png', 'publish', 8, '2026-01-05 04:58:42', NULL),
(17, 1, 'Bakmi Mbah Gito', 'Bakmi Jawa Mbah Gito adalah kuliner legendaris Yogyakarta yang terkenal dengan bakmi tradisional Jawa bercita rasa autentik serta suasana pedesaan Jawa tempo dulu. Menu seperti bakmi goreng, bakmi godhog, dan magelangan dimasak satu per satu memakai anglo dan arang, menghasilkan rasa manis-gurih khas Jogja dengan aroma asap yang khas. Tempatnya bernuansa kayu dan bambu dengan ornamen tradisional, pelayanannya ramah, dan pengalaman menunggunya menjadi bagian dari sensasi menikmati bakmi Jawa yang asli.', '/assets/uploads/kul_695b45321103b9.04487917.png', 'publish', 8, '2026-01-05 04:59:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kuliner_kategori`
--

CREATE TABLE `kuliner_kategori` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kuliner_kategori`
--

INSERT INTO `kuliner_kategori` (`id`, `nama`, `slug`, `created_at`) VALUES
(1, 'Makanan Tradisional', 'makanan-tradisional', '2026-01-05 03:20:12'),
(2, 'Street Food', 'street-food', '2026-01-05 03:20:12'),
(3, 'Jajanan Pasar', 'jajanan-pasar', '2026-01-05 03:20:12'),
(4, 'Minuman', 'minuman', '2026-01-05 03:20:12'),
(5, 'Oleh-oleh', 'oleh-oleh', '2026-01-05 03:20:12');

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
(1, 1, 'beranda', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2025-12-14 19:18:56'),
(2, 8, 'beranda', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-04 17:44:40'),
(3, 8, 'beranda', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-04 17:44:59'),
(4, 8, 'destinasi', 11, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-04 17:49:16'),
(5, 8, 'destinasi', 9, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-04 17:49:33'),
(6, 8, 'beranda', NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-04 17:53:24'),
(7, 8, 'destinasi', 9, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-04 18:35:02'),
(8, 8, 'destinasi', 11, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-05 03:53:56'),
(9, 8, 'destinasi', 19, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-05 10:21:49'),
(10, 8, 'destinasi', 19, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-05 10:47:01'),
(11, 8, 'destinasi', 18, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-05 10:47:17'),
(12, 8, 'destinasi', 22, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-05 13:20:37'),
(13, 8, 'destinasi', 22, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-05 13:30:46'),
(14, 8, 'destinasi', 12, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-05 13:35:12'),
(15, 9, 'destinasi', 22, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-05 13:39:29'),
(16, 13, 'destinasi', 22, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '79f1ed7c3831720253675dcf97a9714711e3e0ec94b6abf77f5bbf0280dc9c0d', '2026-01-05 13:45:36');

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
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint UNSIGNED NOT NULL,
  `id_pengguna` bigint UNSIGNED NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
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

--
-- Dumping data for table `pelaporan`
--

INSERT INTO `pelaporan` (`id_pelaporan`, `id_pengguna`, `jenis_target`, `id_target`, `judul`, `deskripsi`, `status`, `dibuat_pada`) VALUES
(1, 8, 'event', 4, 'Pelaporan', 'loading nya lama banget', 'selesai', '2026-01-04 14:44:50');

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
(4, 9, 'gateway', 300000, 'SUDAH_BAYAR', NULL, '2026-01-05 13:35:57', '2026-01-05 13:35:54'),
(5, 10, 'gateway', 300000, 'SUDAH_BAYAR', NULL, '2026-01-05 13:40:11', '2026-01-05 13:40:08'),
(6, 11, 'gateway', 300000, 'SUDAH_BAYAR', NULL, '2026-01-05 13:46:17', '2026-01-05 13:46:13');

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
(8, 'Administrator', 'admin', 'admin@jogjaverse.com', '0192023a7bbd73250516f069df18b500', 'admin', '081234567890', '/public/user/img/profile/profile_8_1767534950.jpg', 1, '2025-12-16 15:17:22', '2026-01-05 12:59:57'),
(9, 'User', 'user', 'user@jogjaverse.com', '6ad14ba9986e3615423dfca256d04e3f', 'user', '081234567891', NULL, 1, '2025-12-16 15:17:22', '2026-01-05 13:41:32'),
(10, 'adminn', 'adminn', 'uadyi@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 'admin', '09123456789', NULL, 1, '2025-12-18 17:42:59', '2026-01-03 02:09:02'),
(11, 'userrr', 'userrr', 'user@113.com', '96e79218965eb72c92a549dd5a330112', 'user', '0887982747214', NULL, 1, '2025-12-26 18:35:30', '2025-12-26 18:35:58'),
(12, 'eja engkol', 'eja', 'ejaengkol@email.com', '0603a3bce7c5288c1445c4ba1a5916c4', 'user', '', NULL, 1, '2026-01-05 13:42:55', '2026-01-05 13:42:55'),
(13, 'yusril', 'yusril', 'yusril@gmail.com', 'f486144d6ec1952bcc438fae0bb8ba4a', 'user', '08123456789', NULL, 1, '2026-01-05 13:44:48', '2026-01-05 13:47:34');

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
(9, 15, 8, 2, 150000, 300000, 'DIKONFIRMASI', '2026-01-05 20:50:54', '', '2026-01-05 13:35:54', '2026-01-05 13:35:57'),
(10, 15, 9, 2, 150000, 300000, 'DIKONFIRMASI', '2026-01-05 20:55:08', '', '2026-01-05 13:40:08', '2026-01-05 13:40:11'),
(11, 15, 13, 2, 150000, 300000, 'DIKONFIRMASI', '2026-01-05 21:01:13', '', '2026-01-05 13:46:13', '2026-01-05 13:46:17');

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
(4, 9, 'EVT-9-B1427A-1', 0, NULL, '2026-01-05 13:35:54'),
(5, 9, 'EVT-9-FB65A2-2', 0, NULL, '2026-01-05 13:35:54'),
(6, 10, 'EVT-10-973190-1', 0, NULL, '2026-01-05 13:40:08'),
(7, 10, 'EVT-10-CEE55D-2', 0, NULL, '2026-01-05 13:40:08'),
(8, 11, 'EVT-11-1FE4AA-1', 0, NULL, '2026-01-05 13:46:13'),
(9, 11, 'EVT-11-F6E60B-2', 0, NULL, '2026-01-05 13:46:13');

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
-- Dumping data for table `ulasan`
--

INSERT INTO `ulasan` (`id_ulasan`, `id_pengguna`, `jenis_target`, `id_target`, `rating`, `komentar`, `status`, `dibuat_pada`) VALUES
(1, 8, 'event', 8, 5, 'event nya seru', 'tampil', '2026-01-04 12:42:31'),
(2, 8, 'kuliner', 3, 4, 'makanannya lumayan enak', 'tampil', '2026-01-04 14:45:41'),
(3, 9, 'kuliner', 16, 5, 'enek betss', 'sembunyi', '2026-01-05 13:40:57'),
(4, 13, 'kuliner', 13, 5, 'josjis bets kopinya', 'sembunyi', '2026-01-05 13:46:54');

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
  ADD KEY `fk_kul_dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `idx_kul_status` (`status`),
  ADD KEY `idx_kuliner_kategori_id` (`kuliner_kategori_id`);

--
-- Indexes for table `kuliner_kategori`
--
ALTER TABLE `kuliner_kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_kuliner_kategori_slug` (`slug`);

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
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_password_resets_pengguna` (`id_pengguna`),
  ADD KEY `idx_password_resets_token` (`token`);

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
  MODIFY `id_destinasi` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id_event` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id_galeri` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kuliner`
--
ALTER TABLE `kuliner`
  MODIFY `id_kuliner` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `kuliner_kategori`
--
ALTER TABLE `kuliner_kategori`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kunjungan`
--
ALTER TABLE `kunjungan`
  MODIFY `id_kunjungan` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `log_admin`
--
ALTER TABLE `log_admin`
  MODIFY `id_log` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pelaporan`
--
ALTER TABLE `pelaporan`
  MODIFY `id_pelaporan` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `relasi_tag`
--
ALTER TABLE `relasi_tag`
  MODIFY `id_relasi_tag` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservasi_event`
--
ALTER TABLE `reservasi_event`
  MODIFY `id_reservasi` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
  MODIFY `id_tag` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiket_event`
--
ALTER TABLE `tiket_event`
  MODIFY `id_tiket` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `id_ulasan` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  ADD CONSTRAINT `fk_kul_dibuat_oleh` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kuliner_kategori` FOREIGN KEY (`kuliner_kategori_id`) REFERENCES `kuliner_kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `log_admin`
--
ALTER TABLE `log_admin`
  ADD CONSTRAINT `fk_log_admin` FOREIGN KEY (`id_admin`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE;

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
