   <!-- Script wajib untuk tombol garis tiga (Hamburger Menu) -->
   <script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar.classList.contains('sidebar-closed')) {
        sidebar.classList.remove('sidebar-closed');
        sidebar.classList.add('sidebar-open');
        if (overlay) overlay.classList.remove('hidden');
        if (overlay) setTimeout(() => overlay.classList.remove('opacity-0'), 10);
    } else {
        sidebar.classList.remove('sidebar-open');
        sidebar.classList.add('sidebar-closed');
        if (overlay) overlay.classList.add('opacity-0');
        if (overlay) setTimeout(() => overlay.classList.add('hidden'), 300);
    }
}
   </script>
   </body>

   </html>