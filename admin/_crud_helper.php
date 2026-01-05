<?php
// Helper CRUD generik untuk halaman admin
if (!function_exists('h')) {
  function h($val) {
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
  }
}

function describe_table(mysqli $koneksi, string $table): array {
  $cols = [];
  $pk = null;

  $res = $koneksi->query("SHOW FULL COLUMNS FROM `$table`");
  if ($res) {
    while ($row = $res->fetch_assoc()) {
      $cols[] = $row;
      if (($row['Key'] ?? '') === 'PRI') {
        $pk = $row['Field'];
      }
    }
  }

  return [$cols, $pk];
}

function pick_col(array $colNames, array $candidates): ?string {
  foreach ($candidates as $c) {
    foreach ($colNames as $n) {
      if (strtolower($n) === strtolower($c)) return $n;
    }
  }
  return null;
}

if (!function_exists('table_has_column')) {
  function table_has_column(mysqli $koneksi, string $table, string $column): bool {
    $stmt = $koneksi->prepare("
      SELECT COUNT(*) as total
      FROM information_schema.columns
      WHERE table_schema = DATABASE()
        AND table_name = ?
        AND column_name = ?
    ");
    if (!$stmt) {
      return false;
    }
    $stmt->bind_param("ss", $table, $column);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return ((int)($row['total'] ?? 0)) > 0;
  }
}

if (!function_exists('slugify')) {
  function slugify(string $text): string {
    $text = trim($text);
    if ($text === '') {
      return '';
    }
    if (function_exists('iconv')) {
      $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
      if ($converted !== false) {
        $text = $converted;
      }
    }
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\\s-]/', '', $text);
    $text = preg_replace('/[\\s-]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
  }
}

if (!function_exists('generate_unique_slug')) {
  function generate_unique_slug(
    mysqli $koneksi,
    string $table,
    string $slugCol,
    string $baseSlug,
    ?string $excludeId = null,
    string $idCol = 'id'
  ): string {
    $baseSlug = $baseSlug !== '' ? $baseSlug : 'item';
    $candidate = $baseSlug;
    $suffix = 1;

    while (true) {
      if ($excludeId !== null && $excludeId !== '') {
        $stmt = $koneksi->prepare("SELECT COUNT(*) as total FROM `$table` WHERE `$slugCol` = ? AND `$idCol` <> ?");
        if (!$stmt) {
          return $candidate;
        }
        $stmt->bind_param("ss", $candidate, $excludeId);
      } else {
        $stmt = $koneksi->prepare("SELECT COUNT(*) as total FROM `$table` WHERE `$slugCol` = ?");
        if (!$stmt) {
          return $candidate;
        }
        $stmt->bind_param("s", $candidate);
      }
      $stmt->execute();
      $row = $stmt->get_result()->fetch_assoc();
      $stmt->close();
      if (((int)($row['total'] ?? 0)) === 0) {
        return $candidate;
      }
      $suffix++;
      $candidate = $baseSlug . "-" . $suffix;
    }
  }
}

function guess_time_col(array $colNames): ?string {
  foreach ($colNames as $n) {
    $ln = strtolower($n);
    if (
      str_contains($ln, 'waktu') ||
      str_contains($ln, 'created') ||
      str_contains($ln, 'tanggal') ||
      str_contains($ln, 'tgl') ||
      str_ends_with($ln, '_at')
    ) {
      return $n;
    }
  }
  return null;
}

function fetch_rows(mysqli $koneksi, string $table, ?string $pk, string $q, ?array $cols = null): mysqli_result {
  if ($cols === null) {
    [$cols] = describe_table($koneksi, $table);
  }

  $searchable = [];
  foreach ($cols as $c) {
    $type = strtolower($c['Type'] ?? '');
    if (str_contains($type, 'char') || str_contains($type, 'text')) {
      $searchable[] = $c['Field'];
    }
  }
  $searchable = array_slice($searchable, 0, 5); // batasi supaya query ringan

  $sql = "SELECT * FROM `$table`";
  $types = "";
  $params = [];
  if ($q !== "" && count($searchable) > 0) {
    $like = "%" . $q . "%";
    $whereParts = [];
    foreach ($searchable as $f) {
      $whereParts[] = "`$f` LIKE ?";
      $types .= "s";
      $params[] = $like;
    }
    $sql .= " WHERE " . implode(" OR ", $whereParts);
  }
  if ($pk) {
    $sql .= " ORDER BY `$pk` DESC";
  }

  $stmt = $koneksi->prepare($sql);
  if (!$stmt) {
    throw new Exception("Gagal menyiapkan query: " . $koneksi->error);
  }
  if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  return $stmt->get_result();
}

function fetch_options_by_table(mysqli $koneksi, array $tableCandidates, array $labelCandidates): array {
  foreach ($tableCandidates as $table) {
    try {
      $check = $koneksi->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1");
      if (!$check) continue;
      $check->bind_param("s", $table);
      $check->execute();
      $exists = $check->get_result();
      if (!$exists || $exists->num_rows === 0) continue;

      [$cols, $pk] = describe_table($koneksi, $table);
      if (!$pk) continue;

      $colNames = array_map(fn($c) => $c['Field'], $cols);
      $labelCol = pick_col($colNames, $labelCandidates) ?? $pk;

      $sql = "SELECT `$pk`, `$labelCol` FROM `$table` ORDER BY `$labelCol`";
      $stmt = $koneksi->prepare($sql);
      if (!$stmt) continue;
      $stmt->execute();

      $res = $stmt->get_result();
      if (!$res) continue;

      $rows = [];
      while ($row = $res->fetch_assoc()) {
        $rows[] = [
          'id' => $row[$pk],
          'label' => $row[$labelCol] ?? $row[$pk],
        ];
      }
      if (count($rows) > 0) {
        return $rows;
      }
    } catch (Throwable $e) {
      continue;
    }
  }

  return [];
}

function fetch_trend(mysqli $koneksi, string $table): array {
  [$cols] = describe_table($koneksi, $table);
  $colNames = array_map(fn($c) => $c['Field'], $cols);
  $timeCol = guess_time_col($colNames);
  $pk = null;
  foreach ($cols as $c) {
    if (($c['Key'] ?? '') === 'PRI') $pk = $c['Field'];
  }

  $result = [
    'total' => 0,
    'today' => 0,
    'per_hari' => [],
    'recent' => [],
  ];

  $stmt = $koneksi->prepare("SELECT COUNT(*) as total FROM `$table`");
  if ($stmt) {
    $stmt->execute();
    $result['total'] = (int)$stmt->get_result()->fetch_assoc()['total'];
  }

  if ($timeCol) {
    $stmt = $koneksi->prepare("SELECT COUNT(*) as total FROM `$table` WHERE DATE(`$timeCol`) = CURDATE()");
    if ($stmt) {
      $stmt->execute();
      $result['today'] = (int)$stmt->get_result()->fetch_assoc()['total'];
    }

    $stmt = $koneksi->prepare("
      SELECT DATE(`$timeCol`) as tgl, COUNT(*) as total
      FROM `$table`
      WHERE `$timeCol` >= DATE_SUB(NOW(), INTERVAL 7 DAY)
      GROUP BY DATE(`$timeCol`)
      ORDER BY tgl
    ");
    if ($stmt) {
      $res = $stmt->execute();
      if ($res) {
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $result['per_hari'] = $rows;
      }
    }

    if ($pk) {
      $stmt = $koneksi->prepare("
        SELECT * FROM `$table`
        ORDER BY `$timeCol` DESC
        LIMIT 10
      ");
      if ($stmt) {
        $stmt->execute();
        $result['recent'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
      }
    }
  }

  return $result;
}
