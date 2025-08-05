<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
  #sidebar {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-right: 1px solid #dee2e6;
    min-height: 100vh;
    padding: 0.5rem;
  }
  
  .nav-link {
    color: #495057;
    border-radius: 3px;
    padding: 6px 10px;
    margin: 1px 0;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    line-height: 1.3;
    position: relative;
  }
  
  .nav-link:hover {
    background-color: #e9ecef;
    color: #0d6efd;
  }
  
  .nav-link.active {
    background-color: #e9ecef;
    color: #0d6efd;
    font-weight: 500;
  }

  .nav-link.current-page {
    background-color: #e7f1ff;
    color: #0d6efd;
    font-weight: 500;
  }
  
  .nav-link.current-page:before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background-color: #0d6efd;
    border-radius: 3px 0 0 3px;
  }
  
  .nav-link i {
    margin-right: 8px;
    font-size: 0.95rem;
    width: 20px;
    text-align: center;
  }
  
  .nav-flex-column {
    padding-left: 3px;
    gap: 0.1rem;
  }
  
  .ms-3 {
    margin-left: 12px !important;
    border-left: 1px solid #dee2e6;
  }
  
  .submenu-header {
    font-weight: 500;
    position: relative;
    cursor: pointer;
  }
  
  .submenu-header:after {
    content: "\F282";
    font-family: "bootstrap-icons";
    position: absolute;
    right: 8px;
    font-size: 0.8rem;
    transition: transform 0.2s;
  }
  
  .submenu-header.submenu-active:after {
    transform: rotate(90deg);
    color: #0d6efd;
  }
  
  .submenu-item {
    padding-left: 10px;
    font-size: 0.85rem;
  }
  
  #sidebar .text-center.mb-4 {
    margin-bottom: 0.75rem !important;
    padding: 0.25rem;
  }
  
  #sidebar .text-center h5 {
    font-size: 1rem;
    padding: 8px 0;
  }
</style>

<div id="sidebar" class="p-2">
    <div class="text-center mb-3">
        <h5 class="text-primary">MENU NAVIGASI</h5>
    </div>
    
<style>
  #sidebar {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-right: 1px solid #dee2e6;
    min-height: 100vh;
    padding: 0.5rem;
  }
  
  .nav-link {
    color: #495057;
    border-radius: 3px;
    padding: 6px 10px;
    margin: 1px 0;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    font-size: 0.9rem;
    line-height: 1.3;
    position: relative;
  }
  
  .nav-link:hover {
    background-color: #e9ecef;
    color: #0d6efd;
  }
  
  .nav-link.active {
    background-color: #e9ecef;
    color: #0d6efd;
    font-weight: 500;
  }

  .nav-link.current-page {
    background-color: #e7f1ff;
    color: #0d6efd;
    font-weight: 500;
  }
  
  .nav-link.current-page:before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background-color: #0d6efd;
    border-radius: 3px 0 0 3px;
  }
  
  .nav-link i {
    margin-right: 8px;
    font-size: 0.95rem;
    width: 20px;
    text-align: center;
  }
  
  .nav-flex-column {
    padding-left: 3px;
    gap: 0.1rem;
  }
  
  .ms-3 {
    margin-left: 12px !important;
    border-left: 1px solid #dee2e6;
  }
  
  .submenu-header {
    font-weight: 500;
    position: relative;
    cursor: pointer;
  }
  
  .submenu-header:after {
    content: "\F282";
    font-family: "bootstrap-icons";
    position: absolute;
    right: 8px;
    font-size: 0.8rem;
    transition: transform 0.2s;
  }
  
  .submenu-header.submenu-active:after {
    transform: rotate(90deg);
    color: #0d6efd;
  }
  
  .submenu-item {
    padding-left: 10px;
    font-size: 0.85rem;
  }
  
  #sidebar .text-center.mb-4 {
    margin-bottom: 0.75rem !important;
    padding: 0.25rem;
  }
  
  #sidebar .text-center h5 {
    font-size: 1rem;
    padding: 8px 0;
  }
</style>

<div id="sidebar" class="p-2">
    <div class="text-center mb-3">
        <h5 class="text-primary">MENU NAVIGASI</h5>
    </div>
    
    <ul class="nav flex-column">
        <!-- Master Menu -->
        <!-- Master Menu -->
        <li>
            <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-master', this)">
            <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-master', this)">
                <i class="bi bi-folder"></i><span>Master</span>
            </a>
            <ul id="submenu-master" class="nav flex-column ms-3" style="display:none;">
                <li><a href="/bahan" class="nav-link submenu-item"><i class="bi bi-box"></i>Data Bahan</a></li>
                <li><a href="/produk" class="nav-link submenu-item"><i class="bi bi-cup-straw"></i>Data Produk</a></li>
                <li><a href="/supplier" class="nav-link submenu-item"><i class="bi bi-truck"></i>Data Supplier</a></li>
                <li><a href="/karyawan" class="nav-link submenu-item"><i class="bi bi-people"></i>Data Karyawan</a></li>
                <li><a href="/pelanggan" class="nav-link submenu-item"><i class="bi bi-person"></i>Data Pelanggan</a></li>
                <li><a href="/consignor" class="nav-link submenu-item"><i class="bi bi-person-badge"></i>Data Consignor</a></li>
                <li><a href="/consignee" class="nav-link submenu-item"><i class="bi bi-person-bounding-box"></i>Data Consignee</a></li>
                <li><a href="/bahan" class="nav-link submenu-item"><i class="bi bi-box"></i>Data Bahan</a></li>
                <li><a href="/produk" class="nav-link submenu-item"><i class="bi bi-cup-straw"></i>Data Produk</a></li>
                <li><a href="/supplier" class="nav-link submenu-item"><i class="bi bi-truck"></i>Data Supplier</a></li>
                <li><a href="/karyawan" class="nav-link submenu-item"><i class="bi bi-people"></i>Data Karyawan</a></li>
                <li><a href="/pelanggan" class="nav-link submenu-item"><i class="bi bi-person"></i>Data Pelanggan</a></li>
                <li><a href="/consignor" class="nav-link submenu-item"><i class="bi bi-person-badge"></i>Data Consignor</a></li>
                <li><a href="/consignee" class="nav-link submenu-item"><i class="bi bi-person-bounding-box"></i>Data Consignee</a></li>
            </ul>
        </li>
        
        <!-- Transaksi Menu -->
        
        <!-- Transaksi Menu -->
        <li>
            <a href="javascript:void(0)" class="nav-link active submenu-header" onclick="toggleSubMenu('submenu-transaksi', this)">
            <a href="javascript:void(0)" class="nav-link active submenu-header" onclick="toggleSubMenu('submenu-transaksi', this)">
                <i class="bi bi-arrow-left-right"></i><span>Transaksi</span>
            </a>
            <ul id="submenu-transaksi" class="nav flex-column ms-3" style="display:block;">
                <!-- Pembelian Submenu -->
            <ul id="submenu-transaksi" class="nav flex-column ms-3" style="display:block;">
                <!-- Pembelian Submenu -->
                <li>
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-pembelian', this)">
                        <i class="bi bi-cart"></i><span>Pembelian</span>
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-pembelian', this)">
                        <i class="bi bi-cart"></i><span>Pembelian</span>
                    </a>
                    <ul id="submenu-pembelian" class="nav flex-column ms-3" style="display:none;">
                        <li><a href="/orderbeli" class="nav-link submenu-item">Order Pembelian</a></li>
                        <li><a href="/terimabahan" class="nav-link submenu-item">Penerimaan Bahan</a></li>
                        <li><a href="/pembelian" class="nav-link submenu-item">Pembelian</a></li>
                        <li><a href="/returbeli" class="nav-link submenu-item">Retur Pembelian</a></li>
                        <li><a href="/hutang" class="nav-link submenu-item">Hutang</a></li>
                        <li><a href="/orderbeli" class="nav-link submenu-item">Order Pembelian</a></li>
                        <li><a href="/terimabahan" class="nav-link submenu-item">Penerimaan Bahan</a></li>
                        <li><a href="/pembelian" class="nav-link submenu-item">Pembelian</a></li>
                        <li><a href="/returbeli" class="nav-link submenu-item">Retur Pembelian</a></li>
                        <li><a href="/hutang" class="nav-link submenu-item">Hutang</a></li>
                    </ul>
                </li>
                
                <!-- Produksi Submenu -->
                
                <!-- Produksi Submenu -->
                <li>
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-produksi', this)">
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-produksi', this)">
                        <i class="bi bi-gear"></i><span>Produksi</span>
                    </a>
                    <ul id="submenu-produksi" class="nav flex-column ms-3" style="display:none;">
                        <li><a href="/permintaan_produksi" class="nav-link submenu-item"><i class="bi bi-clipboard-plus"></i>Permintaan Produksi</a></li>
                        <li><a href="/jadwal" class="nav-link submenu-item"><i class="bi bi-calendar-event"></i>Jadwal Produksi</a></li>
                        <li><a href="/produksi" class="nav-link submenu-item"><i class="bi bi-gear-fill"></i>Produksi</a></li>
                        <li><a href="/hpp" class="nav-link submenu-item"><i class="bi bi-calculator"></i>HPP</a></li>
                    </ul>
                </li>
                
                <!-- Penjualan Submenu -->
                
                <!-- Penjualan Submenu -->
                <li>
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-penjualan', this)">
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-penjualan', this)">
                        <i class="bi bi-basket"></i><span>Penjualan</span>
                    </a>
                    <ul id="submenu-penjualan" class="nav flex-column ms-3" style="display:none;">
                        <li><a href="/penjualan" class="nav-link submenu-item"><i class="bi bi-bag"></i>Penjualan</a></li>
                        <li><a href="/pesananpenjualan" class="nav-link submenu-item"><i class="bi bi-bag-dash"></i>Pesanan</a></li>
                        <li><a href="/piutang" class="nav-link submenu-item"><i class="bi bi-cash-stack"></i>Piutang</a></li>
                        <li><a href="/returjual" class="nav-link submenu-item"><i class="bi bi-arrow-counterclockwise"></i>Retur Penjualan</a></li>
                        <li><a href="/penjualan" class="nav-link submenu-item"><i class="bi bi-bag"></i>Penjualan</a></li>
                        <li><a href="/pesananpenjualan" class="nav-link submenu-item"><i class="bi bi-bag-dash"></i>Pesanan</a></li>
                        <li><a href="/piutang" class="nav-link submenu-item"><i class="bi bi-cash-stack"></i>Piutang</a></li>
                        <li><a href="/returjual" class="nav-link submenu-item"><i class="bi bi-arrow-counterclockwise"></i>Retur Penjualan</a></li>
                    </ul>
                </li>
                <!-- Konsinyasi Masuk Submenu -->
                <li>
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-konsinyasi', this)">
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-konsinyasi', this)">
                        <i class="bi bi-box-arrow-in-down"></i><span>Konsinyasi Masuk</span>
                    </a>
                    <ul id="submenu-konsinyasi" class="nav flex-column ms-3" style="display:none;">
                        <li><a href="{{ route('konsinyasimasuk.index') }}" class="nav-link"><i class="bi bi-box-seam"></i><span>Input Data Produk dan Komisi Konsinyasi Masuk</span></a></li>
                        <li><a href="{{ route('jualkonsinyasimasuk.index') }}" class="nav-link"><i class="bi bi-arrow-left-right"></i><span>Penjualan Produk Konsinyasi Masuk</span></a></li>
                        <li><a href="{{ route('bayarconsignor.index') }}" class="nav-link"><i class="bi bi-credit-card-2-back"></i><span>Pembayaran ke Consignor (Pemilik Barang)</span></a></li>
                        <li><a href="{{ route('returconsignor.index') }}" class="nav-link"><i class="bi bi-arrow-return-left"></i><span>Retur ke Consignor (Pemilik Barang)</span></a></li>
                        <li><a href="/konsinyasimasuk" class="nav-link submenu-item"><i class="bi bi-box-seam"></i>Input Data Produk Konsinyasi Masuk</a></li>
                        <li><a href="/jualkonsinyasimasuk" class="nav-link submenu-item"><i class="bi bi-arrow-left-right"></i>Penjualan Produk Konsinyasi Masuk</a></li>
                        <li><a href="/bayarconsignor" class="nav-link submenu-item"><i class="bi bi-credit-card-2-back"></i>Pembayaran ke Consignor</a></li>
                        <li><a href="/komisijual" class="nav-link submenu-item"><i class="bi bi-percent"></i>Komisi Penjualan Konsinyasi</a></li>
                        <li><a href="/returconsignor" class="nav-link submenu-item"><i class="bi bi-arrow-return-left"></i>Retur ke Consignor</a></li>
                    </ul>
                </li>

                <!-- Konsinyasi Keluar Submenu -->
                <!-- Konsinyasi Keluar Submenu -->
                <li>
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-konsinyasikeluar', this)">
                    <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-konsinyasikeluar', this)">
                        <i class="bi bi-box-arrow-up"></i><span>Konsinyasi Keluar</span>
                    </a>
                    <ul id="submenu-konsinyasikeluar" class="nav flex-column ms-3" style="display:none;">
                        <li><a href="{{ route('konsinyasikeluar.index') }}" class="nav-link"><i class="bi bi-box-seam"></i><span>Input Data Produk Konsinyasi Keluar</span></a></li>
                        <li><a href="{{ route('penerimaankonsinyasi.index') }}" class="nav-link"><i class="bi bi-cash-coin"></i><span>Penerimaan Hasil Penjualan Produk Konsinyasi Keluar</span></a></li>
                        <li><a href="{{ route('returconsignee.index') }}" class="nav-link"><i class="bi bi-arrow-return-right"></i><span>Retur dari Consignee (Mitra)</span></a></li>
                        <li><a href="/konsinyasikeluar" class="nav-link submenu-item"><i class="bi bi-box-seam"></i>Input Data Produk Konsinyasi Keluar</a></li>
                        <li><a href="/penerimaankonsinyasi" class="nav-link submenu-item"><i class="bi bi-cash-coin"></i>Penerimaan Hasil Penjualan Produk Konsinyasi</a></li>
                        <li><a href="/returconsignee" class="nav-link submenu-item"><i class="bi bi-arrow-return-right"></i>Retur dari Consignee</a></li>
                    </ul>
                </li>

                <!-- Pengeluaran Kas -->
                <li>
                    <a href="/kaskeluar" class="nav-link">
                        <i class="bi bi-cash-stack"></i><span>Pengeluaran Kas</span>
                    </a>
                </li>
                <!-- Pengeluaran Kas -->
                <li>
                    <a href="/kaskeluar" class="nav-link">
                        <i class="bi bi-cash-stack"></i><span>Pengeluaran Kas</span>
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Penyesuaian Menu -->
        
        <!-- Penyesuaian Menu -->
        <li>
            <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-penyesuaian', this)">
            <a href="javascript:void(0)" class="nav-link submenu-header" onclick="toggleSubMenu('submenu-penyesuaian', this)">
                <i class="bi bi-sliders"></i><span>Penyesuaian</span>
            </a>
            <ul id="submenu-penyesuaian" class="nav flex-column ms-3" style="display:none;">
                <li><a href="/stokopname/bahan" class="nav-link submenu-item"><i class="bi bi-clipboard-check"></i>Stok Opname Bahan</a></li>
                <li><a href="/stokopname/produk" class="nav-link submenu-item"><i class="bi bi-clipboard-data"></i>Stok Opname Produk</a></li>
            </ul>
                <li><a href="/stokopname/bahan" class="nav-link submenu-item"><i class="bi bi-clipboard-check"></i>Stok Opname Bahan</a></li>
                <li><a href="/stokopname/produk" class="nav-link submenu-item"><i class="bi bi-clipboard-data"></i>Stok Opname Produk</a></li>
            </ul>
        </li>
        
        <!-- Laporan Menu -->
        
        <!-- Laporan Menu -->
        <li>
            <a href="javascript:void(0)" class="nav-link active submenu-header" onclick="toggleSubMenu('submenu-laporan', this)">
            <a href="javascript:void(0)" class="nav-link active submenu-header" onclick="toggleSubMenu('submenu-laporan', this)">
                <i class="bi bi-file-earmark-text"></i><span>Laporan</span>
            </a>
            <ul id="submenu-laporan" class="nav flex-column ms-3" style="display:block;">
                <!-- Persediaan Submenu -->
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
            <ul id="submenu-laporan" class="nav flex-column ms-3" style="display:block;">
                <!-- Persediaan Submenu -->
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
    </ul>
</div>

<script>
// Function to highlight current page and open parent menus
function highlightCurrentPage() {
    const currentPath = window.location.pathname;
    
    document.querySelectorAll('#sidebar .nav-link[href]').forEach(link => {
        const linkPath = new URL(link.href).pathname;
        
        if (currentPath === linkPath) {
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
function toggleSubMenu(id, element) {
    const submenu = document.getElementById(id);
    if (submenu.style.display === 'none') {
        submenu.style.display = 'block';
        element.classList.add('submenu-active');
    } else {
        submenu.style.display = 'none';
        element.classList.remove('submenu-active');
    }
}

// Initialize sidebar
document.addEventListener('DOMContentLoaded', function() {
    // Open default menus
    document.getElementById('submenu-transaksi').style.display = 'block';
    document.getElementById('submenu-laporan').style.display = 'block';
    
    // Highlight current page
    highlightCurrentPage();
});
</script>
<script>
// Function to highlight current page and open parent menus
function highlightCurrentPage() {
    const currentPath = window.location.pathname;
    
    document.querySelectorAll('#sidebar .nav-link[href]').forEach(link => {
        const linkPath = new URL(link.href).pathname;
        
        if (currentPath === linkPath) {
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
function toggleSubMenu(id, element) {
    const submenu = document.getElementById(id);
    if (submenu.style.display === 'none') {
        submenu.style.display = 'block';
        element.classList.add('submenu-active');
    } else {
        submenu.style.display = 'none';
        element.classList.remove('submenu-active');
    }
}

// Initialize sidebar
document.addEventListener('DOMContentLoaded', function() {
    // Open default menus
    document.getElementById('submenu-transaksi').style.display = 'block';
    document.getElementById('submenu-laporan').style.display = 'block';
    
    // Highlight current page
    highlightCurrentPage();
});
</script>