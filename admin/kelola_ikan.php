<?php
// admin/kelola_ikan.php
session_start();
require_once '../koneksi.php';

// ========================================================
// KONFIGURASI HALAMAN
// ========================================================
$halaman = 'ikan';
$judul_halaman = 'Manajemen & Riwayat Stok Ikan';

$upload_dir = __DIR__ . '/../uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

// ========================================================
// 1. PROSES TAMBAH DATA IKAN BARU
// ========================================================
if (isset($_POST['tambah_ikan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_ikan']);
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $satuan = mysqli_real_escape_string($koneksi, $_POST['satuan']);
    
    $tanggal_input = $_POST['tanggal'];
    $tanggal = date('Y-m-d H:i:s', strtotime($tanggal_input));

    $nama_file_gambar = 'default.jpg';

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_size = $_FILES['gambar']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed_ext)) {
            if ($file_size <= 2097152) {
                $nama_file_gambar = time() . '_' . uniqid() . '.' . $file_ext;
                $path_tujuan = $upload_dir . $nama_file_gambar;

                if (!move_uploaded_file($file_tmp, $path_tujuan)) {
                    $_SESSION['pesan_error'] = "Sistem gagal memindahkan foto. Periksa izin folder uploads.";
                }
            } else {
                $_SESSION['pesan_error'] = "Gagal: Ukuran foto terlalu besar! Maksimal 2MB.";
            }
        } else {
            $_SESSION['pesan_error'] = "Gagal: Format foto tidak didukung!";
        }
    }

    if (!isset($_SESSION['pesan_error'])) {
        $query_insert = "INSERT INTO ikan (nama_ikan, harga, stok, satuan, status_aktif, gambar, created_at) 
                         VALUES ('$nama', '$harga', '$stok', '$satuan', 1, '$nama_file_gambar', '$tanggal')";

        if (mysqli_query($koneksi, $query_insert)) {
            $ikan_id_baru = mysqli_insert_id($koneksi);
            mysqli_query($koneksi, "INSERT INTO riwayat_stok (ikan_id, jumlah_tambah, tanggal_tambah, keterangan) 
                                    VALUES ($ikan_id_baru, $stok, '$tanggal', 'Stok Awal (Input Data Baru)')");
            $_SESSION['pesan_sukses'] = "Berhasil! Ikan baru beserta fotonya telah ditambahkan.";
        } else {
            $_SESSION['pesan_error'] = "Gagal menyimpan ke database: " . mysqli_error($koneksi);
        }
    }
    header("Location: kelola_ikan.php");
    exit;
}

// ========================================================
// 2. PROSES EDIT DATA IKAN (UPDATE)
// ========================================================
if (isset($_POST['edit_ikan'])) {
    $id_edit = (int)$_POST['edit_id'];
    $nama_baru = mysqli_real_escape_string($koneksi, $_POST['edit_nama_ikan']);
    $harga_baru = (int)$_POST['edit_harga'];
    $satuan_baru = mysqli_real_escape_string($koneksi, $_POST['edit_satuan']);
    
    $tanggal_input = $_POST['edit_tanggal'];
    $tanggal = date('Y-m-d H:i:s', strtotime($tanggal_input));

    $query_update = "UPDATE ikan SET nama_ikan = '$nama_baru', harga = $harga_baru, satuan = '$satuan_baru', created_at = '$tanggal'";

    if (isset($_FILES['edit_gambar']) && $_FILES['edit_gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['edit_gambar']['tmp_name'];
        $file_name = $_FILES['edit_gambar']['name'];
        $file_size = $_FILES['edit_gambar']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'webp']) && $file_size <= 2097152) {
            $nama_file_gambar_baru = time() . '_edit_' . uniqid() . '.' . $file_ext;
            $path_tujuan = $upload_dir . $nama_file_gambar_baru;

            if (move_uploaded_file($file_tmp, $path_tujuan)) {
                $q_lama = mysqli_query($koneksi, "SELECT gambar FROM ikan WHERE id = $id_edit");
                if ($row = mysqli_fetch_assoc($q_lama)) {
                    $gambar_lama = $row['gambar'];
                    if ($gambar_lama != 'default.jpg' && file_exists($upload_dir . $gambar_lama)) {
                        unlink($upload_dir . $gambar_lama);
                    }
                }
                $query_update .= ", gambar = '$nama_file_gambar_baru'";
            }
        } else {
            $_SESSION['pesan_error'] = "Gagal Edit: Format/Ukuran gambar baru tidak sesuai!";
            header("Location: kelola_ikan.php");
            exit;
        }
    }

    $query_update .= " WHERE id = $id_edit";

    if (mysqli_query($koneksi, $query_update)) {
        mysqli_query($koneksi, "UPDATE riwayat_stok SET tanggal_tambah = '$tanggal' WHERE ikan_id = $id_edit AND keterangan = 'Stok Awal (Input Data Baru)'");
        $_SESSION['pesan_sukses'] = "Data ikan berhasil diperbarui!";
    } else {
        $_SESSION['pesan_error'] = "Gagal memperbarui data: " . mysqli_error($koneksi);
    }
    header("Location: kelola_ikan.php");
    exit;
}

// ========================================================
// 3. PROSES TAMBAH STOK (RESTOCK)
// ========================================================
if (isset($_POST['submit_restock'])) {
    $ikan_id = (int)$_POST['ikan_id_restock'];
    $jumlah_tambah = (int)$_POST['jumlah_tambah'];
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan_restock']);
    
    $tanggal_input = $_POST['tanggal_restock'];
    $tanggal = date('Y-m-d H:i:s', strtotime($tanggal_input));

    if ($jumlah_tambah > 0) {
        mysqli_query($koneksi, "UPDATE ikan SET stok = stok + $jumlah_tambah WHERE id = $ikan_id");
        mysqli_query($koneksi, "INSERT INTO riwayat_stok (ikan_id, jumlah_tambah, tanggal_tambah, keterangan) 
                                VALUES ($ikan_id, $jumlah_tambah, '$tanggal', '$keterangan')");
        $_SESSION['pesan_sukses'] = "Berhasil menambahkan stok baru!";
    }
    header("Location: kelola_ikan.php");
    exit;
}

// ========================================================
// 4. PROSES HAPUS DATA IKAN
// ========================================================
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $q_gambar = mysqli_query($koneksi, "SELECT gambar FROM ikan WHERE id = $id_hapus");

    if ($row = mysqli_fetch_assoc($q_gambar)) {
        $file_path_hapus = $upload_dir . $row['gambar'];
        if ($row['gambar'] != 'default.jpg' && file_exists($file_path_hapus)) {
            unlink($file_path_hapus);
        }
    }

    mysqli_query($koneksi, "DELETE FROM ikan WHERE id = $id_hapus");
    $_SESSION['pesan_sukses'] = "Data ikan beserta fotonya berhasil dihapus bersih!";
    header("Location: kelola_ikan.php");
    exit;
}

// ========================================================
// 5. AMBIL DATA UNTUK DITAMPILKAN
// ========================================================
$query_ikan = mysqli_query($koneksi, "
    SELECT ikan.*, 
    (SELECT MAX(tanggal_tambah) FROM riwayat_stok WHERE ikan_id = ikan.id) as tgl_terakhir_tambah 
    FROM ikan ORDER BY ikan.id DESC
");

$q_riwayat = mysqli_query($koneksi, "SELECT r.*, i.satuan FROM riwayat_stok r JOIN ikan i ON r.ikan_id = i.id ORDER BY r.tanggal_tambah DESC");
$data_riwayat = [];
while ($r = mysqli_fetch_assoc($q_riwayat)) {
    $data_riwayat[] = $r;
}

// ========================================================
// PANGGIL HEADER HTML
// ========================================================
include '../components/header.php';
?>

<!-- Tambahkan Library DataTables CSS di sini -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
    /* Kustomisasi DataTables agar cocok dengan Tailwind */
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

<!-- PANGGIL SIDEBAR KIRI -->
<?php include '../components/sidebar.php'; ?>

<!-- PEMBUNGKUS KONTEN -->
<main class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50">

    <!-- PANGGIL NAVBAR ATAS -->
    <?php include '../components/navbar.php'; ?>

    <!-- AREA KONTEN (SCROLLABLE) -->
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

        <!-- Tabel dengan Card Pembungkus -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div
                class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Daftar Produk Ikan</h2>
                    <p class="text-sm text-gray-500">Gunakan pencarian DataTables untuk mencari produk dengan cepat.</p>
                </div>
                <button onclick="bukaModal('modal-tambah')"
                    class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Ikan Baru
                </button>
            </div>

            <!-- Tabel DataTables (Beri ID id="tabel-ikan") -->
            <div class="overflow-x-auto w-full">
                <table id="tabel-ikan" class="w-full text-left whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                            <th class="p-4 font-semibold w-16 text-center">No</th>
                            <th class="p-4 font-semibold">Produk</th>
                            <th class="p-4 font-semibold text-right">Harga</th>
                            <th class="p-4 font-semibold text-center">Stok Sisa</th>
                            <th class="p-4 font-semibold">Terakhir Ditambah</th>
                            <th class="p-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        <?php if ($query_ikan && mysqli_num_rows($query_ikan) > 0): ?>
                            <?php
                            $no = 1;
                            while ($ikan = mysqli_fetch_assoc($query_ikan)): ?>
                                <tr class="hover:bg-blue-50/30 transition-colors group border-b border-gray-50">
                                    <td class="p-4 text-center text-gray-500 font-medium">#<?php echo $no++; ?></td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-3">
                                            <?php
                                            $src = $ikan['gambar'];
                                            if (!filter_var($src, FILTER_VALIDATE_URL)) $src = '../uploads/' . $src;
                                            ?>
                                            <img src="<?php echo $src; ?>"
                                                onerror="this.src='https://via.placeholder.com/150?text=No+Foto'"
                                                class="w-10 h-10 rounded-lg object-cover bg-gray-100 border border-gray-200 shadow-sm">
                                            <div>
                                                <p class="font-bold text-gray-800 text-sm">
                                                    <?php echo htmlspecialchars($ikan['nama_ikan']); ?></p>
                                                <p class="text-xs text-gray-500">/
                                                    <?php echo htmlspecialchars($ikan['satuan']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Atribut data-order penting untuk fitur sorting harga DataTables yang akurat -->
                                    <td class="p-4 text-right font-bold text-gray-700"
                                        data-order="<?php echo $ikan['harga']; ?>">
                                        <?php echo formatRupiah($ikan['harga']); ?>
                                    </td>

                                    <td class="p-4 text-center" data-order="<?php echo $ikan['stok']; ?>">
                                        <?php if ($ikan['stok'] > 0): ?>
                                            <span
                                                class="inline-flex px-3 py-1 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 shadow-sm">
                                                <?php echo $ikan['stok']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex px-2 py-1 rounded bg-red-50 text-red-600 text-xs font-bold border border-red-100">Habis</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="p-4 text-gray-600 text-sm"
                                        data-order="<?php echo $ikan['tgl_terakhir_tambah'] ? strtotime($ikan['tgl_terakhir_tambah']) : 0; ?>">
                                        <?php
                                        if ($ikan['tgl_terakhir_tambah']) {
                                            echo formatTanggalIndonesia($ikan['tgl_terakhir_tambah']);
                                        } else {
                                            echo '<span class="text-gray-400 italic">Belum ada riwayat</span>';
                                        }
                                        ?>
                                    </td>

                                    <td class="p-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <button
                                                onclick="bukaModalEdit(<?php echo $ikan['id']; ?>, '<?php echo htmlspecialchars($ikan['nama_ikan'], ENT_QUOTES); ?>', <?php echo $ikan['harga']; ?>, '<?php echo $ikan['satuan']; ?>', '<?php echo date('Y-m-d\TH:i', strtotime($ikan['created_at'])); ?>')"
                                                class="p-2 bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg transition"
                                                title="Edit Data Ikan">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </button>

                                            <button
                                                onclick="bukaModalRestock(<?php echo $ikan['id']; ?>, '<?php echo htmlspecialchars($ikan['nama_ikan'], ENT_QUOTES); ?>', '<?php echo $ikan['satuan']; ?>')"
                                                class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white rounded-lg transition"
                                                title="Tambah Stok Baru">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4"></path>
                                                </svg>
                                            </button>

                                            <button
                                                onclick="bukaModalRiwayat(<?php echo $ikan['id']; ?>, '<?php echo htmlspecialchars($ikan['nama_ikan'], ENT_QUOTES); ?>')"
                                                class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg transition"
                                                title="Lihat Riwayat Stok">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </button>

                                            <div class="w-px h-6 bg-gray-200 mx-1"></div>

                                            <a href="kelola_ikan.php?hapus=<?php echo $ikan['id']; ?>"
                                                onclick="return confirm('HAPUS PERMANEN? Data dan Foto ikan akan dihapus dari server!');"
                                                class="p-2 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg transition"
                                                title="Hapus Ikan">
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
        <!-- MODAL 1: TAMBAH IKAN BARU                      -->
        <!-- ============================================== -->
        <div id="modal-tambah"
            class="fixed inset-0 bg-gray-900/60 z-[60] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl transform scale-95 transition-transform duration-300 max-h-[90vh] overflow-y-auto"
                id="modal-tambah-content">
                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-white z-10">
                    <h3 class="text-xl font-bold text-gray-800">Tambah Ikan Baru</h3>
                    <button type="button" onclick="tutupModal('modal-tambah')"
                        class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition"><svg class="w-6 h-6"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <form action="kelola_ikan.php" method="POST" enctype="multipart/form-data" class="p-6">
                    <div class="space-y-4">
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Nama Ikan</label><input
                                type="text" name="nama_ikan" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal & Waktu Ditambahkan</label>
                            <input type="datetime-local" name="tanggal" required value="<?php echo date('Y-m-d\TH:i'); ?>"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Ikan <span
                                    class="text-gray-400 font-normal">(Opsional, Maks 2MB)</span></label>
                            <input name="gambar" type="file" accept=".jpg,.jpeg,.png,.webp"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-semibold text-gray-700 mb-1">Harga (Rp)</label><input
                                    type="number" name="harga" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white">
                            </div>
                            <div><label class="block text-sm font-semibold text-gray-700 mb-1">Stok Awal</label><input
                                    type="number" name="stok" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white text-emerald-600 font-bold">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Satuan</label>
                            <select name="satuan"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white">
                                <option value="kg">Kilogram (kg)</option>
                                <option value="ekor">Ekor</option>
                                <option value="gram">Gram (gr)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-8"><button type="submit" name="tambah_ikan"
                            class="w-full px-4 py-3.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition">Simpan
                            Data & Stok</button></div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL 2: EDIT DATA IKAN                        -->
        <!-- ============================================== -->
        <div id="modal-edit"
            class="fixed inset-0 bg-gray-900/60 z-[60] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl transform scale-95 transition-transform duration-300 max-h-[90vh] overflow-y-auto"
                id="modal-edit-content">
                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-white z-10">
                    <h3 class="text-xl font-bold text-gray-800">Edit Data Ikan</h3>
                    <button type="button" onclick="tutupModal('modal-edit')"
                        class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition"><svg class="w-6 h-6"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>

                <form action="kelola_ikan.php" method="POST" enctype="multipart/form-data" class="p-6">
                    <input type="hidden" name="edit_id" id="input_edit_id">
                    <div class="space-y-4">
                        <div><label class="block text-sm font-semibold text-gray-700 mb-1">Nama Ikan</label><input
                                type="text" name="edit_nama_ikan" id="input_edit_nama" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal & Waktu Ditambahkan</label>
                            <input type="datetime-local" name="edit_tanggal" id="input_edit_tanggal" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Ganti Foto <span
                                    class="text-amber-500 font-normal">(Kosongkan jika tidak ingin ganti)</span></label>
                            <input name="edit_gambar" type="file" accept=".jpg,.jpeg,.png,.webp"
                                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-sm font-semibold text-gray-700 mb-1">Harga (Rp)</label><input
                                    type="number" name="edit_harga" id="input_edit_harga" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Satuan</label>
                                <select name="edit_satuan" id="input_edit_satuan"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-amber-500 outline-none bg-gray-50 focus:bg-white">
                                    <option value="kg">Kilogram (kg)</option>
                                    <option value="ekor">Ekor</option>
                                    <option value="gram">Gram (gr)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6"><button type="submit" name="edit_ikan"
                            class="w-full px-4 py-3.5 bg-amber-500 text-white font-bold rounded-xl hover:bg-amber-600 transition shadow-lg shadow-amber-500/30">Simpan
                            Perubahan</button></div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL 3: FORM RESTOCK                          -->
        <!-- ============================================== -->
        <div id="modal-restock"
            class="fixed inset-0 bg-gray-900/60 z-[70] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white rounded-2xl w-full max-w-sm shadow-2xl transform scale-95 transition-transform duration-300"
                id="modal-restock-content">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-gray-800">Tambah Stok Baru</h3>
                    <button type="button" onclick="tutupModal('modal-restock')"
                        class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition"><svg class="w-6 h-6"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <form action="kelola_ikan.php" method="POST" class="p-6">
                    <input type="hidden" name="ikan_id_restock" id="input_ikan_id_restock">
                    <div class="mb-5 text-center">
                        <p class="text-sm text-gray-500">Menambah stok untuk produk:</p>
                        <h4 id="nama_ikan_restock" class="font-bold text-lg text-blue-600">Nama Ikan</h4>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Ditambahkan</label>
                            <div class="relative">
                                <input type="number" name="jumlah_tambah" required min="1"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 outline-none text-emerald-600 font-bold text-lg text-center"
                                    placeholder="cth. 10">
                                <span id="satuan_restock"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 font-medium">kg</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Keterangan / Catatan</label>
                            <input type="text" name="keterangan_restock"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 outline-none bg-gray-50"
                                placeholder="">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal & Waktu Ditambahkan</label>
                            <input type="datetime-local" name="tanggal_restock" required value="<?php echo date('Y-m-d\TH:i'); ?>"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-emerald-500 outline-none bg-gray-50">
                        </div>
                    </div>
                    <div class="mt-8 flex gap-3">
                        <button type="submit" name="submit_restock"
                            class="w-full px-4 py-3.5 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 transition shadow-lg shadow-emerald-500/30">+
                            Simpan Stok</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- MODAL 4: LIHAT TABEL RIWAYAT                   -->
        <!-- ============================================== -->
        <div id="modal-riwayat"
            class="fixed inset-0 bg-gray-900/60 z-[80] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl transform scale-95 transition-transform duration-300 flex flex-col max-h-[85vh]"
                id="modal-riwayat-content">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Riwayat Penambahan Stok</h3>
                        <p id="nama_ikan_riwayat" class="text-sm text-blue-600 font-semibold"></p>
                    </div>
                    <button type="button" onclick="tutupModal('modal-riwayat')"
                        class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition"><svg class="w-6 h-6"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <div class="overflow-y-auto flex-1 p-6 bg-gray-50">
                    <table
                        class="w-full text-left border-collapse bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
                        <thead class="bg-slate-800 text-white">
                            <tr>
                                <th class="p-3 text-sm font-semibold">Tanggal & Waktu</th>
                                <th class="p-3 text-sm font-semibold text-center">Jumlah Ditambah</th>
                                <th class="p-3 text-sm font-semibold">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="tabel-body-riwayat" class="divide-y divide-gray-100 text-sm"></tbody>
                    </table>
                    <p id="pesan-kosong-riwayat" class="text-center text-gray-500 py-6 hidden">Belum ada riwayat
                        penambahan stok.</p>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- ======================================================== -->
<!-- SCRIPT: JQUERY, DATATABLES, DAN LOGIKA MODAL             -->
<!-- ======================================================== -->

<!-- 1. Tambahkan jQuery dari CDN (Wajib untuk DataTables) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- 2. Tambahkan JS DataTables dari CDN -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
    // INISIALISASI DATATABLES
    $(document).ready(function() {
        $('#tabel-ikan').DataTable({
            // Terjemahkan antarmuka DataTables ke Bahasa Indonesia
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            },
            // Matikan sorting pada kolom Aksi (Kolom terakhir index ke 5)
            columnDefs: [{
                orderable: false,
                targets: 5
            }],
            // Urutkan berdasarkan kolom ID (Index 0) secara Menurun (Terbaru di atas)
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
            lengthMenu: [
                [5, 10, 25, 50, -1],
                [5, 10, 25, 50, "Semua"]
            ]
        });
    });

    const dataRiwayatSemua = <?php echo json_encode($data_riwayat); ?>;

    function bukaModal(idModal) {
        const modal = document.getElementById(idModal);
        const content = document.getElementById(idModal + '-content');
        modal.classList.remove('opacity-0', 'pointer-events-none');
        content.classList.remove('scale-95');
    }

    function tutupModal(idModal) {
        const modal = document.getElementById(idModal);
        const content = document.getElementById(idModal + '-content');
        content.classList.add('scale-95');
        setTimeout(() => modal.classList.add('opacity-0', 'pointer-events-none'), 200);
    }

    function bukaModalEdit(id, nama, harga, satuan, tanggal) {
        document.getElementById('input_edit_id').value = id;
        document.getElementById('input_edit_nama').value = nama;
        document.getElementById('input_edit_harga').value = harga;
        document.getElementById('input_edit_satuan').value = satuan;
        document.getElementById('input_edit_tanggal').value = tanggal;
        bukaModal('modal-edit');
    }

    function bukaModalRestock(id_ikan, nama_ikan, satuan) {
        document.getElementById('input_ikan_id_restock').value = id_ikan;
        document.getElementById('nama_ikan_restock').innerText = nama_ikan;
        document.getElementById('satuan_restock').innerText = satuan;
        bukaModal('modal-restock');
    }

    function bukaModalRiwayat(id_ikan, nama_ikan) {
        document.getElementById('nama_ikan_riwayat').innerText = "Produk: " + nama_ikan;
        const tbody = document.getElementById('tabel-body-riwayat');
        const pesanKosong = document.getElementById('pesan-kosong-riwayat');
        tbody.innerHTML = '';

        const riwayatIkanIni = dataRiwayatSemua.filter(r => r.ikan_id == id_ikan);

        if (riwayatIkanIni.length > 0) {
            pesanKosong.classList.add('hidden');
            riwayatIkanIni.forEach(row => {
                const tgl = new Date(row.tanggal_tambah);
                const formattedDate = tgl.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const tr = `
                    <tr class="hover:bg-blue-50/50">
                        <td class="p-3 text-gray-600">${formattedDate}</td>
                        <td class="p-3 text-center">
                            <span class="text-emerald-600 font-bold bg-emerald-50 px-2 py-1 rounded border border-emerald-100">+ ${row.jumlah_tambah} ${row.satuan}</span>
                        </td>
                        <td class="p-3 text-gray-500 italic">${row.keterangan || '-'}</td>
                    </tr>
                `;
                tbody.innerHTML += tr;
            });
        } else {
            pesanKosong.classList.remove('hidden');
        }
        bukaModal('modal-riwayat');
    }

    setTimeout(() => {
        const alerts = ['alert-msg'];
        alerts.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.classList.add('opacity-0');
                setTimeout(() => el.style.display = 'none', 300);
            }
        });
    }, 4000);
</script>

<?php include '../components/footer.php'; ?>