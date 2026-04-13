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
// PROSES CHECKOUT TRANSAKSI
// ===================================================================
if (isset($_POST['proses_bayar'])) {
    $keranjang_json = $_POST['data_keranjang'];
    $keranjang = json_decode($keranjang_json, true);

    // Tangkap data nama pembeli (Jika kosong, set sebagai 'Pelanggan Umum')
    $nama_pembeli = mysqli_real_escape_string($koneksi, trim($_POST['nama_pembeli']));
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
            $kode_trx = 'TRX-' . date('Ymd-His');

            // Insert data transaksi beserta NAMA PEMBELI
            $query_trx = "INSERT INTO transaksi (kode_transaksi, nama_pembeli, total_belanja, jumlah_bayar, kembalian, user_id) 
                          VALUES ('$kode_trx', '$nama_pembeli', $total_belanja, $jumlah_bayar, $kembalian, $user_id)";

            if (mysqli_query($koneksi, $query_trx)) {
                $transaksi_id = mysqli_insert_id($koneksi);
                $items_struk = []; // Array untuk menampung detail cetak struk

                foreach ($keranjang as $item) {
                    $id_ikan = $item['id'];
                    $qty = $item['qty'];
                    $harga = $item['harga'];
                    $subtotal = $qty * $harga;

                    mysqli_query($koneksi, "INSERT INTO detail_transaksi (transaksi_id, ikan_id, qty, harga_satuan, subtotal) 
                                            VALUES ($transaksi_id, $id_ikan, $qty, $harga, $subtotal)");
                    mysqli_query($koneksi, "UPDATE ikan SET stok = stok - $qty WHERE id = $id_ikan");

                    // Simpan ke array struk
                    $items_struk[] = [
                        'nama' => $item['nama'],
                        'qty' => $qty,
                        'harga' => $harga,
                        'subtotal' => $subtotal
                    ];
                }

                $_SESSION['pesan_sukses'] = "Transaksi <b>$kode_trx</b> Berhasil! Kembalian: " . formatRupiah($kembalian);

                // Simpan data struk ke session untuk diprint di halaman
                $_SESSION['struk_terakhir'] = [
                    'kode' => $kode_trx,
                    'kasir' => isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Kasir',
                    'pembeli' => $nama_pembeli,
                    'tanggal' => date('d M Y H:i:s'),
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

// Ambil data produk Ikan
$query_ikan = mysqli_query($koneksi, "SELECT * FROM ikan WHERE status_aktif = 1 ORDER BY id DESC");
$data_ikan_json = [];
while ($row = mysqli_fetch_assoc($query_ikan)) {
    $data_ikan_json[] = $row;
}

// Ambil data riwayat pelanggan untuk fitur Auto-suggest (Datalist)
$q_pelanggan = mysqli_query($koneksi, "SELECT DISTINCT nama_pembeli FROM transaksi WHERE nama_pembeli != 'Pelanggan Umum' AND nama_pembeli != '' ORDER BY nama_pembeli ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir (POS) - SimabeniPangkah</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

        /* Sembunyikan panah default input number */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* ========================================== */
        /* CSS KHUSUS UNTUK CETAK STRUK THERMAL      */
        /* ========================================== */
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
                /* Standar struk kasir 80mm */
                padding: 10px !important;
                margin: 0 !important;
                font-family: monospace !important;
            }

            /* Hilangkan dekorasi Tailwind saat cetak */
            #area-struk .border-dashed {
                border-style: dashed !important;
                border-width: 1px !important;
                border-color: #000 !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800 antialiased h-screen overflow-hidden flex flex-col">

    <!-- AREA STRUK TERSEMBUNYI (Hanya muncul saat print) -->
    <?php if (isset($_SESSION['struk_terakhir'])):
        $struk = $_SESSION['struk_terakhir'];
        unset($_SESSION['struk_terakhir']); // Langsung hapus setelah di-render agar tidak print terus menerus
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
                <span>TOTAL</span>
                <span><?php echo number_format($struk['total'], 0, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between text-xs mb-1">
                <span>Tunai</span>
                <span><?php echo number_format($struk['bayar'], 0, ',', '.'); ?></span>
            </div>
            <div class="flex justify-between text-xs font-bold mb-4">
                <span>KEMBALI</span>
                <span><?php echo number_format($struk['kembali'], 0, ',', '.'); ?></span>
            </div>
            <div class="text-center text-xs mt-6 mb-4">
                <p>Terima Kasih Atas Kunjungan Anda</p>
                <p class="mt-1">Barang yang dibeli tidak dapat ditukar/dikembalikan</p>
            </div>
        </div>

        <!-- Script Pemicu Print Otomatis -->
        <script>
            window.addEventListener('DOMContentLoaded', (event) => {
                setTimeout(() => {
                    window.print();
                }, 500); // Tunggu sebentar agar halaman termuat sempurna lalu print
            });
        </script>
    <?php endif; ?>
    <!-- AKHIR AREA STRUK -->

    <header class="bg-slate-900 text-white h-16 flex items-center justify-between px-6 shrink-0 shadow-md z-20">
        <div class="flex items-center gap-4">
            <a href="admin/index.php" class="text-gray-400 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-xl font-bold tracking-tight">Simabeni<span class="text-blue-400">Pangkah</span> <span
                    class="font-normal text-slate-400">| POS System</span></h1>
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
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="font-medium text-sm"><?php echo $_SESSION['pesan_sukses']; ?></p>
                </div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['pesan_error'])): ?>
                <div id="alert-msg"
                    class="bg-red-500 text-white px-6 py-4 rounded-xl shadow-xl flex items-center gap-3 animate-fade-in-down pointer-events-auto">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="font-medium text-sm"><?php echo $_SESSION['pesan_error']; ?></p>
                </div>
                <?php unset($_SESSION['pesan_error']); ?>
            <?php endif; ?>
        </div>

        <div class="flex-1 bg-gray-50 flex flex-col h-full border-r border-gray-200 z-10">

            <!-- HEADER PRODUK DENGAN FITUR PENCARIAN -->
            <div
                class="p-4 bg-white border-b border-gray-200 shrink-0 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Menu Produk</h2>
                    <p class="text-xs text-gray-500">Klik produk untuk menambahkan ke keranjang.</p>
                </div>

                <!-- Kotak Pencarian -->
                <div class="relative w-full sm:w-1/2 lg:w-64 xl:w-80">
                    <input type="text" id="input-pencarian" onkeyup="cariProduk()" placeholder="Cari nama ikan..."
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border border-gray-300 rounded-xl text-sm font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-transparent transition">
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
            class="w-full lg:w-[400px] xl:w-[450px] bg-white flex flex-col h-[50vh] lg:h-full shrink-0 shadow-lg lg:shadow-none z-20 transition-all">
            <div class="p-4 bg-slate-50 border-b border-gray-200 shrink-0 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    Troli Pesanan
                </h2>
                <button onclick="kosongkanKeranjang()"
                    class="text-xs text-red-500 hover:text-red-700 font-semibold bg-red-50 px-2 py-1 rounded">Kosongkan</button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50/50" id="tempat-keranjang"></div>

            <form action="pos.php" method="POST"
                class="shrink-0 bg-white border-t border-gray-200 p-5 pb-6 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)]">
                <div class="space-y-2 mb-5">
                    <div class="flex justify-between text-gray-500 text-sm">
                        <span>Subtotal Item</span>
                        <span id="subtotal-teks">Rp 0</span>
                    </div>
                    <div
                        class="flex justify-between font-extrabold text-xl text-gray-800 pt-2 border-t border-gray-100">
                        <span>Total Bayar</span>
                        <span id="total-bayar-teks" class="text-blue-600">Rp 0</span>
                    </div>
                </div>

                <div class="space-y-4 mb-6">

                    <!-- INPUT NAMA PELANGGAN (AUTO SUGGEST) -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Nama
                            Pelanggan <span class="text-gray-400 font-normal capitalize">(Opsional)</span></label>
                        <input type="text" name="nama_pembeli" list="data-pelanggan" autocomplete="off"
                            placeholder="Ketik atau pilih nama..."
                            class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm font-bold text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">

                        <!-- Datalist dari Database -->
                        <datalist id="data-pelanggan">
                            <?php if ($q_pelanggan): while ($pel = mysqli_fetch_assoc($q_pelanggan)): ?>
                                    <option value="<?php echo htmlspecialchars($pel['nama_pembeli']); ?>"></option>
                            <?php endwhile;
                            endif; ?>
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Uang Diterima
                            (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-400">Rp</span>
                            <input type="text" id="input-uang" name="jumlah_bayar_input" required autocomplete="off"
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-xl font-bold text-xl text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition"
                                placeholder="0" oninput="hitungKembalian(this.value)">
                        </div>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-bold text-gray-600">Kembalian</span>
                            <span id="teks-kembalian" class="font-extrabold text-lg text-emerald-500">Rp 0</span>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="data_keranjang" id="input-keranjang-json">

                <button type="submit" name="proses_bayar" id="btn-proses" disabled
                    class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl shadow-lg transition-all active:scale-[0.98] flex justify-center items-center gap-2">
                    Proses & Cetak Struk
                </button>
            </form>
        </div>
    </main>

    <script>
        const dbIkan = <?php echo json_encode($data_ikan_json); ?>;
        let keranjang = [];
        let totalHargaState = 0;

        const formatRp = (angka) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(angka);
        };

        // FUNGSI PENCARIAN PRODUK
        window.cariProduk = function() {
            const kataKunci = document.getElementById('input-pencarian').value.toLowerCase();
            const kartuProduk = document.querySelectorAll('.kartu-produk');

            kartuProduk.forEach(kartu => {
                const namaIkan = kartu.getAttribute('data-nama').toLowerCase();
                if (namaIkan.includes(kataKunci)) {
                    kartu.style.display = 'flex'; // Munculkan kembali (ingat kartu kita flex)
                } else {
                    kartu.style.display = 'none'; // Sembunyikan
                }
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

                let gambarSrc = ikan.gambar;
                if (!gambarSrc.startsWith('http')) {
                    gambarSrc = 'uploads/' + gambarSrc;
                }

                // Tambahan: class "kartu-produk" dan "data-nama" untuk keperluan pencarian
                const cardHTML = `
                    <div onclick="${isHabis ? '' : `tambahItem(${ikan.id})`}" 
                         data-nama="${ikan.nama_ikan}"
                         class="kartu-produk bg-white rounded-xl shadow-sm border ${isHabis ? 'border-gray-200 opacity-60 cursor-not-allowed' : 'border-gray-200 hover:border-blue-500 cursor-pointer hover:shadow-md'} overflow-hidden transition-all flex flex-col relative group">
                        <div class="h-28 bg-gray-100 overflow-hidden relative">
                            <img src="${gambarSrc}" onerror="this.src='https://via.placeholder.com/300?text=Ikan'" class="w-full h-full object-cover ${isHabis ? 'grayscale' : 'group-hover:scale-110 transition-transform duration-300'}">
                            <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/70 to-transparent p-2">
                                <span class="text-xs font-bold text-white">Stok: ${ikan.stok} ${ikan.satuan}</span>
                            </div>
                        </div>
                        <div class="p-3 flex flex-col flex-grow">
                            <h3 class="text-sm font-bold text-gray-800 leading-tight mb-1 line-clamp-2">${ikan.nama_ikan}</h3>
                            <p class="text-blue-600 font-extrabold text-sm mt-auto">${formatRp(ikan.harga)}</p>
                        </div>
                        ${isHabis ? '<div class="absolute inset-0 bg-white/40 flex items-center justify-center"><span class="bg-red-500 text-white text-xs px-2 py-1 rounded font-bold">Maksimal</span></div>' : ''}
                    </div>
                `;
                grid.innerHTML += cardHTML;
            });

            // Panggil filter lagi kalau-kalau kursor masih ada di kolom search
            cariProduk();
        }

        function tambahItem(id) {
            const produk = dbIkan.find(i => i.id == id);
            if (!produk) return;

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
            updateUI(); // Mengupdate penuh jika ada item baru
        }

        // Tombol +/- Memanggil Re-render penuh
        function ubahQty(index, perubahan) {
            const item = keranjang[index];
            const produk = dbIkan.find(i => i.id == item.id);
            const qtyBaru = item.qty + perubahan;

            if (qtyBaru <= 0) {
                keranjang.splice(index, 1);
            } else if (qtyBaru <= produk.stok) {
                item.qty = qtyBaru;
            }
            updateUI();
        }

        // FUNGSI BARU: oninput = Menghitung Real-time saat mengetik (Tanpa merusak fokus kursor)
        window.setQtyManualRealtime = function(index, elemenInput) {
            const item = keranjang[index];
            const produk = dbIkan.find(i => i.id == item.id);
            let nilai_baru = elemenInput.value;
            let qtyBaru = parseInt(nilai_baru);

            // Jika input dikosongkan (backspace sampai habis), biarkan array menyimpan nilai 0 sementara
            if (isNaN(qtyBaru) || qtyBaru < 0) {
                item.qty = 0;
            }
            // Jika mengetik lebih dari stok, tolak dan kembalikan ke stok maks
            else if (qtyBaru > produk.stok) {
                elemenInput.value = produk.stok; // Paksa kembalikan angka di input
                item.qty = parseInt(produk.stok);
                alert('Stok tidak mencukupi! Sisa stok ' + produk.nama_ikan + ' adalah ' + produk.stok);
            }
            // Normal
            else {
                item.qty = qtyBaru;
            }

            // HITUNG DAN UPDATE TEKS SAJA (Jangan panggil renderKeranjang agar kursor input tidak hilang)
            kalkulasiHargaDinamis();
        }

        // FUNGSI BARU: onblur = Mengecek saat selesai mengetik/pindah tempat
        window.cekEmptyQty = function(index, elemenInput) {
            const item = keranjang[index];
            // Jika saat ditinggalkan angkanya kosong atau 0, hapus dari keranjang
            if (item.qty === 0 || elemenInput.value === '') {
                keranjang.splice(index, 1);
                updateUI(); // Render ulang keranjang jika ada item dihapus
            }
        }

        // Fungsi khusus untuk merubah nominal teks tanpa menyentuh DOM inputan
        function kalkulasiHargaDinamis() {
            let total = 0;
            keranjang.forEach((item, index) => {
                const subtotal = item.qty * item.harga;
                total += subtotal;

                // Update teks harga subtotal per baris item
                const elSubtotal = document.getElementById(`subtotal-item-${index}`);
                if (elSubtotal) {
                    elSubtotal.innerText = formatRp(subtotal);
                }
            });

            totalHargaState = total;
            document.getElementById('subtotal-teks').innerText = formatRp(total);
            document.getElementById('total-bayar-teks').innerText = formatRp(total);
            document.getElementById('input-keranjang-json').value = JSON.stringify(keranjang);

            hitungKembalian(document.getElementById('input-uang').value);
            renderProduk(); // Update sisa stok di katalog kiri
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
                tempat.innerHTML = `
                    <div class="h-full flex flex-col items-center justify-center text-gray-400 opacity-70 pt-10">
                        <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <p>Troli masih kosong</p>
                    </div>`;
            } else {
                keranjang.forEach((item, index) => {
                    const subtotal = item.qty * item.harga;
                    total += subtotal;

                    let imgUrl = item.gambar.startsWith('http') ? item.gambar : 'uploads/' + item.gambar;

                    tempat.innerHTML += `
                        <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex gap-3 animate-fade-in">
                            <img src="${imgUrl}" onerror="this.src='https://via.placeholder.com/100'" class="w-12 h-12 rounded-lg object-cover bg-gray-100">
                            <div class="flex-grow">
                                <h4 class="text-sm font-bold text-gray-800 line-clamp-1">${item.nama}</h4>
                                <p class="text-xs text-gray-500">${formatRp(item.harga)} /${item.satuan}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1.5">
                                <!-- Id ditambahkan ke subtotal agar bisa diubah via Javascript dinamis -->
                                <div id="subtotal-item-${index}" class="font-bold text-gray-800 text-sm">${formatRp(subtotal)}</div>
                                
                                <div class="flex items-center gap-1 bg-gray-50 rounded-lg p-1 border border-gray-200">
                                    <button type="button" onclick="ubahQty(${index}, -1)" class="w-6 h-6 flex justify-center items-center rounded bg-white text-gray-600 hover:bg-gray-200 hover:text-blue-600 shadow-sm transition-colors">-</button>
                                    
                                    <input type="number" 
                                           value="${item.qty}" 
                                           oninput="setQtyManualRealtime(${index}, this)" 
                                           onblur="cekEmptyQty(${index}, this)"
                                           class="w-10 text-center text-sm font-bold text-gray-800 bg-transparent border-none focus:ring-0 focus:outline-none p-0 m-0 cursor-text">
                                           
                                    <button type="button" onclick="ubahQty(${index}, 1)" class="w-6 h-6 flex justify-center items-center rounded bg-white text-gray-600 hover:bg-gray-200 hover:text-blue-600 shadow-sm transition-colors">+</button>
                                </div>
                            </div>
                        </div>
                    `;
                });
            }

            totalHargaState = total;
            document.getElementById('subtotal-teks').innerText = formatRp(total);
            document.getElementById('total-bayar-teks').innerText = formatRp(total);
            document.getElementById('input-keranjang-json').value = JSON.stringify(keranjang);

            hitungKembalian(document.getElementById('input-uang').value);
        }

        // Oninput untuk format kembalian
        window.hitungKembalian = function(nilaiInput) {
            let angka = nilaiInput.replace(/[^,\d]/g, '').toString();
            let inputEl = document.getElementById('input-uang');

            if (angka) inputEl.value = new Intl.NumberFormat('id-ID').format(angka);
            else inputEl.value = '';

            let uangBayar = parseInt(angka) || 0;
            let btnProses = document.getElementById('btn-proses');
            let teksKembalian = document.getElementById('teks-kembalian');

            if (keranjang.length > 0 && uangBayar >= totalHargaState) {
                let kembalian = uangBayar - totalHargaState;
                teksKembalian.innerText = formatRp(kembalian);
                teksKembalian.classList.replace('text-red-500', 'text-emerald-500');
                btnProses.disabled = false;
            } else {
                teksKembalian.innerText = "Uang Kurang";
                teksKembalian.classList.replace('text-emerald-500', 'text-red-500');
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
                alert.classList.replace('animate-fade-in-down', 'opacity-0');
                setTimeout(() => alert.remove(), 500);
            }
        }, 4000);

        renderProduk();
        renderKeranjang();
    </script>
</body>

</html>