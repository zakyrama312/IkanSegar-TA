<?php
// admin/riwayat_stok.php
session_start();
require_once '../koneksi.php';

// ========================================================
// KONFIGURASI HALAMAN
// ========================================================
$halaman = 'riwayat_stok';
$judul_halaman = 'Laporan Riwayat Stok Masuk';

// ========================================================
// 1. AMBIL DATA RINGKASAN (LAPORAN HARI INI & BULAN INI)
// ========================================================
$hari_ini = date('Y-m-d');
$bulan_ini = date('Y-m');

// Total Ikan & Aktivitas Hari Ini
$q_hari_ini = mysqli_query($koneksi, "SELECT SUM(jumlah_tambah) as total_ikan, COUNT(id) as total_aktivitas FROM riwayat_stok WHERE DATE(tanggal_tambah) = '$hari_ini'");
$data_hari_ini = mysqli_fetch_assoc($q_hari_ini);
$ikan_hari_ini = $data_hari_ini['total_ikan'] ? $data_hari_ini['total_ikan'] : 0;
$aktivitas_hari_ini = $data_hari_ini['total_aktivitas'] ? $data_hari_ini['total_aktivitas'] : 0;

// Total Ikan Bulan Ini
$q_bulan_ini = mysqli_query($koneksi, "SELECT SUM(jumlah_tambah) as total_ikan FROM riwayat_stok WHERE DATE_FORMAT(tanggal_tambah, '%Y-%m') = '$bulan_ini'");
$data_bulan_ini = mysqli_fetch_assoc($q_bulan_ini);
$ikan_bulan_ini = $data_bulan_ini['total_ikan'] ? $data_bulan_ini['total_ikan'] : 0;

// ========================================================
// 2. LOGIKA FILTER TANGGAL
// ========================================================
$filter_mulai = isset($_GET['tgl_mulai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_mulai']) : '';
$filter_selesai = isset($_GET['tgl_selesai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_selesai']) : '';

$where_clause = "";
if (!empty($filter_mulai) && !empty($filter_selesai)) {
    // Tambahkan kondisi WHERE jika form filter disubmit
    $where_clause = " WHERE DATE(r.tanggal_tambah) BETWEEN '$filter_mulai' AND '$filter_selesai' ";
}

// ========================================================
// 3. AMBIL DATA RIWAYAT STOK (DENGAN FILTER)
// ========================================================
$query_riwayat = mysqli_query($koneksi, "
    SELECT r.*, i.nama_ikan, i.satuan 
    FROM riwayat_stok r 
    LEFT JOIN ikan i ON r.ikan_id = i.id 
    $where_clause
    ORDER BY r.tanggal_tambah DESC
");

// PANGGIL HEADER HTML
include '../components/header.php';
?>

<!-- Tambahkan Library DataTables CSS & Buttons Extension -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<style>
    .dataTables_wrapper {
        padding: 1.5rem;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.35rem 0.75rem;
        margin-left: 0.5rem;
        outline: none;
        transition: all 0.2s;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }

    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.25rem 1rem 0.25rem 0.5rem;
        outline: none;
    }

    /* Pagination Styling */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #3b82f6 !important;
        color: white !important;
        border: 1px solid #3b82f6 !important;
        border-radius: 0.5rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 0.5rem;
        margin: 0 2px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #eff6ff !important;
        color: #1d4ed8 !important;
        border: 1px solid #bfdbfe !important;
    }

    /* Hilangkan styling bawaan tombol export DataTables agar bisa pakai Tailwind */
    button.dt-button {
        background: none !important;
        border: none !important;
        padding: 0 !important;
        margin-right: 0.5rem !important;
    }

    button.dt-button:hover:not(.disabled) {
        background: none !important;
        border: none !important;
    }

    table.dataTable.no-footer {
        border-bottom: 1px solid #f1f5f9;
    }

    table.dataTable thead th,
    table.dataTable thead td {
        border-bottom: 1px solid #f1f5f9;
    }
</style>

<!-- PANGGIL SIDEBAR -->
<?php include '../components/sidebar.php'; ?>

<!-- PEMBUNGKUS KONTEN -->
<main class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50">

    <!-- PANGGIL NAVBAR ATAS -->
    <?php include '../components/navbar.php'; ?>

    <!-- AREA KONTEN -->
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative">

        <!-- KARTU LAPORAN RINGKASAN -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-emerald-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Total Ikan Masuk Hari Ini</p>
                <h3 class="text-3xl font-extrabold text-emerald-600 relative z-10">
                    <?php echo number_format($ikan_hari_ini, 0, ',', '.'); ?> <span
                        class="text-sm font-medium text-emerald-400">Unit</span></h3>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-blue-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Aktivitas Restock Hari Ini</p>
                <h3 class="text-3xl font-extrabold text-blue-600 relative z-10"><?php echo $aktivitas_hari_ini; ?> <span
                        class="text-sm font-medium text-gray-400">Kali</span></h3>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-amber-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Total Ikan Masuk Bulan Ini</p>
                <h3 class="text-3xl font-extrabold text-amber-600 relative z-10">
                    <?php echo number_format($ikan_bulan_ini, 0, ',', '.'); ?> <span
                        class="text-sm font-medium text-amber-400">Unit</span></h3>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- FORM FILTER TANGGAL                            -->
        <!-- ============================================== -->
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <form action="riwayat_stok.php" method="GET" class="flex flex-col sm:flex-row sm:items-end gap-4 flex-1">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Dari
                        Tanggal</label>
                    <input type="date" name="tgl_mulai" value="<?php echo $filter_mulai; ?>" required
                        class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Sampai
                        Tanggal</label>
                    <input type="date" name="tgl_selesai" value="<?php echo $filter_selesai; ?>" required
                        class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition shadow-sm text-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                        Filter
                    </button>
                    <?php if (!empty($filter_mulai)): ?>
                        <a href="riwayat_stok.php"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl transition text-sm">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- TABEL RIWAYAT STOK                             -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div
                class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">
                        Catatan Riwayat Stok Masuk
                        <?php if (!empty($filter_mulai)): ?>
                            <span
                                class="text-sm font-normal text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md ml-2 border border-blue-100">
                                (Filter: <?php echo date('d/m/Y', strtotime($filter_mulai)); ?> -
                                <?php echo date('d/m/Y', strtotime($filter_selesai)); ?>)
                            </span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-sm text-gray-500">Semua aktivitas penambahan stok ikan (restock) tercatat di sini.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto w-full">
                <!-- Tabel ditambahkan id agar dikenali DataTables -->
                <table id="tabel-riwayat-stok" class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold">Tanggal & Waktu</th>
                            <th class="p-4 font-semibold">Nama Produk</th>
                            <th class="p-4 font-semibold">Jumlah Ditambah</th>
                            <th class="p-4 font-semibold">Keterangan / Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php if ($query_riwayat && mysqli_num_rows($query_riwayat) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($query_riwayat)): ?>
                                <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-50">
                                    <td class="p-4 text-left text-gray-500"
                                        data-order="<?php echo strtotime($row['tanggal_tambah']); ?>">
                                        <span
                                            class="font-bold text-gray-800 block"><?php echo date('d M Y', strtotime($row['tanggal_tambah'])); ?></span>
                                        <span class="text-xs"><?php echo date('H:i:s', strtotime($row['tanggal_tambah'])); ?>
                                            WIB</span>
                                    </td>
                                    <td class="p-4 font-bold text-blue-600">
                                        <?php echo $row['nama_ikan'] ? htmlspecialchars($row['nama_ikan']) : '<i class="text-red-500 font-normal">Ikan Terhapus</i>'; ?>
                                    </td>
                                    <td class="p-4">
                                        <span
                                            class="inline-flex px-3 py-1 items-center rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                                            + <?php echo number_format($row['jumlah_tambah'], 0, ',', '.'); ?>
                                            <?php echo $row['satuan']; ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-gray-600 italic">
                                        <?php echo htmlspecialchars($row['keterangan'] ? $row['keterangan'] : '-'); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<!-- JQUERY & DATATABLES JS UTAMA -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<!-- LIBRARY TAMBAHAN KHUSUS EXPORT (PDF & EXCEL) -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabel-riwayat-stok').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            order: [
                [0, 'desc']
            ], // Urutkan Tanggal terbaru (berdasarkan data-order timestamp)
            pageLength: 15,
            // Modifikasi DOM DataTables untuk menyisipkan tombol Export
            dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"Bf>rt<"flex flex-col sm:flex-row justify-between items-center mt-4 gap-4"ip>',
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<div class="flex items-center bg-green-500 text-white rounded-xl p-2 gap-2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg> Export Excel</div>',
                    className: 'bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl shadow-sm text-sm',
                    title: 'Laporan Riwayat Stok SegarLaut'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<div class="flex items-center bg-red-500 text-white rounded-xl p-2 gap-2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path></svg> Cetak PDF</div>',
                    className: 'bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-xl shadow-sm text-sm ml-2',
                    title: 'Laporan Riwayat Stok SegarLaut',
                    customize: function(doc) {
                        // Kustomisasi layout PDF: Tanggal 25%, Produk 30%, Jumlah 15%, Keterangan 30%
                        doc.content[1].table.widths = ['25%', '30%', '15%', '30%'];
                        doc.defaultStyle.fontSize = 10;
                    }
                }
            ]
        });
    });
</script>

<?php include '../components/footer.php'; ?>