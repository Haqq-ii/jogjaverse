-- Reuse tabel galeri untuk detail gambar
ALTER TABLE `galeri`
  ADD INDEX `idx_gal_urutan` (`urutan`);
