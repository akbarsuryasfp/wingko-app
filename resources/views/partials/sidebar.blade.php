<div class="bg-light p-3" style="width: 250px; min-height: 100vh;">
    <h4>Menu</h4>
    <ul class="nav flex-column">
        <li class="nav-item"><strong>Master</strong></li>
        <li><a href="{{ route('bahan.index') }}" class="nav-link">Data Bahan</a></li>
        <li><a href="{{ route('produk.index') }}" class="nav-link">Data Produk</a></li>
        <li><a href="{{ route('supplier.index') }}" class="nav-link">Data Supplier</a></li>
        <li><a href="{{ route('resep.index') }}" class="nav-link">Resep Produk</a></li>

        <li class="mt-2 nav-item"><strong>Transaksi</strong></li>
        <li><a href="{{ route('orderbeli.index') }}" class="nav-link">Order Pembelian</a></li>
        <li><a href="{{ route('permintaan_produksi.index') }}" class="nav-link">Permintaan Produksi</a></li> 
        <li><a href="{{ route('jadwal.index') }}" class="nav-link">Jadwal Produksi</a></li>
        <!-- Tambah menu transaksi lain di sini -->
        <li class="mt-2 nav-item"><strong>Laporan</strong></li>
        <li><a href="#" class="nav-link">Laporan Pembelian</a></li>
        <!-- Tambah menu laporan lain di sini -->
    </ul>
</div>