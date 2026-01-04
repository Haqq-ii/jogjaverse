<?php
require_once __DIR__ . '/tracker.php';

if (!function_exists('jv_get_trend_7hari')) {
  function jv_get_trend_7hari(mysqli $koneksi): array {
    $cols = jv_get_kunjungan_columns($koneksi);
    if (empty($cols)) {
      return ['daily' => [], 'top' => []];
    }

    $colDate = jv_pick_kunjungan_col($cols, ['created_at', 'dibuat_pada', 'createdAt', 'waktu']);
    $colHalaman = jv_pick_kunjungan_col($cols, ['halaman', 'jenis_halaman', 'jenis', 'tipe']);
    $colTargetType = jv_pick_kunjungan_col($cols, ['target_type', 'jenis_target']);
    $colTargetId = jv_pick_kunjungan_col($cols, ['target_id', 'id_target']);

    $halamanValue = 'destinasi_detail';
    if ($colHalaman) {
      $halamanValue = jv_resolve_enum_value($cols[$colHalaman] ?? null, $halamanValue, 'destinasi');
    }

    $dates = [];
    $today = new DateTimeImmutable('today');
    for ($i = 6; $i >= 0; $i--) {
      $dates[$today->modify("-{$i} day")->format('Y-m-d')] = 0;
    }

    $daily = [];
    if ($colDate) {
      $where = ["`$colDate` >= (NOW() - INTERVAL 6 DAY)"];
      $params = [];
      $types = '';

      if ($colHalaman) {
        $where[] = "`$colHalaman` = ?";
        $params[] = $halamanValue;
        $types .= 's';
      }
      if ($colTargetType) {
        $where[] = "`$colTargetType` = ?";
        $params[] = 'destinasi';
        $types .= 's';
      }

      $sql = "SELECT DATE(`$colDate`) as tgl, COUNT(*) as total FROM kunjungan";
      if ($where) {
        $sql .= " WHERE " . implode(' AND ', $where);
      }
      $sql .= " GROUP BY DATE(`$colDate`) ORDER BY DATE(`$colDate`) ASC";

      $stmt = $koneksi->prepare($sql);
      if ($stmt) {
        if ($types !== '') {
          $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) {
          while ($row = $res->fetch_assoc()) {
            $key = $row['tgl'] ?? null;
            if ($key && array_key_exists($key, $dates)) {
              $dates[$key] = (int)($row['total'] ?? 0);
            }
          }
        }
        $stmt->close();
      }
    }

    foreach ($dates as $date => $count) {
      $daily[] = ['date' => $date, 'count' => $count];
    }

    $top = [];
    if ($colDate && $colTargetId) {
      $where = ["k.`$colDate` >= (NOW() - INTERVAL 6 DAY)", "k.`$colTargetId` IS NOT NULL"];
      $params = [];
      $types = '';

      if ($colHalaman) {
        $where[] = "k.`$colHalaman` = ?";
        $params[] = $halamanValue;
        $types .= 's';
      }
      if ($colTargetType) {
        $where[] = "k.`$colTargetType` = ?";
        $params[] = 'destinasi';
        $types .= 's';
      }

      $sql = "
        SELECT
          k.`$colTargetId` as id,
          d.nama as nama,
          d.gambar_sampul_url as gambar,
          d.jam_operasional as jam_operasional,
          COUNT(*) as total
        FROM kunjungan k
        JOIN destinasi d ON d.id_destinasi = k.`$colTargetId`
      ";
      if ($where) {
        $sql .= " WHERE " . implode(' AND ', $where);
      }
      $sql .= "
        GROUP BY k.`$colTargetId`, d.nama, d.gambar_sampul_url, d.jam_operasional
        ORDER BY total DESC
        LIMIT 5
      ";

      $stmt = $koneksi->prepare($sql);
      if ($stmt) {
        if ($types !== '') {
          $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res) {
          while ($row = $res->fetch_assoc()) {
            $top[] = [
              'id' => (int)($row['id'] ?? 0),
              'nama' => $row['nama'] ?? '-',
              'count' => (int)($row['total'] ?? 0),
              'gambar' => $row['gambar'] ?? null,
              'jam_operasional' => $row['jam_operasional'] ?? null,
            ];
          }
        }
        $stmt->close();
      }
    }

    return ['daily' => $daily, 'top' => $top];
  }
}
