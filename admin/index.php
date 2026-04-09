<?php
// admin/index.php
session_start();
require_once '../koneksi.php';

// Konfigurasi Halaman
$halaman = 'dashboard';
$judul_halaman = 'Dashboard Utama';

// ========================================================
// 1. QUERY RINGKASAN DATA (HARI INI)
// ========================================================
$hari_ini = date('Y-m-d');

// Pendapatan Hari Ini
$q_pendapatan = mysqli_query($koneksi, "SELECT SUM(total_belanja) as total FROM transaksi WHERE DATE(tanggal_waktu) = '$hari_ini'");
$pendapatan_hari_ini = mysqli_fetch_assoc($q_pendapatan)['total'] ?? 0;

// Total Transaksi Hari Ini
$q_trx = mysqli_query($koneksi, "SELECT COUNT(id) as total FROM transaksi WHERE DATE(tanggal_waktu) = '$hari_ini'");
$trx_hari_ini = mysqli_fetch_assoc($q_trx)['total'] ?? 0;

// Ikan Stok Habis
$q_habis = mysqli_query($koneksi, "SELECT COUNT(id) as total FROM ikan WHERE stok <= 0");
$ikan_habis = mysqli_fetch_assoc($q_habis)['total'] ?? 0;

// Total Produk Aktif
$q_produk = mysqli_query($koneksi, "SELECT COUNT(id) as total FROM ikan WHERE status_aktif = 1");
$total_produk = mysqli_fetch_assoc($q_produk)['total'] ?? 0;


// ========================================================
// 2. PERSIAPKAN DATA UNTUK GRAFIK (7 HARI TERAKHIR)
// ========================================================
$label_grafik = [];
$data_grafik = [];

// Looping mundur dari 6 hari yang lalu sampai hari ini (total 7 hari)
for ($i = 6; $i >= 0; $i--) {
    $tanggal_loop = date('Y-m-d', strtotime("-$i days"));
    $label_grafik[] = date('d M', strtotime($tanggal_loop)); // Contoh: 15 Mei

    // Ambil total pendapatan di tanggal tersebut
    $q_sales = mysqli_query($koneksi, "SELECT SUM(total_belanja) as total FROM transaksi WHERE DATE(tanggal_waktu) = '$tanggal_loop'");
    $row_sales = mysqli_fetch_assoc($q_sales);
    $data_grafik[] = $row_sales['total'] ? (int)$row_sales['total'] : 0;
}

// Ubah array ke JSON agar bisa dibaca oleh Javascript (Chart.js)
$label_json = json_encode($label_grafik);
$data_json = json_encode($data_grafik);


// ========================================================
// 3. DATA TRANSAKSI TERBARU (LIMIT 5)
// ========================================================
$q_trx_terbaru = mysqli_query($koneksi, "
    SELECT t.kode_transaksi, t.total_belanja, t.tanggal_waktu, u.nama_lengkap 
    FROM transaksi t 
    LEFT JOIN users u ON t.user_id = u.id 
    ORDER BY t.tanggal_waktu DESC LIMIT 5
");

// PANGGIL HEADER HTML
include '../components/header.php';
?>

<!-- PANGGIL SIDEBAR -->
<?php include '../components/sidebar.php'; ?>

<!-- PEMBUNGKUS KONTEN -->
<main class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50">

    <!-- PANGGIL NAVBAR ATAS -->
    <?php include '../components/navbar.php'; ?>

    <!-- AREA KONTEN (SCROLLABLE) -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative">

        <!-- Ucapan Selamat Datang -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight mb-1">Halo,
                <?php echo isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Admin'; ?>! 👋</h1>
            <p class="text-gray-500 text-sm">Berikut adalah ringkasan performa toko IkanSegar hari ini.</p>
        </div>

        <!-- ============================================== -->
        <!-- 4 KARTU METRIK UTAMA                           -->
        <!-- ============================================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <!-- Kartu 1: Pendapatan -->
            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-emerald-100 flex items-center gap-5 relative overflow-hidden group">
                <div
                    class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500">
                </div>
                <div
                    class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 z-10">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
                <div class="z-10">
                    <p class="text-sm font-semibold text-gray-500 mb-1">Pendapatan Hari Ini</p>
                    <h3 class="text-2xl font-black text-gray-800"><?php echo formatRupiah($pendapatan_hari_ini); ?></h3>
                </div>
            </div>

            <!-- Kartu 2: Transaksi -->
            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-blue-100 flex items-center gap-5 relative overflow-hidden group">
                <div
                    class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500">
                </div>
                <div
                    class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 z-10">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
                <div class="z-10">
                    <p class="text-sm font-semibold text-gray-500 mb-1">Transaksi Hari Ini</p>
                    <h3 class="text-2xl font-black text-gray-800"><?php echo $trx_hari_ini; ?> <span
                            class="text-sm text-gray-400 font-medium">Nota</span></h3>
                </div>
            </div>

            <!-- Kartu 3: Total Produk -->
            <div
                class="bg-white rounded-2xl p-6 shadow-sm border border-purple-100 flex items-center gap-5 relative overflow-hidden group">
                <div
                    class="absolute -right-4 -top-4 w-24 h-24 bg-purple-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500">
                </div>
                <div
                    class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center shrink-0 z-10">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                        </path>
                    </svg>
                </div>
                <div class="z-10">
                    <p class="text-sm font-semibold text-gray-500 mb-1">Total Jenis Produk</p>
                    <h3 class="text-2xl font-black text-gray-800"><?php echo $total_produk; ?> <span
                            class="text-sm text-gray-400 font-medium">Ikan</span></h3>
                </div>
            </div>

            <!-- Kartu 4: Stok Peringatan -->
            <div
                class="bg-white rounded-2xl p-6 shadow-sm border <?php echo $ikan_habis > 0 ? 'border-red-200' : 'border-gray-100'; ?> flex items-center gap-5 relative overflow-hidden group">
                <div
                    class="absolute -right-4 -top-4 w-24 h-24 <?php echo $ikan_habis > 0 ? 'bg-red-50' : 'bg-gray-50'; ?> rounded-full opacity-50 group-hover:scale-150 transition-transform duration-500">
                </div>
                <div
                    class="w-14 h-14 rounded-2xl <?php echo $ikan_habis > 0 ? 'bg-red-100 text-red-600 animate-pulse' : 'bg-gray-100 text-gray-500'; ?> flex items-center justify-center shrink-0 z-10">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
                <div class="z-10">
                    <p
                        class="text-sm font-semibold <?php echo $ikan_habis > 0 ? 'text-red-500' : 'text-gray-500'; ?> mb-1">
                        Stok Ikan Habis</p>
                    <h3 class="text-2xl font-black text-gray-800"><?php echo $ikan_habis; ?> <span
                            class="text-sm text-gray-400 font-medium">Ikan</span></h3>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- BAGIAN GRAFIK & TABEL MINI                     -->
        <!-- ============================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Kolom Kiri: GRAFIK (Makan 2 Kolom di Desktop) -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Grafik Penjualan</h2>
                        <p class="text-sm text-gray-500">Performa pendapatan dalam 7 hari terakhir.</p>
                    </div>
                </div>
                <!-- Canvas untuk Chart.js -->
                <div class="w-full h-[300px]">
                    <canvas id="grafikPenjualan"></canvas>
                </div>
            </div>

            <!-- Kolom Kanan: TRANSAKSI TERBARU -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-bold text-gray-800">Transaksi Terbaru</h2>
                    <a href="transaksi.php" class="text-sm font-semibold text-blue-600 hover:underline">Lihat Semua</a>
                </div>

                <div class="flex-1 flex flex-col gap-4">
                    <?php if ($q_trx_terbaru && mysqli_num_rows($q_trx_terbaru) > 0): ?>
                        <?php while ($trx = mysqli_fetch_assoc($q_trx_terbaru)): ?>
                            <div
                                class="flex items-center justify-between p-3 rounded-xl bg-gray-50 border border-gray-100 hover:bg-blue-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-gray-800"><?php echo $trx['kode_transaksi']; ?></p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo date('H:i', strtotime($trx['tanggal_waktu'])); ?> |
                                            <?php echo $trx['nama_lengkap'] ?? 'Admin'; ?></p>
                                    </div>
                                </div>
                                <div class="font-bold text-emerald-600 text-sm">
                                    <?php echo formatRupiah($trx['total_belanja']); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="flex-1 flex flex-col items-center justify-center text-gray-400">
                            <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <p class="text-sm">Belum ada transaksi hari ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</main>

<!-- TAMBAHKAN LIBRARY CHART.JS DARI CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Tangkap data array PHP yang sudah diubah ke JSON
    const chartLabels = <?php echo $label_json; ?>;
    const chartData = <?php echo $data_json; ?>;

    // Konfigurasi Chart.js
    const ctx = document.getElementById('grafikPenjualan').getContext('2d');

    // Membuat Gradient Warna untuk area bawah grafik (opsional agar cantik)
    let gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)'); // Biru Tailwind semi transparan
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    const myChart = new Chart(ctx, {
        type: 'line', // Jenis grafik garis
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Total Pendapatan (Rp)',
                data: chartData,
                borderColor: '#3b82f6', // Warna garis (blue-500 Tailwind)
                backgroundColor: gradient, // Warna area bawah garis
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#3b82f6',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true, // Aktifkan gradient warna di bawah garis
                tension: 0.4 // Membuat garis melengkung/smooth (bukan kaku)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // Sembunyikan legenda atas agar lebih bersih
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: {
                        size: 13,
                        family: "'Plus Jakarta Sans', sans-serif"
                    },
                    bodyFont: {
                        size: 14,
                        weight: 'bold',
                        family: "'Plus Jakarta Sans', sans-serif"
                    },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('id-ID', {
                                    style: 'currency',
                                    currency: 'IDR',
                                    minimumFractionDigits: 0
                                }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9', // Warna garis grid horizontal
                        drawBorder: false,
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            family: "'Plus Jakarta Sans', sans-serif",
                            size: 11
                        },
                        callback: function(value, index, values) {
                            if (value >= 1000000) return 'Rp ' + (value / 1000000) + ' Jt';
                            if (value >= 1000) return 'Rp ' + (value / 1000) + ' Rb';
                            return 'Rp ' + value;
                        }
                    }
                },
                x: {
                    grid: {
                        display: false, // Hilangkan garis grid vertikal
                        drawBorder: false,
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            family: "'Plus Jakarta Sans', sans-serif",
                            size: 11
                        }
                    }
                }
            }
        }
    });
</script>

<?php include '../components/footer.php'; ?>