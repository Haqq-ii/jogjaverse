<?php
require_once __DIR__ . "/../../../../config/koneksi.php";
require_once __DIR__ . "/../../../../config/env.php";
require_once __DIR__ . "/../helpers/trend.php";

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

header("Content-Type: application/json; charset=utf-8");

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
  $payload = [];
}
$message = trim((string)($payload['message'] ?? ''));
$len = function_exists('mb_strlen') ? mb_strlen($message) : strlen($message);

if ($message === '' || $len > 500) {
  echo json_encode([
    "reply" => "Pesan tidak valid. Maksimal 500 karakter ya."
  ]);
  exit();
}

$limit = 20;
$window = 600;
$now = time();
if (!isset($_SESSION['jove_rate']) || !is_array($_SESSION['jove_rate'])) {
  $_SESSION['jove_rate'] = [];
}
$_SESSION['jove_rate'] = array_values(array_filter($_SESSION['jove_rate'], function ($ts) use ($now, $window) {
  return ($now - (int)$ts) < $window;
}));
if (count($_SESSION['jove_rate']) >= $limit) {
  echo json_encode([
    "reply" => "Terlalu banyak permintaan. Coba lagi beberapa menit ya."
  ]);
  exit();
}
$_SESSION['jove_rate'][] = $now;

$apiKey = getenv('OPENAI_API_KEY') ?: ($_ENV['OPENAI_API_KEY'] ?? '');
$model = getenv('OPENAI_MODEL') ?: ($_ENV['OPENAI_MODEL'] ?? 'gpt-4o-mini');
if ($model === '') {
  $model = 'gpt-4o-mini';
}

$trend = jv_get_trend_7hari($koneksi);
$topSummary = 'Top Destinasi 7 hari: ';
if (!empty($trend['top'])) {
  $parts = [];
  foreach ($trend['top'] as $row) {
    $parts[] = ($row['nama'] ?? '-') . '(' . (int)($row['count'] ?? 0) . ')';
  }
  $topSummary .= implode(', ', $parts);
} else {
  $topSummary .= 'belum ada data.';
}

$dailySummary = 'Daily clicks: ';
if (!empty($trend['daily'])) {
  $parts = [];
  foreach ($trend['daily'] as $row) {
    $parts[] = ($row['date'] ?? '-') . '=' . (int)($row['count'] ?? 0);
  }
  $dailySummary .= implode(', ', $parts);
} else {
  $dailySummary .= 'belum ada data.';
}

$systemMessage = implode("\n", [
  "Kamu adalah JOVE, asisten wisata JogjaVerse.",
  "Fokus rekomendasi destinasi, event, dan kuliner di Yogyakarta.",
  "Jika pertanyaan di luar konteks wisata Jogja, arahkan kembali dengan sopan.",
  "Jika user meminta rekomendasi, prioritaskan destinasi trending namun tetap sesuaikan preferensi (pantai, sejarah, pegunungan).",
  $topSummary,
  $dailySummary,
]);

if ($apiKey === '') {
  echo json_encode([
    "reply" => "Maaf, JOVE sedang sibuk. Coba lagi ya."
  ]);
  exit();
}

if (!function_exists('curl_init')) {
  echo json_encode([
    "reply" => "Maaf, JOVE sedang sibuk. Coba lagi ya."
  ]);
  exit();
}

$requestBody = [
  "model" => $model,
  "messages" => [
    ["role" => "system", "content" => $systemMessage],
    ["role" => "user", "content" => $message],
  ],
  "temperature" => 0.7,
  "max_tokens" => 300,
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Authorization: Bearer " . $apiKey,
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);

$response = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode < 200 || $httpCode >= 300 || $curlErr) {
  echo json_encode([
    "reply" => "Maaf, JOVE sedang sibuk. Coba lagi ya."
  ]);
  exit();
}

$data = json_decode($response, true);
$reply = trim((string)($data['choices'][0]['message']['content'] ?? ''));
if ($reply === '') {
  $reply = "Maaf, JOVE sedang sibuk. Coba lagi ya.";
}

echo json_encode([
  "reply" => $reply
]);
