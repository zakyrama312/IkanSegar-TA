<?php
// koneksi.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_ikan";

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

function formatTanggalIndonesia($tanggal, $tampil_waktu = true)
{
    if (empty($tanggal)) return '-';
    
    $bulan = array(
        1 =>   'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $split = explode('-', date('Y-m-d', strtotime($tanggal)));
    $waktu = date('H:i', strtotime($tanggal));
    
    $tanggal_indo = $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
    
    if ($tampil_waktu) {
        return $tanggal_indo . ', ' . $waktu;
    }
    return $tanggal_indo;
}