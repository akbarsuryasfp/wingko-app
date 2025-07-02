@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>INPUT PENJUALAN LANGSUNG</h3>
    <form action="{{ route('penjualan.store') }}" method="POST">
        @csrf
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Penjualan -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">No Jual</label>
                    <input type="text" name="no_jual" class="form-control" value="{{ $no_jual }}" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Tanggal Jual</label>
                    <input type="date" name="tanggal_jual" class="form-control" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Pelanggan</label>
                    <select name="kode_pelanggan" class="form-control" required>
                        <option value="">---Pilih Pelanggan---</option>
                        @foreach($pelanggan as $p)
                            <option value="{{ $p->kode_pelanggan }}">{{ $p->nama_pelanggan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                        <option value="">---Pilih Metode---</option>
                        <option value="tunai">Tunai</option>
                        <option value="non tunai">Non Tunai</option>
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control">
                </div>
            </div>
            <!-- Kolom Kanan: Data Produk -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Nama Produk</label>
                    <select id="kode_produk" class="form-control">
                        <option value="">---Pilih Produk---</option>
                        @foreach($produk as $pr)
                            <option value="{{ $pr->kode_produk }}" data-nama="{{ $pr->nama_produk }}"
                                data-harga="{{ $pr->nama_produk == 'Moaci' ? 25000 : ($pr->nama_produk == 'Wingko Babat' ? 20000 : 0) }}">
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
                    <label class="me-2" style="width: 120px;">Harga/Satuan</label>
                    <input type="number" id="harga_satuan" class="form-control">
                </div>
                <div class="mb-3 d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary w-100" onclick="tambahProduk()">Tambah Produk</button>
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PENJUALAN PRODUK</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga/Satuan</th>
                    <th>Subtotal</th> <!-- Ubah dari Total ke Subtotal -->
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <!-- Tambahan: Total dan Lain-lain -->
        <div class="row justify-content-start">
            <div class="col-md-6">
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Harga</label>
                    <div class="col-sm-8">
                        <input type="text" id="total_harga" name="total_harga" class="form-control" readonly>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Diskon</label>
                    <div class="col-sm-8">
                        <input type="number" id="diskon" name="diskon" class="form-control" value="0" min="0" oninput="hitungTotalLain()">
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Jual</label>
                    <div class="col-sm-8">
                        <input type="text" id="total_jual" name="total_jual" class="form-control" readonly>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Bayar</label>
                    <div class="col-sm-8">
                        <input type="number" id="total_bayar" name="total_bayar" class="form-control" value="0" min="0" oninput="hitungTotalLain()">
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Kembalian</label>
                    <div class="col-sm-8">
                        <input type="text" id="kembalian" name="kembalian" class="form-control" readonly>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Piutang</label>
                    <div class="col-sm-8">
                        <input type="text" id="piutang" name="piutang" class="form-control" readonly>
                    </div>
                </div>
                <!-- Hapus/komentari bagian status pembayaran berikut -->
                <!--
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Status Pembayaran</label>
                    <div class="col-sm-8">
                        <select name="status_pembayaran" id="status_pembayaran" class="form-control" required readonly>
                            <option value="lunas">Lunas</option>
                            <option value="belum lunas">Belum Lunas</option>
                        </select>
                    </div>
                </div>
                -->
            </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <div>
                <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning">Reset</button>
            </div>
            <button type="submit" class="btn btn-success">Submit</button>
        </div>
        <input type="hidden" name="detail_json" id="detail_json">
        <input type="hidden" name="jenis_penjualan" value="{{ $jenis_penjualan }}">
    </form>
</div>

<script>
    let daftarProduk = [];

    function tambahProduk() {
        const produkSelect = document.getElementById('kode_produk');
        const kode_produk = produkSelect.value;
        const nama_produk = produkSelect.options[produkSelect.selectedIndex].dataset.nama;
        const jumlah = parseFloat(document.getElementById('jumlah').value);
        const harga_satuan = parseFloat(document.getElementById('harga_satuan').value);

        if (!kode_produk || !jumlah || !harga_satuan || jumlah <= 0 || harga_satuan <= 0) {
            alert("Silakan lengkapi data produk.");
            return;
        }

        // Cek apakah produk sudah ada di daftar
        if (daftarProduk.some(item => item.kode_produk === kode_produk)) {
            alert("Produk sudah ada di daftar!");
            return;
        }

        const subtotal = jumlah * harga_satuan;
        daftarProduk.push({ kode_produk, nama_produk, jumlah, harga_satuan, subtotal });
        updateTabel();

        // Reset input produk
        produkSelect.selectedIndex = 0;
        document.getElementById('jumlah').value = '';
        document.getElementById('harga_satuan').value = '';
    }

    function hapusBaris(index) {
        daftarProduk.splice(index, 1);
        updateTabel();
    }

    function updateTabel() {
        const tbody = document.querySelector('#daftar-produk tbody');
        tbody.innerHTML = '';

        let totalHarga = 0;

        daftarProduk.forEach((item, index) => {
            totalHarga += item.subtotal;

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_produk}</td>
                    <td>${item.jumlah}</td>
                    <td>${item.harga_satuan}</td>
                    <td>${item.subtotal}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(${index})" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('total_harga').value = totalHarga;
        document.getElementById('detail_json').value = JSON.stringify(daftarProduk);
        hitungTotalLain();
    }

    function hitungTotalLain() {
        let totalHarga = parseFloat(document.getElementById('total_harga').value) || 0;
        let diskon = parseFloat(document.getElementById('diskon').value) || 0;
        let totalJual = totalHarga - diskon;
        if (totalJual < 0) totalJual = 0;
        document.getElementById('total_jual').value = totalJual;

        let totalBayar = parseFloat(document.getElementById('total_bayar').value) || 0;
        let kembalian = 0, piutang = 0;

        const metode = document.getElementById('metode_pembayaran') ? document.getElementById('metode_pembayaran').value : 'tunai';
        const status = document.querySelector('select[name="status_pembayaran"]') ? document.querySelector('select[name="status_pembayaran"]').value : 'belum lunas';

        if (metode === 'tunai') {
            if (status === 'belum lunas') {
                piutang = totalJual - totalBayar > 0 ? totalJual - totalBayar : 0;
                kembalian = 0;
            } else {
                kembalian = totalBayar > totalJual ? totalBayar - totalJual : 0;
                piutang = 0;
            }
        } else {
            kembalian = 0;
            piutang = totalJual - totalBayar > 0 ? totalJual - totalBayar : 0;
        }
        document.getElementById('kembalian').value = kembalian;
        document.getElementById('piutang').value = piutang;
    }

    document.getElementById('diskon').addEventListener('input', hitungTotalLain);
    document.getElementById('total_bayar').addEventListener('input', hitungTotalLain);
    if(document.getElementById('metode_pembayaran')) {
        document.getElementById('metode_pembayaran').addEventListener('change', hitungTotalLain);
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        if (daftarProduk.length === 0) {
            alert('Minimal 1 produk harus ditambahkan!');
            e.preventDefault();
            return false;
        }
    });

    document.getElementById('kode_produk').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const harga = selected.getAttribute('data-harga');
        document.getElementById('harga_satuan').value = harga ? harga : '';
    });
</script>
@endsection

