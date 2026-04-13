<?php
// components/header.php
// File ini khusus untuk kerangka atas HTML (Head)
require_once '../cek_login.php';
if (!isset($judul_halaman)) $judul_halaman = 'Panel Admin';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $judul_halaman; ?> - Simabeni Pangkah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

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

<body class="bg-gray-50 text-gray-800 antialiased overflow-hidden flex h-screen"></body>