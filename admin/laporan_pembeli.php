<?php
// admin/laporan_pembeli.php
session_start();
require_once '../koneksi.php';

// ========================================================
// KONFIGURASI HALAMAN
// ========================================================
$halaman = 'laporan_pembeli';
$judul_halaman = 'Laporan Data Pelanggan';

// ========================================================
// LOGIKA FILTER TANGGAL
// ========================================================
$filter_mulai = isset($_GET['tgl_mulai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_mulai']) : '';
$filter_selesai = isset($_GET['tgl_selesai']) ? mysqli_real_escape_string($koneksi, $_GET['tgl_selesai']) : '';

$where_clause = "";
if (!empty($filter_mulai) && !empty($filter_selesai)) {
    $where_clause = " WHERE DATE(tanggal_waktu) BETWEEN '$filter_mulai' AND '$filter_selesai' ";
}

// ========================================================
// QUERY: KELOMPOKKAN BERDASARKAN NAMA PEMBELI
// ========================================================
$query_pembeli = mysqli_query($koneksi, "
    SELECT 
        nama_pembeli, 
        COUNT(id) as jumlah_transaksi, 
        SUM(total_belanja) as total_belanja, 
        MAX(tanggal_waktu) as transaksi_terakhir 
    FROM transaksi 
    $where_clause 
    GROUP BY nama_pembeli 
    ORDER BY total_belanja DESC
");

// Variabel untuk menampung ringkasan
$total_pelanggan_unik = 0;
$pelanggan_terbaik = '-';
$belanja_terbaik = 0;
$data_tabel = [];

if ($query_pembeli && mysqli_num_rows($query_pembeli) > 0) {
    while ($row = mysqli_fetch_assoc($query_pembeli)) {
        // Jangan hitung 'Pelanggan Umum' sebagai pelanggan unik terdaftar
        if (strtolower($row['nama_pembeli']) !== 'pelanggan umum') {
            $total_pelanggan_unik++;

            // Cari pelanggan dengan nominal belanja tertinggi
            if ($row['total_belanja'] > $belanja_terbaik) {
                $pelanggan_terbaik = $row['nama_pembeli'];
                $belanja_terbaik = $row['total_belanja'];
            }
        }
        $data_tabel[] = $row;
    }
}

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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-purple-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-purple-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Total Pelanggan Terdata</p>
                <h3 class="text-3xl font-extrabold text-purple-600 relative z-10"><?php echo $total_pelanggan_unik; ?>
                    <span class="text-sm font-medium text-purple-400">Orang</span>
                </h3>
                <p class="text-xs text-gray-400 mt-2 relative z-10">*Tidak termasuk Pelanggan Umum</p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-amber-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <div class="flex justify-between items-start relative z-10">
                    <div>
                        <p class="text-sm font-bold text-gray-500 mb-1">Pelanggan Top Spender (Terbaik)</p>
                        <h3 class="text-2xl font-extrabold text-gray-800">
                            <?php echo htmlspecialchars($pelanggan_terbaik); ?></h3>
                        <p class="text-sm font-bold text-amber-500 mt-1"><?php echo formatRupiah($belanja_terbaik); ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 text-amber-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- FORM FILTER TANGGAL                            -->
        <!-- ============================================== -->
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <form action="laporan_pembeli.php" method="GET" class="flex flex-col sm:flex-row sm:items-end gap-4 flex-1">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Dari Tanggal
                        Transaksi</label>
                    <input type="date" name="tgl_mulai" value="<?php echo $filter_mulai; ?>" required
                        class="px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Sampai Tanggal
                        Transaksi</label>
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
                        <a href="laporan_pembeli.php"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold rounded-xl transition text-sm">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- TABEL DATA PEMBELI                             -->
        <!-- ============================================== -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-10">
            <div
                class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">
                        Daftar Pembeli & Total Belanja
                        <?php if (!empty($filter_mulai)): ?>
                            <span
                                class="text-sm font-normal text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md ml-2 border border-blue-100">
                                (Filter: <?php echo date('d/m/Y', strtotime($filter_mulai)); ?> -
                                <?php echo date('d/m/Y', strtotime($filter_selesai)); ?>)
                            </span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-sm text-gray-500">Gunakan tombol export untuk menyimpan database pelanggan.</p>
                </div>
            </div>

            <div class="overflow-x-auto w-full">
                <!-- Tabel dengan ID khusus untuk DataTables -->
                <table id="tabel-pembeli" class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-800 text-white text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold text-center w-16">No</th>
                            <th class="p-4 font-semibold">Nama Pembeli</th>
                            <th class="p-4 font-semibold text-center">Jml. Transaksi</th>
                            <th class="p-4 font-semibold text-right">Total Belanja (Rp)</th>
                            <th class="p-4 font-semibold text-center">Transaksi Terakhir</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                        <?php
                        if (!empty($data_tabel)):
                            $no = 1;
                            foreach ($data_tabel as $row):
                                $is_umum = (strtolower($row['nama_pembeli']) === 'pelanggan umum');
                        ?>
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="p-4 text-center text-gray-500"><?php echo $no++; ?></td>

                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white text-xs <?php echo $is_umum ? 'bg-slate-400' : 'bg-purple-500'; ?>">
                                                <?php echo strtoupper(substr($row['nama_pembeli'], 0, 1)); ?>
                                            </div>
                                            <span class="font-bold <?php echo $is_umum ? 'text-gray-500' : 'text-gray-800'; ?>">
                                                <?php echo htmlspecialchars($row['nama_pembeli']); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <td class="p-4 text-center">
                                        <span
                                            class="inline-flex px-3 py-1 items-center rounded-full bg-blue-50 text-blue-700 font-bold border border-blue-200 text-xs">
                                            <?php echo $row['jumlah_transaksi']; ?> Kali
                                        </span>
                                    </td>

                                    <td class="p-4 text-right font-black text-emerald-600"
                                        data-order="<?php echo $row['total_belanja']; ?>">
                                        <?php echo formatRupiah($row['total_belanja']); ?>
                                    </td>

                                    <td class="p-4 text-center text-gray-500"
                                        data-order="<?php echo strtotime($row['transaksi_terakhir']); ?>">
                                        <?php echo date('d M Y, H:i', strtotime($row['transaksi_terakhir'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-500 italic">Belum ada data pembeli pada
                                    periode ini.</td>
                            </tr>
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
        $('#tabel-pembeli').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            order: [
                [3, 'desc']
            ], // Urutkan berdasarkan total belanja tertinggi secara default (kolom index 3)
            pageLength: 25,
            dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"Bf>rt<"flex flex-col sm:flex-row justify-between items-center mt-4 gap-4"ip>',
            buttons: [{
                    extend: 'excelHtml5',
                    text: '<div class="flex items-center bg-green-500 text-white rounded-xl p-2 gap-2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg> Export Excel</div>',
                    className: 'bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl shadow-sm text-sm',
                    title: 'Laporan Data Pelanggan Simabeni Pangkah'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<div class="flex items-center bg-red-500 text-white rounded-xl p-2 gap-2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path></svg> Cetak PDF</div>',
                    className: 'bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-xl shadow-sm text-sm ml-2',
                    title: 'Laporan Data Pelanggan Simabeni Pangkah',
                    customize: function(doc) {
                        doc.content[1].table.widths = ['10%', '35%', '15%', '20%', '20%'];
                        doc.defaultStyle.fontSize = 10;
                    }
                }
            ]
        });
    });
</script>

<?php include '../components/footer.php'; ?>