<?php
// admin/transaksi.php
session_start();
require_once '../koneksi.php';

$halaman = 'transaksi';
$judul_halaman = 'Laporan & Riwayat Transaksi';

// ========================================================
// 1. AMBIL DATA RINGKASAN (LAPORAN HARI INI & BULAN INI)
// ========================================================
$hari_ini = date('Y-m-d');
$bulan_ini = date('Y-m');

$q_hari_ini = mysqli_query($koneksi, "SELECT SUM(total_belanja) as total_uang, COUNT(id) as total_trx FROM transaksi WHERE DATE(tanggal_waktu) = '$hari_ini'");
$data_hari_ini = mysqli_fetch_assoc($q_hari_ini);
$uang_hari_ini = $data_hari_ini['total_uang'] ? $data_hari_ini['total_uang'] : 0;
$trx_hari_ini = $data_hari_ini['total_trx'] ? $data_hari_ini['total_trx'] : 0;

$q_bulan_ini = mysqli_query($koneksi, "SELECT SUM(total_belanja) as total_uang FROM transaksi WHERE DATE_FORMAT(tanggal_waktu, '%Y-%m') = '$bulan_ini'");
$data_bulan_ini = mysqli_fetch_assoc($q_bulan_ini);
$uang_bulan_ini = $data_bulan_ini['total_uang'] ? $data_bulan_ini['total_uang'] : 0;

// ========================================================
// 2. LOGIKA FILTER TANGGAL
// ========================================================
$filter_mulai = isset($_GET['tgl_mulai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_mulai']) : '';
$filter_selesai = isset($_GET['tgl_selesai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_selesai']) : '';

$where_clause = "";
if (!empty($filter_mulai) && !empty($filter_selesai)) {
    // Tambahkan kondisi WHERE jika form filter disubmit
    $where_clause = " WHERE DATE(t.tanggal_waktu) BETWEEN '$filter_mulai' AND '$filter_selesai' ";
}

// ========================================================
// 3. AMBIL DATA TRANSAKSI UTAMA (DENGAN FILTER)
// ========================================================
$query_transaksi = mysqli_query($koneksi, "
    SELECT t.*, u.nama_lengkap as nama_kasir 
    FROM transaksi t 
    LEFT JOIN users u ON t.user_id = u.id 
    $where_clause
    ORDER BY t.tanggal_waktu DESC
");

// ========================================================
// 4. AMBIL DETAIL TRANSAKSI (Hanya untuk transaksi yang tampil)
// ========================================================
$semua_detail = [];
// Kumpulkan ID transaksi yang tampil untuk menghemat memori
$id_transaksi_list = [];
$data_transaksi_tampil = [];

if ($query_transaksi && mysqli_num_rows($query_transaksi) > 0) {
    while ($trx = mysqli_fetch_assoc($query_transaksi)) {
        $data_transaksi_tampil[] = $trx;
        $id_transaksi_list[] = $trx['id'];
    }

    // Ambil detailnya
    $id_transaksi_string = implode(',', $id_transaksi_list);
    $query_detail = mysqli_query($koneksi, "
        SELECT dt.*, i.nama_ikan, i.satuan 
        FROM detail_transaksi dt 
        LEFT JOIN ikan i ON dt.ikan_id = i.id
        WHERE dt.transaksi_id IN ($id_transaksi_string)
    ");

    while ($row = mysqli_fetch_assoc($query_detail)) {
        $semua_detail[$row['transaksi_id']][] = $row;
    }
}

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

    /* Khusus untuk area Print Struk */
    @media print {
        body * {
            visibility: hidden;
        }

        #modal-struk-content,
        #modal-struk-content * {
            visibility: visible;
        }

        #modal-struk-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            box-shadow: none !important;
        }

        .no-print {
            display: none !important;
        }
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
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Pendapatan Hari Ini</p>
                <h3 class="text-3xl font-extrabold text-emerald-600 relative z-10">
                    <?php echo formatRupiah($uang_hari_ini); ?></h3>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-blue-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Transaksi Hari Ini</p>
                <h3 class="text-3xl font-extrabold text-blue-600 relative z-10"><?php echo $trx_hari_ini; ?> <span
                        class="text-sm font-medium text-gray-400">Nota</span></h3>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-purple-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-purple-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Total Pendapatan Bulan Ini</p>
                <h3 class="text-3xl font-extrabold text-purple-600 relative z-10">
                    <?php echo formatRupiah($uang_bulan_ini); ?></h3>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- FORM FILTER TANGGAL                            -->
        <!-- ============================================== -->
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <form action="transaksi.php" method="GET" class="flex flex-col sm:flex-row sm:items-end gap-4 flex-1">
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
                        <a href="transaksi.php"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl transition text-sm">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- TABEL RIWAYAT TRANSAKSI                        -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div
                class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">
                        Daftar Riwayat Transaksi
                        <?php if (!empty($filter_mulai)): ?>
                            <span
                                class="text-sm font-normal text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md ml-2 border border-blue-100">
                                (Filter: <?php echo date('d/m/Y', strtotime($filter_mulai)); ?> -
                                <?php echo date('d/m/Y', strtotime($filter_selesai)); ?>)
                            </span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-sm text-gray-500">Gunakan tombol export di tabel untuk menyimpan laporan PDF/Excel.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto w-full">
                <!-- Tabel ditambahkan class khusus agar tombol export bisa di-inject via JS -->
                <table id="tabel-transaksi" class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold ">Tanggal & Waktu</th>
                            <th class="p-4 font-semibold">Kode TRX</th>
                            <th class="p-4 font-semibold">Kasir</th>
                            <th class="p-4 font-semibold text-right">Total Belanja</th>
                            <th class="p-4 font-semibold text-center">Status</th>
                            <th class="p-4 font-semibold text-center">Aksi (Struk)</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php if (!empty($data_transaksi_tampil)): ?>
                            <?php foreach ($data_transaksi_tampil as $trx):
                                $detail_json = isset($semua_detail[$trx['id']]) ? json_encode($semua_detail[$trx['id']]) : '[]';
                                $tgl_lengkap = date('d M Y, H:i', strtotime($trx['tanggal_waktu']));
                            ?>
                                <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-50">
                                    <td class="p-4  text-gray-500" data-order="<?php echo strtotime($trx['tanggal_waktu']); ?>">
                                        <span
                                            class="font-bold text-gray-800 block"><?php echo date('d M Y', strtotime($trx['tanggal_waktu'])); ?></span>
                                        <span
                                            class="text-xs"><?php echo date('H:i:s', strtotime($trx['tanggal_waktu'])); ?></span>
                                    </td>
                                    <td class="p-4 font-bold text-blue-600"><?php echo $trx['kode_transaksi']; ?></td>
                                    <td class="p-4 flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                            <?php echo strtoupper(substr($trx['nama_kasir'] ? $trx['nama_kasir'] : '?', 0, 1)); ?>
                                        </div>
                                        <?php echo $trx['nama_kasir'] ? $trx['nama_kasir'] : '<i class="text-gray-400">Terhapus</i>'; ?>
                                    </td>
                                    <td class="p-4 text-right font-bold text-emerald-600"
                                        data-order="<?php echo $trx['total_belanja']; ?>">
                                        <?php echo formatRupiah($trx['total_belanja']); ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span
                                            class="inline-flex px-3 py-1 items-center rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 text-xs">
                                            Selesai
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <button
                                            onclick='bukaStruk("<?php echo $trx['kode_transaksi']; ?>", "<?php echo $tgl_lengkap; ?>", "<?php echo addslashes($trx['nama_kasir']); ?>", <?php echo $trx['total_belanja']; ?>, <?php echo $trx['jumlah_bayar']; ?>, <?php echo $trx['kembalian']; ?>, <?php echo $detail_json; ?>)'
                                            class="px-4 py-2 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg font-semibold transition text-xs shadow-sm">
                                            Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL STRUK TRANSAKSI (POP-UP)                 -->
        <!-- ============================================== -->
        <!-- (Kode Modal Struk ini sama dengan sebelumnya, tidak ada perubahan) -->
        <div id="modal-struk"
            class="fixed inset-0 bg-gray-900/70 z-[80] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh] overflow-hidden relative"
                id="modal-struk-content">
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-5 no-print">
                    <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                </div>
                <button type="button" onclick="tutupModal('modal-struk')"
                    class="absolute top-4 right-4 text-gray-400 hover:text-red-500 bg-gray-100 hover:bg-red-50 p-2 rounded-full transition z-10 no-print">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>

                <div class="overflow-y-auto flex-1 p-8 relative z-0">
                    <div class="text-center border-b-2 border-dashed border-gray-300 pb-6 mb-6">
                        <h2 class="text-2xl font-black text-gray-800 tracking-tight mb-1">IkanSegar.</h2>
                        <p class="text-xs text-gray-500 font-medium">Ikan Segar Langsung dari Tambak</p>
                        <p class="text-xs text-gray-400 mt-1">Jl. Contoh Perikanan No. 123, Kota Laut</p>
                    </div>

                    <div class="space-y-1 mb-6 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">No. Nota</span> <span
                                class="font-bold text-gray-800" id="struk-kode"></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Tanggal</span> <span
                                class="font-semibold text-gray-800" id="struk-tgl"></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Kasir</span> <span
                                class="font-semibold text-gray-800" id="struk-kasir"></span></div>
                    </div>

                    <div class="mb-6">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-y-2 border-gray-200 text-gray-600">
                                    <th class="py-2 text-left font-semibold">Item</th>
                                    <th class="py-2 text-center font-semibold w-12">Qty</th>
                                    <th class="py-2 text-right font-semibold">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="struk-items" class="divide-y divide-gray-100"></tbody>
                        </table>
                    </div>

                    <div class="space-y-2 text-sm border-t-2 border-dashed border-gray-300 pt-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600 font-semibold">Total Belanja</span>
                            <span class="font-black text-lg text-gray-800" id="struk-total"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tunai (Bayar)</span>
                            <span class="font-semibold text-gray-700" id="struk-bayar"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Kembalian</span>
                            <span class="font-bold text-gray-700" id="struk-kembali"></span>
                        </div>
                    </div>

                    <div class="text-center mt-10 text-xs text-gray-400">
                        <p>Terima kasih atas kunjungan Anda!</p>
                        <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
                    </div>
                </div>

                <div class="p-4 bg-gray-50 border-t border-gray-200 shrink-0 no-print flex gap-3">
                    <button onclick="window.print()"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                            </path>
                        </svg>
                        Cetak Struk
                    </button>
                </div>
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
        $('#tabel-transaksi').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            columnDefs: [{
                orderable: false,
                targets: 5
            }],
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
            // Modifikasi DOM DataTables untuk menyisipkan tombol Export
            dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"Bf>rt<"flex flex-col sm:flex-row justify-between items-center mt-4 gap-4"ip>',
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<div class="flex items-center bg-green-500 text-white rounded-xl p-2 gap-2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg> Export Excel</div>',
                    className: 'bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl shadow-sm text-sm',
                    title: 'Laporan Penjualan IkanSegar',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    } // Jangan ikut sertakan kolom ke-6 (Tombol Detail)
                },
                {
                    extend: 'pdfHtml5',
                    text: '<div class="flex items-center bg-red-500 text-white rounded-xl p-2 gap-2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path></svg> Cetak PDF</div>',
                    className: 'bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-xl shadow-sm text-sm ml-2',
                    title: 'Laporan Penjualan IkanSegar',
                    customize: function(doc) {
                        // Kustomisasi layout PDF
                        doc.content[1].table.widths = ['25%', '25%', '20%', '15%', '15%'];
                        doc.defaultStyle.fontSize = 10;
                    },
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    }
                }
            ]
        });
    });

    const formatRp = (angka) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    };

    function tutupModal(idModal) {
        document.getElementById(idModal + '-content').classList.add('scale-95');
        setTimeout(() => document.getElementById(idModal).classList.add('opacity-0', 'pointer-events-none'), 200);
    }

    function bukaStruk(kode, tanggal, kasir, total, bayar, kembali, details) {
        document.getElementById('struk-kode').innerText = kode;
        document.getElementById('struk-tgl').innerText = tanggal;
        document.getElementById('struk-kasir').innerText = kasir ? kasir : 'Admin Terhapus';
        document.getElementById('struk-total').innerText = formatRp(total);
        document.getElementById('struk-bayar').innerText = formatRp(bayar);
        document.getElementById('struk-kembali').innerText = formatRp(kembali);

        const listItems = document.getElementById('struk-items');
        listItems.innerHTML = '';

        if (details.length > 0) {
            details.forEach(item => {
                let htmlRow = `
                    <tr>
                        <td class="py-3">
                            <p class="font-bold text-gray-800 leading-tight">${item.nama_ikan || '<i class="text-red-500">Ikan Terhapus</i>'}</p>
                            <p class="text-xs text-gray-400">${formatRp(item.harga_satuan)}</p>
                        </td>
                        <td class="py-3 text-center font-bold text-gray-600">x ${item.qty}</td>
                        <td class="py-3 text-right font-bold text-gray-800">${formatRp(item.subtotal)}</td>
                    </tr>
                `;
                listItems.innerHTML += htmlRow;
            });
        } else {
            listItems.innerHTML =
                `<tr><td colspan="3" class="py-4 text-center text-gray-400 italic">Detail item tidak ditemukan.</td></tr>`;
        }

        const modal = document.getElementById('modal-struk');
        const content = document.getElementById('modal-struk-content');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-95');
    }
</script>

<?php include '../components/footer.php'; ?>