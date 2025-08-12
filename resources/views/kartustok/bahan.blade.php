@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4 px-2">
    <div class="card shadow" style="max-width:100vw;">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">KARTU PERSEDIAAN BAHAN</h4>
                <a href="{{ route('kartustok.laporan_bahan') }}" class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-text"></i> Laporan Persediaan Bahan
                </a>
            </div>

            <form method="GET" class="row g-2 align-items-end mb-4">
                <div class="col">
                    <label for="kode_bahan" class="form-label">Nama Bahan</label>
                    <select id="kode_bahan" name="kode_bahan" class="form-control" onchange="setSatuanOtomatis()">
                        <option value="">-- Pilih Bahan --</option>
                        @foreach($bahanList as $bahan)
                            <option value="{{ $bahan->kode_bahan }}" data-satuan="{{ $bahan->satuan }}">{{ $bahan->nama_bahan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label for="satuan" class="form-label">Satuan</label>
                    <input type="text" id="satuan" name="satuan" class="form-control" value="{{ $satuan ?? '' }}" readonly>
                </div>
                <div class="col">
                    <label for="periode" class="form-label">Periode</label>
                    <input type="month" id="periode" name="periode" class="form-control" value="{{ date('Y-m') }}">
                </div>
                <div class="col">
                    <label for="rowsPerPage" class="form-label">Show</label>
                    <select id="rowsPerPage" class="form-select">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="all">All</option>
                    </select>
                </div>
            </form>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div id="riwayat-title" style="display:none;">
                    <span style="font-size:1.2em;">🔍</span>
                    <b>Riwayat Masuk dan Keluar <span id="nama-bahan-title"></span></b>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tabel-persediaan" class="table table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>No Transaksi</th>
                            <th>Keterangan</th>
                            <th>Tanggal</th>
                            <th>Harga per kg</th>
                            <th>Masuk (kg)</th>
                            <th>Keluar (kg)</th>
                            <th>Sisa (kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data persediaan.</td>
                        </tr>
                    </tbody>
                </table>
                <nav>
                    <ul class="pagination justify-content-end" id="pagination"></ul>
                </nav>
            </div>

            <div id="stok-akhir-box" class="mt-4" style="display:none;">
                <span style="font-size:1.2em;">📊</span>
                <b>Stok Akhir <span id="nama-bahan-stok"></span></b>
                <ul id="stok-akhir-list" class="mt-2"></ul>
            </div>
            <div id="stok-akhir-box-produk" class="mt-4" style="display:none;">
                <span style="font-size:1.2em;">📊</span>
                <b>Stok Akhir <span id="nama-produk-stok"></span></b>
                <ul id="stok-akhir-list-produk" class="mt-2"></ul>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
let persediaanData = [];
let currentPage = 1;
let rowsPerPage = 10;

function setSatuanOtomatis() {
    var select = document.getElementById('kode_bahan');
    var satuan = select.options[select.selectedIndex].getAttribute('data-satuan') || '';
    document.getElementById('satuan').value = satuan;
    document.getElementById('rowsPerPage').value = '10';
    rowsPerPage = 10;
    // Ambil nama bahan untuk judul
    var namaBahan = select.options[select.selectedIndex].text || '';
    document.getElementById('nama-bahan-title').innerText = namaBahan;
    document.getElementById('nama-bahan-stok').innerText = namaBahan;

    if (select.value) {
        document.getElementById('riwayat-title').style.display = '';
        fetch('/kartustok/api/' + select.value)
            .then(res => res.json())
            .then(data => {
                persediaanData = data;
                // Hitung total halaman
                let perPage = document.getElementById('rowsPerPage').value;
                let showAll = (perPage === 'all');
                let totalPages = showAll ? 1 : Math.ceil(persediaanData.length / perPage);
                // Langsung ke halaman terakhir
                currentPage = totalPages > 0 ? totalPages : 1;
                renderTable();
                renderPagination();
                renderStokAkhir();
            });
    } else {
        document.getElementById('riwayat-title').style.display = 'none';
        document.querySelector('#tabel-persediaan tbody').innerHTML = `<tr><td colspan="8" class="text-center">Tidak ada data persediaan.</td></tr>`;
        document.getElementById('stok-akhir-box').style.display = 'none';
        persediaanData = [];
        renderPagination();
    }
}

function renderTable() {
    let tbody = '';
    let saldoQty = 0;
    let saldoPerRow = [];
    let data = persediaanData;

    // Ambil filter bulan dan tanggal
    let filterPeriode = document.getElementById('periode').value; // format: 'YYYY-MM'
    let filterTanggal = document.getElementById('tanggal') ? document.getElementById('tanggal').value : '';

    // Filter data
    if (filterTanggal) {
        // Jika tanggal dipilih, filter per tanggal
        data = data.filter(row => row.tanggal && row.tanggal.startsWith(filterTanggal));
    } else if (filterPeriode) {
        // Jika tanggal kosong, filter per bulan
        data = data.filter(row => row.tanggal && row.tanggal.startsWith(filterPeriode));
    }

    let start = 0, end = data.length;
    let showAll = (rowsPerPage === 'all');
    if (!showAll) {
        rowsPerPage = parseInt(document.getElementById('rowsPerPage').value);
        start = (currentPage - 1) * rowsPerPage;
        end = Math.min(start + rowsPerPage, data.length);
    }
    if (data.length === 0) {
        tbody = `<tr><td colspan="8" class="text-center">Tidak ada data persediaan.</td></tr>`;
    } else {
        for (let i = 0; i < data.length; i++) {
            let row = data[i];
            let masuk = parseFloat(row.masuk) || 0;
            let keluar = parseFloat(row.keluar) || 0;
            let harga = parseFloat(row.harga);
            let keterangan = row.keterangan || '-';
            saldoQty += masuk - keluar;
            saldoPerRow.push(saldoQty);

            if (showAll || (i >= start && i < end)) {
                tbody += `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${row.no_transaksi}</td>
                        <td>${keterangan}</td>
                        <td>${formatTanggal(row.tanggal)}</td>
                        <td>Rp${harga.toLocaleString('id-ID')}</td>
                        <td>${masuk}</td>
                        <td>${keluar}</td>
                        <td>${saldoQty}</td>
                    </tr>
                `;
            }
        }
    }
    document.querySelector('#tabel-persediaan tbody').innerHTML = tbody;
}

function renderPagination() {
    let data = persediaanData;
    let totalRows = data.length;
    let perPage = document.getElementById('rowsPerPage').value;
    let showAll = (perPage === 'all');
    let totalPages = showAll ? 1 : Math.ceil(totalRows / perPage);

    let pag = '';
    if (totalPages > 1) {
        for (let i = 1; i <= totalPages; i++) {
            pag += `<li class="page-item${i === currentPage ? ' active' : ''}">
                        <a class="page-link" href="#" onclick="gotoPage(${i});return false;">${i}</a>
                    </li>`;
        }
    }
    document.getElementById('pagination').innerHTML = pag;
}

function gotoPage(page) {
    currentPage = page;
    renderTable();
    renderPagination();
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('rowsPerPage').addEventListener('change', function () {
        rowsPerPage = this.value;
        currentPage = 1;
        renderTable();
        renderPagination();
    });
    document.getElementById('periode').addEventListener('change', function () {
        // Reset tanggal jika bulan diubah
        if(document.getElementById('tanggal')) document.getElementById('tanggal').value = '';
        currentPage = 1;
        renderTable();
        renderPagination();
    });
    if(document.getElementById('tanggal')) {
        document.getElementById('tanggal').addEventListener('change', function () {
            currentPage = 1;
            renderTable();
            renderPagination();
        });
    }
    // Set default bulan ini
    document.getElementById('periode').value = new Date().toISOString().slice(0, 7);
    // Set default tanggal kosong
    if(document.getElementById('tanggal')) document.getElementById('tanggal').value = '';
});

function renderStokAkhir() {
    let data = persediaanData;
    let stokAkhirMap = {};
    let adaStok = false;
    data.forEach(function(row) {
        let masuk = parseFloat(row.masuk) || 0;
        let keluar = parseFloat(row.keluar) || 0;
        let harga = parseFloat(row.harga);
        if (!stokAkhirMap[harga]) stokAkhirMap[harga] = { masuk: 0, keluar: 0 };
        stokAkhirMap[harga].masuk += masuk;
        stokAkhirMap[harga].keluar += keluar;
    });
    let stokAkhirList = '';
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