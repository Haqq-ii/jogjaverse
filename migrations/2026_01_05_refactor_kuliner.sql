-- Migration: refactor kuliner agar berdiri sendiri
-- Catatan: gunakan approach aman dengan cek information_schema sebelum ALTER.

CREATE TABLE IF NOT EXISTS `kuliner_kategori` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_kuliner_kategori_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `kuliner_kategori` (`nama`, `slug`, `created_at`) VALUES
('Makanan Tradisional', 'makanan-tradisional', NOW()),
('Street Food', 'street-food', NOW()),
('Jajanan Pasar', 'jajanan-pasar', NOW()),
('Minuman', 'minuman', NOW()),
('Oleh-oleh', 'oleh-oleh', NOW());

-- Tambah kolom kuliner_kategori_id jika belum ada
SET @has_col_kuliner_kategori := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'kuliner_kategori_id'
);
SET @sql := IF(@has_col_kuliner_kategori = 0,
  'ALTER TABLE `kuliner` ADD COLUMN `kuliner_kategori_id` BIGINT UNSIGNED NULL AFTER `id_kuliner`',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Drop FK dan index lama untuk id_destinasi
SET @fk_dest := (
  SELECT CONSTRAINT_NAME
  FROM information_schema.KEY_COLUMN_USAGE
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'id_destinasi'
    AND REFERENCED_TABLE_NAME IS NOT NULL
  LIMIT 1
);
SET @sql := IF(@fk_dest IS NULL,
  'SELECT 1',
  CONCAT('ALTER TABLE `kuliner` DROP FOREIGN KEY `', @fk_dest, '`')
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_dest := (
  SELECT INDEX_NAME
  FROM information_schema.STATISTICS
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'id_destinasi'
    AND index_name <> 'PRIMARY'
  LIMIT 1
);
SET @sql := IF(@idx_dest IS NULL,
  'SELECT 1',
  CONCAT('ALTER TABLE `kuliner` DROP INDEX `', @idx_dest, '`')
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Drop FK dan index lama untuk id_kategori (kategori destinasi)
SET @fk_kat := (
  SELECT CONSTRAINT_NAME
  FROM information_schema.KEY_COLUMN_USAGE
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'id_kategori'
    AND REFERENCED_TABLE_NAME IS NOT NULL
  LIMIT 1
);
SET @sql := IF(@fk_kat IS NULL,
  'SELECT 1',
  CONCAT('ALTER TABLE `kuliner` DROP FOREIGN KEY `', @fk_kat, '`')
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_kat := (
  SELECT INDEX_NAME
  FROM information_schema.STATISTICS
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'id_kategori'
    AND index_name <> 'PRIMARY'
  LIMIT 1
);
SET @sql := IF(@idx_kat IS NULL,
  'SELECT 1',
  CONCAT('ALTER TABLE `kuliner` DROP INDEX `', @idx_kat, '`')
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Drop kolom yang tidak dipakai (aman jika kolom tidak ada)
SET @sql := IF((
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'id_destinasi'
) > 0, 'ALTER TABLE `kuliner` DROP COLUMN `id_destinasi`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF((
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'id_kategori'
) > 0, 'ALTER TABLE `kuliner` DROP COLUMN `id_kategori`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF((
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'latitude'
) > 0, 'ALTER TABLE `kuliner` DROP COLUMN `latitude`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF((
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'longitude'
) > 0, 'ALTER TABLE `kuliner` DROP COLUMN `longitude`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF((
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'alamat'
) > 0, 'ALTER TABLE `kuliner` DROP COLUMN `alamat`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF((
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'jam_operasional'
) > 0, 'ALTER TABLE `kuliner` DROP COLUMN `jam_operasional`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF((
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'rentang_harga'
) > 0, 'ALTER TABLE `kuliner` DROP COLUMN `rentang_harga`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql := IF((
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'nomor_kontak'
) > 0, 'ALTER TABLE `kuliner` DROP COLUMN `nomor_kontak`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Tambah index + FK baru ke kuliner_kategori jika belum ada
SET @has_table_kuliner_kategori := (
  SELECT COUNT(*) FROM information_schema.tables
  WHERE table_schema = DATABASE() AND table_name = 'kuliner_kategori'
);
SET @has_col_kuliner_kategori := (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND column_name = 'kuliner_kategori_id'
);
SET @idx_new := (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'kuliner'
    AND index_name = 'idx_kuliner_kategori_id'
);
SET @sql := IF(@has_table_kuliner_kategori > 0 AND @has_col_kuliner_kategori > 0 AND @idx_new = 0,
  'ALTER TABLE `kuliner` ADD INDEX `idx_kuliner_kategori_id` (`kuliner_kategori_id`)',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_new := (
  SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS
  WHERE constraint_schema = DATABASE()
    AND table_name = 'kuliner'
    AND constraint_name = 'fk_kuliner_kategori'
);
SET @sql := IF(@has_table_kuliner_kategori > 0 AND @has_col_kuliner_kategori > 0 AND @fk_new = 0,
  'ALTER TABLE `kuliner` ADD CONSTRAINT `fk_kuliner_kategori` FOREIGN KEY (`kuliner_kategori_id`) REFERENCES `kuliner_kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- SQL inti (opsional, jika jalankan manual pada environment yang mendukung):
-- ALTER TABLE `kuliner` ADD COLUMN `kuliner_kategori_id` BIGINT UNSIGNED NULL AFTER `id_kuliner`;
-- ALTER TABLE `kuliner` DROP COLUMN `id_destinasi`, DROP COLUMN `id_kategori`, DROP COLUMN `latitude`,
--   DROP COLUMN `longitude`, DROP COLUMN `alamat`, DROP COLUMN `jam_operasional`,
--   DROP COLUMN `rentang_harga`, DROP COLUMN `nomor_kontak`;
-- ALTER TABLE `kuliner` ADD CONSTRAINT `fk_kuliner_kategori` FOREIGN KEY (`kuliner_kategori_id`)
--   REFERENCES `kuliner_kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
