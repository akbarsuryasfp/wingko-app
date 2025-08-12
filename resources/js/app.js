import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    var sidebar = document.getElementById('sidebar');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var rootLayout = document.getElementById('root-layout');
    var sidebarOverlay = document.querySelector('.sidebar-overlay');

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
                if (sidebarOverlay) sidebarOverlay.classList.toggle('active');
            }
        };
    }

    // Jika resize, terapkan ulang state
    window.addEventListener('resize', applySidebarState);

    // Untuk mobile: toggle .active (jika pakai tombol khusus mobile)
    window.toggleSidebarMobile = function() {
        sidebar.classList.toggle('active');
    };

    if (sidebarOverlay) {
        sidebarOverlay.onclick = function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        };
    }

    // Tutup sidebar mobile saat klik link di dalam sidebar, kecuali submenu-header
    document.querySelectorAll('#sidebar .nav-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            // Jika klik submenu-header, jangan tutup sidebar
            if (link.classList.contains('submenu-header')) {
                return;
            }
            if (window.innerWidth <= 576) {
                sidebar.classList.remove('active');
                if (sidebarOverlay) sidebarOverlay.classList.remove('active');
            }
        });
    });

    // Pastikan toggleSubMenu tetap bisa dipanggil di mobile
    window.toggleSubMenu = function(id) {
        var clickedUl = document.getElementById(id);
        if (!clickedUl) return;

        // Cari parent <li> dari submenu yang diklik
        var parentLi = clickedUl.parentElement;
        // Cari parent <ul> dari parent <li>
        var parentUl = parentLi ? parentLi.parentElement : null;
        if (!parentUl) return;

        // Tutup semua submenu yang satu level di dalam parent UL,
        // tapi JANGAN tutup parentUl itu sendiri!
        parentUl.querySelectorAll(':scope > li > ul[id^="submenu-"]').forEach(function(ul) {
            if (ul !== clickedUl) ul.style.display = 'none';
        });

        // Toggle submenu yang diklik
        if (clickedUl.style.display === 'none' || clickedUl.style.display === '') {
            clickedUl.style.display = 'block';
        } else {
            clickedUl.style.display = 'none';
        }
        // Tambahkan/tanggalkan class submenu-active pada header
        var header = parentUl.querySelector('.submenu-header[onclick*="' + id + '"]');
        if (header) {
            header.classList.toggle('submenu-active', clickedUl.style.display === 'block');
        }
    };

       document.querySelectorAll('.btn-toggle-detail').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var kode = this.getAttribute('data-resep');
            var detailRow = document.getElementById('resep-detail-' + kode);
            // Toggle tampil/sembunyi
            if (detailRow.style.display === 'none' || detailRow.style.display === '') {
                detailRow.style.display = 'table-row';
            } else {
                detailRow.style.display = 'none';
            }
        });
    });
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
    var clickedUl = document.getElementById(id);
    if (!clickedUl) return;

    // Cari parent <li> dari submenu yang diklik
    var parentLi = clickedUl.parentElement;
    // Cari parent <ul> dari parent <li>
    var parentUl = parentLi ? parentLi.parentElement : null;
    if (!parentUl) return;

    // Tutup semua submenu yang satu level di dalam parent UL,
    // tapi JANGAN tutup parentUl itu sendiri!
    parentUl.querySelectorAll(':scope > li > ul[id^="submenu-"]').forEach(function(ul) {
        if (ul !== clickedUl) ul.style.display = 'none';
    });

    // Toggle submenu yang diklik
    if (clickedUl.style.display === 'none' || clickedUl.style.display === '') {
        clickedUl.style.display = 'block';
    } else {
        clickedUl.style.display = 'none';
    }
};
