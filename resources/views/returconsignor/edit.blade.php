
@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-4">EDIT RETUR CONSIGNOR</h3>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('returconsignor.update', $returconsignor->no_returconsignor) }}" method="POST">
                @csrf
                @method('PUT')
                <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                    <div style="flex: 1;">
                        <div class="mb-3 d-flex align-items-center">
                            <label class="me-2" style="width: 180px;">No Retur Consignor</label>
                            <input type="text" name="no_returconsignor" class="form-control" value="{{ $returconsignor->no_returconsignor }}" readonly style="pointer-events: none; background: #e9ecef;">
                        </div>
                        <div class="mb-3 d-flex align-items-center">
                            <label class="me-2" style="width: 180px;">No Konsinyasi Masuk</label>
                            <input type="text" class="form-control" value="{{ $returconsignor->no_konsinyasimasuk }} | {{ $returconsignor->consignor->nama_consignor ?? '-' }}" readonly style="pointer-events: none; background: #e9ecef;">
                            <input type="hidden" name="no_konsinyasimasuk" id="no_konsinyasimasuk" value="{{ $returconsignor->no_konsinyasimasuk }}">
                        </div>
                        <div class="mb-3 d-flex align-items-center">
                            <label class="me-2" style="width: 180px;">Tanggal Retur</label>
                            <input type="date" name="tanggal_returconsignor" class="form-control" value="{{ $returconsignor->tanggal_returconsignor }}">
                        </div>
                        <div class="mb-3 d-flex align-items-center">
                            <label class="me-2" style="width: 180px;">Nama Consignor (Pemilik Barang)</label>
                            <input type="text" name="nama_consignor" id="nama_consignor" class="form-control" value="{{ $returconsignor->consignor->nama_consignor ?? '' }}" readonly style="pointer-events: none; background: #e9ecef;">
                            <input type="hidden" name="kode_consignor" id="kode_consignor" value="{{ $returconsignor->kode_consignor }}">
                        </div>
                        <div class="mb-3 d-flex align-items-center">
                            <label class="me-2" style="width: 180px;">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" value="{{ $returconsignor->keterangan }}">
                        </div>
                    </div>
                </div>
                <hr>
                <h4 class="text-center mb-3">DAFTAR PRODUK RETUR CONSIGNOR</h4>
                <table class="table table-bordered text-center align-middle" id="daftar-produk-retur">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Satuan</th>
                            <th>Jumlah Retur</th>
                            <th>Harga/Satuan</th>
                            <th>Alasan</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                <div class="d-flex justify-content-between mt-4">
                    <div>
                        <a href="{{ route('returconsignor.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <label class="mb-0">Total Retur</label>
                        <input type="text" id="total_nilai_retur_view" readonly class="form-control" style="width: 160px;">
                        <input type="hidden" id="total_nilai_retur" name="total_nilai_retur">
                        <button type="submit" class="btn btn-success">Update</button>
                    </div>
                </div>
                <input type="hidden" name="detail_json" id="detail_json">
<script>
    let daftarProdukRetur = [];
    let maxJumlahPerProduk = {};

    function hapusBarisRetur(index) {
        daftarProdukRetur.splice(index, 1);
        updateTabelRetur();
    }

    function formatRupiah(angka) {
        return 'Rp' + Number(angka).toLocaleString('id-ID');
    }

    function updateTabelRetur() {
        const tbody = document.querySelector('#daftar-produk-retur tbody');
        tbody.innerHTML = '';

        let totalRetur = 0;

        if (daftarProdukRetur.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7">Tidak ada produk konsinyasi masuk.</td></tr>';
        } else {
            daftarProdukRetur.forEach((item, index) => {
                const subtotal = Number(item.jumlah_retur) * Number(item.harga_satuan) || 0;
                totalRetur += subtotal;

                const max = maxJumlahPerProduk[item.kode_produk] || 0;
                const row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.nama_produk}</td>
                        <td>${item.satuan || '-'}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm" min="0" max="${max}" value="${item.jumlah_retur}" 
                                onchange="updateJumlahRetur(${index}, this.value)">
                            <small class="text-muted">Maks: ${max}</small>
                        </td>
                        <td>${formatRupiah(item.harga_satuan)}</td>
                        <td>
                            <input type="text" class="form-control form-control-sm" value="${item.alasan || ''}" 
                                onchange="updateAlasanRetur(${index}, this.value)">
                        </td>
                        <td>${formatRupiah(subtotal)}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="hapusBarisRetur(${index})" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        }

        document.getElementById('total_nilai_retur_view').value = formatRupiah(totalRetur);
        document.getElementById('total_nilai_retur').value = totalRetur;
        document.getElementById('detail_json').value = JSON.stringify(daftarProdukRetur);
    }

    function updateJumlahRetur(index, value) {
        const kode_produk = daftarProdukRetur[index].kode_produk;
        const max = maxJumlahPerProduk[kode_produk] || 0;
        if (Number(value) > max) {
            alert("Jumlah retur melebihi jumlah setor (" + max + ")");
            daftarProdukRetur[index].jumlah_retur = max;
        } else {
            daftarProdukRetur[index].jumlah_retur = Number(value);
        }
        daftarProdukRetur[index].subtotal = daftarProdukRetur[index].jumlah_retur * daftarProdukRetur[index].harga_satuan;
        updateTabelRetur();
    }

    function updateAlasanRetur(index, value) {
        daftarProdukRetur[index].alasan = value;
        updateTabelRetur();
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        if (daftarProdukRetur.length === 0) {
            alert('Minimal 1 produk retur harus ditambahkan!');
            e.preventDefault();
            return false;
        }
    });

    // Inisialisasi data produk pada edit, mirip create, tapi ambil value dari input hidden
    window.addEventListener('DOMContentLoaded', function() {
        var initialKonsinyasi = document.getElementById('no_konsinyasimasuk').value;
        if (initialKonsinyasi) {
            fetch('/returconsignor/produk-masuk?no_konsinyasimasuk=' + initialKonsinyasi)
                .then(response => response.json())
                .then(data => {
                    maxJumlahPerProduk = {};
                    daftarProdukRetur = [];
                    if (data.produk && Array.isArray(data.produk)) {
                        data.produk.forEach(function(item) {
                            // Cari data detail yang sudah ada
                            let existing = null;
                            if (window.existingDetails) {
                                existing = window.existingDetails.find(d => d.kode_produk === item.kode_produk);
                            }
                            daftarProdukRetur.push({
                                kode_produk: item.kode_produk,
                                nama_produk: item.nama_produk,
                                satuan: item.satuan || '-',
                                jumlah_retur: existing ? existing.jumlah_retur : 0,
                                harga_satuan: item.harga_setor,
                                alasan: existing ? existing.alasan : '',
                                subtotal: existing ? existing.subtotal : 0
                            });
                            maxJumlahPerProduk[item.kode_produk] = item.jumlah_setor;
                        });
                    }
                    updateTabelRetur();
                });
        }
    });

    // Data detail dari backend
    window.existingDetails = @json($detailsArray);
</script>
    </form>
</div>
@endsection
