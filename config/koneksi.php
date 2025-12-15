<?php
$host = getenv("DB_HOST") ?: getenv("MYSQL_HOST") ?: "db";            // nama service database di compose
$user = getenv("DB_USER") ?: getenv("MYSQL_USER") ?: "root";          // sesuai MYSQL_USER
$pass = getenv("DB_PASS") ?: getenv("MYSQL_PASSWORD") ?: "rootpassword";  // sesuai MYSQL_PASSWORD
$db   = getenv("DB_NAME") ?: getenv("MYSQL_DATABASE") ?: "jogjaverse";    // sesuai MYSQL_DATABASE

$koneksi = new mysqli($host, $user, $pass, $db);
if ($koneksi->connect_errno) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}
$koneksi->set_charset("utf8mb4");
