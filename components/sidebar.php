<?php
// components/sidebar.php
// Pastikan variabel $halaman sudah didefinisikan di file yang memanggil komponen ini
if (!isset($halaman)) $halaman = '';
?>

<!-- Overlay Mobile -->
<div id="sidebar-overlay" onclick="toggleSidebar()"
    class="fixed inset-0 bg-gray-900/50 z-40 hidden lg:hidden backdrop-blur-sm transition-opacity"></div>

<!-- Sidebar Utama -->
<aside id="sidebar"
    class="sidebar-closed fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white flex flex-col shadow-2xl lg:relative lg:translate-x-0">

    <!-- Logo Header -->
    <div class="h-20 flex items-center justify-between px-6 border-b border-slate-800">
        <span class="text-2xl font-extrabold tracking-tight">Segar<span class="text-blue-400">Laut</span>.</span>
        <!-- Tombol Tutup (Hanya Mobile) -->
        <button onclick="toggleSidebar()" class="lg:hidden text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Profil Admin -->
    <div class="px-6 py-5 border-b border-slate-800 bg-slate-800/30">
        <div class="flex items-center gap-3">
            <img src="https://ui-avatars.com/api/?name=Admin+Budi&background=3b82f6&color=fff"
                class="w-10 h-10 rounded-full border-2 border-slate-700">
            <div>
                <h4 class="text-sm font-bold text-gray-100">Admin Budi</h4>
                <p class="text-xs text-green-400 font-medium flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-green-400"></span> Online
                </p>
            </div>
        </div>
    </div>

    <!-- Menu Navigasi -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <p class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Menu Utama</p>

        <a href="index.php"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors <?php echo $halaman == 'dashboard' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
            <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            Dashboard
        </a>

        <a href="kelola_ikan.php"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors <?php echo $halaman == 'ikan' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
            <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            Kelola Data Ikan
        </a>

        <a href="transaksi.php"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition-colors <?php echo $halaman == 'transaksi' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white'; ?>">
            <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            Riwayat Transaksi
        </a>
    </nav>

    <!-- Tombol Logout -->
    <div class="p-4 border-t border-slate-800">
        <a href="logout.php"
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