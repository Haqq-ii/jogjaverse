<?php
session_start();

if (!empty($_SESSION['user']) && $_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
} else {
    header("Location: public/user/php/langingPage.php");
}
exit;
