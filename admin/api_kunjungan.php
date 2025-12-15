<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/koneksi.php";
require_once __DIR__ . "/_crud_helper.php";
wajib_admin();

header("Content-Type: application/json");

try {
  $table = "kunjungan";
  [$cols] = describe_table($koneksi, $table);
  $colNames = array_map(fn($c) => $c['Field'], $cols);
  $colJenis = pick_col($colNames, ["jenis_halaman", "jenis", "tipe"]);
  $timeCol = guess_time_col($colNames);
  $pk = null;
  foreach ($cols as $c) {
    if (($c['Key'] ?? '') === 'PRI') {
      $pk = $c['Field'];
      break;
    }
  }

  $kunjungan = fetch_trend($koneksi, $table);

  $jenisSummary = [];
  if ($colJenis) {
    $sql = "SELECT `$colJenis` as jenis, COUNT(*) as total FROM `$table` GROUP BY `$colJenis` ORDER BY total DESC LIMIT 10";
    $stmt = $koneksi->prepare($sql);
    if ($stmt) {
      $stmt->execute();
      $jenisSummary = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
  }

  echo json_encode([
    "ok" => true,
    "data" => [
      "total" => $kunjungan["total"] ?? 0,
      "today" => $kunjungan["today"] ?? 0,
      "per_hari" => $kunjungan["per_hari"] ?? [],
      "recent" => $kunjungan["recent"] ?? [],
      "jenis" => $jenisSummary,
      "meta" => [
        "time_col" => $timeCol,
        "jenis_col" => $colJenis,
        "pk" => $pk
      ]
    ]
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    "ok" => false,
    "error" => $e->getMessage()
  ]);
}
?>
