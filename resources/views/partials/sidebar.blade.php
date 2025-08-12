<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
  #sidebar {
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    min-height: 100vh;
    padding: 1rem;
    transition: all 0.3s ease;
    overflow-y: auto; /* Tambahkan baris ini */

  }
  
  .nav-link {
    color: rgba(255, 255, 255, 0.8);
    border-radius: 5px;
    padding: 10px 15px;
    margin: 3px 0;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    font-size: 0.95rem;
    line-height: 1.4;
    position: relative;
  }
  
  .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
  }
  
  .nav-link.active {
    background-color: rgba(16, 185, 129, 0.2); /* Teal background */
    color: white;
    font-weight: 500;
    box-shadow: inset 0 0 0 1px rgba(16, 185, 129, 0.3); /* Subtle border */
  }

  .nav-link.current-page {
    background-color: rgba(16, 185, 129, 0.25); /* Stronger teal */
    color: white;
    font-weight: 500;
  }
  
  .nav-link.current-page:before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background-color: #10b981; /* Solid teal */
    border-radius: 3px 0 0 3px;
  }
  
  .nav-link i {
    margin-right: 12px;
    font-size: 1.1rem;
    width: 24px;
    text-align: center;
  }
  
  /* Dark mode adjustments */
  .dark-mode .nav-link.active {
    background-color: rgba(16, 185, 129, 0.15);
    box-shadow: inset 0 0 0 1px rgba(16, 185, 129, 0.2);
  }


  .dark-mode .nav-link.current-page {
    background-color: rgba(16, 185, 129, 0.2);
  }

  .dark-mode .nav-link.current-page:before {
    background-color: #0d9488; /* Darker teal for dark mode */
  }

  /* Keep all other existing styles the same */
  .nav-flex-column {
    padding-left: 8px;
    gap: 0.3rem;
  }
  
  .ms-3 {
    margin-left: 20px !important;
    border-left: 1px solid rgba(255, 255, 255, 0.1);
  }
  
  .submenu-header {
    font-weight: 500;
    position: relative;
    cursor: pointer;
    font-size: 0.95rem;
  }
  
  .submenu-header:after {
    content: "\F282";
    font-family: "bootstrap-icons";
    position: absolute;
    right: 12px;
    font-size: 1rem;
    transition: transform 0.2s;
    color: rgba(255, 255, 255, 0.6);
  }
  
  .submenu-header.submenu-active:after {
    transform: rotate(90deg);
    color: white;
  }
  
  .submenu-item {
    padding-left: 20px;
    font-size: 0.9rem;
  }
  
  #sidebar .text-center.mb-4 {
    margin-bottom: 1.5rem !important;
    padding: 0.75rem;
  }
  
  #sidebar .text-center h5 {
    font-size: 1rem;
    padding: 8px 0;
  }
</style>


<div id="sidebar" class="p-2">
    <div class="text-center mb-3">
        <h5 class="text-white">MENU NAVIGASI</h5>
    </div>
    
    <ul class="nav flex-column">
        <!-- Master Menu -->
        <li>
            <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-master', this)">
                <i class="bi bi-folder"></i><span>Master</span>
            </a>
            <ul id="submenu-master" class="nav flex-column ms-3" style="display:none;">
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'gudang')
                <li><a href="/bahan" class="nav-link submenu-item"><i class="bi bi-box"></i>Data Bahan</a></li>
                <li><a href="/supplier" class="nav-link submenu-item"><i class="bi bi-truck"></i>Data Supplier</a></li>

                <li><a href="/produk" class="nav-link submenu-item"><i class="bi bi-cup-straw"></i>Data Produk</a></li>
                @endif
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'produksi')
                <li><a href="/aset-tetap" class="nav-link submenu-item"><i class="bi bi-box"></i>Data Aset Tetap</a></li>
                <li><a href="/resep" class="nav-link submenu-item"><i class="bi bi-egg-fried"></i>Data Resep</a></li>
                <li><a href="/karyawan" class="nav-link submenu-item"><i class="bi bi-people"></i>Data Karyawan</a></li>
                @endif
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'penjualan')
                <li><a href="/pelanggan" class="nav-link submenu-item"><i class="bi bi-person"></i>Data Pelanggan</a></li>
                <li><a href="/consignor" class="nav-link submenu-item"><i class="bi bi-person-badge"></i>Data Consignor</a></li>
                <li><a href="/consignee" class="nav-link submenu-item"><i class="bi bi-person-bounding-box"></i>Data Consignee</a></li>
                @endif
            </ul>
        </li>
        
        <!-- Transaksi Menu -->

        <li>
            <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-transaksi', this)">
                <i class="bi bi-arrow-left-right"></i><span>Transaksi</span>
            </a>
            <ul id="submenu-transaksi" class="nav flex-column ms-3" style="display:none;">
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'gudang')
                <!-- Pembelian Submenu -->
                <li>
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-pembelian', this)">
                        <i class="bi bi-cart"></i><span>Pembelian</span>
                    </a>
                    <ul id="submenu-pembelian" class="nav flex-column ms-3" style="display:none;">

                        <li><a href="/orderbeli" class="nav-link submenu-item"><i class="bi bi-cart-plus"></i> Order Pembelian</a></li>
                        <li><a href="/terimabahan" class="nav-link submenu-item"><i class="bi bi-truck"></i> Penerimaan Bahan</a></li>
                        <li><a href="/pembelian" class="nav-link submenu-item"><i class="bi bi-credit-card"></i> Pembelian</a></li>
                        <li><a href="/returbeli" class="nav-link submenu-item"><i class="bi bi-arrow-return-left"></i> Retur Pembelian</a></li>
                        @if(auth()->user()->role == 'admin')
<li><a href="/hutang" class="nav-link submenu-item"><i class="bi bi-receipt"></i> Hutang </a></li>
@endif                    </ul>
                </li>
                @endif
                <!-- Produksi Submenu -->
                @if(auth()->user()->role == 'admin' || auth()->user()->role == 'produksi')
                <li>
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-produksi', this)">
                        <i class="bi bi-gear"></i><span>Produksi</span>
                    </a>
                    <ul id="submenu-produksi" class="nav flex-column ms-3" style="display:none;">
                        <li><a href="/permintaan_produksi" class="nav-link submenu-item"><i class="bi bi-clipboard-plus"></i>Permintaan Produksi</a></li>
                        <li><a href="/jadwal-produksi" class="nav-link submenu-item"><i class="bi bi-calendar-event"></i>Jadwal Produksi</a></li>
                        <li><a href="/produksi" class="nav-link submenu-item"><i class="bi bi-gear-fill"></i>Produksi</a></li>
                        <li><a href="/hpp" class="nav-link submenu-item"><i class="bi bi-calculator"></i>HPP</a></li>
                    </ul>
                </li>
                @endif
                <!-- Penjualan Submenu -->
                 @if(auth()->user()->role == 'admin' || auth()->user()->role == 'penjualan')
                <li>
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-penjualan', this)">
                        <i class="bi bi-basket"></i><span>Penjualan</span>
                    </a>
                    <ul id="submenu-penjualan" class="nav flex-column ms-3" style="display:none;">
                        <li><a href="/penjualan" class="nav-link submenu-item"><i class="bi bi-bag"></i>Penjualan</a></li>
                        <li><a href="/pesananpenjualan" class="nav-link submenu-item"><i class="bi bi-bag-dash"></i>Pesanan</a></li>
                        <li><a href="/piutang" class="nav-link submenu-item"><i class="bi bi-cash-stack"></i>Piutang</a></li>
                        <li><a href="/returjual" class="nav-link submenu-item"><i class="bi bi-arrow-counterclockwise"></i>Retur Penjualan</a></li>
                    </ul>
                </li>

                <!-- Konsinyasi Masuk Submenu -->
                <li>
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-konsinyasi', this)">
                        <i class="bi bi-box-arrow-in-down"></i><span>Konsinyasi Masuk</span>
                    </a>
                    <ul id="submenu-konsinyasi" class="nav flex-column ms-3" style="display:none;">
                        <li><a href="/konsinyasimasuk" class="nav-link submenu-item"><i class="bi bi-box-seam"></i>Input Data Produk Konsinyasi Masuk</a></li>
                        <li><a href="/jualkonsinyasimasuk" class="nav-link submenu-item"><i class="bi bi-arrow-left-right"></i>Penjualan Produk Konsinyasi Masuk</a></li>
                        <li><a href="/bayarconsignor" class="nav-link submenu-item"><i class="bi bi-credit-card-2-back"></i>Pembayaran ke Consignor (Pemilik Barang)</a></li>
                        <li><a href="/returconsignor" class="nav-link submenu-item"><i class="bi bi-arrow-return-left"></i>Retur ke Consignor (Pemilik Barang)</a></li>
                    </ul>
                </li>

                <!-- Konsinyasi Keluar Submenu -->
                <li>
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-konsinyasikeluar', this)">
                        <i class="bi bi-box-arrow-up"></i><span>Konsinyasi Keluar</span>
                    </a>
                    <ul id="submenu-konsinyasikeluar" class="nav flex-column ms-3" style="display:none;">
                        <li><a href="/konsinyasikeluar" class="nav-link submenu-item"><i class="bi bi-box-seam"></i>Input Data Produk Konsinyasi Keluar</a></li>
                        <li><a href="/penerimaankonsinyasi" class="nav-link submenu-item"><i class="bi bi-cash-coin"></i>Penerimaan Hasil Penjualan Produk Konsinyasi Keluar</a></li>
                        <li><a href="/returconsignee" class="nav-link submenu-item"><i class="bi bi-arrow-return-right"></i>Retur dari Consignee (Mitra)</a></li>
                    </ul>
                </li>

                @endif
                <!-- Pengeluaran Kas -->
                <li>
                    <a href="/kaskeluar" class="nav-link">
                        <i class="bi bi-cash-stack"></i><span>Pengeluaran Kas</span>
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Penyesuaian Menu -->

        @if(auth()->user()->role == 'admin' || auth()->user()->role == 'gudang')
        <li>
            <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-penyesuaian', this)">
                <i class="bi bi-sliders"></i><span>Penyesuaian</span>
            </a>
            <ul id="submenu-penyesuaian" class="nav flex-column ms-3" style="display:none;">
                <li><a href="/stokopname/bahan" class="nav-link submenu-item"><i class="bi bi-clipboard-check"></i>Stok Opname Bahan</a></li>
                <li><a href="/stokopname/produk" class="nav-link submenu-item"><i class="bi bi-clipboard-data"></i>Stok Opname Produk</a></li>
            </ul>

            @endif
            @if(auth()->user()->role == 'admin' || auth()->user()->role == 'gudang')
            <ul id="submenu-penyesuaian" class="nav flex-column ms-3" style="display:none;">
            <li><a href="/overhead" class="nav-link submenu-item"><i class="bi bi-clipboard-check"></i>Realisasi Overhead</a></li>
            </ul>
            @endif
            </li>
            <li>
                <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-kartustok', this)">
                    <i class="bi bi-cart"></i><span>Persediaan</span>
                </a>
                <ul id="submenu-kartustok" class="nav flex-column ms-3" style="display:none;">
                    <li><a href="/kartustok/bahan" class="nav-link submenu-item"><i class="bi bi-box"></i>Kartu Persediaan Bahan</a></li>
                    <li><a href="/kartustok/produk" class="nav-link submenu-item"><i class="bi bi-cup-straw"></i>Kartu Persediaan Produk</a></li>
                    <li><a href="/kartuperskonsinyasi" class="nav-link submenu-item"><i class="bi bi-boxes"></i>Kartu Persediaan Produk Konsinyasi Masuk</a></li>
                </ul>
            </li>
        <!-- Laporan Menu -->
         
        @if(auth()->user()->role == 'admin' )
        <li>
            <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-laporan', this)">
                <i class="bi bi-file-earmark-text"></i><span>Laporan</span>
            </a>
            <ul id="submenu-laporan" class="nav flex-column ms-3" style="display:none;">
                <!-- Persediaan Submenu -->
                <li></li>
                <!-- Jurnal Umum -->
                <li>
                    <a href="/jurnal" class="nav-link">
                        <i class="bi bi-journal-bookmark"></i><span>Jurnal Umum</span>
                    </a>
                </li>

                <!-- Buku Besar -->
                <li>
                    <a href="/jurnal/buku_besar" class="nav-link">
                        <i class="bi bi-book"></i><span>Buku Besar</span>
                    </a>
                </li>
            </ul>
        </li>
        @endif
    </ul>
</div>


<script>
// Function to highlight current page and open parent menus
function highlightCurrentPage() {
    const currentPath = window.location.pathname;

    document.querySelectorAll('#sidebar .nav-link[href]').forEach(link => {
        const linkPath = new URL(link.href).pathname;

        // Gunakan endsWith agar cocok walau ada subfolder
        if (currentPath.endsWith(linkPath) && linkPath !== "/") {
            link.classList.add('current-page');

            // Open all parent menus
            let parentMenu = link.closest('ul');
            while (parentMenu && parentMenu.id) {
                parentMenu.style.display = 'block';

                // Find and activate the parent header
                const parentHeader = parentMenu.previousElementSibling;
                if (parentHeader && parentHeader.classList.contains('submenu-header')) {
                    parentHeader.classList.add('submenu-active');
                }

                parentMenu = parentMenu.parentElement.closest('ul');
            }
        }
    });
}

// Function to toggle submenus
window.toggleSubMenu = function(id, header) {
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
        if (ul !== clickedUl) {
            ul.style.display = 'none';
            // Hapus submenu-active dari header lain
            var otherHeader = ul.parentElement.querySelector('.submenu-header');
            if (otherHeader) otherHeader.classList.remove('submenu-active');
        }
    });

    // Toggle submenu yang diklik
    if (clickedUl.style.display === 'none' || clickedUl.style.display === '') {
        clickedUl.style.display = 'block';
        if (header) header.classList.add('submenu-active');
        else {
            // fallback: cari header di parent
            var h = parentLi.querySelector('.submenu-header');
            if (h) h.classList.add('submenu-active');
        }
    } else {
        clickedUl.style.display = 'none';
        if (header) header.classList.remove('submenu-active');
        else {
            var h = parentLi.querySelector('.submenu-header');
            if (h) h.classList.remove('submenu-active');
        }
    }
};

// Initialize sidebar
document.addEventListener('DOMContentLoaded', function() {
    
    // Highlight current page
    highlightCurrentPage();
});

</script>
