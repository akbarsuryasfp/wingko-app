@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h4 class="mb-4">KARTU PERSEDIAAN PRODUK KONSINYASI</h4>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="kode_produk_konsinyasi" class="form-label">Nama Produk Konsinyasi</label>
            <select id="kode_produk_konsinyasi" name="kode_produk_konsinyasi" class="form-control" onchange="setSatuanProdukKonsinyasiOtomatis()">
                <option value="">-- Pilih Produk Konsinyasi --</option>
                @foreach($produkKonsinyasiList as $produk)
                    <option value="{{ $produk->kode_produk }}" data-satuan="{{ $produk->satuan }}">{{ $produk->nama_produk }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="satuan_produk_konsinyasi" class="form-label">Satuan</label>
            <input type="text" id="satuan_produk_konsinyasi" name="satuan_produk_konsinyasi" class="form-control" value="{{ $satuan ?? '' }}" readonly>
        </div>
        <div class="col-md-4">
            <label for="lokasi_konsinyasi" class="form-label">Lokasi</label>
            <select id="lokasi_konsinyasi" name="lokasi_konsinyasi" class="form-control" onchange="setSatuanProdukKonsinyasiOtomatis()">
                <option value="">-- Semua Lokasi --</option>
                <option value="Gudang">Gudang</option>
                <option value="Toko 1">Toko 1</option>
                <option value="Toko 2">Toko 2</option>
            </select>
        </div>
    </form>

    <div id="riwayat-title-produk-konsinyasi" class="mb-2" style="display:none;">
        <span style="font-size:1.2em;">🔍</span>
        <b>Riwayat Masuk dan Keluar <span id="nama-produk-konsinyasi-title"></span></b>
    </div>

    <div class="table-responsive">
        <table id="tabel-persediaan-produk-konsinyasi" class="table table-bordered text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>No Transaksi</th>
                    <th>Tanggal</th>
                    <th>Harga</th>
                    <th>Masuk (Qty)</th>
                    <th>Keluar (Qty)</th>
                    <th>Sisa (Qty)</th>
                    <th>HPP</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data persediaan.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="stok-akhir-box-produk-konsinyasi" class="mt-4" style="display:none;">
        <span style="font-size:1.2em;">📊</span>
        <b>Stok Akhir <span id="nama-produk-konsinyasi-stok"></span></b>
        <ul id="stok-akhir-list-produk-konsinyasi" class="mt-2"></ul>
    </div>
</div>
@endsection

<script>
function setSatuanProdukKonsinyasiOtomatis() {
    var select = document.getElementById('kode_produk_konsinyasi');
    var satuan = select.options[select.selectedIndex].getAttribute('data-satuan') || '';
    document.getElementById('satuan_produk_konsinyasi').value = satuan;

    // Ambil nama produk untuk judul
    var namaProduk = select.options[select.selectedIndex].text || '';
    document.getElementById('nama-produk-konsinyasi-title').innerText = namaProduk;
    document.getElementById('nama-produk-konsinyasi-stok').innerText = namaProduk;

    if (select.value) {
        document.getElementById('riwayat-title-produk-konsinyasi').style.display = '';
        var lokasi = document.getElementById('lokasi_konsinyasi').value;
        fetch('/kartuperskonsinyasi/api-produk/' + select.value + '?lokasi=' + encodeURIComponent(lokasi))
            .then(res => res.json())
            .then(data => {
                let tbody = '';
                let saldoQty = 0;
                let saldoPerRow = [];
                // Akumulasi stok akhir per harga
                let stokAkhirMap = {};
                if (data.length === 0) {
                    tbody = `<tr><td colspan="8" class="text-center">Tidak ada data persediaan.</td></tr>`;
                } else {
                    data.forEach(function(row, idx) {
                        let masuk = parseFloat(row.masuk) || 0;
                        let keluar = parseFloat(row.keluar) || 0;
                        let harga = parseFloat(row.harga_konsinyasi) || 0; // harga = harga_konsinyasi
                        let hpp = harga;

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
                                <td>${formatTanggal(row.tanggal)}</td>
                                <td>Rp${harga.toLocaleString('id-ID')}</td>
                                <td>${masuk}</td>
                                <td>${keluar}</td>
                                <td>${saldoQty}</td>
                                <td>Rp${hpp.toLocaleString('id-ID')}</td>
                            </tr>
                        `;
                    });
                }

                document.querySelector('#tabel-persediaan-produk-konsinyasi tbody').innerHTML = tbody;

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

                document.getElementById('stok-akhir-list-produk-konsinyasi').innerHTML = stokAkhirList;
                document.getElementById('stok-akhir-box-produk-konsinyasi').style.display = '';
            });
    } else {
        document.getElementById('riwayat-title-produk-konsinyasi').style.display = 'none';
        document.querySelector('#tabel-persediaan-produk-konsinyasi tbody').innerHTML = `<tr><td colspan="8" class="text-center">Tidak ada data persediaan.</td></tr>`;
        document.getElementById('stok-akhir-box-produk-konsinyasi').style.display = 'none';
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
