CREATE TABLE IF NOT EXISTS password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pengguna BIGINT NOT NULL,
  token VARCHAR(100) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_password_resets_token (token),
  INDEX idx_password_resets_pengguna (id_pengguna),
  CONSTRAINT fk_password_resets_pengguna
    FOREIGN KEY (id_pengguna) REFERENCES pengguna(id_pengguna)
    ON DELETE CASCADE
);
