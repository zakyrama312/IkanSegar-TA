<?php
// components/sidebar.php
if (!isset($halaman)) $halaman = '';

// PENTING: Baris ini wajib ada agar sistem tahu siapa yang sedang login!
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>
<!-- Overlay Mobile -->
<div id="sidebar-overlay" onclick="toggleSidebar()"
    class="fixed inset-0 bg-gray-900/50 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity opacity-0"></div>

<!-- Sidebar Utama -->
<aside id="sidebar"
    class="sidebar-closed fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white flex flex-col shadow-2xl lg:relative lg:translate-x-0">

    <!-- Logo Header -->
    <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800 shrink-0">
        <span class="text-2xl font-extrabold tracking-tight">Simabeni<span class="text-blue-400">Pangkah</span>.</span>
        <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Profil Admin -->
    <div class="px-6 py-5 border-b border-slate-800 bg-slate-800/30 shrink-0">
        <div class="flex items-center gap-3">
            <img src="https://ui-avatars.com/api/?name=<?= isset($_SESSION['nama_lengkap']) ? urlencode($_SESSION['nama_lengkap']) : 'Kasir+Utama'; ?>&background=3b82f6&color=fff"
                class="w-10 h-10 rounded-full border-2 border-slate-700">
            <div>
                <h4 class="text-sm font-bold text-gray-100 line-clamp-1">
                    <?= isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Admin Utama'; ?>
                </h4>
                <p class="text-xs text-green-400 font-medium flex items-center gap-1 uppercase">
                    <span class="w-2 h-2 rounded-full bg-green-400"></span> <?= $user_role ? $user_role : 'Online'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Menu Navigasi (Mulai Grouping) -->
    <nav class="flex-1 px-4 py-6 overflow-y-auto">

        <!-- GRUP 1: MENU UTAMA -->
        <div class="space-y-1">
            <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Menu Utama</p>

            <a href="index.php"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors <?php echo $halaman == 'dashboard' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                    </path>
                </svg>
                Dashboard
            </a>
        </div>

        <!-- GRUP 2: MASTER DATA -->
        <?php if ($user_role === 'admin'): ?>
            <div class="mt-8 space-y-1">
                <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Master Data</p>

                <a href="kelola_karyawan.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors <?php echo $halaman == 'karyawan' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    Kelola Karyawan
                </a>
                <a href="pengeluaran.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors <?php echo $halaman == 'pengeluaran' ? 'bg-red-500 text-white shadow-md shadow-red-500/30' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                    Pengeluaran Toko
                </a>
                <a href="kelola_ikan.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors <?php echo $halaman == 'ikan' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Kelola Data Ikan
                </a>
            </div>
        <?php endif; ?>

        <!-- GRUP 3: LAPORAN & HISTORI -->
        <?php if ($user_role === 'admin' || $user_role === 'owner'): ?>
            <div class="mt-8 space-y-1">
                <p class="px-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Laporan Data</p>

                <a href="laporan_karyawan.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors <?php echo $halaman == 'laporan_karyawan' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Laporan Karyawan
                </a>

                <a href="transaksi.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors <?php echo $halaman == 'transaksi' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Laporan Transaksi
                </a>

                <a href="laporan_pembeli.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors <?php echo $halaman == 'laporan_pembeli' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    Laporan Pembeli
                </a>

                <a href="riwayat_stok.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors <?php echo $halaman == 'riwayat_stok' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2">
                        </path>
                    </svg>
                    Riwayat Stok Masuk
                </a>

                <a href="laporan_keuangan.php"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors <?php echo $halaman == 'laporan_keuangan' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                    Laporan Keuangan
                </a>
            </div>
        <?php endif; ?>
    </nav>

    <!-- Tombol Logout -->
    <div class="p-4 border-t border-slate-800 shrink-0">
        <a href="../logout.php"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white rounded-lg font-semibold transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                </path>
            </svg>
            Keluar
        </a>
    </div>
</aside>