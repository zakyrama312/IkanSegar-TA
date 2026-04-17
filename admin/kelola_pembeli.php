<?php
// admin/pembeli.php
session_start();
require_once '../koneksi.php';

// ========================================================
// KONFIGURASI HALAMAN
// ========================================================
$halaman = 'pembeli';
$judul_halaman = 'Kelola Data Pelanggan';

// ========================================================
// 1. PROSES TAMBAH PEMBELI
// ========================================================
if (isset($_POST['tambah_pembeli'])) {
    $nama_pembeli = mysqli_real_escape_string($koneksi, trim($_POST['nama_pembeli']));
    $no_hp = mysqli_real_escape_string($koneksi, trim($_POST['no_hp']));
    $alamat = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));

    $query_insert = "INSERT INTO pembeli (nama_pembeli, no_hp, alamat) 
                     VALUES ('$nama_pembeli', '$no_hp', '$alamat')";

    if (mysqli_query($koneksi, $query_insert)) {
        $_SESSION['pesan_sukses'] = "Berhasil menambahkan data pelanggan baru!";
    } else {
        $_SESSION['pesan_error'] = "Gagal menambahkan pelanggan: " . mysqli_error($koneksi);
    }
    header("Location: pembeli.php");
    exit;
}

// ========================================================
// 2. PROSES EDIT PEMBELI
// ========================================================
if (isset($_POST['edit_pembeli'])) {
    $id_edit = (int)$_POST['edit_id'];
    $nama_pembeli = mysqli_real_escape_string($koneksi, trim($_POST['edit_nama']));
    $no_hp = mysqli_real_escape_string($koneksi, trim($_POST['edit_no_hp']));
    $alamat = mysqli_real_escape_string($koneksi, trim($_POST['edit_alamat']));

    $query_update = "UPDATE pembeli SET 
                     nama_pembeli = '$nama_pembeli', 
                     no_hp = '$no_hp', 
                     alamat = '$alamat' 
                     WHERE id = $id_edit";

    if (mysqli_query($koneksi, $query_update)) {
        $_SESSION['pesan_sukses'] = "Data pelanggan berhasil diperbarui!";
    } else {
        $_SESSION['pesan_error'] = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
    header("Location: pembeli.php");
    exit;
}

// ========================================================
// 3. PROSES HAPUS PEMBELI
// ========================================================
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM pembeli WHERE id = $id_hapus");
    $_SESSION['pesan_sukses'] = "Data pelanggan berhasil dihapus!";
    header("Location: pembeli.php");
    exit;
}

// ========================================================
// 4. AMBIL DATA PEMBELI
// ========================================================
$query_pembeli = mysqli_query($koneksi, "SELECT * FROM pembeli ORDER BY id DESC");

// Hitung total pelanggan
$total_pelanggan = mysqli_num_rows($query_pembeli);

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

        <!-- Header / Statistik -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Database Pelanggan</h1>
                <p class="text-gray-500 text-sm">Kelola daftar pembeli, nomor WhatsApp, dan alamat pengiriman.</p>
            </div>
            <div class="bg-white px-5 py-3 rounded-xl shadow-sm border border-purple-100 flex items-center gap-4">
                <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Pelanggan</p>
                    <p class="text-xl font-black text-purple-600"><?php echo $total_pelanggan; ?> Orang</p>
                </div>
            </div>
        </div>

        <!-- Tabel Data Pembeli -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div
                class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
                <h2 class="text-lg font-bold text-gray-800">Daftar Pelanggan</h2>
                <button onclick="bukaModal('modal-tambah')"
                    class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition flex items-center justify-center gap-2 shadow-sm shadow-blue-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Pelanggan
                </button>
            </div>

            <div class="overflow-x-auto w-full">
                <table id="tabel-pembeli" class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold text-center w-12">No</th>
                            <th class="p-4 font-semibold">Nama Lengkap</th>
                            <th class="p-4 font-semibold">No. WhatsApp</th>
                            <th class="p-4 font-semibold">Alamat</th>
                            <th class="p-4 font-semibold text-center">Tgl Terdaftar</th>
                            <th class="p-4 font-semibold text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php if ($query_pembeli && mysqli_num_rows($query_pembeli) > 0): $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($query_pembeli)): ?>
                                <tr class="hover:bg-blue-50/30 transition-colors border-b border-gray-50">
                                    <td class="p-4 text-center text-gray-500"><?php echo $no++; ?></td>
                                    <td class="p-4 font-bold text-gray-800">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs">
                                                <?php echo strtoupper(substr($row['nama_pembeli'], 0, 1)); ?>
                                            </div>
                                            <?php echo htmlspecialchars($row['nama_pembeli']); ?>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <?php if (!empty($row['no_hp'])): ?>
                                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $row['no_hp']); ?>"
                                                target="_blank"
                                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-xs font-bold hover:bg-emerald-100 transition">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                                </svg>
                                                <?php echo htmlspecialchars($row['no_hp']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs italic">- Kosong -</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-4 text-gray-500 text-xs whitespace-normal max-w-[200px] line-clamp-2">
                                        <?php echo htmlspecialchars($row['alamat'] ?? '-'); ?>
                                    </td>
                                    <td class="p-4 text-center text-gray-500">
                                        <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td class="p-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button
                                                onclick="bukaModalEdit(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['nama_pembeli'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['no_hp'] ?? '', ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['alamat'] ?? '', ENT_QUOTES); ?>')"
                                                class="p-2 bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg transition"
                                                title="Edit">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </button>
                                            <a href="pembeli.php?hapus=<?php echo $row['id']; ?>"
                                                onclick="return confirm('Yakin ingin menghapus data pelanggan ini?');"
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
        <!-- MODAL TAMBAH PELANGGAN                         -->
        <!-- ============================================== -->
        <div id="modal-tambah"
            class="fixed inset-0 bg-gray-900/60 z-[60] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl transform scale-95 transition-transform duration-300"
                id="modal-tambah-content">
                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-blue-50/50 rounded-t-2xl">
                    <h3 class="text-xl font-bold text-blue-600">Tambah Pelanggan</h3>
                    <button type="button" onclick="tutupModal('modal-tambah')"
                        class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition"><svg class="w-6 h-6"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <form action="pembeli.php" method="POST" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama_pembeli" required placeholder="Cth: Budi Santoso"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">No. WhatsApp <span
                                    class="text-xs font-normal text-gray-400">(Awali dengan 62)</span></label>
                            <input type="text" name="no_hp" placeholder="Cth: 628123456789"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Pengiriman</label>
                            <textarea name="alamat" rows="3"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white resize-none transition-colors"
                                placeholder="Cth: Jl. Ikan Arwana No. 123..."></textarea>
                        </div>
                    </div>
                    <div class="mt-8"><button type="submit" name="tambah_pembeli"
                            class="w-full px-4 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition shadow-lg shadow-blue-500/30">Simpan
                            Data</button></div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL EDIT PELANGGAN                           -->
        <!-- ============================================== -->
        <div id="modal-edit"
            class="fixed inset-0 bg-gray-900/60 z-[60] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl transform scale-95 transition-transform duration-300"
                id="modal-edit-content">
                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-amber-50/50 rounded-t-2xl">
                    <h3 class="text-xl font-bold text-amber-600">Edit Pelanggan</h3>
                    <button type="button" onclick="tutupModal('modal-edit')"
                        class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition"><svg class="w-6 h-6"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <form action="pembeli.php" method="POST" class="p-6">
                    <input type="hidden" name="edit_id" id="input_edit_id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="edit_nama" id="input_edit_nama" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">No. WhatsApp</label>
                            <input type="text" name="edit_no_hp" id="input_edit_no_hp"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white transition-colors">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Pengiriman</label>
                            <textarea name="edit_alamat" id="input_edit_alamat" rows="3"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white resize-none transition-colors"></textarea>
                        </div>
                    </div>
                    <div class="mt-8"><button type="submit" name="edit_pembeli"
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
        $('#tabel-pembeli').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            columnDefs: [{
                orderable: false,
                targets: 5
            }],
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

    function bukaModalEdit(id, nama, no_hp, alamat) {
        document.getElementById('input_edit_id').value = id;
        document.getElementById('input_edit_nama').value = nama;
        document.getElementById('input_edit_no_hp').value = no_hp;
        document.getElementById('input_edit_alamat').value = alamat;
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