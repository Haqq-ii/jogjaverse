CREATE TABLE IF NOT EXISTS `kunjungan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(128) NOT NULL,
  `user_id` BIGINT NULL,
  `halaman` VARCHAR(50) NOT NULL,
  `target_type` VARCHAR(30) NOT NULL,
  `target_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_kunjungan_created_at` (`created_at`),
  KEY `idx_kunjungan_target` (`target_type`, `target_id`),
  KEY `idx_kunjungan_session` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
