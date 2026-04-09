<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah session is_logged_in ada dan bernilai true
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    // Jika tidak ada atau bukan true, paksa kembali ke halaman login

    // Opsional: Buat pesan error untuk ditampilkan di halaman login
    $_SESSION['pesan_error'] = "Anda harus login terlebih dahulu untuk mengakses halaman ini.";

    // Redirect ke login.php
    header("Location: ../login.php");
    exit; // Wajib memanggil exit setelah header agar eksekusi script selanjutnya dihentikan
}
