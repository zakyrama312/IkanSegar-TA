<?php
// ===================================================================
// FILE: pos.php (Halaman Sistem Kasir / Point of Sale)
// ===================================================================

session_start();
require_once 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['is_logged_in'])) {
    header("Location: login.php");
    exit;
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// ===================================================================
// AUTO FIX DATABASE SCHEMA (Mencegah Error "Unknown column")
// ===================================================================
// 1. Pastikan tabel 'transaksi' punya kolom 'nama_pembeli'
$cek_kolom_trx = mysqli_query($koneksi, "SHOW COLUMNS FROM transaksi LIKE 'nama_pembeli'");
if ($cek_kolom_trx && mysqli_num_rows($cek_kolom_trx) == 0) {
    mysqli_query($koneksi, "ALTER TABLE transaksi ADD nama_pembeli VARCHAR(150) NOT NULL DEFAULT 'Pelanggan Umum' AFTER kode_transaksi");
}

// 2. Pastikan tabel 'pembeli' (CRM) sudah ada untuk menghindari error
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS pembeli (
  id int(11) NOT NULL AUTO_INCREMENT,
  nama_pembeli varchar(150) NOT NULL,
  no_hp varchar(25) DEFAULT NULL,
  alamat text DEFAULT NULL,
  created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");


// ===================================================================
// PROSES CHECKOUT TRANSAKSI & SIMPAN PELANGGAN
// ===================================================================
if (isset($_POST['proses_bayar'])) {
    $keranjang_json = $_POST['data_keranjang'];
    $keranjang = json_decode($keranjang_json, true);

    // Tangkap data pelanggan dari form modal
    $nama_pembeli = mysqli_real_escape_string($koneksi, trim($_POST['nama_pembeli']));
    $no_hp_pembeli = mysqli_real_escape_string($koneksi, trim($_POST['no_hp']));
    $alamat_pembeli = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));

    if (empty($nama_pembeli)) {
        $nama_pembeli = 'Pelanggan Umum';
    }

    // Hilangkan format Rupiah (Rp, titik, spasi)
    $jumlah_bayar_raw = preg_replace('/[^0-9]/', '', $_POST['jumlah_bayar_input']);
    $jumlah_bayar = (int)$jumlah_bayar_raw;

    if (is_array($keranjang) && !empty($keranjang)) {
        // Hitung total belanja riil di Backend
        $total_belanja = 0;
        foreach ($keranjang as $item) {
            $total_belanja += ($item['harga'] * $item['qty']);
        }

        $kembalian = $jumlah_bayar - $total_belanja;

        if ($kembalian >= 0) {
            $tanggal_input = $_POST['tanggal_transaksi'];
            $tanggal = date('Y-m-d H:i:s', strtotime($tanggal_input));
            
            $kode_trx = 'TRX-' . date('Ymd-His', strtotime($tanggal_input));

            // 1. Simpan Transaksi Utama
            $query_trx = "INSERT INTO transaksi (kode_transaksi, nama_pembeli, tanggal_waktu, total_belanja, jumlah_bayar, kembalian, user_id) 
                          VALUES ('$kode_trx', '$nama_pembeli', '$tanggal', $total_belanja, $jumlah_bayar, $kembalian, $user_id)";

            if (mysqli_query($koneksi, $query_trx)) {
                $transaksi_id = mysqli_insert_id($koneksi);
                $items_struk = [];

                // 2. Simpan/Update Data CRM Pembeli (Jika bukan Pelanggan Umum)
                if ($nama_pembeli !== 'Pelanggan Umum') {
                    $cek_pembeli = mysqli_query($koneksi, "SELECT id FROM pembeli WHERE nama_pembeli = '$nama_pembeli'");

                    if ($cek_pembeli && mysqli_num_rows($cek_pembeli) > 0) {
                        // Jika sudah ada, Update No HP & Alamatnya (barangkali kasir memperbarui data)
                        mysqli_query($koneksi, "UPDATE pembeli SET no_hp = '$no_hp_pembeli', alamat = '$alamat_pembeli' WHERE nama_pembeli = '$nama_pembeli'");
                    } else {
                        // Jika belum ada, Insert sebagai pelanggan baru
                        mysqli_query($koneksi, "INSERT INTO pembeli (nama_pembeli, no_hp, alamat) VALUES ('$nama_pembeli', '$no_hp_pembeli', '$alamat_pembeli')");
                    }
                }

                // 3. Simpan Detail Transaksi & Kurangi Stok
                foreach ($keranjang as $item) {
                    $id_ikan = $item['id'];
                    $qty = $item['qty'];
                    $harga = $item['harga'];
                    $subtotal = $qty * $harga;

                    mysqli_query($koneksi, "INSERT INTO detail_transaksi (transaksi_id, ikan_id, qty, harga_satuan, subtotal) 
                                            VALUES ($transaksi_id, $id_ikan, $qty, $harga, $subtotal)");
                    mysqli_query($koneksi, "UPDATE ikan SET stok = stok - $qty WHERE id = $id_ikan");

                    $items_struk[] = [
                        'nama' => $item['nama'],
                        'qty' => $qty,
                        'harga' => $harga,
                        'subtotal' => $subtotal
                    ];
                }

                $_SESSION['pesan_sukses'] = "Transaksi <b>$kode_trx</b> Berhasil! Kembalian: " . formatRupiah($kembalian);

                // Simpan data struk ke session
                $_SESSION['struk_terakhir'] = [
                    'kode' => $kode_trx,
                    'kasir' => isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Kasir',
                    'pembeli' => $nama_pembeli,
                    'no_hp' => $no_hp_pembeli,
                    'alamat' => $alamat_pembeli,
                    'tanggal' => formatTanggalIndonesia($tanggal),
                    'total' => $total_belanja,
                    'bayar' => $jumlah_bayar,
                    'kembali' => $kembalian,
                    'items' => $items_struk
                ];
            } else {
                $_SESSION['pesan_error'] = "Gagal menyimpan transaksi: " . mysqli_error($koneksi);
            }
        } else {
            $_SESSION['pesan_error'] = "Transaksi Gagal: Uang pelanggan kurang dari Total Belanja!";
        }
    } else {
        $_SESSION['pesan_error'] = "Keranjang belanja masih kosong!";
    }

    header("Location: pos.php");
    exit;
}

// ===================================================================
// AMBIL DATA UNTUK TAMPILAN KASIR
// ===================================================================
// 1. Data Ikan
$query_ikan = mysqli_query($koneksi, "SELECT * FROM ikan WHERE status_aktif = 1 ORDER BY id DESC");
$data_ikan_json = [];
while ($row = mysqli_fetch_assoc($query_ikan)) {
    $data_ikan_json[] = $row;
}

// 2. Data Pelanggan Lengkap (Nama, HP, Alamat)
$q_pelanggan = mysqli_query($koneksi, "SELECT * FROM pembeli ORDER BY nama_pembeli ASC");
$data_pelanggan_array = [];
if ($q_pelanggan) {
    while ($pel = mysqli_fetch_assoc($q_pelanggan)) {
        $data_pelanggan_array[] = $pel;
    }
}
$json_pelanggan = json_encode($data_pelanggan_array);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir (POS) - SimabeniPangkah</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Kustomisasi Select2 agar menyatu dengan Tailwind */
        .select2-container--default .select2-selection--single {
            height: 46px !important;
            padding: 8px 12px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.75rem !important;
            /* Tailwind xl */
            background-color: #f9fafb !important;
            /* Tailwind gray-50 */
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px !important;
            right: 12px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1f2937 !important;
            /* Tailwind gray-800 */
            font-weight: 700 !important;
            padding-left: 0 !important;
        }

        .select2-dropdown {
            border: 1px solid #d1d5db !important;
            border-radius: 0.75rem !important;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #area-struk,
            #area-struk * {
                visibility: visible;
                color: #000 !important;
            }

            #area-struk {
                position: absolute;
                left: 0;
                top: 0;
                display: block !important;
                width: 80mm !important;
                padding: 10px !important;
                margin: 0 !important;
                font-family: monospace !important;
            }

            #area-struk .border-dashed {
                border-style: dashed !important;
                border-width: 1px !important;
                border-color: #000 !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800 antialiased h-screen overflow-hidden flex flex-col">

    <?php if (isset($_SESSION['struk_terakhir'])):
        $struk = $_SESSION['struk_terakhir'];
        unset($_SESSION['struk_terakhir']);
    ?>
        <div id="area-struk" class="hidden text-sm bg-white">
            <div class="text-center mb-4">
                <h2 class="font-bold text-2xl uppercase">SimabeniPangkah.</h2>
                <p class="text-xs">Sistem Penjualan & POS</p>
                <div class="border-b border-dashed border-black mt-3 mb-3"></div>
            </div>
            <div class="mb-3 text-xs leading-tight">
                <p>No. Nota : <?php echo $struk['kode']; ?></p>
                <p>Tanggal : <?php echo $struk['tanggal']; ?></p>
                <p>Kasir : <?php echo $struk['kasir']; ?></p>
                <p>Pembeli : <?php echo htmlspecialchars($struk['pembeli']); ?></p>
                <?php if (!empty($struk['no_hp'])): ?>
                    <p>No. HP : <?php echo htmlspecialchars($struk['no_hp']); ?></p>
                <?php endif; ?>
                <?php if (!empty($struk['alamat'])): ?>
                    <p>Alamat : <?php echo htmlspecialchars($struk['alamat']); ?></p>
                <?php endif; ?>
            </div>
            <div class="border-b border-dashed border-black mb-3"></div>
            <table class="w-full text-xs mb-3">
                <?php foreach ($struk['items'] as $item): ?>
                    <tr>
                        <td colspan="3" class="pb-1 font-bold"><?php echo $item['nama']; ?></td>
                    </tr>
                    <tr>
                        <td class="w-10"><?php echo $item['qty']; ?> x</td>
                        <td class="text-right"><?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                        <td class="text-right font-bold"><?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="border-b border-dashed border-black mb-3"></div>
            <div class="flex justify-between font-bold text-sm mb-1">
                <span>TOTAL</span><span><?php echo number_format($struk['total'], 0, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between text-xs mb-1">
                <span>Tunai</span><span><?php echo number_format($struk['bayar'], 0, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between text-xs font-bold mb-4">
                <span>KEMBALI</span><span><?php echo number_format($struk['kembali'], 0, ',', '.'); ?></span>
            </div>
            <div class="text-center text-xs mt-6 mb-4">
                <p>Terima Kasih Atas Kunjungan Anda</p>
            </div>
        </div>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.print();
                }, 500);
            });
        </script>
    <?php endif; ?>

    <header class="bg-slate-900 text-white h-16 flex items-center justify-between px-6 shrink-0 shadow-md z-20">
        <div class="flex items-center gap-4">
            <a href="admin/index.php" class="text-gray-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-xl font-bold tracking-tight">Simabeni<span class="text-blue-400">Pangkah</span> <span
                    class="font-normal text-slate-400">| Kasir</span></h1>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-bold leading-tight">
                    <?php echo isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Kasir Utama'; ?></p>
                <p class="text-xs text-emerald-400">Siap melayani</p>
            </div>
            <div
                class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center font-bold border-2 border-slate-700">
                K</div>
        </div>
    </header>

    <main class="flex-1 flex flex-col lg:flex-row overflow-hidden relative">
        <div class="absolute top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md px-4 pointer-events-none">
            <?php if (isset($_SESSION['pesan_sukses'])): ?>
                <div id="alert-msg"
                    class="bg-emerald-500 text-white px-6 py-4 rounded-xl shadow-xl flex items-center gap-3 animate-fade-in-down pointer-events-auto">
                    <p class="font-medium text-sm"><?php echo $_SESSION['pesan_sukses']; ?></p>
                </div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['pesan_error'])): ?>
                <div id="alert-msg"
                    class="bg-red-500 text-white px-6 py-4 rounded-xl shadow-xl flex items-center gap-3 animate-fade-in-down pointer-events-auto">
                    <p class="font-medium text-sm"><?php echo $_SESSION['pesan_error']; ?></p>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php endif; ?>
        </div>

        <div class="flex-1 bg-gray-50 flex flex-col h-full border-r border-gray-200 z-10">
            <div
                class="p-4 bg-white border-b border-gray-200 shrink-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Menu Produk</h2>
                    <p class="text-xs text-gray-500">Klik produk untuk menambahkan ke keranjang.</p>
                </div>
                <div class="relative w-full sm:w-1/2 lg:w-64 xl:w-80">
                    <input type="text" id="input-pencarian" onkeyup="cariProduk()" placeholder="Cari nama ikan..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border border-gray-300 rounded-xl text-sm font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4" id="grid-produk"></div>
            </div>
        </div>

        <div
            class="w-full lg:w-[400px] xl:w-[450px] bg-white flex flex-col h-[50vh] lg:h-full shrink-0 shadow-lg lg:shadow-none z-20 transition-all relative">
            <div class="p-4 bg-slate-50 border-b border-gray-200 shrink-0 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">Troli Pesanan</h2>
                <button onclick="kosongkanKeranjang()"
                    class="text-xs text-red-500 hover:text-red-700 font-semibold bg-red-50 px-2 py-1 rounded">Kosongkan</button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50/50" id="tempat-keranjang"></div>

            <div
                class="shrink-0 bg-white border-t border-gray-200 p-5 pb-6 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)]">
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between font-extrabold text-xl text-gray-800">
                        <span>Total Bayar</span>
                        <span id="total-bayar-teks" class="text-blue-600">Rp 0</span>
                    </div>
                </div>
                <button type="button" onclick="bukaModalCheckout()" id="btn-modal-checkout" disabled
                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl shadow-lg transition-all flex justify-center items-center gap-2">
                    Lanjut Pembayaran
                </button>
            </div>
        </div>
    </main>

    <div id="modal-checkout"
        class="fixed inset-0 bg-gray-900/60 z-[60] backdrop-blur-sm flex justify-center items-center p-4 opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl transform scale-95 transition-transform duration-300 flex flex-col max-h-[90vh]"
            id="modal-checkout-content">

            <div
                class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 rounded-t-2xl shrink-0">
                <h3 class="text-xl font-bold text-gray-800">Selesaikan Pembayaran</h3>
                <button type="button" onclick="tutupModalCheckout()"
                    class="text-gray-400 hover:text-red-500 transition"><svg class="w-6 h-6" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg></button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 no-scrollbar">
                <form action="pos.php" method="POST" id="form-checkout">
                    <input type="hidden" name="data_keranjang" id="input-keranjang-json">

                    <div class="space-y-4">

                        <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                            <label class="block text-xs font-bold text-blue-700 uppercase tracking-wider mb-2">Data
                                Pelanggan <span class="font-normal capitalize text-blue-500">(Otomatis
                                    Simpan)</span></label>

                            <select name="nama_pembeli" id="select-pembeli" class="w-full" style="width: 100%;">
                                <option value="">Pelanggan Umum (Pilih/Ketik Nama Baru)</option>
                                <?php foreach ($data_pelanggan_array as $pel): ?>
                                    <option value="<?php echo htmlspecialchars($pel['nama_pembeli']); ?>">
                                        <?php echo htmlspecialchars($pel['nama_pembeli']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="grid grid-cols-1 gap-3 mt-3">
                                <div>
                                    <input type="text" name="no_hp" id="input-hp-pelanggan"
                                        placeholder="No. WhatsApp (Opsional)"
                                        class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <textarea name="alamat" id="input-alamat-pelanggan" rows="2"
                                        placeholder="Alamat (Opsional)"
                                        class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggal Transaksi</label>
                            <input type="datetime-local" name="tanggal_transaksi" required value="<?php echo date('Y-m-d\TH:i'); ?>"
                                   class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                        </div>

                        <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-100 text-center">
                            <p class="text-sm font-semibold text-emerald-800 mb-1">Total Harus Dibayar</p>
                            <p class="text-3xl font-black text-emerald-600" id="modal-total-rp">Rp 0</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Uang
                                Diterima (Rp)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-400">Rp</span>
                                <input type="text" id="input-uang" name="jumlah_bayar_input" required autocomplete="off"
                                    class="w-full pl-10 pr-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl font-bold text-xl text-gray-800 focus:outline-none focus:border-emerald-500 focus:ring-0 transition text-center"
                                    placeholder="0" oninput="hitungKembalian(this.value)">
                            </div>
                        </div>

                        <div class="flex justify-between items-center px-2 pt-2 border-t border-gray-100">
                            <span class="text-sm font-bold text-gray-600">Kembalian:</span>
                            <span id="teks-kembalian" class="font-extrabold text-xl text-gray-400">Rp 0</span>
                        </div>

                    </div>
                </form>
            </div>

            <div class="p-6 border-t border-gray-100 shrink-0 bg-white rounded-b-2xl">
                <button type="submit" form="form-checkout" name="proses_bayar" id="btn-proses-final" disabled
                    class="w-full bg-emerald-500 hover:bg-emerald-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl shadow-lg transition-all active:scale-[0.98] flex justify-center items-center gap-2">
                    Simpan & Cetak Struk
                </button>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const dbIkan = <?php echo json_encode($data_ikan_json); ?>;

        // Data master pelanggan dari PHP ke Javascript untuk Auto-fill
        const dbPelanggan = <?php echo $json_pelanggan; ?>;

        let keranjang = [];
        let totalHargaState = 0;

        const formatRp = (angka) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(angka);
        };

        // INISIALISASI SELECT2 & LOGIKA AUTO-FILL
        $(document).ready(function() {
            $('#select-pembeli').select2({
                tags: true, // Memungkinkan user mengetik teks bebas (Nama Baru)
                placeholder: "Cari atau ketik nama pelanggan...",
                allowClear: true,
                dropdownParent: $(
                    '#modal-checkout'), // Penting agar dropdown tidak sembunyi di belakang modal
                minimumInputLength: 1, // FITUR BARU: Hanya munculkan dropdown jika sudah mengetik minimal 1 huruf
                language: {
                    inputTooShort: function() {
                        return "Mulai ketik nama pelanggan...";
                    },
                    noResults: function() {
                        return "Tekan Enter untuk simpan pelanggan baru.";
                    }
                }
            });

            // Event saat nama pelanggan dipilih atau diketik
            $('#select-pembeli').on('change', function() {
                let namaYangDipilih = $(this).val();

                // Cari di array database pelanggan
                let pelangganDitemukan = dbPelanggan.find(p => p.nama_pembeli === namaYangDipilih);

                if (pelangganDitemukan) {
                    // AUTO-FILL JIKA DATA SUDAH ADA
                    $('#input-hp-pelanggan').val(pelangganDitemukan.no_hp);
                    $('#input-alamat-pelanggan').val(pelangganDitemukan.alamat);
                } else {
                    // KOSONGKAN JIKA INI ADALAH KETIKAN NAMA BARU
                    $('#input-hp-pelanggan').val('');
                    $('#input-alamat-pelanggan').val('');
                }
            });
        });

        // ================== LOGIKA KERANJANG (Sama seperti sebelumnya) ==================
        window.cariProduk = function() {
            const kataKunci = document.getElementById('input-pencarian').value.toLowerCase();
            const kartuProduk = document.querySelectorAll('.kartu-produk');
            kartuProduk.forEach(kartu => {
                const namaIkan = kartu.getAttribute('data-nama').toLowerCase();
                kartu.style.display = namaIkan.includes(kataKunci) ? 'flex' : 'none';
            });
        }

        function renderProduk() {
            const grid = document.getElementById('grid-produk');
            grid.innerHTML = '';
            dbIkan.forEach(ikan => {
                const itemDiKeranjang = keranjang.find(k => k.id == ikan.id);
                const qtyDiKeranjang = itemDiKeranjang ? itemDiKeranjang.qty : 0;
                const stokTersedia = ikan.stok - qtyDiKeranjang;
                const isHabis = stokTersedia <= 0;
                let gambarSrc = ikan.gambar.startsWith('http') ? ikan.gambar : 'uploads/' + ikan.gambar;

                grid.innerHTML += `
                    <div onclick="${isHabis ? '' : `tambahItem(${ikan.id})`}" data-nama="${ikan.nama_ikan}"
                         class="kartu-produk bg-white rounded-xl shadow-sm border ${isHabis ? 'border-gray-200 opacity-60 cursor-not-allowed' : 'border-gray-200 hover:border-blue-500 cursor-pointer hover:shadow-md'} overflow-hidden transition-all flex flex-col relative group">
                        <div class="h-28 bg-gray-100 overflow-hidden relative">
                            <img src="${gambarSrc}" onerror="this.src='https://via.placeholder.com/300?text=Ikan'" class="w-full h-full object-cover ${isHabis ? 'grayscale' : 'group-hover:scale-110 transition-transform duration-300'}">
                            <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/70 to-transparent p-2"><span class="text-xs font-bold text-white">Stok: ${ikan.stok} ${ikan.satuan}</span></div>
                        </div>
                        <div class="p-3 flex flex-col flex-grow">
                            <h3 class="text-sm font-bold text-gray-800 line-clamp-2">${ikan.nama_ikan}</h3>
                            <p class="text-blue-600 font-extrabold text-sm mt-auto">${formatRp(ikan.harga)}</p>
                        </div>
                    </div>`;
            });
            cariProduk();
        }

        function tambahItem(id) {
            const produk = dbIkan.find(i => i.id == id);
            const existingItem = keranjang.find(k => k.id == id);
            if (existingItem) {
                if (existingItem.qty < produk.stok) existingItem.qty++;
            } else {
                keranjang.push({
                    id: produk.id,
                    nama: produk.nama_ikan,
                    harga: parseInt(produk.harga),
                    qty: 1,
                    satuan: produk.satuan,
                    gambar: produk.gambar
                });
            }
            updateUI();
        }

        function ubahQty(index, perubahan) {
            const item = keranjang[index];
            const produk = dbIkan.find(i => i.id == item.id);
            const qtyBaru = item.qty + perubahan;
            if (qtyBaru <= 0) keranjang.splice(index, 1);
            else if (qtyBaru <= produk.stok) item.qty = qtyBaru;
            updateUI();
        }

        window.setQtyManualRealtime = function(index, el) {
            const item = keranjang[index];
            const produk = dbIkan.find(i => i.id == item.id);
            let qtyBaru = parseInt(el.value);
            if (isNaN(qtyBaru) || qtyBaru < 0) item.qty = 0;
            else if (qtyBaru > produk.stok) {
                el.value = produk.stok;
                item.qty = parseInt(produk.stok);
                alert('Stok tidak mencukupi!');
            } else item.qty = qtyBaru;
            kalkulasiHargaDinamis();
        }

        window.cekEmptyQty = function(index, el) {
            if (keranjang[index].qty === 0 || el.value === '') {
                keranjang.splice(index, 1);
                updateUI();
            }
        }

        function kalkulasiHargaDinamis() {
            let total = 0;
            keranjang.forEach((item, index) => {
                const subtotal = item.qty * item.harga;
                total += subtotal;
                let elSubtotal = document.getElementById(`subtotal-item-${index}`);
                if (elSubtotal) elSubtotal.innerText = formatRp(subtotal);
            });
            totalHargaState = total;
            document.getElementById('total-bayar-teks').innerText = formatRp(total);
            document.getElementById('input-keranjang-json').value = JSON.stringify(keranjang);
            hitungKembalian(document.getElementById('input-uang').value);
            renderProduk();
        }

        window.kosongkanKeranjang = function() {
            keranjang = [];
            document.getElementById('input-uang').value = '';
            updateUI();
        }

        function renderKeranjang() {
            const tempat = document.getElementById('tempat-keranjang');
            tempat.innerHTML = '';
            let total = 0;

            if (keranjang.length === 0) {
                tempat.innerHTML =
                    `<div class="h-full flex flex-col items-center justify-center text-gray-400 pt-10"><p>Troli masih kosong</p></div>`;
                document.getElementById('btn-modal-checkout').disabled = true;
            } else {
                document.getElementById('btn-modal-checkout').disabled = false;
                keranjang.forEach((item, index) => {
                    const subtotal = item.qty * item.harga;
                    total += subtotal;
                    let imgUrl = item.gambar.startsWith('http') ? item.gambar : 'uploads/' + item.gambar;

                    tempat.innerHTML += `
                        <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex gap-3">
                            <img src="${imgUrl}" onerror="this.src='https://via.placeholder.com/100'" class="w-12 h-12 rounded-lg object-cover bg-gray-100">
                            <div class="flex-grow">
                                <h4 class="text-sm font-bold text-gray-800 line-clamp-1">${item.nama}</h4>
                                <p class="text-xs text-gray-500">${formatRp(item.harga)} /${item.satuan}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1.5">
                                <div id="subtotal-item-${index}" class="font-bold text-gray-800 text-sm">${formatRp(subtotal)}</div>
                                <div class="flex items-center gap-1 bg-gray-50 rounded-lg p-1 border border-gray-200">
                                    <button type="button" onclick="ubahQty(${index}, -1)" class="w-6 h-6 flex justify-center items-center rounded bg-white hover:bg-gray-200">-</button>
                                    <input type="number" value="${item.qty}" oninput="setQtyManualRealtime(${index}, this)" onblur="cekEmptyQty(${index}, this)" class="w-10 text-center text-sm font-bold text-gray-800 bg-transparent border-none focus:ring-0 focus:outline-none p-0 m-0">
                                    <button type="button" onclick="ubahQty(${index}, 1)" class="w-6 h-6 flex justify-center items-center rounded bg-white hover:bg-gray-200">+</button>
                                </div>
                            </div>
                        </div>`;
                });
            }
            totalHargaState = total;
            document.getElementById('total-bayar-teks').innerText = formatRp(total);
            document.getElementById('input-keranjang-json').value = JSON.stringify(keranjang);
            hitungKembalian(document.getElementById('input-uang').value);
        }

        // ================== LOGIKA MODAL CHECKOUT ==================
        window.bukaModalCheckout = function() {
            if (keranjang.length === 0) return;
            document.getElementById('modal-total-rp').innerText = formatRp(totalHargaState);
            document.getElementById('modal-checkout').classList.remove('opacity-0', 'pointer-events-none');
            document.getElementById('modal-checkout-content').classList.remove('scale-95');

            // Fokus otomatis ke pencarian nama/pelanggan
            setTimeout(() => {
                $('#select-pembeli').select2('open');
            }, 300);
        }

        window.tutupModalCheckout = function() {
            document.getElementById('modal-checkout-content').classList.add('scale-95');
            setTimeout(() => {
                document.getElementById('modal-checkout').classList.add('opacity-0', 'pointer-events-none');
            }, 200);
        }

        window.hitungKembalian = function(nilaiInput) {
            let angka = nilaiInput.replace(/[^,\d]/g, '').toString();
            let inputEl = document.getElementById('input-uang');
            if (angka) inputEl.value = new Intl.NumberFormat('id-ID').format(angka);
            else inputEl.value = '';

            let uangBayar = parseInt(angka) || 0;
            let btnProses = document.getElementById('btn-proses-final');
            let teksKembalian = document.getElementById('teks-kembalian');

            if (keranjang.length > 0 && uangBayar >= totalHargaState) {
                let kembalian = uangBayar - totalHargaState;
                teksKembalian.innerText = formatRp(kembalian);
                teksKembalian.classList.replace('text-gray-400', 'text-emerald-500');
                teksKembalian.classList.replace('text-red-500', 'text-emerald-500');
                btnProses.disabled = false;
            } else {
                teksKembalian.innerText = "Uang Kurang!";
                teksKembalian.classList.replace('text-emerald-500', 'text-red-500');
                teksKembalian.classList.replace('text-gray-400', 'text-red-500');
                btnProses.disabled = true;
            }
        }

        function updateUI() {
            renderKeranjang();
            renderProduk();
        }

        setTimeout(() => {
            const alert = document.getElementById('alert-msg');
            if (alert) {
                alert.classList.add('opacity-0');
                setTimeout(() => alert.remove(), 500);
            }
        }, 4000);

        renderProduk();
        renderKeranjang();
    </script>
</body>

</html>