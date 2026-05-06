<?php
// admin/riwayat_stok.php
session_start();
require_once '../koneksi.php';

// ========================================================
// KONFIGURASI HALAMAN
// ========================================================
// Biarkan $halaman tetap 'riwayat_stok' agar sorotan menu di sidebar tetap menyala
$halaman = 'riwayat_stok';
$judul_halaman = 'Laporan Data Ikan (Rekap Stok)';

// ========================================================
// LOGIKA FILTER TANGGAL
// ========================================================
$filter_mulai = isset($_GET['tgl_mulai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_mulai']) : '';
$filter_selesai = isset($_GET['tgl_selesai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_selesai']) : '';

$where_riwayat = "";
$where_trx = "";
if (!empty($filter_mulai) && !empty($filter_selesai)) {
    // Filter untuk tabel riwayat_stok (Stok Masuk)
    $where_riwayat = " AND DATE(tanggal_tambah) BETWEEN '$filter_mulai' AND '$filter_selesai' ";
    // Filter untuk tabel transaksi (Stok Keluar)
    $where_trx = " AND DATE(t.tanggal_waktu) BETWEEN '$filter_mulai' AND '$filter_selesai' ";
}

// ========================================================
// AMBIL DATA REKAPITULASI (PER IKAN)
// ========================================================
// Query ini akan menghitung Total Masuk, Total Keluar, dan Tanggal Terakhir Masuk untuk setiap ikan
$query_laporan = mysqli_query($koneksi, "
    SELECT 
        i.id,
        i.nama_ikan,
        i.satuan,
        i.stok as stok_sekarang,
        (SELECT SUM(jumlah_tambah) FROM riwayat_stok WHERE ikan_id = i.id $where_riwayat) as total_masuk,
        (SELECT SUM(dt.qty) FROM detail_transaksi dt JOIN transaksi t ON dt.transaksi_id = t.id WHERE dt.ikan_id = i.id $where_trx) as total_keluar,
        (SELECT MAX(tanggal_tambah) FROM riwayat_stok WHERE ikan_id = i.id $where_riwayat) as tgl_terakhir_masuk
    FROM ikan i
    ORDER BY i.nama_ikan DESC
");

// Variabel untuk menampung total di kartu atas
$total_semua_masuk = 0;
$total_semua_keluar = 0;
$total_semua_stok = 0;

$data_tabel = [];

if ($query_laporan) {
    while ($row = mysqli_fetch_assoc($query_laporan)) {
        $masuk = $row['total_masuk'] ? $row['total_masuk'] : 0;
        $keluar = $row['total_keluar'] ? $row['total_keluar'] : 0;
        $stok_saat_ini = $row['stok_sekarang'];

        $total_semua_masuk += $masuk;
        $total_semua_keluar += $keluar;
        $total_semua_stok += $stok_saat_ini;

        $row['masuk'] = $masuk;
        $row['keluar'] = $keluar;
        $data_tabel[] = $row;
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
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Total Stok Masuk (Periode)</p>
                <h3 class="text-3xl font-extrabold text-emerald-600 relative z-10">+
                    <?php echo number_format($total_semua_masuk, 0, ',', '.'); ?> <span
                        class="text-sm font-medium text-emerald-400">Unit</span></h3>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-red-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-red-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Total Stok Keluar/Terjual (Periode)</p>
                <h3 class="text-3xl font-extrabold text-red-500 relative z-10">-
                    <?php echo number_format($total_semua_keluar, 0, ',', '.'); ?> <span
                        class="text-sm font-medium text-red-400">Unit</span></h3>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-blue-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Total Stok Tersedia Saat Ini</p>
                <h3 class="text-3xl font-extrabold text-blue-600 relative z-10">
                    <?php echo number_format($total_semua_stok, 0, ',', '.'); ?> <span
                        class="text-sm font-medium text-blue-400">Unit</span></h3>
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
        <!-- TABEL LAPORAN DATA IKAN                        -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-10">
            <div
                class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">
                        Rekapitulasi Laporan Data Ikan
                        <?php if (!empty($filter_mulai)): ?>
                        <span
                            class="text-sm font-normal text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md ml-2 border border-blue-100">
                            (Periode: <?php echo date('d/m/Y', strtotime($filter_mulai)); ?> -
                            <?php echo date('d/m/Y', strtotime($filter_selesai)); ?>)
                        </span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-sm text-gray-500">Mencatat akumulasi stok masuk dan stok yang berhasil terjual.</p>
                </div>
            </div>

            <div class="overflow-x-auto w-full">
                <!-- Tabel ditambahkan id agar dikenali DataTables -->
                <table id="tabel-laporan-ikan" class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-800 text-white text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold text-center w-16">No</th>

                            <th class="p-4 font-semibold">Tgl Terakhir Masuk</th>
                            <th class="p-4 font-semibold">Nama Produk</th>
                            <th class="p-4 font-semibold text-center">Stok Masuk (+)</th>
                            <th class="p-4 font-semibold text-center">Stok Keluar (-)</th>
                            <th class="p-4 font-semibold text-center">Sisa Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php if (!empty($data_tabel)) { ?>
                        <?php $no = 1;
                            foreach ($data_tabel as $row) { ?>
                        <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-50">
                            <td class="p-4 text-gray-500 font-medium"><?php echo $no++; ?></td>
                            <!-- Kolom Tgl Terakhir Masuk Baru -->
                            <td class="p-4 text-gray-500 text-xs"
                                data-order="<?php echo $row['tgl_terakhir_masuk'] ? strtotime($row['tgl_terakhir_masuk']) : 0; ?>">
                                <?php echo $row['tgl_terakhir_masuk'] ? date('d M Y, H:i', strtotime($row['tgl_terakhir_masuk'])) : '<span class="italic text-gray-400">-</span>'; ?>
                            </td>
                            <td class="p-4 font-bold text-blue-600">
                                <?php echo htmlspecialchars($row['nama_ikan']); ?>
                            </td>



                            <!-- Kolom Stok Masuk -->
                            <td class="p-4 text-center" data-order="<?php echo $row['masuk']; ?>">
                                <span
                                    class="inline-flex px-3 py-1 items-center rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 shadow-sm text-xs">
                                    + <?php echo number_format($row['masuk'], 0, ',', '.'); ?>
                                    <?php echo $row['satuan']; ?>
                                </span>
                            </td>

                            <!-- Kolom Stok Keluar -->
                            <td class="p-4 text-center" data-order="<?php echo $row['keluar']; ?>">
                                <span
                                    class="inline-flex px-3 py-1 items-center rounded-lg bg-red-50 text-red-600 font-bold border border-red-100 shadow-sm text-xs">
                                    - <?php echo number_format($row['keluar'], 0, ',', '.'); ?>
                                    <?php echo $row['satuan']; ?>
                                </span>
                            </td>

                            <!-- Kolom Sisa Saat Ini -->
                            <td class="p-4 text-center" data-order="<?php echo $row['stok_sekarang']; ?>">
                                <span
                                    class="inline-flex px-3 py-1.5 items-center rounded-lg <?php echo $row['stok_sekarang'] <= 0 ? 'bg-gray-100 text-gray-400' : 'bg-blue-50 text-blue-700'; ?> font-black border border-blue-100 shadow-sm text-sm">
                                    <?php echo number_format($row['stok_sekarang'], 0, ',', '.'); ?>
                                    <?php echo $row['satuan']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php }
                        } ?>
                    </tbody>

                    <!-- Footer Total -->
                    <?php if (!empty($data_tabel)): ?>
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <!-- Colspan diubah dari 2 menjadi 3 karena ada tambahan kolom tanggal -->
                            <td colspan="3" class="p-4 text-right text-gray-600 uppercase tracking-wider text-xs">Total
                                Keseluruhan:</td>
                            <td class="p-4 text-center text-emerald-600">+
                                <?php echo number_format($total_semua_masuk, 0, ',', '.'); ?></td>
                            <td class="p-4 text-center text-red-500">-
                                <?php echo number_format($total_semua_keluar, 0, ',', '.'); ?></td>
                            <td class="p-4 text-center text-blue-600 border-t-2 border-blue-500">
                                <?php echo number_format($total_semua_stok, 0, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
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
    // Deklarasikan instance DataTables ke dalam variabel agar bisa dipanggil event listnernya nanti
    var table = $('#tabel-laporan-ikan').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        // PENTING: Matikan order dan search pada kolom index ke 0 (Kolom No) agar urutan angkanya tidak kacau
        columnDefs: [{
            searchable: false,
            orderable: false,
            targets: 0
        }],
        order: [
            [1, 'asc']
        ], // Urutkan berdasarkan Nama Produk Abjad (A-Z)
        pageLength: 25,
        // Modifikasi DOM DataTables untuk menyisipkan tombol Export
        dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"Bf>rt<"flex flex-col sm:flex-row justify-between items-center mt-4 gap-4"ip>',
        buttons: [{
                extend: 'excelHtml5',
                text: '<div class="flex items-center bg-green-500 text-white rounded-xl p-2 gap-2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg> Export Excel</div>',
                className: 'bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl shadow-sm text-sm',
                title: 'Laporan Data Ikan Simabeni Pangkah',
                footer: true
            },
            {
                extend: 'pdfHtml5',
                text: '<div class="flex items-center bg-red-500 text-white rounded-xl p-2 gap-2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path></svg> Cetak PDF</div>',
                className: 'bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-xl shadow-sm text-sm ml-2',
                title: 'Laporan Data Ikan Simabeni Pangkah',
                footer: true,
                customize: function(doc) {
                    // PANGGIL FUNGSI KOP SURAT
                    tambahkanKopSuratPdf(doc, 'REKAPITULASI LAPORAN DATA IKAN');

                    // Kustomisasi layout PDF untuk mengakomodasi 6 kolom (Total 100%)
                    // Perhatikan: doc.content[1] diubah menjadi doc.content[3]
                    doc.content[3].table.widths = ['5%', '30%', '20%', '15%', '15%', '15%'];
                    doc.defaultStyle.fontSize = 10;

                    // --- PERBAIKAN COLSPAN FOOTER ---
                    // Cari baris terakhir (footer) di dalam tabel
                    let lastRowIndex = doc.content[3].table.body.length - 1;
                    let footerRow = doc.content[3].table.body[lastRowIndex];

                    // Jadikan kolom pertama membentang 3 kolom (karena colspan di HTML adalah 3)
                    footerRow[0].colSpan = 3;
                    footerRow[0].alignment = 'right';

                    // Wajib mengosongkan kolom ke-2 dan ke-3 agar pdfmake tidak mencetak duplikat
                    footerRow[1] = {};
                    footerRow[2] = {};

                    // --- TAMBAHAN TEMPAT TANDA TANGAN ---
                    // 1. Buat format tanggal bahasa Indonesia
                    const bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    const tgl = new Date();
                    const tglFormat = 'Slawi, ' + tgl.getDate() + ' ' + bulanIndo[tgl
                        .getMonth()] + ' ' + tgl.getFullYear();

                    // 2. Suntikkan layout kolom tanda tangan ke bagian paling bawah PDF
                    doc.content.push({
                        margin: [0, 40, 0,
                            0
                        ], // Beri jarak (margin) dari tabel ke tanda tangan
                        columns: [
                            // Sisi Kiri (Kepala UPT BBI PANGKAH)
                            {
                                width: '50%',
                                alignment: 'center',
                                text: [
                                    '\n', // Spacer agar sejajar dengan tanggal di kanan
                                    'Mengetahui,\n',
                                    'Atasan Langsung\n',
                                    'KEPALA UPT BBI PANGKAH\n\n\n\n\n\n',
                                    {
                                        text: 'MARDI HARTANTO, S.ST,M.M',
                                        bold: true,
                                        decoration: 'underline'
                                    },
                                    '\nNIP. 19730619 199503 1 004'
                                ]
                            },
                            // Sisi Kanan (Petugas / Yang membuat pernyataan)
                            {
                                width: '50%',
                                alignment: 'center',
                                text: [
                                    tglFormat + '\n\n', // Tanggal dinamis
                                    'Yang membuat pernyataan\n\n\n\n\n\n\n',
                                    {
                                        text: 'ALI APRIYANTO',
                                        bold: true,
                                        decoration: 'underline'
                                    },
                                    '\nNIP. 199304202025211084'
                                ]
                            }
                        ]
                    });
                }
            }
        ]
    });

    // RE-NUMBERING SCRIPT
    // Setiap kali DataTables di-sort atau di-search, kolom 'No' (index 0) akan di-reset ulang dari angka 1
    table.on('order.dt search.dt', function() {
        let i = 1;
        table.cells(null, 0, {
            search: 'applied',
            order: 'applied'
        }).every(function(cell) {
            this.data(i++);
        });
    }).draw();
});
</script>

<?php include '../components/footer.php'; ?>