-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Dec 15, 2025 at 04:17 PM
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
(6, NULL, 'Prambanan', 'prambanan', 'saya aoijsjoifaoiaoihoihnveoujvhnveovuihv[osbojaio[jr goiajgoirjisjgiueh9 vonrdoonoivdjhijvoi ej', 'Yogyakarta', 'Sleman', -7.7521040, 110.4914540, '08:00-16:00', 50000, '08675324533', '/assets/uploads/dest_693f0d79643443.67776680.jpg', 'draft', NULL, '2025-12-14 19:18:17', '2025-12-14 19:18:36');

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
(1, 'Admin JogjaVerse', 'admin', 'admin@jogjaverse.com', '0192023a7bbd73250516f069df18b500', 'admin', NULL, NULL, 1, '2025-12-14 16:40:11', NULL);

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
  MODIFY `id_atraksi` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `destinasi`
--
ALTER TABLE `destinasi`
  MODIFY `id_destinasi` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id_event` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id_galeri` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kuliner`
--
ALTER TABLE `kuliner`
  MODIFY `id_kuliner` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id_pembayaran` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `relasi_tag`
--
ALTER TABLE `relasi_tag`
  MODIFY `id_relasi_tag` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservasi_event`
--
ALTER TABLE `reservasi_event`
  MODIFY `id_reservasi` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
  MODIFY `id_tag` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tiket_event`
--
ALTER TABLE `tiket_event`
  MODIFY `id_tiket` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

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
