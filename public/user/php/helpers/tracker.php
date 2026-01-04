<?php
if (!function_exists('jv_get_kunjungan_columns')) {
  function jv_get_kunjungan_columns(mysqli $koneksi): array {
    static $cache = null;
    if ($cache !== null) {
      return $cache;
    }
    $cache = [];
    $stmt = $koneksi->prepare("
      SELECT COLUMN_NAME, COLUMN_TYPE
      FROM information_schema.columns
      WHERE table_schema = DATABASE() AND table_name = 'kunjungan'
    ");
    if (!$stmt) {
      return $cache;
    }
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
      while ($row = $res->fetch_assoc()) {
        $cache[$row['COLUMN_NAME']] = $row['COLUMN_TYPE'];
      }
    }
    $stmt->close();
    return $cache;
  }
}

if (!function_exists('jv_pick_kunjungan_col')) {
  function jv_pick_kunjungan_col(array $cols, array $candidates): ?string {
    foreach ($candidates as $candidate) {
      if (array_key_exists($candidate, $cols)) {
        return $candidate;
      }
    }
    return null;
  }
}

if (!function_exists('jv_resolve_enum_value')) {
  function jv_resolve_enum_value(?string $columnType, string $value, string $fallback): string {
    if ($columnType && stripos($columnType, 'enum(') !== false) {
      if (strpos($columnType, "'" . $value . "'") === false) {
        return $fallback;
      }
    }
    return $value;
  }
}

if (!function_exists('track_visit')) {
  function track_visit(mysqli $koneksi, string $halaman, string $targetType, int $targetId, ?int $userIdNullable = null): bool {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    $targetId = (int)$targetId;
    if ($targetId <= 0) {
      return false;
    }

    $key = "visit_{$halaman}_{$targetType}_{$targetId}";
    $now = time();
    if (!empty($_SESSION[$key]) && ($now - (int)$_SESSION[$key]) < 600) {
      return false;
    }
    $_SESSION[$key] = $now;

    $cols = jv_get_kunjungan_columns($koneksi);
    if (empty($cols)) {
      return false;
    }

    $data = [];
    $types = '';
    $params = [];

    if (isset($cols['session_id'])) {
      $data['session_id'] = session_id();
      $types .= 's';
      $params[] = $data['session_id'];
    }

    $colUser = jv_pick_kunjungan_col($cols, ['user_id', 'id_pengguna']);
    if ($colUser) {
      $data[$colUser] = $userIdNullable !== null ? (int)$userIdNullable : null;
      $types .= 'i';
      $params[] = $data[$colUser];
    }

    $colHalaman = jv_pick_kunjungan_col($cols, ['halaman', 'jenis_halaman', 'jenis', 'tipe']);
    if ($colHalaman) {
      $value = jv_resolve_enum_value($cols[$colHalaman] ?? null, $halaman, $targetType);
      $data[$colHalaman] = $value;
      $types .= 's';
      $params[] = $value;
    }

    $colTargetType = jv_pick_kunjungan_col($cols, ['target_type', 'jenis_target']);
    if ($colTargetType) {
      $data[$colTargetType] = $targetType;
      $types .= 's';
      $params[] = $targetType;
    }

    $colTargetId = jv_pick_kunjungan_col($cols, ['target_id', 'id_target']);
    if ($colTargetId) {
      $data[$colTargetId] = $targetId;
      $types .= 'i';
      $params[] = $targetId;
    }

    if (isset($cols['user_agent'])) {
      $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
      $data['user_agent'] = $ua;
      $types .= 's';
      $params[] = $ua;
    }

    if (isset($cols['ip_hash'])) {
      $ip = $_SERVER['REMOTE_ADDR'] ?? '';
      $hash = $ip !== '' ? hash('sha256', $ip) : null;
      $data['ip_hash'] = $hash;
      $types .= 's';
      $params[] = $hash;
    }

    if (empty($data)) {
      return false;
    }

    $columns = array_keys($data);
    $placeholders = implode(',', array_fill(0, count($data), '?'));
    $columnSql = '`' . implode('`,`', $columns) . '`';
    $sql = "INSERT INTO `kunjungan` ($columnSql) VALUES ($placeholders)";
    $stmt = $koneksi->prepare($sql);
    if (!$stmt) {
      return false;
    }
    $stmt->bind_param($types, ...$params);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
  }
}
