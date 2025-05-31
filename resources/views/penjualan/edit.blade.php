@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">EDIT PENJUALAN</h3>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('penjualan.update', $penjualan->no_jual) }}" method="POST">
        @csrf
        @method('PUT')
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Penjualan -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">No Penjualan</label>
                    <input type="text" name="no_jual" class="form-control" value="{{ $penjualan->no_jual }}" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Tanggal Jual</label>
                    <input type="date" name="tanggal_jual" class="form-control" value="{{ $penjualan->tanggal_jual }}" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Pelanggan</label>
                    <select name="kode_pelanggan" class="form-control" required>
                        <option value="">---Pilih Pelanggan---</option>
                        @foreach($pelanggan as $p)
                            <option value="{{ $p->kode_pelanggan }}" {{ $penjualan->kode_pelanggan == $p->kode_pelanggan ? 'selected' : '' }}>{{ $p->nama_pelanggan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Metode Pembayaran</label>
                    <select name="metode_pembayaran" class="form-control" required>
                        <option value="tunai" {{ $penjualan->metode_pembayaran == 'tunai' ? 'selected' : '' }}>Tunai</option>
                        <option value="kredit" {{ $penjualan->metode_pembayaran == 'kredit' ? 'selected' : '' }}>Kredit</option>
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Status Pembayaran</label>
                    <select name="status_pembayaran" class="form-control" required>
                        <option value="belum lunas" {{ $penjualan->status_pembayaran == 'belum lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        <option value="lunas" {{ $penjualan->status_pembayaran == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" value="{{ $penjualan->keterangan }}">
                </div>
            </div>

            <!-- Kolom Kanan: Data Produk -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Produk</label>
                    <select id="kode_produk" class="form-control">
                        <option value="">---Pilih Produk---</option>
                        @foreach($produk as $pr)
                            <option value="{{ $pr->kode_produk }}" data-nama="{{ $pr->nama_produk }}">{{ $pr->nama_produk }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Jumlah</label>
                    <input type="number" id="jumlah" class="form-control">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Harga Satuan</label>
                    <input type="number" id="harga_satuan" class="form-control">
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary w-100" onclick="tambahProduk()">Tambah Produk</button>
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PRODUK TERJUAL</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="d-flex justify-content-between mt-4">
            <div>
                <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning">Reset</button>
            </div>
            <div class="d-flex align-items-center gap-3">
                <label class="mb-0">Total</label>
                <input type="text" id="total_jual" name="total" readonly class="form-control" style="width: 160px;">
                <button type="submit" class="btn btn-success">Update</button>
            </div>
        </div>

        <input type="hidden" name="detail_json" id="detail_json">
    </form>
</div>

<script>
    // Inisialisasi dari backend
    let daftarProduk = @json($details);

    function tambahProduk() {
        const produkSelect = document.getElementById('kode_produk');
        const kode_produk = produkSelect.value;
        const nama_produk = produkSelect.options[produkSelect.selectedIndex].dataset.nama;
        const jumlah = parseFloat(document.getElementById('jumlah').value);
        const harga_satuan = parseFloat(document.getElementById('harga_satuan').value);

        if (!kode_produk || !jumlah || !harga_satuan) {
            alert("Silakan lengkapi data produk.");
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

        let totalJual = 0;

        daftarProduk.forEach((item, index) => {
            totalJual += item.subtotal;

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_produk}</td>
                    <td>${item.jumlah}</td>
                    <td>${item.harga_satuan}</td>
                    <td>${item.subtotal}</td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(${index})">X</button></td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('total_jual').value = totalJual;
        document.getElementById('detail_json').value = JSON.stringify(daftarProduk);
    }

    // Inisialisasi tabel saat halaman dibuka
    updateTabel();

    // Cegah submit jika belum ada produk
    document.querySelector('form').addEventListener('submit', function(e) {
        if (daftarProduk.length === 0) {
            alert('Minimal 1 produk harus ditambahkan!');
            e.preventDefault();
            return false;
        }
    });
</script>
@endsection