<?php
// components/footer.php

// =========================================================================
// AUTO-CONVERT GAMBAR KE BASE64 VIA PHP (Mencegah VS Code Lag)
// =========================================================================
// Pastikan Anda menaruh file logo (misal: logo.png) di folder 'image' proyek Anda.
// Ganti path di bawah ini jika letak folder atau nama file logo Anda berbeda.
$logo_path = '../image/logo.png';
$logo_base64 = '';

if (file_exists($logo_path)) {
    // Ambil isi file gambar
    $logo_data = file_get_contents($logo_path);
    // Dapatkan ekstensi file (png/jpg)
    $type = pathinfo($logo_path, PATHINFO_EXTENSION);
    // Konversi menjadi string Base64 yang siap dipakai di PDFMake
    $logo_base64 = 'data:image/' . $type . ';base64,' . base64_encode($logo_data);
}
?>
<!-- Script wajib untuk interaksi global admin -->
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (sidebar.classList.contains('sidebar-closed')) {
            sidebar.classList.remove('sidebar-closed');
            sidebar.classList.add('sidebar-open');
            if (overlay) {
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            }
        } else {
            sidebar.classList.remove('sidebar-open');
            sidebar.classList.add('sidebar-closed');
            if (overlay) {
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }
    }

    // =========================================================================
    // FUNGSI GLOBAL: PEMBUAT KOP SURAT UNTUK EXPORT PDF DATATABLES
    // =========================================================================
    function tambahkanKopSuratPdf(doc, judulLaporan) {
        // 1. Cek orientasi kertas untuk menyesuaikan panjang garis pembatas
        let isLandscape = doc.pageOrientation === 'landscape';
        let lebarGaris = isLandscape ? 760 : 515;

        // 2. Ambil data Base64 dari variabel PHP
        let dataLogoBase64 = '<?php echo $logo_base64; ?>';

        // 3. Logika Pintar: Jika logo ditemukan, pakai gambar. Jika tidak, pakai Teks Placeholder.
        let kolomLogo = (dataLogoBase64 !== '') ? {
            image: dataLogoBase64,
            width: 80,
            alignment: 'center',
            margin: [0, 0, 0, 0]
        } : {
            width: 80,
            text: '[ LOGO ]\nKAB. TEGAL',
            fontSize: 10,
            bold: true,
            color: '#1e3a8a',
            alignment: 'center',
            margin: [0, 10, 0, 0]
        };

        // 4. Desain Teks Kop Surat
        var kopSurat = {
            columns: [
                kolomLogo,
                {
                    // Kolom Kanan/Tengah: Teks Utama
                    width: '*',
                    text: [{
                            text: 'PEMERINTAH KABUPATEN TEGAL\n',
                            fontSize: 12,
                            alignment: 'center'
                        },
                        {
                            text: 'DINAS PERIKANAN\n',
                            fontSize: 18,
                            bold: true,
                            alignment: 'center'
                        },
                        {
                            text: 'Jl. RA Kartini No.1 Dukuhwringin Slawi Kab. Tegal, Jawa Tengah, Kode Pos: 52417\n',
                            fontSize: 10,
                            alignment: 'center'
                        },
                        {
                            text: 'Telepon (0283) 491618, Faksimile (0283) 491618\n',
                            fontSize: 10,
                            alignment: 'center'
                        },
                        {
                            text: 'Laman: https://diskan.tegalkab.go.id/, Pos-el: dkpp@tegalkab.go.id\n',
                            fontSize: 10,
                            alignment: 'center'
                        }
                    ]
                }
            ],
            margin: [0, -10, 0, 5]
        };

        // 5. Desain Garis Ganda Pembatas Kop
        var garisBatas = {
            canvas: [{
                    type: 'line',
                    x1: 0,
                    y1: 0,
                    x2: lebarGaris,
                    y2: 0,
                    lineWidth: 3
                }, // Garis tebal
                {
                    type: 'line',
                    x1: 0,
                    y1: 4,
                    x2: lebarGaris,
                    y2: 4,
                    lineWidth: 1
                } // Garis tipis
            ],
            margin: [0, 0, 0, 15]
        };

        // 6. Judul Dinamis Laporan
        var judul = {
            text: judulLaporan,
            fontSize: 14,
            bold: true,
            alignment: 'center',
            margin: [0, 0, 0, 15]
        };

        // 7. Suntikkan ke dokumen PDF (Menimpa judul bawaan DataTables)
        doc.content.splice(0, 1); // Hapus judul bawaan yang jelek

        // Masukkan secara berurutan (Kop -> Garis -> Judul)
        doc.content.unshift(judul);
        doc.content.unshift(garisBatas);
        doc.content.unshift(kopSurat);

        // ============================================================
        // 8. KUSTOMISASI STYLING & BORDER TABEL (MEMUNCULKAN GARIS)
        // ============================================================

        // A. Warna Header Tabel
        doc.styles.tableHeader = {
            bold: true,
            fontSize: 10,
            color: 'black',
            fillColor: '#e2e8f0', // Abu-abu sedikit gelap agar kontras
            alignment: 'center'
        };

        // B. Paksa Tampilkan Border Tabel (Tabel ada di index ke-3 setelah Kop)
        doc.content[3].layout = {
            hLineWidth: function(i, node) {
                return 1;
            }, // Garis horizontal
            vLineWidth: function(i, node) {
                return 1;
            }, // Garis vertikal
            hLineColor: function(i, node) {
                return '#94a3b8';
            }, // Warna border abu-abu
            vLineColor: function(i, node) {
                return '#94a3b8';
            }, // Warna border abu-abu
            paddingLeft: function(i, node) {
                return 5;
            },
            paddingRight: function(i, node) {
                return 5;
            },
            paddingTop: function(i, node) {
                return 4;
            },
            paddingBottom: function(i, node) {
                return 4;
            }
        };
    }
</script>
</body>

</html>