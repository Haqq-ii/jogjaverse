<?php
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/auth.php";
require_once __DIR__ . "/../../config/koneksi.php";
require_once __DIR__ . "/../../public/user/php/helpers/trend.php";
wajib_admin();

header("Content-Type: application/json; charset=utf-8");

$trend = jv_get_trend_7hari($koneksi);
$top = [];
if (!empty($trend['top'])) {
  foreach ($trend['top'] as $row) {
    $top[] = [
      "id" => (int)($row['id'] ?? 0),
      "nama" => $row['nama'] ?? '-',
      "count" => (int)($row['count'] ?? 0),
    ];
  }
}

echo json_encode([
  "daily" => $trend['daily'] ?? [],
  "top" => $top,
]);
