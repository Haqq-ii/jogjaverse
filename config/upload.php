<?php
if (!function_exists('upload_image')) {
  function upload_image(array $file, string $prefix = 'img_', string $folder = 'assets/uploads/detail'): ?string {
    if (!isset($file['tmp_name']) || $file['tmp_name'] === '' || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      return null;
    }

    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    if ($ext === '' || !in_array($ext, $allowed, true)) {
      return null;
    }

    $root = realpath(__DIR__ . '/..');
    if ($root === false) {
      return null;
    }

    $folder = trim(str_replace(['\\', '/'], '/', $folder), '/');
    $targetDir = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $folder);
    if (!is_dir($targetDir)) {
      mkdir($targetDir, 0777, true);
    }

    $safeName = uniqid($prefix, true) . '.' . $ext;
    $targetPath = $targetDir . DIRECTORY_SEPARATOR . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
      return null;
    }

    $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
    return $base . '/' . $folder . '/' . $safeName;
  }
}

if (!function_exists('delete_uploaded_image')) {
  function delete_uploaded_image(string $url, string $baseFolder = 'assets/uploads/detail'): bool {
    if ($url === '') {
      return false;
    }

    $path = parse_url($url, PHP_URL_PATH);
    if (!$path) {
      return false;
    }
    $path = ltrim($path, '/');

    $basePath = '';
    if (defined('BASE_URL') && BASE_URL !== '') {
      $basePath = trim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
    }
    if ($basePath !== '' && strpos($path, $basePath . '/') === 0) {
      $path = substr($path, strlen($basePath) + 1);
    }

    $baseFolder = trim(str_replace(['\\', '/'], '/', $baseFolder), '/');
    if ($baseFolder !== '' && strpos($path, $baseFolder . '/') !== 0 && $path !== $baseFolder) {
      return false;
    }

    $root = realpath(__DIR__ . '/..');
    if ($root === false) {
      return false;
    }
    $fullPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    if (!is_file($fullPath)) {
      return false;
    }
    return unlink($fullPath);
  }
}
?>
