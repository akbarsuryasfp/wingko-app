<div class="bg-light p-3" style="width: 250px; min-height: 100vh;">
    <h4>Menu</h4>
    <ul class="nav flex-column">
        <li class="nav-item"><strong>Master</strong></li>
        <li><a href="{{ route('bahan.index') }}" class="nav-link">Data Bahan</a></li>
        <li><a href="{{ route('produk.index') }}" class="nav-link">Data Produk</a></li>
        <li><a href="{{ route('supplier.index') }}" class="nav-link">Data Supplier</a></li>
        <li><a href="{{ route('pelanggan.index') }}" class="nav-link">Data Pelanggan</a></li>
        <li><a href="{{ route('consignor.index') }}" class="nav-link">Data Consignor</a></li>
        <li><a href="{{ route('consignee.index') }}" class="nav-link">Data Consignee</a></li>

        <li class="mt-2 nav-item"><strong>Transaksi</strong></li>
        <!-- Sub menu Transaksi -->
        <li>
            <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-pembelian')"><strong>Pembelian</strong></a>
            <ul id="submenu-pembelian" class="nav flex-column ms-3" style="display:none;">
                <li><a href="{{ route('orderbeli.index') }}" class="nav-link">Order Pembelian</a></li>
                <li><a href="{{ route('terimabahan.index') }}" class="nav-link">Penerimaan Bahan</a></li>
                <li><a href="#" class="nav-link">Pembelian</a></li>
                <li><a href="#" class="nav-link">Retur Pembelian</a></li>
                <li><a href="#" class="nav-link">Pelunasan Hutang</a></li>
            </ul>
        </li>
        <li>
            <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-produksi')"><strong>Produksi</strong></a>
            <ul id="submenu-produksi" class="nav flex-column ms-3" style="display:none;">
                <li><a href="#" class="nav-link">Produksi</a></li>
                <!-- Tambah sub menu produksi lain di sini -->
            </ul>
        </li>
        <li>
            <a href="javascript:void(0)" class="nav-link" onclick="toggleSubMenu('submenu-penjualan')"><strong>Penjualan</strong></a>
            <ul id="submenu-penjualan" class="nav flex-column ms-3" style="display:none;">
                <li><a href="{{ route('penjualan.index') }}" class="nav-link">Penjualan</a></li>
                <li><a href="{{ route('pesananpenjualan.index') }}" class="nav-link">Pesanan</a></li>
                <!-- Tambah sub menu penjualan lain di sini -->
            </ul>
        </li>

        <li><a href="{{ route('resep.index') }}" class="nav-link">Resep Produk</a></li>

        <li class="mt-2 nav-item"><strong>Transaksi</strong></li>
        <li><a href="{{ route('orderbeli.index') }}" class="nav-link">Order Pembelian</a></li>
        <li><a href="{{ route('permintaan_produksi.index') }}" class="nav-link">Permintaan Produksi</a></li> 
        <li><a href="{{ route('jadwal.index') }}" class="nav-link">Jadwal Produksi</a></li>
        <!-- Tambah menu transaksi lain di sini -->
        <li class="mt-2 nav-item"><strong>Laporan</strong></li>
        <li><a href="#" class="nav-link">Laporan Pembelian</a></li>
        <li><a href="#" class="nav-link">Laporan Penjualan</a></li>
        <!-- Tambah menu laporan lain di sini -->
    </ul>
</div>
<script>
function toggleSubMenu(id) {
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
}
</script>