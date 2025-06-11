import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    var sidebar = document.getElementById('sidebar');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var rootLayout = document.getElementById('root-layout');

    // Ambil state dari localStorage (untuk desktop)
    var sidebarState = localStorage.getItem('sidebarState') || 'expanded';

    // Terapkan state awal (desktop)
    function applySidebarState() {
        if (window.innerWidth > 576) {
            if (sidebarState === 'collapsed') {
                sidebar.classList.add('collapsed');
                rootLayout.classList.add('sidebar-collapsed');
            } else {
                sidebar.classList.remove('collapsed');
                rootLayout.classList.remove('sidebar-collapsed');
            }
            sidebar.classList.remove('active');
        } else {
            sidebar.classList.remove('collapsed');
            rootLayout.classList.remove('sidebar-collapsed');
        }
    }
    applySidebarState();

    // Toggle sidebar saat tombol diklik
    if (sidebarToggle) {
        sidebarToggle.onclick = function() {
            if (window.innerWidth > 576) {
                sidebar.classList.toggle('collapsed');
                rootLayout.classList.toggle('sidebar-collapsed', sidebar.classList.contains('collapsed'));
                // Simpan state ke localStorage
                if (sidebar.classList.contains('collapsed')) {
                    localStorage.setItem('sidebarState', 'collapsed');
                } else {
                    localStorage.setItem('sidebarState', 'expanded');
                }
            } else {
                sidebar.classList.toggle('active');
                var overlay = document.querySelector('.sidebar-overlay');
                if (overlay) overlay.classList.toggle('active');
            }
        };
    }

    // Jika resize, terapkan ulang state
    window.addEventListener('resize', applySidebarState);

    // Untuk mobile: toggle .active (jika pakai tombol khusus mobile)
    window.toggleSidebarMobile = function() {
        sidebar.classList.toggle('active');
    };

    var overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.onclick = function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        };
    }
});

// Pastikan ini di JS-mu
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
