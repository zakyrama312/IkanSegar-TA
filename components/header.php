<?php
// components/header.php
// File ini khusus untuk kerangka atas HTML (Head)
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

        /* ========================================================
           KUSTOMISASI SCROLLBAR GLOBAL AGAR TIPIS & ELEGAN
           ======================================================== */
        ::-webkit-scrollbar {
            width: 5px;
            /* Lebar scrollbar vertikal */
            height: 5px;
            /* Tinggi scrollbar horizontal */
        }

        ::-webkit-scrollbar-track {
            background: transparent;
            /* Background track transparan */
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.4);
            /* Warna thumb abu-abu semi-transparan */
            border-radius: 10px;
            /* Ujung scrollbar membulat */
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.8);
            /* Warna thumb saat disorot mouse */
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 antialiased overflow-hidden flex h-screen">