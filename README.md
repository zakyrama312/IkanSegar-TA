🐟 IkanSegar (SegarLaut) - Sistem Kasir & Manajemen Stok Ikan

Aplikasi Point of Sale (POS) dan Semi E-Commerce berbasis PHP Native yang dirancang khusus untuk manajemen penjualan ikan segar. Aplikasi ini dilengkapi dengan fitur kasir offline, sinkronisasi stok otomatis, manajemen karyawan, dan pelaporan yang mendukung ekspor ke PDF & Excel.

🚀 Fitur Utama

Landing Page Dinamis: Katalog ikan yang terhubung langsung dengan database.

Sistem Kasir (POS): Kalkulasi belanja, perhitungan kembalian, dan cetak struk otomatis.

Manajemen Stok & Riwayat: Pencatatan arus barang masuk (restock) secara mendetail.

Hak Akses Multi-Level: Administrator, Owner (Pimpinan), dan Kasir POS.

Laporan Ekspor: Ekspor laporan transaksi, riwayat stok, dan data karyawan ke Excel/PDF.

🛠️ Persyaratan Sistem (Prerequisites)

Sebelum menjalankan aplikasi ini, pastikan sistem Anda telah terinstal perangkat lunak berikut:

Web Server & Database: XAMPP, LAMPP (untuk Linux), atau Laragon.

PHP Version: Minimal PHP 7.4 atau PHP 8.x.

Web Browser: Google Chrome, Brave, Mozilla Firefox, atau Safari.

📖 Panduan Instalasi (Untuk Linux LAMPP / Windows XAMPP)

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek secara lokal di komputer Anda:

Langkah 1: Clone atau Download Proyek

Download file ZIP proyek ini atau lakukan clone via terminal:

git clone [https://github.com/zakyrama312/IkanSegar-TA.git](https://github.com/zakyrama312/IkanSegar-TA.git)


Pindahkan folder proyek ke dalam direktori web server Anda:

Windows (XAMPP): Pindahkan ke C:\xampp\htdocs\perikanan-ta

Linux (LAMPP): Pindahkan ke /opt/lampp/htdocs/perikanan-ta

Langkah 2: Konfigurasi Database (MySQL)

Buka aplikasi XAMPP/LAMPP Control Panel dan jalankan layanan Apache dan MySQL.

Buka browser dan akses http://localhost/phpmyadmin.

Buat database baru dengan nama ikansegar_db.

Klik database segar_laut_db, lalu pilih tab Import.

Pilih file segar_laut.sql (atau db_ikan.sql jika itu nama file ekspor Anda) yang ada di dalam folder proyek, lalu klik Go / Kirim.

Langkah 3: Pengaturan Hak Akses Folder (Khusus Pengguna Linux)

Jika Anda menggunakan Linux (seperti Zorin OS / Ubuntu), server Apache seringkali tidak memiliki izin untuk mengunggah foto ke folder uploads. Anda wajib menjalankan perintah berikut di terminal:

# Masuk ke direktori htdocs Anda
cd /opt/lampp/htdocs/perikanan-ta

# Buat folder uploads jika belum ada
sudo mkdir uploads

# Berikan hak akses penuh agar PHP bisa menyimpan gambar ikan
sudo chmod -R 777 uploads


Langkah 4: Cek File Koneksi

Buka file koneksi.php menggunakan Code Editor (seperti VS Code). Pastikan konfigurasi database sesuai dengan pengaturan lokal Anda:

$host = "localhost";
$user = "root";
$pass = ""; // Kosongkan jika password MySQL default Anda kosong
$db   = "ikansegar_db";


💻 Cara Menjalankan Aplikasi

Setelah semua konfigurasi selesai, buka browser Anda dan akses tautan berikut:

Halaman Utama & Katalog (Pelanggan):
👉 http://localhost/perikanan-ta/

Sistem Kasir POS (Kasir Offline):
👉 http://localhost/perikanan-ta/pos.php

Panel Admin (Manajemen Data):
👉 http://localhost/perikanan-ta/admin/ (atau akses file login.php)

🔑 Akun Default untuk Login

Gunakan kredensial berikut untuk masuk pertama kali ke dalam sistem Admin/Kasir:

Role

Username

Password

Keterangan

Administrator

admin

123456

Akses penuh ke seluruh sistem dan laporan

Kasir

kasir1

123456

Hanya bisa mengakses menu POS untuk transaksi

(Sangat disarankan untuk mengubah password admin dan membuat akun baru setelah Anda berhasil login pertama kali).

🐛 Troubleshooting (Solusi Masalah Umum)

HTTP ERROR 500 saat buka Admin: Pastikan struktur folder Anda benar. File komponen (navbar.php, sidebar.php) harus berada di tempat yang tepat. Cek pemanggilan require_once '../koneksi.php'; apakah jalurnya sudah sesuai dengan posisi file.

Foto ikan gagal di-upload: Cek kembali Langkah 3 (Hak Akses Folder). Pastikan folder uploads memiliki izin 777.

Username / Password Salah saat Login: Jika Anda gagal login dengan akun default, login menggunakan username admin dan password 123456. Sistem telah dilengkapi skrip Auto-Fix Hash yang akan otomatis meregenerasi enkripsi password agar sesuai dengan versi PHP lokal Anda.

