 <?php
    require_once '../cek_login.php';
    include '../components/sidebar.php';

    ?>

 <!-- PEMBUNGKUS KONTEN UTAMA -->
 <main class="flex-1 flex flex-col h-screen overflow-hidden bg-gray-50">

     <!-- KOMPONEN 2: NAVBAR -->
     <?php include '../components/navbar.php'; ?>

     <!-- AREA KONTEN (Bisa di-scroll ke bawah) -->
     <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

         <h1 class="text-2xl font-bold">Halo! Ini adalah area konten Anda.</h1>
         <p>Masukkan tabel, form, atau dashboard di dalam div ini.</p>

     </div>
 </main>

 <!-- KOMPONEN 3: SIDEBAR -->
 <?php include '../components/footer.php'; ?>