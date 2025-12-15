<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function wajib_admin() {
  if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: " . (defined('BASE_URL') ? BASE_URL : '') . "/public/login.php");
    exit();
  }
}
