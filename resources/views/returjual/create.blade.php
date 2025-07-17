@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">INPUT RETUR PENJUALAN</h3>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('returjual.store') }}" method="POST">
        @csrf
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Retur -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Retur Jual</label>
                    <input type="text" name="no_returjual" class="form-control" value="{{ $no_returjual ?? '' }}" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Jual</label>
                    <select name="no_jual" id="no_jual" class="form-control" required>
                        <option value="">---Pilih No Penjualan---</option>
                        @foreach($penjualan as $item)
                            <option value="{{ $item->no_jual }}" data-pelanggan="{{ $item->kode_pelanggan }}">
                                {{ $item->no_jual }} | {{ $item->tanggal_jual }} | {{ $item->kode_pelanggan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Tanggal Retur</label>
                    <input type="date" name="tanggal_returjual" class="form-control" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Pelanggan</label>
                    <select name="kode_pelanggan" id="kode_pelanggan" class="form-control" required>
                        <option value="">---Pilih Pelanggan---</option>
                        @foreach($pelanggan as $item)
                            <option value="{{ $item->kode_pelanggan }}">{{ $item->nama_pelanggan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Jenis Retur</label>
                    <select name="jenis_retur" class="form-control" required>
                        <option value="">---Pilih Jenis Retur---</option>
                        <option value="Barang">Barang</option>
                        <option value="Uang">Uang</option>
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control">
                </div>
            </div>
            <!-- Kolom Kanan: Data Produk Retur -->
            <!-- HAPUS seluruh input manual produk di sini -->
        </div>

        <hr>

        <!-- Judul di atas tabel, tengah -->
        <h4 class="text-center mb-3">DAFTAR PRODUK RETUR PENJUALAN</h4>

        <!-- Tabel Produk Retur -->
        <table class="table table-bordered text-center align-middle" id="daftar-produk-retur">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Jumlah Retur</th>
                    <th>Harga Satuan</th>
                    <th>Alasan</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="d-flex justify-content-between mt-4">
            <div>
                <a href="{{ route('returjual.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning">Reset</button>
            </div>
            <div class="d-flex align-items-center gap-3">
                <label class="mb-0">Total Retur</label>
                <input type="text" id="total_nilai_retur_view" readonly class="form-control" style="width: 160px;">
                <input type="hidden" id="total_nilai_retur" name="total_nilai_retur">
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
        </div>

        <input type="hidden" name="detail_json" id="detail_json">
    </form>
</div>

<script>
    let daftarProdukRetur = [];
    let maxJumlahPerProduk = {}; // Tambahan: untuk menyimpan jumlah maksimal per produk

    function hapusBarisRetur(index) {
        daftarProdukRetur.splice(index, 1);
        updateTabelRetur();
    }

    function formatRupiah(angka) {
        return 'Rp' + angka.toLocaleString('id-ID');
    }

    function updateTabelRetur() {
        const tbody = document.querySelector('#daftar-produk-retur tbody');
        tbody.innerHTML = '';

        let totalRetur = 0;

        daftarProdukRetur.forEach((item, index) => {
            const subtotal = Number(item.jumlah_retur) * Number(item.harga_satuan) || 0;
            totalRetur += subtotal;

            const max = maxJumlahPerProduk[item.kode_produk] || 0;
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_produk}</td>
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

        document.getElementById('total_nilai_retur_view').value = formatRupiah(totalRetur);
        document.getElementById('total_nilai_retur').value = totalRetur;
        document.getElementById('detail_json').value = JSON.stringify(daftarProdukRetur);
    }

    function updateJumlahRetur(index, value) {
        const kode_produk = daftarProdukRetur[index].kode_produk;
        const max = maxJumlahPerProduk[kode_produk] || 0;
        if (Number(value) > max) {
            alert("Jumlah retur melebihi jumlah penjualan (" + max + ")");
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

    document.getElementById('no_jual').addEventListener('change', function() {
        var no_jual = this.value;
        var pelangganSelect = document.getElementById('kode_pelanggan');
        if (!no_jual) {
            pelangganSelect.value = '';
            daftarProdukRetur = [];
            maxJumlahPerProduk = {};
            updateTabelRetur();
            return;
        }

        fetch('/returjual/detail-penjualan/' + no_jual)
            .then(response => response.json())
            .then(data => {
                if (data.kode_pelanggan) {
                    pelangganSelect.value = data.kode_pelanggan;
                }
                // Simpan jumlah maksimal per produk
                maxJumlahPerProduk = {};
                data.details.forEach(function(item) {
                    maxJumlahPerProduk[item.kode_produk] = item.jumlah;
                });
                daftarProdukRetur = data.details.map(function(item) {
                    return {
                        kode_produk: item.kode_produk,
                        nama_produk: item.nama_produk,
                        jumlah_retur: item.jumlah, // default: semua qty diretur, bisa diubah user
                        harga_satuan: item.harga_satuan,
                        alasan: '',
                        subtotal: item.jumlah * item.harga_satuan
                    };
                });
                updateTabelRetur();
            });
    });
</script>
@endsection

@php
    if (!isset($jenisList)) {
        $jenisList = ['Penjualan', 'Pengembalian'];
    }
@endphp

