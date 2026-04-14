<?php
// ===================================================================
// FILE: index.php (Halaman Depan / Landing Page Pelanggan)
// Lokasi: Root folder proyek (misal: htdocs/segar-laut/index.php)
// ===================================================================

// Panggil file koneksi database
require_once 'koneksi.php';

// Nomor WhatsApp Toko (Ganti dengan nomor asli Anda)
$nomor_wa = "6285225082736";

// Query untuk mengambil data ikan dari database yang statusnya aktif (1)
// Ikan yang disembunyikan oleh admin (status_aktif = 0) tidak akan tampil
$query_ikan = mysqli_query($koneksi, "SELECT * FROM ikan WHERE status_aktif = 1 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimabeniPangkah - Jual Ikan Segar Langsung dari Tambak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Animasi untuk Carousel */
        .carousel-slide {
            transition: opacity 0.8s ease-in-out;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen selection:bg-blue-200 selection:text-blue-900">

    <!-- ================= NAVBAR ================= -->
    <nav class="bg-white/90 backdrop-blur-md shadow-sm sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="index.php" class="flex-shrink-0 flex items-center">
                    <span class="text-3xl font-extrabold text-blue-600 tracking-tighter">Simabeni<span
                            class="text-sky-400">Pangkah</span>.</span>
                </a>

                <!-- Menu Links Desktop -->
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="index.php"
                        class="text-blue-600 font-semibold border-b-2 border-blue-600 py-2 transition-colors">
                        Katalog Ikan
                    </a>
                    <a href="login.php"
                        class="text-slate-500 font-medium hover:text-blue-600 py-2 transition-colors border-b-2 border-transparent">
                        Login Admin
                    </a>
                </div>

                <!-- Tombol WhatsApp Navbar -->
                <div class="flex items-center">
                    <a href="https://wa.me/<?php echo $nomor_wa; ?>" target="_blank"
                        class="flex items-center gap-2 bg-emerald-100 text-emerald-700 px-4 py-2 rounded-full font-bold text-sm hover:bg-emerald-200 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                        </svg>
                        <span class="hidden sm:inline">Hubungi Kami</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- MAIN APP WRAPPER -->
    <main class="flex-grow">

        <!-- ================= HERO CAROUSEL ================= -->
        <section class="relative w-full h-[60vh] min-h-[400px] max-h-[600px] bg-slate-900 overflow-hidden"
            id="hero-carousel">
            <!-- Slide 1 -->
            <div class="carousel-slide absolute inset-0 w-full h-full flex flex-col justify-center items-center text-center p-6 opacity-100 z-10"
                id="slide-1">
                <img src="image/tambak2.jpeg" class="absolute inset-0 w-full h-full object-cover z-0" alt="Banner 1">
                <div class="absolute inset-0 bg-slate-900/60 z-0"></div>
                <div class="relative z-20 max-w-3xl transform transition-transform duration-1000 translate-y-0 scale-100"
                    id="slide-content-1">
                    <h1
                        class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-white mb-6 drop-shadow-lg tracking-tight">
                        Dari Tambak ke Akuarium Anda</h1>
                    <p class="text-lg sm:text-xl text-slate-200 drop-shadow-md font-medium leading-relaxed">Kami
                        menjamin kesegaran ikan tangkapan hari ini langsung dari nelayan lokal.</p>
                    <a href="#katalog"
                        class="inline-block mt-8 bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded-full font-bold transition-all shadow-lg hover:shadow-blue-500/50 hover:-translate-y-1">Lihat
                        Hasil Tangkapan</a>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-slide absolute inset-0 w-full h-full flex flex-col justify-center items-center text-center p-6 opacity-0 z-0"
                id="slide-2">
                <img src="image/tambak3.jpeg" class="absolute inset-0 w-full h-full object-cover z-0" alt="Banner 2">
                <div class="absolute inset-0 bg-slate-900/60 z-0"></div>
                <div class="relative z-20 max-w-3xl transform transition-transform duration-1000 translate-y-8 scale-95"
                    id="slide-content-2">
                    <h1
                        class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-white mb-6 drop-shadow-lg tracking-tight">
                        Kualitas Premium Terjaga</h1>
                    <p class="text-lg sm:text-xl text-slate-200 drop-shadow-md font-medium leading-relaxed">Ikan
                        dibesarkan di lingkungan air bersih dan disortir dengan standar ketat untuk nutrisi terbaik
                        keluarga Anda.</p>
                    <a href="#katalog"
                        class="inline-block mt-8 bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded-full font-bold transition-all shadow-lg hover:shadow-blue-500/50 hover:-translate-y-1">Lihat
                        Hasil Panen</a>
                </div>
            </div>
        </section>

        <!-- ================= KATALOG PRODUK REAL DARI DATABASE ================= -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16" id="katalog">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-800 mb-4">Hasil Tangkapan Hari Ini</h2>
                <div class="w-24 h-1 bg-blue-500 mx-auto rounded-full mb-4"></div>
                <p class="text-slate-500 max-w-2xl mx-auto">Pilih ikan segar favorit Anda. Stok diperbarui secara
                    otomatis. Hubungi kami via WhatsApp untuk melakukan pemesanan dan pengiriman.</p>
            </div>

            <!-- Grid Produk -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">

                <?php
                // Cek apakah query berhasil dan ada datanya
                if ($query_ikan && mysqli_num_rows($query_ikan) > 0) {

                    // Lakukan perulangan untuk menampilkan setiap baris ikan dari database
                    while ($ikan = mysqli_fetch_assoc($query_ikan)) {

                        // Cek status stok
                        $is_habis = $ikan['stok'] <= 0;

                        // Siapkan Pesan WhatsApp Pembelian
                        $pesan_wa = "Halo admin SegarLaut, saya tertarik untuk membeli *" . $ikan['nama_ikan'] . "*. Apakah stoknya masih tersedia?";
                        $link_wa = "https://wa.me/" . $nomor_wa . "?text=" . urlencode($pesan_wa);

                        // Logika Gambar (Fallback jika file tidak ada)
                        $gambar = $ikan['gambar'];
                        // Jika gambar tidak diawali "http" (berarti gambar lokal yang diupload)
                        if (!preg_match("/^https?:\/\//", $gambar)) {
                            // Asumsi folder uploads ada di root: "uploads/nama_file.jpg"
                            $gambar = 'uploads/' . $gambar;
                        }
                ?>

                        <!-- Kartu Produk -->
                        <div
                            class="group bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col transform hover:-translate-y-1">

                            <!-- Container Gambar -->
                            <div class="relative h-56 bg-slate-100 overflow-hidden">
                                <!-- Gunakan atribut onerror sebagai fallback jika gambar lokal tidak ditemukan/error upload -->
                                <img src="<?php echo $gambar; ?>"
                                    onerror="this.src='https://images.unsplash.com/photo-1524683745036-b46f52b8505a?auto=format&fit=crop&q=80&w=600&h=400'"
                                    alt="<?php echo $ikan['nama_ikan']; ?>"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110 <?php echo $is_habis ? 'grayscale opacity-80' : ''; ?>">

                                <!-- Lencana / Badge Stok -->
                                <div class="absolute top-4 left-4 flex flex-col gap-2">
                                    <?php if ($is_habis): ?>
                                        <span
                                            class="bg-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm">Habis
                                            Terjual</span>
                                    <?php else: ?>
                                        <span
                                            class="bg-white/95 backdrop-blur text-slate-700 text-xs font-bold px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1.5">
                                            <span
                                                class="w-2 h-2 rounded-full <?php echo $ikan['stok'] < 10 ? 'bg-orange-500 animate-pulse' : 'bg-emerald-500'; ?>"></span>
                                            Sisa: <?php echo $ikan['stok'] . ' ' . $ikan['satuan']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Konten Kartu -->
                            <div class="p-6 flex flex-col flex-grow">
                                <h3 class="font-bold text-xl text-slate-800 mb-2 leading-tight">
                                    <?php echo $ikan['nama_ikan']; ?></h3>
                                <p class="text-sm text-slate-500 mb-4 line-clamp-2">
                                    <?php echo !empty($ikan['deskripsi']) ? $ikan['deskripsi'] : 'Ikan segar pilihan, kualitas premium dengan harga terbaik.'; ?>
                                </p>

                                <div class="mt-auto">
                                    <!-- Harga -->
                                    <p class="text-blue-600 font-extrabold text-2xl mb-5">
                                        <?php echo formatRupiah($ikan['harga']); ?>
                                        <span class="text-sm font-medium text-slate-400">/<?php echo $ikan['satuan']; ?></span>
                                    </p>

                                    <!-- Tombol Aksi -->
                                    <?php if ($is_habis): ?>
                                        <button disabled
                                            class="w-full bg-slate-100 text-slate-400 font-bold py-3.5 rounded-xl cursor-not-allowed border border-slate-200">
                                            Stok Kosong Sementara
                                        </button>
                                    <?php else: ?>
                                        <a href="<?php echo $link_wa; ?>" target="_blank" rel="noopener noreferrer"
                                            class="flex items-center justify-center gap-2 w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3.5 rounded-xl transition-colors shadow-lg shadow-emerald-500/30">
                                            <!-- Icon WhatsApp SVG -->
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path
                                                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                            </svg>
                                            Pesan via WhatsApp
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    <?php
                    } // Akhir perulangan while 
                } else {
                    // Tampilan jika tabel ikan kosong atau gagal terhubung ke database
                    ?>
                    <div
                        class="col-span-1 sm:col-span-2 lg:col-span-3 xl:col-span-4 text-center py-20 bg-white rounded-2xl border border-dashed border-slate-300">
                        <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-2">Stok Kosong</h3>
                        <p class="text-slate-500 max-w-md mx-auto">Maaf, saat ini belum ada ikan yang tersedia atau sedang
                            dalam proses restock oleh nelayan kami.</p>
                    </div>
                <?php } ?>

            </div>
        </section>
    </main>

    <!-- ================= FOOTER ================= -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl font-bold text-white mb-4 tracking-tighter">Simabeni<span
                    class="text-sky-400">Pangkah</span>.
            </h2>
            <p class="mb-6 max-w-md mx-auto">Platform penjualan ikan segar terpercaya. Dapatkan ikan berkualitas tinggi
                langsung dari tambak dengan harga terbaik.</p>
            <!-- <div class="flex justify-center space-x-4 mb-8">
                <a href="#" class="text-slate-400 hover:text-white transition-colors">Instagram</a>
                <span class="text-slate-600">•</span>
                <a href="https://wa.me/<?php echo $nomor_wa; ?>"
                    class="text-slate-400 hover:text-white transition-colors">WhatsApp</a>
                <span class="text-slate-600">•</span>
                <a href="#" class="text-slate-400 hover:text-white transition-colors">Facebook</a>
            </div> -->
            <p class="text-sm">&copy; <?php echo date('Y'); ?> SimabeniPangkah. </p>
        </div>
    </footer>

    <!-- Javascript Logic -->
    <script>
        // Logika sederhana untuk Carousel
        let currentSlide = 1;

        function gantiSlide() {
            // Hilangkan slide aktif saat ini
            document.getElementById(`slide-${currentSlide}`).classList.replace('opacity-100', 'opacity-0');
            document.getElementById(`slide-${currentSlide}`).classList.replace('z-10', 'z-0');
            document.getElementById(`slide-content-${currentSlide}`).classList.replace('translate-y-0', 'translate-y-8');
            document.getElementById(`slide-content-${currentSlide}`).classList.replace('scale-100', 'scale-95');

            // Ganti angka (Jika 1 jadi 2, jika 2 jadi 1)
            currentSlide = currentSlide === 1 ? 2 : 1;

            // Tampilkan slide baru
            document.getElementById(`slide-${currentSlide}`).classList.replace('opacity-0', 'opacity-100');
            document.getElementById(`slide-${currentSlide}`).classList.replace('z-0', 'z-10');
            document.getElementById(`slide-content-${currentSlide}`).classList.replace('translate-y-8', 'translate-y-0');
            document.getElementById(`slide-content-${currentSlide}`).classList.replace('scale-95', 'scale-100');
        }

        // Jalankan animasi slide setiap 5 detik
        setInterval(gantiSlide, 5000);
    </script>
</body>

</html>