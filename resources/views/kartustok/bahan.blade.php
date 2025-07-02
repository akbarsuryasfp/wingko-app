@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h4 class="mb-4">KARTU PERSEDIAAN BAHAN</h4>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="kode_bahan" class="form-label">Nama Bahan</label>
            <select id="kode_bahan" name="kode_bahan" class="form-control" onchange="setSatuanOtomatis()">
                <option value="">-- Pilih Bahan --</option>
                @foreach($bahanList as $bahan)
                    <option value="{{ $bahan->kode_bahan }}" data-satuan="{{ $bahan->satuan }}">{{ $bahan->nama_bahan }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="satuan" class="form-label">Satuan</label>
            <input type="text" id="satuan" name="satuan" class="form-control" value="{{ $satuan ?? '' }}" readonly>
        </div>
    </form>

    <div id="riwayat-title" class="mb-2" style="display:none;">
        <span style="font-size:1.2em;">🔍</span>
        <b>Riwayat Masuk dan Keluar <span id="nama-bahan-title"></span></b>
    </div>

    <div class="table-responsive">
        <table id="tabel-persediaan" class="table table-bordered text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>No Transaksi</th>
                    <th>Tanggal</th>
                    <th>Harga per kg</th>
                    <th>Masuk (kg)</th>
                    <th>Keluar (kg)</th>
                    <th>Sisa (kg)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data persediaan.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="stok-akhir-box" class="mt-4" style="display:none;">
        <span style="font-size:1.2em;">📊</span>
        <b>Stok Akhir <span id="nama-bahan-stok"></span></b>
        <ul id="stok-akhir-list" class="mt-2"></ul>
    </div>
</div>
@endsection

<script>
function setSatuanOtomatis() {
    var select = document.getElementById('kode_bahan');
    var satuan = select.options[select.selectedIndex].getAttribute('data-satuan') || '';
    document.getElementById('satuan').value = satuan;

    // Ambil nama bahan untuk judul
    var namaBahan = select.options[select.selectedIndex].text || '';
    document.getElementById('nama-bahan-title').innerText = namaBahan;
    document.getElementById('nama-bahan-stok').innerText = namaBahan;

    if (select.value) {
        document.getElementById('riwayat-title').style.display = '';
        fetch('/kartustok/api/' + select.value)
            .then(res => res.json())
            .then(data => {
                let tbody = '';
                let saldoQty = 0;
                let saldoPerRow = [];
                // --- Akumulasi stok akhir per harga ---
                let stokAkhirMap = {};
                if (data.length === 0) {
                    tbody = `<tr><td colspan="7" class="text-center">Tidak ada data persediaan.</td></tr>`;
                } else {
                    data.forEach(function(row, idx) {
                        let masuk = parseFloat(row.masuk) || 0;
                        let keluar = parseFloat(row.keluar) || 0;
                        let harga = parseFloat(row.harga);

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
                            </tr>
                        `;
                    });
                }

                document.querySelector('#tabel-persediaan tbody').innerHTML = tbody;

                // Tampilkan stok akhir per harga
                let stokAkhirList = '';
                let adaStok = false;
                Object.entries(stokAkhirMap).forEach(([h, v]) => {
                    let sisa = v.masuk - v.keluar;
                    if (sisa > 0) {
                        adaStok = true;
                        stokAkhirList += `<li><b>${sisa} kg</b> dengan harga <b>Rp${parseFloat(h).toLocaleString('id-ID')}</b>/kg</li>`;
                    }
                });
                if (!adaStok) stokAkhirList = `<li>0</li>`;

                document.getElementById('stok-akhir-list').innerHTML = stokAkhirList;
                document.getElementById('stok-akhir-box').style.display = '';
            });
    } else {
        document.getElementById('riwayat-title').style.display = 'none';
        document.querySelector('#tabel-persediaan tbody').innerHTML = `<tr><td colspan="7" class="text-center">Tidak ada data persediaan.</td></tr>`;
        document.getElementById('stok-akhir-box').style.display = 'none';
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
