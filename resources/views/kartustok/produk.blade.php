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
    
    <form method="GET" class="row g-3 mb-4 align-items-end">
    <!-- Nama Produk -->
    <div class="col-md-4">
        <label for="kode_produk" class="form-label">Nama Produk</label>
        <select id="kode_produk" name="kode_produk" class="form-select" onchange="setSatuanProdukOtomatis()">
            <option value="">-- Pilih Produk --</option>
            @foreach($produkList as $produk)
                <option value="{{ $produk->kode_produk }}" data-satuan="{{ $produk->satuan }}">{{ $produk->nama_produk }}</option>
            @endforeach
        </select>
    </div>
    
    <!-- Satuan -->
    <div class="col-md-2">
        <label for="satuan_produk" class="form-label">Satuan</label>
        <input type="text" id="satuan_produk" name="satuan_produk" class="form-control" value="{{ $satuan ?? '' }}" readonly>
    </div>
    
    <!-- Periode -->
    <div class="col-md-3">
        <label for="periode_produk" class="form-label">Periode</label>
        <input type="month" id="periode_produk" name="periode_produk" class="form-control" value="{{ date('Y-m') }}">
    </div>
    
    <!-- Lokasi -->
    <div class="col-md-3">
        <label for="lokasi" class="form-label">Lokasi</label>
        <select id="lokasi" name="lokasi" class="form-select">
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
                    <th>HPP</th> <!-- Kolom HPP setelah tanggal -->
                    <th>Masuk (Qty)</th>
                    <th>Keluar (Qty)</th>
                    <th>Sisa (Qty)</th>
                    <th>Lokasi</th> <!-- Kolom Lokasi setelah Sisa -->
                </tr>
            </thead>
            <tbody>
                @php
                    $saldo = 0;
                @endphp
                @foreach($riwayat as $index => $row)
                    @php
                        $saldo += ($row->masuk ?? 0) - ($row->keluar ?? 0);
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row->no_transaksi }}</td>
                        <td>{{ $row->keterangan }}</td>
                        <td>{{ tanggal_indo($row->tanggal) }}</td>
                        <td>{{ 'Rp' . number_format($row->hpp, 0, ',', '.') }}</td>
                        <td>{{ $row->masuk }}</td>
                        <td>{{ $row->keluar }}</td>
                        <td>{{ $saldo }}</td> <!-- Sisa per baris -->
                        <td>{{ $row->lokasi }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end mb-2">
        <label for="rowsPerPageProduk" class="form-label mb-0 me-2">Show</label>
        <select id="rowsPerPageProduk" class="form-select form-select-sm d-inline-block" style="width:auto;">
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="all">All</option>
        </select>
    </div>
    <nav>
        <ul class="pagination justify-content-end" id="pagination-produk"></ul>
    </nav>

    <div id="stok-akhir-box-produk" class="mt-4" style="display:none;">
        <span style="font-size:1.2em;">📊</span>
        <b>Stok Akhir <span id="nama-produk-stok"></span></b>
        <ul id="stok-akhir-list-produk" class="mt-2"></ul>
    </div>
</div>
@endsection

<script>
let produkData = [];
let currentPageProduk = 1;
let rowsPerPageProduk = 10;

function setSatuanProdukOtomatis() {
    var select = document.getElementById('kode_produk');
    // Ambil satuan dari atribut data-satuan
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
                produkData = data;
                // Hitung total halaman
                let perPage = document.getElementById('rowsPerPageProduk').value;
                let showAll = (perPage === 'all');
                let totalPages = showAll ? 1 : Math.ceil(produkData.length / perPage);
                // Langsung ke halaman terakhir
                currentPageProduk = totalPages > 0 ? totalPages : 1;
                renderTableProduk();
                renderPaginationProduk();
                renderStokAkhirProduk(); // Panggil fungsi untuk merender stok akhir
            });
    } else {
        document.getElementById('riwayat-title-produk').style.display = 'none';
        document.querySelector('#tabel-persediaan-produk tbody').innerHTML = `<tr><td colspan="9" class="text-center">Tidak ada data persediaan.</td></tr>`;
        document.getElementById('stok-akhir-box-produk').style.display = 'none';
        produkData = [];
        renderPaginationProduk();
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

function renderTableProduk() {
    let tbody = '';
    let saldoQty = 0;
    let saldoPerRow = [];
    let data = produkData;

    // Filter berdasarkan bulan (periode)
    let filterPeriode = document.getElementById('periode_produk').value; // format: 'YYYY-MM'
    if (filterPeriode) {
        data = data.filter(row => row.tanggal && row.tanggal.startsWith(filterPeriode));
    }

    // Filter berdasarkan lokasi
    let filterLokasi = document.getElementById('lokasi').value;
    if (filterLokasi) {
        data = data.filter(row => row.lokasi === filterLokasi);
    }

    let start = 0, end = data.length;
    let showAll = (rowsPerPageProduk === 'all');
    if (!showAll) {
        rowsPerPageProduk = parseInt(document.getElementById('rowsPerPageProduk').value);
        start = (currentPageProduk - 1) * rowsPerPageProduk;
        end = Math.min(start + rowsPerPageProduk, data.length);
    }
    if (data.length === 0) {
        tbody = `<tr><td colspan="9" class="text-center">Tidak ada data persediaan.</td></tr>`;
    } else {
        for (let i = 0; i < data.length; i++) {
            let row = data[i];
            let masuk = parseFloat(row.masuk) || 0;
            let keluar = parseFloat(row.keluar) || 0;
            let harga = parseFloat(row.hpp) || 0;
            saldoQty += masuk - keluar;
            saldoPerRow.push(saldoQty);

            if (showAll || (i >= start && i < end)) {
                tbody += `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${row.no_transaksi}</td>
                        <td>${row.keterangan || '-'}</td>
                        <td>${formatTanggal(row.tanggal)}</td>
                        <td>Rp${harga.toLocaleString('id-ID')}</td>
                        <td>${masuk}</td>
                        <td>${keluar}</td>
                        <td>${saldoQty}</td>
                        <td>${row.lokasi || '-'}</td>
                    </tr>
                `;
            }
        }
    }
    document.querySelector('#tabel-persediaan-produk tbody').innerHTML = tbody;
}

function renderPaginationProduk() {
    let data = produkData;
    let totalRows = data.length;
    let perPage = document.getElementById('rowsPerPageProduk').value;
    let showAll = (perPage === 'all');
    let totalPages = showAll ? 1 : Math.ceil(totalRows / perPage);

    let pag = '';
    if (totalPages > 1) {
        for (let i = 1; i <= totalPages; i++) {
            pag += `<li class="page-item${i === currentPageProduk ? ' active' : ''}">
                        <a class="page-link" href="#" onclick="gotoPageProduk(${i});return false;">${i}</a>
                    </li>`;
        }
    }
    document.getElementById('pagination-produk').innerHTML = pag;
}

function gotoPageProduk(page) {
    currentPageProduk = page;
    renderTableProduk();
    renderPaginationProduk();
}

function renderStokAkhirProduk() {
    let data = produkData;
    let stokAkhirMap = {};
    let adaStok = false;
    data.forEach(function(row) {
        let masuk = parseFloat(row.masuk) || 0;
        let keluar = parseFloat(row.keluar) || 0;
        let harga = parseFloat(row.hpp) || 0;
        if (!stokAkhirMap[harga]) stokAkhirMap[harga] = { masuk: 0, keluar: 0 };
        stokAkhirMap[harga].masuk += masuk;
        stokAkhirMap[harga].keluar += keluar;
    });
    let stokAkhirList = '';
    Object.entries(stokAkhirMap).forEach(([h, v]) => {
        let sisa = v.masuk - v.keluar;
        if (sisa > 0) {
            adaStok = true;
            stokAkhirList += `<li><b>${sisa} qty</b> dengan HPP <b>Rp${parseFloat(h).toLocaleString('id-ID')}</b></li>`;
        }
    });
    if (!adaStok) stokAkhirList = `<li>0</li>`;
    document.getElementById('stok-akhir-list-produk').innerHTML = stokAkhirList;
    document.getElementById('stok-akhir-box-produk').style.display = '';
}

document.addEventListener('DOMContentLoaded', function () {
    // Set default bulan ini
    document.getElementById('periode_produk').value = new Date().toISOString().slice(0, 7);

    document.getElementById('rowsPerPageProduk').addEventListener('change', function () {
        rowsPerPageProduk = this.value;
        let showAll = (rowsPerPageProduk === 'all');
        let totalPages = showAll ? 1 : Math.ceil(produkData.length / rowsPerPageProduk);
        currentPageProduk = totalPages > 0 ? totalPages : 1;
        renderTableProduk();
        renderPaginationProduk();
    });

    document.getElementById('periode_produk').addEventListener('change', function () {
        currentPageProduk = 1;
        renderTableProduk();
        renderPaginationProduk();
    });

    document.getElementById('lokasi').addEventListener('change', function () {
        currentPageProduk = 1;
        renderTableProduk();
        renderPaginationProduk();
    });
});
</script>