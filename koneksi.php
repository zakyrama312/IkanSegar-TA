<?php
// koneksi.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "ikansegar_db";

// Melakukan koneksi ke MySQL
$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Fungsi global yang sering dipakai
function formatRupiah($angka)
{
    return "Rp " . number_format($angka, 0, ',', '.');
}
