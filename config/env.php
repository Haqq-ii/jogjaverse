<?php
if (!function_exists('load_env')) {
  function load_env(?string $path = null): array {
    $path = $path ?: (dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');
    if (!is_readable($path)) {
      return [];
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) {
      return [];
    }

    $data = [];
    foreach ($lines as $line) {
      $line = trim($line);
      if ($line === '' || str_starts_with($line, '#')) {
        continue;
      }
      $pos = strpos($line, '=');
      if ($pos === false) {
        continue;
      }
      $key = trim(substr($line, 0, $pos));
      $val = trim(substr($line, $pos + 1));
      if ($val !== '' && $val[0] === '"' && str_ends_with($val, '"')) {
        $val = substr($val, 1, -1);
      } elseif ($val !== '' && $val[0] === "'" && str_ends_with($val, "'")) {
        $val = substr($val, 1, -1);
      }
      $data[$key] = $val;
      $_ENV[$key] = $val;
      if (getenv($key) === false) {
        putenv($key . '=' . $val);
      }
    }
    return $data;
  }
}

load_env();
