<?php
// components/navbar.php
if (!isset($judul_halaman)) $judul_halaman = 'Panel Admin';
?>
<header
    class="h-20 bg-white border-b border-gray-200 shadow-sm flex items-center justify-between px-4 sm:px-6 z-10 shrink-0">
    <div class="flex items-center gap-2 sm:gap-4 overflow-hidden">
        <!-- Hamburger Menu Button -->
        <button onclick="toggleSidebar()"
            class="lg:hidden p-2 -ml-2 rounded-md text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                </path>
            </svg>
        </button>

        <!-- NAMA HALAMAN DINAMIS DARI PHP -->
        <h1 class="text-base sm:text-xl font-bold text-gray-800 truncate">
            <?php echo $judul_halaman; ?>
        </h1>
    </div>

    <!-- Navbar Kanan -->
    <div class="flex items-center gap-3 sm:gap-5 shrink-0">
        <span
            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Online
        </span>

        <!-- Tombol Buka POS -->
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'kasir')): ?>
            <a href="../pos.php" target="_blank"
                class="px-3 sm:px-4 py-2 bg-blue-50 text-blue-600 rounded-lg font-bold text-xs sm:text-sm hover:bg-blue-100 transition-colors border border-blue-200 whitespace-nowrap">
                Buka Kasir
            </a>
        <?php endif; ?>
    </div>
</header>