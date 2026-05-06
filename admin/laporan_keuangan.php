<?php
// admin/laporan_keuangan.php
session_start();
require_once '../koneksi.php';

// ========================================================
// KONFIGURASI HALAMAN
// ========================================================
$halaman = 'laporan_keuangan';
$judul_halaman = 'Laporan Keuangan (Laporan)';

// ========================================================
// LOGIKA FILTER TANGGAL & PERHITUNGAN SALDO
// ========================================================
$filter_mulai = isset($_GET['tgl_mulai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_mulai']) : '';
$filter_selesai = isset($_GET['tgl_selesai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_selesai']) : '';

$where_trx = "";
$where_peng = "";
$saldo_awal = 0;

if (!empty($filter_mulai) && !empty($filter_selesai)) {
    // Jika di-filter, batasi tanggalnya
    $where_trx = " WHERE DATE(t.tanggal_waktu) BETWEEN '$filter_mulai' AND '$filter_selesai' ";
    $where_peng = " WHERE p.tanggal BETWEEN '$filter_mulai' AND '$filter_selesai' ";

    // Hitung SALDO AWAL (semua transaksi sebelum tanggal mulai filter)
    $q_masuk_sblm = mysqli_query($koneksi, "SELECT SUM(total_belanja) as total FROM transaksi WHERE DATE(tanggal_waktu) < '$filter_mulai'");
    $q_keluar_sblm = mysqli_query($koneksi, "SELECT SUM(total) as total FROM pengeluaran WHERE tanggal < '$filter_mulai'");

    $masuk_sblm = mysqli_fetch_assoc($q_masuk_sblm)['total'] ?? 0;
    $keluar_sblm = mysqli_fetch_assoc($q_keluar_sblm)['total'] ?? 0;
    $saldo_awal = $masuk_sblm - $keluar_sblm;
}

// ========================================================
// QUERY GABUNGAN: PEMASUKAN (TRANSAKSI) + PENGELUARAN
// ========================================================
$query_arus_kas = "
    SELECT 
        t.tanggal_waktu as waktu, 
        CONCAT('Penjualan POS: ', t.kode_transaksi) as keterangan, 
        t.total_belanja as masuk, 
        0 as keluar, 
        u.nama_lengkap as pembuat
    FROM transaksi t 
    LEFT JOIN users u ON t.user_id = u.id 
    $where_trx

    UNION ALL

    SELECT 
        CONCAT(p.tanggal, ' 23:59:59') as waktu, 
        CONCAT('Pengeluaran: ', p.nama_pengeluaran) as keterangan, 
        0 as masuk, 
        p.total as keluar, 
        u.nama_lengkap as pembuat
    FROM pengeluaran p 
    LEFT JOIN users u ON p.user_id = u.id 
    $where_peng

    ORDER BY waktu ASC
";

$result = mysqli_query($koneksi, $query_arus_kas);

// Proses data untuk menghitung saldo berjalan
$total_masuk_periode = 0;
$total_keluar_periode = 0;
$data_tabel = [];
$saldo_berjalan = $saldo_awal;

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $total_masuk_periode += $row['masuk'];
        $total_keluar_periode += $row['keluar'];

        // Rumus Saldo Berjalan per baris
        $saldo_berjalan += $row['masuk'] - $row['keluar'];
        $row['saldo'] = $saldo_berjalan;

        $data_tabel[] = $row;
    }
}
$saldo_akhir = $saldo_berjalan;

// PANGGIL HEADER HTML
include '../components/header.php';
?>

<!-- Library DataTables CSS & Buttons Extension -->
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

    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative">

        <!-- KARTU LAPORAN RINGKASAN -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-blue-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Total Pemasukan (Periode Ini)</p>
                <h3 class="text-3xl font-extrabold text-blue-600 relative z-10">+
                    <?php echo formatRupiah($total_masuk_periode); ?></h3>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-red-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-red-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Total Pengeluaran (Periode Ini)</p>
                <h3 class="text-3xl font-extrabold text-red-500 relative z-10">-
                    <?php echo formatRupiah($total_keluar_periode); ?></h3>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-emerald-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Total Saldo Saat Ini</p>
                <h3 class="text-3xl font-extrabold text-emerald-600 relative z-10">
                    <?php echo formatRupiah($saldo_akhir); ?></h3>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- FORM FILTER TANGGAL                            -->
        <!-- ============================================== -->
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <form action="laporan_keuangan.php" method="GET"
                class="flex flex-col sm:flex-row sm:items-end gap-4 flex-1">
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
                        <a href="laporan_keuangan.php"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl transition text-sm">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- TABEL Laporan (LAPORAN KEUANGAN)               -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-10">
            <div
                class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">
                        Laporan & Arus Keuangan
                        <?php if (!empty($filter_mulai)): ?>
                            <span
                                class="text-sm font-normal text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md ml-2 border border-blue-100">
                                (Filter: <?php echo date('d/m/Y', strtotime($filter_mulai)); ?> -
                                <?php echo date('d/m/Y', strtotime($filter_selesai)); ?>)
                            </span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-sm text-gray-500">Gunakan tombol export di tabel untuk menyimpan laporan ke
                        PDF/Excel.</p>
                </div>
            </div>

            <div class="overflow-x-auto w-full">
                <!-- Tabel dengan ID khusus untuk DataTables -->
                <table id="tabel-keuangan" class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-800 text-white text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold text-center">No</th>
                            <th class="p-4 font-semibold text-center">Tanggal & Waktu</th>
                            <th class="p-4 font-semibold">Keterangan / Transaksi</th>
                            <th class="p-4 font-semibold text-center">Pencatat</th>
                            <th class="p-4 font-semibold text-right">Masuk (Debit)</th>
                            <th class="p-4 font-semibold text-right">Keluar (Kredit)</th>
                            <th class="p-4 font-semibold text-right">Saldo Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">

                        <!-- BARIS SALDO AWAL (Jika ada filter) -->
                        <?php if (!empty($filter_mulai)): ?>
                            <tr class="bg-amber-50/50 font-semibold">
                                <td class="p-4 text-center text-gray-500">-</td>
                                <td class="p-4 text-amber-700 italic">Saldo Awal (Sebelum
                                    <?php echo date('d/m/Y', strtotime($filter_mulai)); ?>)</td>
                                <td class="p-4 text-center">-</td>
                                <td class="p-4 text-center">-</td>
                                <td class="p-4 text-right">-</td>
                                <td class="p-4 text-right">-</td>
                                <td class="p-4 text-right text-amber-700"><?php echo formatRupiah($saldo_awal); ?></td>
                            </tr>
                        <?php endif; ?>

                        <!-- DATA ARUS KAS -->
                        <?php if (!empty($data_tabel)): ?>
                            <?php $i = 1;
                            foreach ($data_tabel as $row): ?>
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="p-4 text-center text-gray-500"><?php echo $i++; ?></td>
                                    <td class="p-4 text-center text-gray-500">
                                        <?php echo date('d/m/Y H:i', strtotime($row['waktu'])); ?>
                                    </td>
                                    <td class="p-4 font-bold text-gray-800">
                                        <?php
                                        if ($row['masuk'] > 0) {
                                            echo '<span class="text-blue-500 mr-1">↓</span> ' . $row['keterangan'];
                                        } else {
                                            echo '<span class="text-red-500 mr-1">↑</span> ' . $row['keterangan'];
                                        }
                                        ?>
                                    </td>
                                    <td class="p-4 text-center text-xs text-gray-500 font-medium">
                                        <?php echo htmlspecialchars($row['pembuat'] ?? 'Sistem'); ?>
                                    </td>
                                    <td class="p-4 text-right font-bold text-blue-600">
                                        <?php echo $row['masuk'] > 0 ? formatRupiah($row['masuk']) : '-'; ?>
                                    </td>
                                    <td class="p-4 text-right font-bold text-red-500">
                                        <?php echo $row['keluar'] > 0 ? formatRupiah($row['keluar']) : '-'; ?>
                                    </td>
                                    <td class="p-4 text-right font-black text-emerald-600">
                                        <?php echo formatRupiah($row['saldo']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Hapus colspan karena DataTables akan otomatis membuat "No data available" secara cerdas -->
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-500 italic">Belum ada data keuangan pada
                                    periode ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                    <!-- FOOTER TABEL (Selalu tampil agar fitur export dataTables tidak error/crash ketika datanya kosong) -->
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td colspan="4" class="p-4 text-right text-gray-600 uppercase tracking-wider text-xs">Total
                                Mutasi Periode Ini:</td>
                            <td class="p-4 text-right text-blue-600"><?php echo formatRupiah($total_masuk_periode); ?>
                            </td>
                            <td class="p-4 text-right text-red-500"><?php echo formatRupiah($total_keluar_periode); ?>
                            </td>
                            <td class="p-4 text-right text-emerald-600 border-t-2 border-emerald-500">
                                <?php echo formatRupiah($saldo_akhir); ?></td>
                        </tr>
                    </tfoot>
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
        $('#tabel-keuangan').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            ordering: false, // PENTING: Sorting dimatikan agar urutan Saldo Berjalan tidak rusak
            pageLength: 25,
            dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"Bf>rt<"flex flex-col sm:flex-row justify-between items-center mt-4 gap-4"ip>',
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<div class="flex items-center bg-green-500 text-white rounded-xl p-2 gap-2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg> Export Excel</div>',
                    className: 'bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl shadow-sm text-sm',
                    title: 'Laporan keuangan Simabeni Pangkah',
                    footer: true // Agar total di bawah ikut terekspor
                },
                {
                    extend: 'pdfHtml5',
                    text: '<div class="flex items-center bg-red-500 text-white rounded-xl p-2 gap-2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path></svg> Cetak PDF</div>',
                    className: 'bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-xl shadow-sm text-sm ml-2',
                    title: 'Laporan Keuangan Simabeni Pangkah',
                    orientation: 'landscape', // Kertas landscape
                    pageSize: 'A4',
                    footer: true,
                    customize: function(doc) {
                        // PANGGIL FUNGSI KOP SURAT
                        tambahkanKopSuratPdf(doc, 'LAPORAN KEUANGAN & ARUS KAS');

                        // Array widths diubah menjadi 7 sesuai dengan jumlah total kolom (100%)
                        doc.content[3].table.widths = ['5%', '15%', '25%', '10%', '15%', '15%',
                            '15%'
                        ];
                        doc.defaultStyle.fontSize = 10;

                        // --- PERBAIKAN COLSPAN FOOTER ---
                        // Cari baris terakhir (footer) di dalam tabel
                        let lastRowIndex = doc.content[3].table.body.length - 1;
                        let footerRow = doc.content[3].table.body[lastRowIndex];

                        // Jadikan kolom pertama membentang 4 kolom (colSpan: 4)
                        footerRow[0].colSpan = 4;
                        footerRow[0].alignment = 'right';

                        // Wajib mengosongkan kolom ke-2, 3, dan 4 agar pdfmake tidak mencetak duplikat
                        footerRow[1] = {};
                        footerRow[2] = {};
                        footerRow[3] = {};

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
    });
</script>

<?php include '../components/footer.php'; ?>