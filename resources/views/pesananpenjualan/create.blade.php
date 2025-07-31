@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-4">INPUT PESANAN</h3>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('pesananpenjualan.store') }}" method="POST">
                @csrf
                <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Pesanan -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">No Pesanan</label>
                    <input type="text" name="no_pesanan" class="form-control" value="{{ $no_pesanan }}" readonly tabindex="-1" style="background:#e9ecef;pointer-events:none;">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Tanggal Pesanan</label>
                    <input type="date" name="tanggal_pesanan" class="form-control" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Tanggal Pengiriman</label>
                    <input type="date" name="tanggal_pengiriman" class="form-control">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Pelanggan</label>
                    <select name="kode_pelanggan" class="form-control" required>
                        <option value="">---Pilih Pelanggan---</option>
                        @foreach($pelanggan as $p)
                            <option value="{{ $p->kode_pelanggan }}">{{ $p->nama_pelanggan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" maxlength="255">
                </div>
            </div>

            <!-- Kolom Kanan: Data Produk -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Nama Produk</label>
                    <select id="kode_produk" class="form-control">
                        <option value="">---Pilih Produk---</option>
                        @foreach($produk as $pr)
                            <option value="{{ $pr->kode_produk }}" data-nama="{{ $pr->nama_produk }}" data-satuan="{{ $pr->satuan ?? '' }}"
                                @if($pr->nama_produk == 'Moaci') data-harga="25000"
                                @elseif($pr->nama_produk == 'Wingko Babat') data-harga="20000"
                                @else data-harga="0" @endif>
                                {{ $pr->nama_produk }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Jumlah</label>
                    <input type="number" id="jumlah" class="form-control">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Satuan</label>
                    <input type="text" id="satuan" class="form-control" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Harga/Satuan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="harga_satuan" class="form-control" autocomplete="off">
                    </div>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Diskon/Satuan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="diskon_satuan" class="form-control" min="0" autocomplete="off">
                    </div>
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary w-100" onclick="tambahProduk()">Tambah Produk</button>
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PRODUK PESANAN</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Satuan</th>
                    <th>Jumlah</th>
                    <th>Harga/Satuan</th>
                    <th>Diskon/Satuan</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <!-- Total Pesanan, Uang Muka, Sisa Tagihan -->
        <div class="row mt-4 justify-content-start">
            <div class="col-md-6">
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Pesanan</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="total_pesanan_display" readonly class="form-control" tabindex="-1" style="background:#e9ecef;pointer-events:none;">
                        </div>
                        <input type="hidden" id="total_pesanan" name="total_pesanan">
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Uang Muka (DP)</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                        <input type="text" id="uang_muka" name="uang_muka" class="form-control" autocomplete="off" value="{{ old('uang_muka', $uang_muka ?? '') }}">
                        </div>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Sisa Tagihan</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                        <input type="text" id="sisa_tagihan" name="sisa_tagihan" class="form-control" readonly tabindex="-1" style="background:#e9ecef;pointer-events:none;" value="{{ old('sisa_tagihan', $sisa_tagihan ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="detail_json" id="detail_json">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <a href="{{ route('pesananpenjualan.index') }}" class="btn btn-secondary me-2" title="Kembali">Back</a>
                <button type="reset" class="btn btn-warning" title="Reset">Reset</button>
            </div>
            <button type="submit" class="btn btn-success">Submit</button>
        </div>
</div>

<script>
    let daftarProduk = [];

    // Helper: parse input ke integer normal (tanpa ribuan)
    function parseNumberInput(val) {
        val = String(val).replace(/\D/g, '');
        return val ? parseInt(val, 10) : 0;
    }

    // Pastikan input uang_muka dan sisa_tagihan selalu integer tanpa ribuan
    window.addEventListener('DOMContentLoaded', function() {
        var uangMukaInput = document.getElementById('uang_muka');
        if (uangMukaInput) {
            uangMukaInput.value = parseNumberInput(uangMukaInput.value) || '';
        }
        var sisaTagihanInput = document.getElementById('sisa_tagihan');
        if (sisaTagihanInput) {
            sisaTagihanInput.value = parseNumberInput(sisaTagihanInput.value) || '';
        }
    });

    function tambahProduk() {
        const produkSelect = document.getElementById('kode_produk');
        const kode_produk = produkSelect.value;
        const nama_produk = produkSelect.options[produkSelect.selectedIndex].dataset.nama;
        const jumlah = parseFloat(document.getElementById('jumlah').value);
        // Gunakan parseNumberInput agar titik ribuan tidak error
        const harga_satuan = parseNumberInput(document.getElementById('harga_satuan').value);
        const diskon_satuan = parseNumberInput(document.getElementById('diskon_satuan').value) || 0;
        const satuan = document.getElementById('satuan').value;

        if (!kode_produk || !jumlah || !harga_satuan || jumlah <= 0 || harga_satuan <= 0) {
            alert("Silakan lengkapi data produk.");
            return;
        }

        // Cek apakah produk sudah ada di daftar
        if (daftarProduk.some(item => item.kode_produk === kode_produk)) {
            alert("Produk sudah ada di daftar!");
            return;
        }

        const subtotal = jumlah * (harga_satuan - diskon_satuan);
        daftarProduk.push({ kode_produk, nama_produk, satuan, jumlah, harga_satuan, diskon_satuan, subtotal });
        updateTabel();

        // Reset input produk
        produkSelect.selectedIndex = 0;
        document.getElementById('jumlah').value = '';
        document.getElementById('harga_satuan').value = '';
        document.getElementById('diskon_satuan').value = '';
    }

    function hapusBaris(index) {
        daftarProduk.splice(index, 1);
        updateTabel();
    }

    function formatRupiah(angka) {
        if (!angka && angka !== 0) return '';
        // Pastikan angka adalah integer, tidak ada desimal
        angka = Math.floor(Number(angka));
        return 'Rp' + angka.toLocaleString('id-ID');
    }

    function updateTabel() {
        const tbody = document.querySelector('#daftar-produk tbody');
        tbody.innerHTML = '';

        let totalPesanan = 0;

        daftarProduk.forEach((item, index) => {
            totalPesanan += item.subtotal;

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_produk}</td>
                    <td>${item.satuan || ''}</td>
                    <td>${item.jumlah}</td>
                    <td>${formatRupiah(item.harga_satuan)}</td>
                    <td>${formatRupiah(item.diskon_satuan)}</td>
                    <td>${formatRupiah(item.subtotal)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(${index})" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        // Tampilkan total pesanan apa adanya (tanpa titik ribuan)
        document.getElementById('total_pesanan_display').value = totalPesanan > 0 ? totalPesanan : '';
        document.getElementById('total_pesanan').value = totalPesanan;
        document.getElementById('detail_json').value = JSON.stringify(daftarProduk);

        // Hitung sisa tagihan (total pesanan - uang muka)
        const uangMuka = parseNumberInput(document.getElementById('uang_muka').value);
        const sisaTagihan = Math.max(totalPesanan - uangMuka, 0);
        document.getElementById('sisa_tagihan').value = sisaTagihan;
        // Pastikan uang muka juga tanpa titik
        document.getElementById('uang_muka').value = uangMuka ? uangMuka : '';
    }
    // Update sisa tagihan saat uang muka diubah
    document.getElementById('uang_muka').addEventListener('input', function() {
        updateTabel();
        // Nilai tetap integer tanpa titik
        this.value = parseNumberInput(this.value) || '';
    });

    // Cegah submit jika belum ada produk
    document.querySelector('form').addEventListener('submit', function(e) {
        // Pastikan value uang_muka dan sisa_tagihan yang dikirim ke backend adalah numerik (tanpa titik)
        var uangMukaInput = document.getElementById('uang_muka');
        if (uangMukaInput) {
            uangMukaInput.value = parseNumberInput(uangMukaInput.value) || '';
        }
        var sisaTagihanInput = document.getElementById('sisa_tagihan');
        if (sisaTagihanInput) {
            sisaTagihanInput.value = parseNumberInput(sisaTagihanInput.value) || '';
        }
        if (daftarProduk.length === 0) {
            alert('Minimal 1 produk harus ditambahkan!');
            e.preventDefault();
            return false;
        }
    });
    // Pastikan setelah reset, nilai tetap integer tanpa titik
    document.querySelector('form').addEventListener('reset', function() {
        setTimeout(function() {
            var uangMukaInput = document.getElementById('uang_muka');
            if (uangMukaInput) uangMukaInput.value = parseNumberInput(uangMukaInput.value) || '';
            var sisaTagihanInput = document.getElementById('sisa_tagihan');
            if (sisaTagihanInput) sisaTagihanInput.value = parseNumberInput(sisaTagihanInput.value) || '';
        }, 50);
    });

    document.getElementById('kode_produk').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const harga = selected.getAttribute('data-harga');
        const satuan = selected.getAttribute('data-satuan') || '';
        document.getElementById('harga_satuan').value = harga ? parseNumberInput(harga) : '';
        document.getElementById('satuan').value = satuan;
    });
</script>
@endsection