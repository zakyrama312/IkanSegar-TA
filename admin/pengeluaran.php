<?php
// admin/pengeluaran.php
session_start();
require_once '../koneksi.php';

// ========================================================
// KONFIGURASI HALAMAN
// ========================================================
$halaman = 'pengeluaran';
$judul_halaman = 'Data Pengeluaran Operasional';

// ========================================================
// 1. PROSES TAMBAH PENGELUARAN
// ========================================================
if (isset($_POST['tambah_pengeluaran'])) {
    $nama_pengeluaran = mysqli_real_escape_string($koneksi, $_POST['nama_pengeluaran']);
    $total = (int)$_POST['total'];
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $user_id = $_SESSION['user_id']; // ID user yang sedang login

    $query_insert = "INSERT INTO pengeluaran (nama_pengeluaran, total, tanggal, keterangan, user_id) 
                     VALUES ('$nama_pengeluaran', $total, '$tanggal', '$keterangan', $user_id)";

    if (mysqli_query($koneksi, $query_insert)) {
        $_SESSION['pesan_sukses'] = "Berhasil mencatat pengeluaran baru!";
    } else {
        $_SESSION['pesan_error'] = "Gagal mencatat pengeluaran: " . mysqli_error($koneksi);
    }
    header("Location: pengeluaran.php");
    exit;
}

// ========================================================
// 2. PROSES EDIT PENGELUARAN
// ========================================================
if (isset($_POST['edit_pengeluaran'])) {
    $id_edit = (int)$_POST['edit_id'];
    $nama_pengeluaran = mysqli_real_escape_string($koneksi, $_POST['edit_nama_pengeluaran']);
    $total = (int)$_POST['edit_total'];
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['edit_tanggal']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['edit_keterangan']);

    $query_update = "UPDATE pengeluaran SET 
                     nama_pengeluaran = '$nama_pengeluaran', 
                     total = $total, 
                     tanggal = '$tanggal', 
                     keterangan = '$keterangan' 
                     WHERE id = $id_edit";

    if (mysqli_query($koneksi, $query_update)) {
        $_SESSION['pesan_sukses'] = "Data pengeluaran berhasil diperbarui!";
    } else {
        $_SESSION['pesan_error'] = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
    header("Location: pengeluaran.php");
    exit;
}

// ========================================================
// 3. PROSES HAPUS PENGELUARAN
// ========================================================
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM pengeluaran WHERE id = $id_hapus");
    $_SESSION['pesan_sukses'] = "Catatan pengeluaran berhasil dihapus!";
    header("Location: pengeluaran.php");
    exit;
}

// ========================================================
// 4. AMBIL DATA RINGKASAN & TABEL
// ========================================================
$hari_ini = date('Y-m-d');
$bulan_ini = date('Y-m');

// Total Pengeluaran Hari Ini
$q_hari_ini = mysqli_query($koneksi, "SELECT SUM(total) as total_uang FROM pengeluaran WHERE tanggal = '$hari_ini'");
$uang_hari_ini = mysqli_fetch_assoc($q_hari_ini)['total_uang'] ?? 0;

// Total Pengeluaran Bulan Ini
$q_bulan_ini = mysqli_query($koneksi, "SELECT SUM(total) as total_uang FROM pengeluaran WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini'");
$uang_bulan_ini = mysqli_fetch_assoc($q_bulan_ini)['total_uang'] ?? 0;

// Ambil semua data pengeluaran beserta nama user yang menginput
$query_pengeluaran = mysqli_query($koneksi, "
    SELECT p.*, u.nama_lengkap as pembuat 
    FROM pengeluaran p 
    LEFT JOIN users u ON p.user_id = u.id 
    ORDER BY p.tanggal DESC, p.id DESC
");

// PANGGIL HEADER HTML
include '../components/header.php';
?>

<!-- Tambahkan Library DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
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

<main class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50">

    <!-- PANGGIL NAVBAR ATAS -->
    <?php include '../components/navbar.php'; ?>

    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative">

        <!-- Notifikasi -->
        <?php if (isset($_SESSION['pesan_sukses'])): ?>
            <div id="alert-msg"
                class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm animate-fade-in-down">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <p class="font-medium text-sm"><?php echo $_SESSION['pesan_sukses']; ?></p>
                </div>
                <button onclick="this.parentElement.style.display='none'" class="text-emerald-500"><svg class="w-5 h-5"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg></button>
            </div>
            <?php unset($_SESSION['pesan_sukses']); ?>
        <?php endif; ?>

        <!-- Kartu Ringkasan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-red-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-red-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Pengeluaran Hari Ini</p>
                <h3 class="text-3xl font-extrabold text-red-500 relative z-10">
                    <?php echo formatRupiah($uang_hari_ini); ?></h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-orange-100 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 w-24 h-24 bg-orange-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110">
                </div>
                <p class="text-sm font-bold text-gray-500 mb-1 relative z-10">Total Pengeluaran Bulan Ini</p>
                <h3 class="text-3xl font-extrabold text-orange-500 relative z-10">
                    <?php echo formatRupiah($uang_bulan_ini); ?></h3>
            </div>
        </div>

        <!-- Tabel Data Pengeluaran -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div
                class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Riwayat Pengeluaran</h2>
                    <p class="text-sm text-gray-500">Catat biaya operasional seperti listrik, kebersihan, atau restock
                        di luar sistem.</p>
                </div>
                <button onclick="bukaModal('modal-tambah')"
                    class="w-full sm:w-auto px-5 py-2.5 bg-red-500 text-white rounded-xl font-semibold hover:bg-red-600 transition flex items-center justify-center gap-2 shadow-sm shadow-red-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Pengeluaran
                </button>
            </div>

            <div class="overflow-x-auto w-full">
                <table id="tabel-pengeluaran" class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold text-center">Tanggal</th>
                            <th class="p-4 font-semibold">Nama Pengeluaran</th>
                            <th class="p-4 font-semibold text-right">Total (Rp)</th>
                            <th class="p-4 font-semibold">Keterangan</th>
                            <th class="p-4 font-semibold text-center">Pencatat</th>
                            <th class="p-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php if ($query_pengeluaran && mysqli_num_rows($query_pengeluaran) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($query_pengeluaran)): ?>
                                <tr class="hover:bg-red-50/30 transition-colors border-b border-gray-50">
                                    <td class="p-4 text-center font-bold text-gray-600"
                                        data-order="<?php echo strtotime($row['tanggal']); ?>">
                                        <?php echo date('d M Y', strtotime($row['tanggal'])); ?>
                                    </td>
                                    <td class="p-4 font-bold text-gray-800">
                                        <?php echo htmlspecialchars($row['nama_pengeluaran']); ?></td>
                                    <td class="p-4 text-right font-black text-red-500"
                                        data-order="<?php echo $row['total']; ?>">
                                        <?php echo formatRupiah($row['total']); ?>
                                    </td>
                                    <td class="p-4 text-gray-500 italic text-xs max-w-xs truncate">
                                        <?php echo htmlspecialchars($row['keterangan'] ? $row['keterangan'] : '-'); ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span
                                            class="inline-flex px-2 py-1 items-center rounded bg-slate-100 text-slate-600 font-medium text-xs">
                                            <?php echo htmlspecialchars($row['pembuat'] ?? 'Sistem'); ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button
                                                onclick="bukaModalEdit(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_pengeluaran'], ENT_QUOTES); ?>', <?php echo $row['total']; ?>, '<?php echo $row['tanggal']; ?>', '<?php echo htmlspecialchars($row['keterangan'], ENT_QUOTES); ?>')"
                                                class="p-2 bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg transition"
                                                title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </button>
                                            <a href="pengeluaran.php?hapus=<?php echo $row['id']; ?>"
                                                onclick="return confirm('Yakin ingin menghapus catatan pengeluaran ini?');"
                                                class="p-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg transition"
                                                title="Hapus">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL TAMBAH PENGELUARAN                       -->
        <!-- ============================================== -->
        <div id="modal-tambah"
            class="fixed inset-0 bg-gray-900/60 z-[60] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl transform scale-95 transition-transform duration-300"
                id="modal-tambah-content">
                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-red-50/50 rounded-t-2xl">
                    <h3 class="text-xl font-bold text-red-600">Catat Pengeluaran Baru</h3>
                    <button type="button" onclick="tutupModal('modal-tambah')"
                        class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition"><svg class="w-6 h-6"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <form action="pengeluaran.php" method="POST" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tujuan / Nama
                                Pengeluaran</label>
                            <input type="text" name="nama_pengeluaran" required placeholder="Cth: Tagihan Listrik Toko"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-red-500 outline-none bg-gray-50 focus:bg-white">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal</label>
                                <input type="date" name="tanggal" required value="<?php echo date('Y-m-d'); ?>"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-red-500 outline-none bg-gray-50 focus:bg-white text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Total Biaya (Rp)</label>
                                <input type="number" name="total" required placeholder="150000"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-red-500 outline-none bg-gray-50 focus:bg-white font-bold text-red-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan <span
                                    class="text-xs text-gray-400 font-normal">(Opsional)</span></label>
                            <textarea name="keterangan" rows="2"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-red-500 outline-none bg-gray-50 focus:bg-white resize-none"
                                placeholder="Catatan tambahan..."></textarea>
                        </div>
                    </div>
                    <div class="mt-8"><button type="submit" name="tambah_pengeluaran"
                            class="w-full px-4 py-3.5 bg-red-500 hover:bg-red-600 text-white font-bold rounded-xl transition shadow-lg shadow-red-500/30">Simpan
                            Pengeluaran</button></div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL EDIT PENGELUARAN                         -->
        <!-- ============================================== -->
        <div id="modal-edit"
            class="fixed inset-0 bg-gray-900/60 z-[60] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl transform scale-95 transition-transform duration-300"
                id="modal-edit-content">
                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-amber-50/50 rounded-t-2xl">
                    <h3 class="text-xl font-bold text-amber-600">Edit Pengeluaran</h3>
                    <button type="button" onclick="tutupModal('modal-edit')"
                        class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition"><svg class="w-6 h-6"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <form action="pengeluaran.php" method="POST" class="p-6">
                    <input type="hidden" name="edit_id" id="input_edit_id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tujuan / Nama
                                Pengeluaran</label>
                            <input type="text" name="edit_nama_pengeluaran" id="input_edit_nama" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal</label>
                                <input type="date" name="edit_tanggal" id="input_edit_tanggal" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Total Biaya (Rp)</label>
                                <input type="number" name="edit_total" id="input_edit_total" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white font-bold text-red-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan</label>
                            <textarea name="edit_keterangan" id="input_edit_keterangan" rows="2"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white resize-none"></textarea>
                        </div>
                    </div>
                    <div class="mt-8"><button type="submit" name="edit_pengeluaran"
                            class="w-full px-4 py-3.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl transition shadow-lg shadow-amber-500/30">Simpan
                            Perubahan</button></div>
                </form>
            </div>
        </div>

    </div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabel-pengeluaran').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            columnDefs: [{
                orderable: false,
                targets: 5
            }],
            order: [
                [0, 'desc']
            ], // Urutkan tanggal terbaru
            pageLength: 10,
        });
    });

    function bukaModal(idModal) {
        document.getElementById(idModal).classList.remove('opacity-0', 'pointer-events-none');
        document.getElementById(idModal + '-content').classList.remove('scale-95');
    }

    function tutupModal(idModal) {
        document.getElementById(idModal + '-content').classList.add('scale-95');
        setTimeout(() => document.getElementById(idModal).classList.add('opacity-0', 'pointer-events-none'), 200);
    }

    function bukaModalEdit(id, nama, total, tanggal, keterangan) {
        document.getElementById('input_edit_id').value = id;
        document.getElementById('input_edit_nama').value = nama;
        document.getElementById('input_edit_total').value = total;
        document.getElementById('input_edit_tanggal').value = tanggal;
        document.getElementById('input_edit_keterangan').value = keterangan;
        bukaModal('modal-edit');
    }

    setTimeout(() => {
        const alertMsg = document.getElementById('alert-msg');
        if (alertMsg) {
            alertMsg.classList.add('opacity-0');
            setTimeout(() => alertMsg.style.display = 'none', 300);
        }
    }, 4000);
</script>

<?php include '../components/footer.php'; ?>