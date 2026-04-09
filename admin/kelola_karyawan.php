<?php
// admin/kelola_karyawan.php
session_start();
require_once '../koneksi.php';

// ========================================================
// KONFIGURASI HALAMAN
// ========================================================
$halaman = 'karyawan'; // Penanda menu aktif di sidebar
$judul_halaman = 'Manajemen Data Karyawan';

// ========================================================
// 1. PROSES TAMBAH KARYAWAN BARU
// ========================================================
if (isset($_POST['tambah_karyawan'])) {
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, strtolower(trim($_POST['username'])));
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);

    // Enkripsi password menggunakan Bcrypt
    $password_raw = $_POST['password'];
    $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);

    // Cek apakah username sudah ada di database
    $cek_user = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username'");

    if (mysqli_num_rows($cek_user) > 0) {
        $_SESSION['pesan_error'] = "Gagal: Username '$username' sudah terdaftar! Gunakan username lain.";
    } else {
        $query_insert = "INSERT INTO users (nama_lengkap, username, password, role) 
                         VALUES ('$nama_lengkap', '$username', '$password_hash', '$role')";

        if (mysqli_query($koneksi, $query_insert)) {
            $_SESSION['pesan_sukses'] = "Berhasil! Karyawan baru telah ditambahkan.";
        } else {
            $_SESSION['pesan_error'] = "Gagal menyimpan ke database: " . mysqli_error($koneksi);
        }
    }
    header("Location: kelola_karyawan.php");
    exit;
}

// ========================================================
// 2. PROSES EDIT DATA KARYAWAN
// ========================================================
if (isset($_POST['edit_karyawan'])) {
    $id_edit = (int)$_POST['edit_id'];
    $nama_lengkap_baru = mysqli_real_escape_string($koneksi, $_POST['edit_nama_lengkap']);
    $username_baru = mysqli_real_escape_string($koneksi, strtolower(trim($_POST['edit_username'])));
    $role_baru = mysqli_real_escape_string($koneksi, $_POST['edit_role']);

    // Cek apakah username baru dipakai oleh user LAIN
    $cek_user = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username_baru' AND id != $id_edit");

    if (mysqli_num_rows($cek_user) > 0) {
        $_SESSION['pesan_error'] = "Gagal Edit: Username '$username_baru' sudah dipakai oleh karyawan lain!";
    } else {
        // Jika password diisi, maka update passwordnya juga
        if (!empty($_POST['edit_password'])) {
            $password_hash_baru = password_hash($_POST['edit_password'], PASSWORD_DEFAULT);
            $query_update = "UPDATE users SET nama_lengkap = '$nama_lengkap_baru', username = '$username_baru', role = '$role_baru', password = '$password_hash_baru' WHERE id = $id_edit";
        } else {
            // Jika password kosong, jangan update kolom password
            $query_update = "UPDATE users SET nama_lengkap = '$nama_lengkap_baru', username = '$username_baru', role = '$role_baru' WHERE id = $id_edit";
        }

        if (mysqli_query($koneksi, $query_update)) {
            $_SESSION['pesan_sukses'] = "Data karyawan berhasil diperbarui!";
        } else {
            $_SESSION['pesan_error'] = "Gagal memperbarui data: " . mysqli_error($koneksi);
        }
    }
    header("Location: kelola_karyawan.php");
    exit;
}

// ========================================================
// 3. PROSES HAPUS KARYAWAN
// ========================================================
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];

    // Proteksi: Mencegah menghapus diri sendiri yang sedang login
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $id_hapus) {
        $_SESSION['pesan_error'] = "Gagal: Anda tidak bisa menghapus akun Anda sendiri saat sedang login!";
    } else {
        // Cek apakah user ini punya riwayat di tabel transaksi (mencegah error foreign key jika ada nota atas nama kasir ini)
        $cek_trx = mysqli_query($koneksi, "SELECT id FROM transaksi WHERE user_id = $id_hapus LIMIT 1");
        if (mysqli_num_rows($cek_trx) > 0) {
            $_SESSION['pesan_error'] = "Gagal: Karyawan ini tidak bisa dihapus karena sudah memiliki riwayat melayani transaksi penjualan.";
        } else {
            mysqli_query($koneksi, "DELETE FROM users WHERE id = $id_hapus");
            $_SESSION['pesan_sukses'] = "Akun karyawan berhasil dihapus!";
        }
    }
    header("Location: kelola_karyawan.php");
    exit;
}

// ========================================================
// 4. AMBIL DATA UNTUK DITAMPILKAN
// ========================================================
$query_karyawan = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id ASC");

// ========================================================
// PANGGIL HEADER HTML
// ========================================================
include '../components/header.php';
?>

<!-- Tambahkan Library DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
    /* Kustomisasi DataTables ala Tailwind */
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

<!-- PEMBUNGKUS KONTEN -->
<main class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50">

    <!-- PANGGIL NAVBAR ATAS -->
    <?php include '../components/navbar.php'; ?>

    <!-- AREA KONTEN -->
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

        <?php if (isset($_SESSION['pesan_error'])): ?>
            <div id="alert-msg"
                class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm animate-fade-in-down">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <p class="font-medium text-sm"><?php echo $_SESSION['pesan_error']; ?></p>
                </div>
                <button onclick="this.parentElement.style.display='none'" class="text-red-500"><svg class="w-5 h-5"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg></button>
            </div>
            <?php unset($_SESSION['pesan_error']); ?>
        <?php endif; ?>

        <!-- Tabel Data Karyawan -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div
                class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Daftar Karyawan & Hak Akses</h2>
                    <p class="text-sm text-gray-500">Kelola akun admin dan kasir untuk mengakses sistem POS.</p>
                </div>
                <button onclick="bukaModal('modal-tambah')"
                    class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                        </path>
                    </svg>
                    Tambah Karyawan
                </button>
            </div>

            <div class="overflow-x-auto w-full">
                <table id="tabel-karyawan" class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold w-16 text-center">ID</th>
                            <th class="p-4 font-semibold">Nama Lengkap</th>
                            <th class="p-4 font-semibold">Username Login</th>
                            <th class="p-4 font-semibold text-center">Role / Akses</th>
                            <th class="p-4 font-semibold">Tgl Terdaftar</th>
                            <th class="p-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php if ($query_karyawan && mysqli_num_rows($query_karyawan) > 0): ?>
                            <?php while ($user = mysqli_fetch_assoc($query_karyawan)): ?>
                                <tr class="hover:bg-blue-50/30 transition-colors group border-b border-gray-50">
                                    <td class="p-4 text-center text-gray-500 font-medium">#<?php echo $user['id']; ?></td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <!-- Bikin Avatar Berdasarkan Nama (Inisial) -->
                                            <div
                                                class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white shadow-sm
                                            <?php echo ($user['role'] == 'admin') ? 'bg-purple-500' : 'bg-blue-500'; ?>">
                                                <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                                            </div>
                                            <p class="font-bold text-gray-800 text-base">
                                                <?php echo htmlspecialchars($user['nama_lengkap']); ?></p>
                                        </div>
                                    </td>
                                    <td class="p-4 font-medium text-gray-600">
                                        @<?php echo htmlspecialchars($user['username']); ?></td>

                                    <td class="p-4 text-center">
                                        <?php if ($user['role'] == 'admin'): ?>
                                            <span
                                                class="inline-flex px-3 py-1 items-center gap-1.5 rounded-full bg-purple-50 text-purple-700 font-bold border border-purple-200 text-xs">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                                Administrator
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex px-3 py-1 items-center gap-1.5 rounded-full bg-blue-50 text-blue-700 font-bold border border-blue-200 text-xs">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd">
                                                    </path>
                                                </svg>
                                                Kasir POS
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="p-4 text-gray-500 text-sm"
                                        data-order="<?php echo strtotime($user['created_at']); ?>">
                                        <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                                    </td>

                                    <td class="p-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Tombol Edit -->
                                            <button
                                                onclick="bukaModalEdit(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['nama_lengkap'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?>', '<?php echo $user['role']; ?>')"
                                                class="p-2 bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg transition"
                                                title="Edit Karyawan">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </button>

                                            <div class="w-px h-6 bg-gray-200 mx-1"></div>

                                            <!-- Tombol Hapus -->
                                            <a href="kelola_karyawan.php?hapus=<?php echo $user['id']; ?>"
                                                onclick="return confirm('Hapus Karyawan ini? Ia tidak akan bisa login lagi!');"
                                                class="p-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg transition"
                                                title="Hapus Karyawan">
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
        <!-- MODAL 1: TAMBAH KARYAWAN                       -->
        <!-- ============================================== -->
        <div id="modal-tambah"
            class="fixed inset-0 bg-gray-900/60 z-[60] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl transform scale-95 transition-transform duration-300 max-h-[90vh] overflow-y-auto"
                id="modal-tambah-content">
                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-white z-10">
                    <h3 class="text-xl font-bold text-gray-800">Tambah Karyawan Baru</h3>
                    <button type="button" onclick="tutupModal('modal-tambah')"
                        class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition"><svg class="w-6 h-6"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <form action="kelola_karyawan.php" method="POST" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" required placeholder="Cth: Budi Santoso"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Username Login</label>
                                <input type="text" name="username" required placeholder="budi123"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white lowercase">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                                <input type="password" name="password" required placeholder="Minimal 6 karakter"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Hak Akses (Role)</label>
                            <select name="role"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white">
                                <option value="kasir">Kasir POS (Hanya melayani transaksi)</option>
                                <option value="admin">Administrator (Akses penuh Kelola Ikan)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-8"><button type="submit" name="tambah_karyawan"
                            class="w-full px-4 py-3.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">Buat
                            Akun Karyawan</button></div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL 2: EDIT DATA KARYAWAN                    -->
        <!-- ============================================== -->
        <div id="modal-edit"
            class="fixed inset-0 bg-gray-900/60 z-[60] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl transform scale-95 transition-transform duration-300 max-h-[90vh] overflow-y-auto"
                id="modal-edit-content">
                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-white z-10">
                    <h3 class="text-xl font-bold text-gray-800">Edit Data Karyawan</h3>
                    <button type="button" onclick="tutupModal('modal-edit')"
                        class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition"><svg class="w-6 h-6"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <form action="kelola_karyawan.php" method="POST" class="p-6">
                    <input type="hidden" name="edit_id" id="input_edit_id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="edit_nama_lengkap" id="input_edit_nama" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Username Login</label>
                                <input type="text" name="edit_username" id="input_edit_username" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white lowercase">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Ganti Password</label>
                                <input type="password" name="edit_password" placeholder="(Kosongkan jika tidak diganti)"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white placeholder-gray-400 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Hak Akses (Role)</label>
                            <select name="edit_role" id="input_edit_role"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white">
                                <option value="kasir">Kasir POS (Hanya melayani transaksi)</option>
                                <option value="admin">Administrator (Akses penuh Kelola Ikan)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-8"><button type="submit" name="edit_karyawan"
                            class="w-full px-4 py-3.5 bg-amber-500 text-white font-bold rounded-xl hover:bg-amber-600 transition shadow-lg shadow-amber-500/30">Simpan
                            Perubahan</button></div>
                </form>
            </div>
        </div>

    </div>
</main>

<!-- JQUERY & DATATABLES JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tabel-karyawan').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
            },
            columnDefs: [{
                orderable: false,
                targets: 5
            }], // Matikan sort untuk tombol aksi
            order: [
                [0, 'desc']
            ], // Urutkan ID terbaru
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

    function bukaModalEdit(id, nama, username, role) {
        document.getElementById('input_edit_id').value = id;
        document.getElementById('input_edit_nama').value = nama;
        document.getElementById('input_edit_username').value = username;
        document.getElementById('input_edit_role').value = role;
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