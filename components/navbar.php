<?php

// components/navbar.php
if (!isset($judul_halaman)) $judul_halaman = 'Panel Admin';
?>
<?php
// 1. Panggil koneksi dan atur variabel halaman
require_once '../koneksi.php';
$halaman = 'nama_halaman'; // ganti sesuai nama menu
$judul_halaman = 'Judul Halaman di Navbar';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $judul_halaman; ?> - SegarLaut</title>

    <!-- NAH! Tailwind dipanggil di bagian <head> halaman utama ini -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
    /* CSS untuk menyembunyikan/menampilkan sidebar di mobile */
    #sidebar {
        transition: transform 0.3s ease-in-out;
    }

    .sidebar-open {
        transform: translateX(0);
    }

    .sidebar-closed {
        transform: translateX(-100%);
    }

    @media (min-width: 1024px) {
        .sidebar-closed {
            transform: translateX(0);
        }
    }
    </style>
</head>

<!-- Body wajib menggunakan class flex dan h-screen agar sidebar dan konten rapi -->

<body class="bg-gray-50 text-gray-800 antialiased overflow-hidden flex h-screen"></body>
<header
    class="h-20 bg-white border-b border-gray-200 shadow-sm flex items-center justify-between px-4 sm:px-6 z-10 shrink-0">
    <div class="flex items-center gap-4">
        <!-- Hamburger Menu Button (Mobile Only) -->
        <button onclick="toggleSidebar()"
            class="lg:hidden p-2 rounded-md text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>

        <h1 class="text-xl font-bold text-gray-800">
            <?php echo $judul_halaman; ?>
        </h1>
    </div>

    <!-- Navbar Kanan -->
    <div class="flex items-center gap-3 sm:gap-5">
        <span
            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Online
        </span>

        <!-- Tombol Buka POS -->
        <a href="../pos.php" target="_blank"
            class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg font-semibold text-sm hover:bg-blue-100 transition-colors border border-blue-200 hidden sm:block">
            Buka Kasir (POS)
        </a>
    </div>
</header>