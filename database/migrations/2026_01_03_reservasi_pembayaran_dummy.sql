-- Tambahan untuk simulasi pembayaran reservasi event
ALTER TABLE `pembayaran`
  ADD COLUMN `kode_transaksi` varchar(50) NULL AFTER `id_reservasi`;

ALTER TABLE `pembayaran`
  ADD INDEX `idx_bayar_reservasi` (`id_reservasi`);

ALTER TABLE `tiket_event`
  ADD INDEX `idx_tiket_reservasi` (`id_reservasi`);

ALTER TABLE `reservasi_event`
  ADD INDEX `idx_reservasi_event_user` (`id_event`, `id_pengguna`);
