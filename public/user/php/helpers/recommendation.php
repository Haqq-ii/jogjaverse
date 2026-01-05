<?php
require_once __DIR__ . "/../../../../config/env.php";

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!function_exists('jv_openai_enabled')) {
  function jv_openai_enabled(): bool {
    $apiKey = getenv('OPENAI_API_KEY') ?: ($_ENV['OPENAI_API_KEY'] ?? '');
    return $apiKey !== '' && function_exists('curl_init');
  }
}

if (!function_exists('jv_bind_params')) {
  function jv_bind_params(mysqli_stmt $stmt, string $types, array $params): bool {
    $refs = [];
    foreach ($params as $i => $val) {
      $refs[$i] = &$params[$i];
    }
    array_unshift($refs, $types);
    return call_user_func_array([$stmt, 'bind_param'], $refs);
  }
}

if (!function_exists('jv_openai_json_array')) {
  function jv_openai_json_array(string $prompt, string $cacheKey, int $limit = 3): array {
    if ($prompt === '') {
      return [];
    }
    if (!isset($_SESSION['jv_reco_cache']) || !is_array($_SESSION['jv_reco_cache'])) {
      $_SESSION['jv_reco_cache'] = [];
    }
    if (isset($_SESSION['jv_reco_cache'][$cacheKey]) && is_array($_SESSION['jv_reco_cache'][$cacheKey])) {
      $cached = $_SESSION['jv_reco_cache'][$cacheKey];
      if (isset($cached['items']) && is_array($cached['items'])) {
        return $cached['items'];
      }
      return $cached;
    }

    if (!jv_openai_enabled()) {
      return [];
    }

    $apiKey = getenv('OPENAI_API_KEY') ?: ($_ENV['OPENAI_API_KEY'] ?? '');
    $model = getenv('OPENAI_MODEL') ?: ($_ENV['OPENAI_MODEL'] ?? 'gpt-4o-mini');
    if ($model === '') {
      $model = 'gpt-4o-mini';
    }

    $system = "Kembalikan JSON array string saja tanpa teks tambahan.";
    $body = [
      "model" => $model,
      "messages" => [
        ["role" => "system", "content" => $system],
        ["role" => "user", "content" => $prompt],
      ],
      "temperature" => 0.4,
      "max_tokens" => 160,
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json",
      "Authorization: Bearer " . $apiKey,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 12);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300 || $curlErr) {
      return [];
    }

    $data = json_decode($response, true);
    $content = trim((string)($data['choices'][0]['message']['content'] ?? ''));
    if ($content === '') {
      return [];
    }

    if (str_contains($content, '```')) {
      $content = preg_replace('/^```[a-z]*\\s*|```$/m', '', $content);
      $content = trim($content);
    }
    if ($content !== '' && $content[0] !== '[') {
      if (preg_match('/\\[[\\s\\S]*\\]/', $content, $match)) {
        $content = $match[0];
      }
    }

    $decoded = json_decode($content, true);
    if (!is_array($decoded)) {
      return [];
    }

    $items = [];
    foreach ($decoded as $item) {
      if (!is_string($item)) {
        continue;
      }
      $val = trim($item);
      if ($val === '') {
        continue;
      }
      $items[] = $val;
    }
    $items = array_values(array_unique($items));
    if ($limit > 0) {
      $items = array_slice($items, 0, $limit);
    }
    $_SESSION['jv_reco_cache'][$cacheKey] = [
      'items' => $items,
      'ts' => time(),
    ];
    return $items;
  }
}
