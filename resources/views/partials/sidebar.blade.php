<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div id="sidebar" class="bg-light p-3">
    <ul class="nav flex-column">
        <li>
            <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-master')">
                <i class="bi bi-folder"></i><span>Master</span>
            </a>
            <ul id="submenu-master" class="nav flex-column ms-3" style="display:none;">
                <li><a href="{{ route('bahan.index') }}" class="nav-link"><i class="bi bi-box"></i> Data Bahan</a></li>
                <li><a href="{{ route('produk.index') }}" class="nav-link"><i class="bi bi-cup-straw"></i> Data Produk</a></li>
                <li><a href="{{ route('supplier.index') }}" class="nav-link"><i class="bi bi-truck"></i> Data Supplier</a></li>
                <li><a href="{{ route('karyawan.index') }}" class="nav-link"><i class="bi bi-people"></i> Data Karyawan</a></li>
                <li><a href="{{ route('pelanggan.index') }}" class="nav-link"><i class="bi bi-person"></i> Data Pelanggan</a></li>
                <li><a href="{{ route('consignor.index') }}" class="nav-link"><i class="bi bi-person-badge"></i> Data Consignor</a></li>
                <li><a href="{{ route('consignee.index') }}" class="nav-link"><i class="bi bi-person-bounding-box"></i> Data Consignee</a></li>
            </ul>
        </li>
        <li>
            <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-transaksi')">
                <i class="bi bi-arrow-left-right"></i><span>Transaksi</span>
            </a>
            <ul id="submenu-transaksi" class="nav flex-column ms-3" style="display:none;">
                <li>
                    <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-pembelian')">
                        <i class="bi bi-cart"></i>Pembelian
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
                        <li><a href="{{ route('permintaan_produksi.index') }}" class="nav-link"><i class="bi bi-clipboard-plus"></i><span>Permintaan Produksi</span></a></li>
                        <li><a href="{{ route('jadwal.index') }}" class="nav-link"><i class="bi bi-calendar-event"></i><span>Jadwal Produksi</span></a></li>
                        <li><a href="{{ route('produksi.index') }}" class="nav-link"><i class="bi bi-gear-fill"></i><span> Produksi</span></a></li>
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
                        <li><a href="{{ route('piutang.index') }}" class="nav-link"><i class="bi bi-cash-stack"></i><span>Piutang</span></a></li>
                        <li><a href="{{ route('returjual.index') }}" class="nav-link"><i class="bi bi-arrow-counterclockwise"></i><span>Retur Penjualan</span></a></li>
                    </ul>
                </li>

        
                <li>
                    <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-konsinyasi')">
                        <i class="bi bi-box-arrow-in-down"></i><span>Konsinyasi Masuk</span>
                    </a>
                    <ul id="submenu-konsinyasi" class="nav flex-column ms-3" style="display:none;">
                        <li><a href="{{ route('konsinyasimasuk.index') }}" class="nav-link"><i class="bi bi-box-seam"></i><span>Input Data Produk dan Komisi Konsinyasi Masuk</span></a></li>
                        <li><a href="{{ route('jualkonsinyasimasuk.index') }}" class="nav-link"><i class="bi bi-arrow-left-right"></i><span>Penjualan Produk Konsinyasi Masuk</span></a></li>
                        <li><a href="{{ route('bayarconsignor.index') }}" class="nav-link"><i class="bi bi-credit-card-2-back"></i><span>Pembayaran ke Consignor (Pemilik Barang)</span></a></li>
                        <li><a href="{{ route('returconsignor.index') }}" class="nav-link"><i class="bi bi-arrow-return-left"></i><span>Retur ke Consignor (Pemilik Barang)</span></a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-konsinyasikeluar')">
                        <i class="bi bi-box-arrow-up"></i><span>Konsinyasi Keluar</span>
                    </a>
                    <ul id="submenu-konsinyasikeluar" class="nav flex-column ms-3" style="display:none;">
                        <li><a href="{{ route('konsinyasikeluar.index') }}" class="nav-link"><i class="bi bi-box-seam"></i><span>Input Data Produk Konsinyasi Keluar</span></a></li>
                        <li><a href="{{ route('penerimaankonsinyasi.index') }}" class="nav-link"><i class="bi bi-cash-coin"></i><span>Penerimaan Hasil Penjualan Produk Konsinyasi Keluar</span></a></li>
                        <li><a href="{{ route('returconsignee.index') }}" class="nav-link"><i class="bi bi-arrow-return-right"></i><span>Retur dari Consignee (Mitra)</span></a></li>
                    </ul>
                </li>

<li>
    <a href="{{ route('kaskeluar.index') }}" class="nav-link">
        <i class="bi bi-gear"></i><span>Pengeluaran Kas</span>
    </a>
</li>
            </ul>
        </li>
        <li>
            <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-penyesuaian')">
                <i class="bi bi-sliders"></i><span>Penyesuaian</span>
            </a>
            <ul id="submenu-konsinyasikeluar" class="nav flex-column ms-3" style="display:none;">
                <li><a href="{{ route('konsinyasikeluar.index') }}" class="nav-link"><i class="bi bi-box-seam"></i><span>Input Data Produk Konsinyasi Keluar</span></a></li>
                <li><a href="{{ route('penerimaankonsinyasi.index') }}" class="nav-link"><i class="bi bi-cash-coin"></i><span>Penerimaan Hasil Penjualan Produk Konsinyasi Keluar</span></a></li>
                <li><a href="{{ route('returconsignee.index') }}" class="nav-link"><i class="bi bi-arrow-return-right"></i><span>Retur dari Consignee (Mitra)</span></a></li>
            </ul>
            <ul id="submenu-penyesuaian" class="nav flex-column ms-3" style="display:none;">
        <li><a href="{{ route('stokopname.create') }}" class="nav-link"><i class="bi bi-clipboard-check"></i> Stok Opname Bahan</a></li>
        <li><a href="{{ route('stokopname.produk') }}" class="nav-link"><i class="bi bi-clipboard-data"></i> Stok Opname Produk</a></li>            </ul>
        </li>
        <li>
            <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-laporan')">
                <i class="bi bi-file-earmark-text"></i><span>Laporan</span>
            </a>
            <ul id="submenu-laporan" class="nav flex-column ms-3" style="display:none;">
                <li>
    <a href="{{ route('jurnal.index') }}" class="nav-link">
        <i class="bi bi-journal-bookmark"></i>
        <span>Jurnal Umum</span>
    </a>
</li>
<li>
    <a href="{{ route('jurnal.buku_besar') }}" class="nav-link">
        <i class="bi bi-book"></i>
        <span>Buku Besar</span>
    </a>
</li>
<li>
    <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-kartustok')">
        <i class="bi bi-cart"></i><span>Persediaan</span>
    </a>
    <ul id="submenu-kartustok" class="nav flex-column ms-3" style="display:none;">
        <li><a href="{{ route('kartustok.bahan') }}" class="nav-link"><i class="bi bi-box"></i> Kartu Persediaan Bahan</a></li>
        <li><a href="{{ route('kartustok.produk') }}" class="nav-link"><i class="bi bi-cup-straw"></i> Kartu Persediaan Produk</a></li>
        <li><a href="{{ route('kartuperskonsinyasi.index') }}" class="nav-link"><i class="bi bi-boxes"></i> Kartu Persediaan Produk Konsinyasi Masuk</a></li>
        <li><a href="{{ route('stokopname.create') }}" class="nav-link"><i class="bi bi-clipboard-check"></i> Stok Opname Bahan</a></li>
        <li><a href="{{ route('stokopname.produk') }}" class="nav-link"><i class="bi bi-clipboard-data"></i> Stok Opname Produk</a></li>
    </ul>
</li>

                <!-- Menu laporan lain ... -->
            </ul>
        </li>
    </ul>
</div>
