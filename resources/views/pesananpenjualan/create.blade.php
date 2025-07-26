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
                        <input type="text" id="diskon_satuan" class="form-control" value="0" min="0" autocomplete="off">
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

        <div class="d-flex justify-content-between mt-4">
            <div>
                <a href="{{ route('pesananpenjualan.index') }}" class="btn btn-secondary" title="Kembali">Back</a>
                <button type="reset" class="btn btn-warning" title="Reset">Reset</button>
            </div>
            <div class="d-flex align-items-center gap-3">
                <label class="mb-0">Total Pesanan</label>
                <div class="input-group" style="width: 180px;">
                    <span class="input-group-text">Rp</span>
                    <input type="text" id="total_pesanan_display" readonly class="form-control" tabindex="-1" style="background:#e9ecef;pointer-events:none;">
                </div>
                <input type="hidden" id="total_pesanan" name="total_pesanan">
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
        </div>
        <input type="hidden" name="detail_json" id="detail_json">
            </form>
        </div>
    </div>
</div>

<script>
    let daftarProduk = [];

    // Helper format ribuan
    function formatNumberInput(val) {
        val = String(val).replace(/[^\d]/g, '');
        if (!val) return '';
        return val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    function parseNumberInput(val) {
        return parseInt(String(val).replace(/\D/g, '')) || 0;
    }

    // Live format ribuan untuk input harga_satuan dan diskon_satuan
    function addLiveRibuanFormat(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('input', function(e) {
            const cursor = this.selectionStart;
            const oldLength = this.value.length;
            let val = this.value;
            this.value = formatNumberInput(val);
            // Kembalikan posisi kursor
            const newLength = this.value.length;
            this.setSelectionRange(cursor + (newLength - oldLength), cursor + (newLength - oldLength));
        });
    }
    addLiveRibuanFormat('harga_satuan');
    addLiveRibuanFormat('diskon_satuan');

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
        document.getElementById('diskon_satuan').value = 0;
    }

    function hapusBaris(index) {
        daftarProduk.splice(index, 1);
        updateTabel();
    }

    function formatRupiah(angka) {
        if (!angka && angka !== 0) return '';
        return 'Rp ' + parseFloat(angka).toLocaleString('id-ID');
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

        // Format total pesanan for display, but set hidden input for backend
        document.getElementById('total_pesanan_display').value = totalPesanan > 0 ? formatNumberInput(totalPesanan) : '';
        document.getElementById('total_pesanan').value = totalPesanan;
        document.getElementById('detail_json').value = JSON.stringify(daftarProduk);
    }

    // Cegah submit jika belum ada produk
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
        const satuan = selected.getAttribute('data-satuan') || '';
        document.getElementById('harga_satuan').value = harga ? formatNumberInput(harga) : '';
        document.getElementById('satuan').value = satuan;
    });
</script>
@endsection