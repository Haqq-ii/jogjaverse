<?php
require_once __DIR__ . "/../config/koneksi.php";
session_start();

$jenis = $_GET["jenis"] ?? "beranda";     // destinasi/event/atraksi/kuliner/beranda
$id    = $_GET["id"] ?? null;             // id_target (boleh null)
$id_pengguna = $_SESSION["id_pengguna"] ?? null;

$ua = substr($_SERVER["HTTP_USER_AGENT"] ?? "", 0, 255);
$ip = $_SERVER["REMOTE_ADDR"] ?? "";
$ip_hash = hash("sha256", $ip);

$stmt = $koneksi->prepare("INSERT INTO kunjungan (id_pengguna, jenis_halaman, id_target, user_agent, ip_hash)
                           VALUES (?, ?, ?, ?, ?)");
$target = $id !== null ? (int)$id : null;
$stmt->bind_param("isiss", $id_pengguna, $jenis, $target, $ua, $ip_hash);
$stmt->execute();

header("Content-Type: application/json");
echo json_encode(["ok" => true]);
?>