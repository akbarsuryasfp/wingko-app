@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h4 class="mb-4">KARTU PERSEDIAAN PRODUK</h4>

    <div class="mb-3">
        <a href="{{ route('transferproduk.index') }}" class="btn btn-primary">
            <i class="bi bi-truck"></i> Transfer/Pengiriman Produk
        </a>
        <a href="{{ route('kartustok.laporan_produk') }}" class="btn btn-primary">
            Laporan Stok Akhir Produk
        </a>
    </div>
    
    <form method="GET" class="row g-3 mb-4">
<div class="col-md-4">
    <label for="kode_produk" class="form-label">Nama Produk</label>
    <select id="kode_produk" name="kode_produk" class="form-control" onchange="setSatuanProdukOtomatis()">
        <option value="">-- Pilih Produk --</option>
        @foreach($produkList as $produk)
            <option value="{{ $produk->kode_produk }}" data-satuan="{{ $produk->satuan }}">
                {{ $produk->nama_produk }} ({{ $produk->satuan }})
            </option>
        @endforeach
    </select>
</div>
        <div class="col-md-4">
            <label for="lokasi" class="form-label">Lokasi</label>
            <select id="lokasi" name="lokasi" class="form-control" onchange="setSatuanProdukOtomatis()">
                <option value="">-- Semua Lokasi --</option>
                <option value="Gudang">Gudang</option>
                <option value="Toko 1">Toko 1</option>
                <option value="Toko 2">Toko 2</option>
            </select>
        </div>
    </form>

    <div id="riwayat-title-produk" class="mb-2" style="display:none;">
        <span style="font-size:1.2em;">🔍</span>
        <b>Riwayat Masuk dan Keluar <span id="nama-produk-title"></span></b>
    </div>

    <div class="table-responsive">
        <table id="tabel-persediaan-produk" class="table table-bordered text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>No Transaksi</th>
                    <th>Keterangan</th>
                    <th>Tanggal</th>
                    <th>HPP</th>
                    <th>Masuk (Qty)</th>
                    <th>Keluar (Qty)</th>
                    <th>Sisa (Qty)</th>
                    <th>Lokasi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="9" class="text-center">Tidak ada data persediaan.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="stok-akhir-box-produk" class="mt-4" style="display:none;">
        <span style="font-size:1.2em;">📊</span>
        <b>Stok Akhir <span id="nama-produk-stok"></span></b>
        <ul id="stok-akhir-list-produk" class="mt-2"></ul>
    </div>
</div>
@endsection

<script>
function setSatuanProdukOtomatis() {
    var select = document.getElementById('kode_produk');
    var satuan = select.options[select.selectedIndex].getAttribute('data-satuan') || '';
    document.getElementById('satuan_produk').value = satuan;

    // Ambil nama produk untuk judul
    var namaProduk = select.options[select.selectedIndex].text || '';
    document.getElementById('nama-produk-title').innerText = namaProduk;
    document.getElementById('nama-produk-stok').innerText = namaProduk;

    if (select.value) {
        document.getElementById('riwayat-title-produk').style.display = '';
        var lokasi = document.getElementById('lokasi').value;
        fetch('/kartustok/api-produk/' + select.value + '?lokasi=' + encodeURIComponent(lokasi))
            .then(res => res.json())
            .then(data => {
                let tbody = '';
                let saldoQty = 0;
                let saldoPerRow = [];
                // Akumulasi stok akhir per harga
                let stokAkhirMap = {};
                if (data.length === 0) {
                    tbody = `<tr><td colspan="9" class="text-center">Tidak ada data persediaan.</td></tr>`;
                } else {
                    data.forEach(function(row, idx) {
                        let keterangan = row.keterangan || '-'; 
                        let masuk = parseFloat(row.masuk) || 0;
                        let keluar = parseFloat(row.keluar) || 0;
                        let harga = parseFloat(row.hpp) || 0;
                        let hpp = parseFloat(row.hpp) || 0;
                        

                        // Akumulasi stok akhir per harga
                        if (!stokAkhirMap[harga]) stokAkhirMap[harga] = { masuk: 0, keluar: 0 };
                        stokAkhirMap[harga].masuk += masuk;
                        stokAkhirMap[harga].keluar += keluar;

                        saldoQty += masuk - keluar;
                        saldoPerRow.push(saldoQty);

                        tbody += `
    <tr>
        <td>${idx + 1}</td>
        <td>${row.no_transaksi}</td>
        <td>${keterangan}</td> 
        <td>${formatTanggal(row.tanggal)}</td>
        <td>Rp${hpp.toLocaleString('id-ID')}</td>
        <td>${masuk}</td>
        <td>${keluar}</td>
        <td>${saldoQty}</td>
        <td>${row.lokasi || '-'}</td>
        
    </tr>
`;
                    });
                }

                document.querySelector('#tabel-persediaan-produk tbody').innerHTML = tbody;

                // Tampilkan stok akhir per harga
                let stokAkhirList = '';
                let adaStok = false;
                Object.entries(stokAkhirMap).forEach(([h, v]) => {
                    let sisa = v.masuk - v.keluar;
                    if (sisa > 0) {
                        adaStok = true;
                        stokAkhirList += `<li><b>${sisa}</b> ${satuan} dengan harga <b>Rp${parseFloat(h).toLocaleString('id-ID')}</b>/${satuan}</li>`;
                    }
                });
                if (!adaStok) stokAkhirList = `<li>0</li>`;

                document.getElementById('stok-akhir-list-produk').innerHTML = stokAkhirList;
                document.getElementById('stok-akhir-box-produk').style.display = '';
            });
    } else {
        document.getElementById('riwayat-title-produk').style.display = 'none';
        document.querySelector('#tabel-persediaan-produk tbody').innerHTML = `<tr><td colspan="9" class="text-center">Tidak ada data persediaan.</td></tr>`;
        document.getElementById('stok-akhir-box-produk').style.display = 'none';
    }
}

// Format tanggal ke format lokal (misal: 15 Juni 2025)
function formatTanggal(tgl) {
    if (!tgl) return '';
    const bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    const d = new Date(tgl);
    if (isNaN(d)) return tgl;
    return `${d.getDate()} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
}
</script>