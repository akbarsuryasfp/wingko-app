
@extends('layouts.app')


@section('content')
<div class="container mt-5 px-3">
    <div class="card shadow-sm">
        <div class="card-body">
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
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data persediaan.</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr id="total-row-produk-konsinyasi" style="display:none;">
                            <td colspan="6" class="text-end"><b>Total Sisa (Qty):</b></td>
                            <td id="total-sisa-produk-konsinyasi"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="stok-akhir-box-produk-konsinyasi" class="mt-4" style="display:none;">
                <span style="font-size:1.2em;">📊</span>
                <b>Stok Akhir <span id="nama-produk-konsinyasi-stok"></span></b>
                <ul id="stok-akhir-list-produk-konsinyasi" class="mt-2"></ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Daftar no_konsinyasimasuk valid dari backend (harus di-passing dari controller ke view)
window.validKonsinyasiMasuk = window.validKonsinyasiMasuk || [];

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
                let totalMasuk = 0;
                let totalKeluar = 0;
                // Akumulasi stok akhir per harga
                let stokAkhirMap = {};
                if (data.length === 0) {
                    tbody = `<tr><td colspan="8" class="text-center">Tidak ada data persediaan.</td></tr>`;
                } else {
                    // Filter: hanya tampilkan transaksi yang masih eksis di konsinyasimasuk
                    let filtered = data.filter(function(row) {
                        let noTransaksi = (row.no_transaksi || '').toString().trim();
                        if (!noTransaksi || noTransaksi === '-') return false;
                        if (window.validKonsinyasiMasuk.length > 0 && !window.validKonsinyasiMasuk.includes(noTransaksi)) return false;
                        return true;
                    });
                    // Ambil hanya transaksi dengan no_transaksi unik, yang paling akhir (terbaru) saja
                    let uniqueMap = {};
                    filtered.forEach(function(row) {
                        let noTransaksi = (row.no_transaksi || '').toString().trim();
                        // Jika sudah ada, bandingkan tanggal, ambil yang terbaru
                        if (!uniqueMap[noTransaksi]) {
                            uniqueMap[noTransaksi] = row;
                        } else {
                            // Bandingkan tanggal, ambil yang terbaru
                            let tgl1 = new Date(uniqueMap[noTransaksi].tanggal);
                            let tgl2 = new Date(row.tanggal);
                            if (tgl2 > tgl1) {
                                uniqueMap[noTransaksi] = row;
                            }
                        }
                    });
                    // Ubah ke array dan urutkan sesuai tanggal ascending
                    let uniqueRows = Object.values(uniqueMap).sort(function(a, b) {
                        return new Date(a.tanggal) - new Date(b.tanggal);
                    });
                    if (uniqueRows.length === 0) {
                        tbody = `<tr><td colspan="8" class="text-center">Tidak ada data persediaan.</td></tr>`;
                    } else {
                        let saldoQty = 0;
                        uniqueRows.forEach(function(row, idx) {
                            let masuk = parseFloat(row.masuk) || 0;
                            let keluar = parseFloat(row.keluar) || 0;
                            let harga = parseFloat(row.harga_konsinyasi) || 0;

                            // Akumulasi total masuk dan keluar
                            totalMasuk += masuk;
                            totalKeluar += keluar;

                            // Akumulasi stok akhir per harga
                            if (!stokAkhirMap[harga]) stokAkhirMap[harga] = { masuk: 0, keluar: 0 };
                            stokAkhirMap[harga].masuk += masuk;
                            stokAkhirMap[harga].keluar += keluar;

                            saldoQty += masuk - keluar;
                            saldoPerRow.push(saldoQty);

                            let noTransaksi = row.no_transaksi ||'-';
                            tbody += `
                                <tr>
                                    <td>${idx + 1}</td>
                                    <td>${noTransaksi}</td>
                                    <td>${formatTanggal(row.tanggal)}</td>
                                    <td>Rp${harga.toLocaleString('id-ID')}</td>
                                    <td>${masuk}</td>
                                    <td>${keluar}</td>
                                    <td>${saldoQty}</td>
                                </tr>
                            `;
                        });
                    }
                }

                document.querySelector('#tabel-persediaan-produk-konsinyasi tbody').innerHTML = tbody;

                // Hitung total sisa qty dari akumulasi masuk - keluar
                let totalSisa = totalMasuk - totalKeluar;
                document.getElementById('total-sisa-produk-konsinyasi').innerText = totalSisa;
                document.getElementById('total-row-produk-konsinyasi').style.display = '';

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
        document.getElementById('total-row-produk-konsinyasi').style.display = 'none';
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
@endpush
