<?php
// kelola_ikan.php
require_once '../koneksi.php';

// Layout config
$halaman = 'ikan';
$judul_halaman = 'Manajemen Stok Ikan';

// Buat folder uploads jika belum ada (disimpan di luar folder admin)
$upload_dir = '../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ========================================================
// PROSES TAMBAH DATA IKAN (CREATE & UPLOAD FOTO)
// ========================================================
if (isset($_POST['tambah_ikan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_ikan']);
    $harga = (int)$_POST['harga'];
    $stok = (int)$_POST['stok'];
    $satuan = mysqli_real_escape_string($koneksi, $_POST['satuan']);

    $nama_file_gambar = 'default.jpg'; // Gambar default jika tidak upload

    // Cek apakah ada file foto yang diunggah
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $file_tmp = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_size = $_FILES['gambar']['size'];

        // Ambil ekstensi file (jpg, png, dll)
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        // Validasi ekstensi
        if (in_array($file_ext, $allowed_ext)) {
            // Validasi ukuran (Maksimal 2MB = 2097152 bytes)
            if ($file_size <= 2097152) {
                // Buat nama file unik agar tidak bentrok jika nama filenya sama
                $nama_file_gambar = time() . '_' . uniqid() . '.' . $file_ext;
                $tujuan_upload = $upload_dir . $nama_file_gambar;

                // Pindahkan file dari temporary ke folder uploads
                move_uploaded_file($file_tmp, $tujuan_upload);
            } else {
                $_SESSION['pesan_error'] = "Ukuran foto maksimal 2MB!";
            }
        } else {
            $_SESSION['pesan_error'] = "Format foto hanya boleh JPG, JPEG, PNG, atau WEBP!";
        }
    }

    // Jika tidak ada error upload, masukkan ke database
    if (!isset($_SESSION['pesan_error'])) {
        $query_insert = "INSERT INTO ikan (nama_ikan, harga, stok, satuan, status_aktif, gambar) 
                         VALUES ('$nama', '$harga', '$stok', '$satuan', 1, '$nama_file_gambar')";

        if (mysqli_query($koneksi, $query_insert)) {
            $_SESSION['pesan_sukses'] = "Berhasil menambahkan ikan beserta fotonya!";
        } else {
            $_SESSION['pesan_error'] = "Gagal menambah data database: " . mysqli_error($koneksi);
        }
    }

    header("Location: kelola_ikan.php");
    exit;
}

// ========================================================
// PROSES HAPUS DATA (DELETE & HAPUS FILE FISIK)
// ========================================================
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];

    // Cari nama file gambar yang akan dihapus
    $q_gambar = mysqli_query($koneksi, "SELECT gambar FROM ikan WHERE id = $id_hapus");
    if ($row = mysqli_fetch_assoc($q_gambar)) {
        $gambar_lama = $row['gambar'];
        // Jika gambarnya bukan default dan filenya benar-benar ada di folder, hapus filenya
        if ($gambar_lama != 'default.jpg' && file_exists($upload_dir . $gambar_lama)) {
            unlink($upload_dir . $gambar_lama); // Fungsi PHP untuk menghapus file
        }
    }

    // Hapus data dari database
    mysqli_query($koneksi, "DELETE FROM ikan WHERE id = $id_hapus");
    $_SESSION['pesan_sukses'] = "Data ikan dan foto berhasil dihapus!";
    header("Location: kelola_ikan.php");
    exit;
}

// Ambil Semua Data Ikan (READ)
$query_ikan = mysqli_query($koneksi, "SELECT * FROM ikan ORDER BY id DESC");
?>


<!-- Memanggil Sidebar Component -->
<?php include '../components/sidebar.php'; ?>

<main class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50">
    <!-- Memanggil Navbar Component -->
    <?php include '../components/navbar.php'; ?>

    <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 relative">

        <!-- Notifikasi Sukses -->
        <?php if (isset($_SESSION['pesan_sukses'])): ?>
        <div id="alert-sukses"
            class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
                <p class="font-medium text-sm"><?php echo $_SESSION['pesan_sukses']; ?></p>
            </div>
            <button onclick="document.getElementById('alert-sukses').style.display='none'" class="text-green-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <?php unset($_SESSION['pesan_sukses']); ?>
        <?php endif; ?>

        <!-- Notifikasi Error (Jika gagal upload dll) -->
        <?php if (isset($_SESSION['pesan_error'])): ?>
        <div id="alert-error"
            class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"></path>
                </svg>
                <p class="font-medium text-sm"><?php echo $_SESSION['pesan_error']; ?></p>
            </div>
            <button onclick="document.getElementById('alert-error').style.display='none'" class="text-red-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <?php unset($_SESSION['pesan_error']); ?>
        <?php endif; ?>

        <!-- Konten Utama Tabel -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div
                class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/50">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Daftar Produk Ikan</h2>
                </div>
                <button onclick="bukaModalTambah()"
                    class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                        </path>
                    </svg>
                    Tambah Ikan Baru
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                            <th class="p-4 font-semibold w-16 text-center">ID</th>
                            <th class="p-4 font-semibold">Produk</th>
                            <th class="p-4 font-semibold text-right">Harga</th>
                            <th class="p-4 font-semibold text-center">Stok</th>
                            <th class="p-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        <?php if ($query_ikan && mysqli_num_rows($query_ikan) > 0): ?>
                        <?php while ($ikan = mysqli_fetch_assoc($query_ikan)): ?>
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="p-4 text-center text-gray-500 font-medium">#<?php echo $ikan['id']; ?></td>
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <!-- Menampilkan Foto (Cek jika URL eksternal atau lokal) -->
                                    <?php
                                            $src = $ikan['gambar'];
                                            if (!filter_var($src, FILTER_VALIDATE_URL)) {
                                                $src = '../uploads/' . $src;
                                            }
                                            ?>
                                    <img src="<?php echo $src; ?>"
                                        onerror="this.src='https://via.placeholder.com/150?text=No+Foto'"
                                        class="w-12 h-12 rounded-lg object-cover bg-gray-100 shadow-sm border border-gray-200">
                                    <div>
                                        <p class="font-bold text-gray-800 text-base">
                                            <?php echo $ikan['nama_ikan']; ?></p>
                                        <p class="text-xs text-gray-500">Satuan: per <?php echo $ikan['satuan']; ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-right font-bold text-gray-700">
                                <?php echo formatRupiah($ikan['harga']); ?></td>
                            <td class="p-4 text-center">
                                <?php if ($ikan['stok'] > 0): ?>
                                <span
                                    class="inline-flex w-8 h-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 font-bold border border-emerald-100">
                                    <?php echo $ikan['stok']; ?>
                                </span>
                                <?php else: ?>
                                <span
                                    class="inline-flex px-2 py-1 rounded bg-red-50 text-red-600 text-xs font-bold border border-red-100">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-2">
                                    <!-- Tombol Hapus (Ada konfirmasi JS) -->
                                    <a href="kelola_ikan.php?hapus=<?php echo $ikan['id']; ?>"
                                        onclick="return confirm('Yakin ingin menghapus ikan ini beserta fotonya?');"
                                        class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition"
                                        title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">Belum ada data ikan. Silakan
                                tambah data baru.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL TAMBAH IKAN -->
        <div id="modal-tambah"
            class="fixed inset-0 bg-gray-900/60 z-[60] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl transform scale-95 transition-transform duration-300 max-h-[90vh] overflow-y-auto"
                id="modal-content">

                <div
                    class="px-6 py-4 border-b border-gray-100 flex justify-between items-center sticky top-0 bg-white z-10">
                    <h3 class="text-xl font-bold text-gray-800">Tambah Data Ikan</h3>
                    <button type="button" onclick="tutupModalTambah()"
                        class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- PENTING: Tambahkan enctype="multipart/form-data" -->
                <form action="kelola_ikan.php" method="POST" enctype="multipart/form-data" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Ikan <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nama_ikan" required
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white transition-colors"
                                placeholder="Cth: Ikan Bawal Putih">
                        </div>

                        <!-- Input Upload Foto -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Ikan <span
                                    class="text-gray-400 font-normal">(Maks. 2MB)</span></label>
                            <div
                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl bg-gray-50 hover:bg-gray-100 transition relative">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 justify-center">
                                        <label for="gambar"
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 px-1">
                                            <span>Pilih file foto</span>
                                            <!-- Input file diletakkan di sini -->
                                            <input id="gambar" name="gambar" type="file" class="sr-only"
                                                accept=".jpg,.jpeg,.png,.webp" onchange="previewText(this)">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500" id="file-name-preview">PNG, JPG, WEBP hingga
                                        2MB</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Harga (Rp) <span
                                        class="text-red-500">*</span></label>
                                <input type="number" name="harga" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white transition-colors"
                                    placeholder="50000">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Stok Awal <span
                                        class="text-red-500">*</span></label>
                                <input type="number" name="stok" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white transition-colors"
                                    placeholder="10">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Satuan</label>
                            <select name="satuan"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-blue-500 outline-none bg-gray-50 focus:bg-white transition-colors">
                                <option value="kg">Kilogram (kg)</option>
                                <option value="ekor">Ekor</option>
                                <option value="gram">Gram (gr)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button type="submit" name="tambah_ikan"
                            class="w-full px-4 py-3.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                            Simpan Data & Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</main>
<?php include '../components/footer.php' ?>
<script>
// Modal Handlers
function bukaModalTambah() {
    document.getElementById('modal-tambah').classList.remove('opacity-0', 'pointer-events-none');
    document.getElementById('modal-content').classList.remove('scale-95');
}

function tutupModalTambah() {
    document.getElementById('modal-content').classList.add('scale-95');
    setTimeout(() => document.getElementById('modal-tambah').classList.add('opacity-0', 'pointer-events-none'),
        200);
}

// Script untuk mengubah teks saat foto dipilih
function previewText(input) {
    const fileNamePreview = document.getElementById('file-name-preview');
    if (input.files && input.files[0]) {
        fileNamePreview.textContent = "File terpilih: " + input.files[0].name;
        fileNamePreview.classList.add('text-blue-600', 'font-semibold');
    } else {
        fileNamePreview.textContent = "PNG, JPG, WEBP hingga 2MB";
        fileNamePreview.classList.remove('text-blue-600', 'font-semibold');
    }
}
</script>
</body>

</html>