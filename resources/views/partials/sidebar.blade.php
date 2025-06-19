<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div id="sidebar" class="bg-light p-3">
   
    <ul class="nav flex-column">
        <li class="nav-item"><strong>Master</strong></li>
        <li><a href="{{ route('bahan.index') }}" class="nav-link"><i class="bi bi-box"></i><span>Data Bahan</span></a></li>
        <li><a href="{{ route('produk.index') }}" class="nav-link"><i class="bi bi-cup-straw"></i><span>Data Produk</span></a></li>
        <li><a href="{{ route('supplier.index') }}" class="nav-link"><i class="bi bi-truck"></i><span>Data Supplier</span></a></li>
        <li><a href="{{ route('karyawan.index') }}" class="nav-link"><i class="bi bi-people"></i><span>Data Karyawan</span></a></li>
        <li><a href="{{ route('pelanggan.index') }}" class="nav-link"><i class="bi bi-person"></i><span>Data Pelanggan</span></a></li>
        <li><a href="{{ route('consignor.index') }}" class="nav-link"><i class="bi bi-person-badge"></i><span>Data Consignor</span></a></li>
        <li><a href="{{ route('consignee.index') }}" class="nav-link"><i class="bi bi-person-bounding-box"></i><span>Data Consignee</span></a></li>

        <li class="mt-2 nav-item"><strong>Transaksi</strong></li>
        <li>
            <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-pembelian')">
                <i class="bi bi-cart"></i><span>Pembelian</span>
            </a>
            <ul id="submenu-pembelian" class="nav flex-column ms-3" style="display:none;">
                <li><a href="{{ route('orderbeli.index') }}" class="nav-link">Order Pembelian</a></li>
                <li><a href="{{ route('terimabahan.index') }}" class="nav-link">Penerimaan Bahan</a></li>
                <li><a href="{{ route('pembelian.index') }}" class="nav-link">Pembelian</a></li>
                <li><a href="{{ route('returbeli.index') }}" class="nav-link">Retur Pembelian</a></li>
                <li><a href="{{ route('hutang.index') }}" class="nav-link">Hutang</a></li>
            </ul>
        </li>
        <li>
            <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-produksi')">
                <i class="bi bi-gear"></i><span>Produksi</span>
            </a>
            <ul id="submenu-produksi" class="nav flex-column ms-3" style="display:none;">
                <li><a href="#" class="nav-link"><i class="bi bi-hammer"></i><span>Produksi</span></a></li>
                <li><a href="{{ route('hpp.index') }}" class="nav-link"><i class="bi bi-calculator"></i><span>HPP</span></a></li>
            </ul>
        </li>
        <li>
            <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-penjualan')">
                <i class="bi bi-basket"></i><span>Penjualan</span>
            </a>
            <ul id="submenu-penjualan" class="nav flex-column ms-3" style="display:none;">
                <li><a href="{{ route('penjualan.index') }}" class="nav-link"><i class="bi bi-bag"></i><span>Penjualan</span></a></li>
                <li><a href="{{ route('pesananpenjualan.index') }}" class="nav-link"><i class="bi bi-bag-dash"></i><span>Pesanan</span></a></li>
            </ul>
        </li>

        <li><a href="{{ route('resep.index') }}" class="nav-link"><i class="bi bi-journal"></i><span>Resep Produk</span></a></li>
        <li><a href="{{ route('orderbeli.index') }}" class="nav-link"><i class="bi bi-bag-plus"></i><span>Order Pembelian</span></a></li>
        <li><a href="{{ route('permintaan_produksi.index') }}" class="nav-link"><i class="bi bi-clipboard-plus"></i><span>Permintaan Produksi</span></a></li>
        <li><a href="{{ route('jadwal.index') }}" class="nav-link"><i class="bi bi-calendar-event"></i><span>Jadwal Produksi</span></a></li>
        <li class="mt-2 nav-item"><strong>Laporan</strong></li>
        <li>
            <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-kartustok')">
                <i class="bi bi-cart"></i><span>Kartu Persediaan Produk</span>
            </a>
            <ul id="submenu-kartustok" class="nav flex-column ms-3" style="display:none;">
        <li><a href="{{ route('kartustok.bahan') }}" class="nav-link">Kartu Persediaan Bahan</a></li>
        <li><a href="{{ route('kartustok.produk') }}" class="nav-link">Kartu Persediaan Produk</a></li>

    </ul>
</li>
        <!-- Menu laporan lain ... -->
    </ul>
</div>
