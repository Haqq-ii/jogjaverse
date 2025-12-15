<?php
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/auth.php";
require_once __DIR__ . "/../../config/koneksi.php";
wajib_admin();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - JogjaVerse</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
<div class="layout">
  <?php include __DIR__ . "/sidebar.php"; ?>
  <main class="main">
    <?php include __DIR__ . "/topbar.php"; ?>
