import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    var sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.onclick = function() {
            var sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
        };
    }
});

window.toggleSidebar = function() {
    var sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
    // Tutup semua submenu jika sidebar di-collapse
    if (sidebar.classList.contains('collapsed')) {
        document.querySelectorAll('ul[id^="submenu-"]').forEach(function(ul) {
            ul.style.display = 'none';
        });
    }
};

window.toggleSubMenu = function(id) {
    var sidebar = document.getElementById('sidebar');
    if (sidebar.classList.contains('collapsed')) return; // Tidak bisa buka submenu jika collapsed
    // Tutup semua submenu
    document.querySelectorAll('ul[id^="submenu-"]').forEach(function(ul) {
        if (ul.id !== id) ul.style.display = 'none';
    });
    // Toggle submenu yang diklik
    var el = document.getElementById(id);
    if (el.style.display === 'none' || el.style.display === '') {
        el.style.display = 'block';
    } else {
        el.style.display = 'none';
    }
};
