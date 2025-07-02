@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>INPUT PENJUALAN PESANAN</h3>
    <form action="{{ route('penjualan.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">No Jual</label>
                    <input type="text" name="no_jual" class="form-control" value="{{ $no_jual ?? '' }}" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">No Pesanan</label>
                    <select name="no_pesanan" id="no_pesanan" class="form-control" required>
                        <option value="">---Pilih Pesanan---</option>
                        @foreach($pesanan as $psn)
                            <option value="{{ $psn->no_pesanan }}"
                                data-tanggal="{{ $psn->tanggal_pesanan }}"
                                data-pelanggan="{{ $psn->nama_pelanggan }}"
                                data-kodepelanggan="{{ $psn->kode_pelanggan }}">
                                {{ $psn->no_pesanan }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Tanggal Jual</label>
                    <input type="date" name="tanggal_jual" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Tanggal Pesanan</label>
                    <input type="text" id="tanggal_pesanan" class="form-control" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <!-- Kosong atau bisa diisi field lain jika ada -->
            </div>
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Pelanggan</label>
                    <input type="text" id="nama_pelanggan" class="form-control" readonly>
                    <input type="hidden" name="kode_pelanggan" id="kode_pelanggan">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <!-- Kosong atau bisa diisi field lain jika ada -->
            </div>
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                        <option value="">---Pilih Metode---</option>
                        <option value="tunai">Tunai</option>
                        <option value="non tunai">Non Tunai</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <!-- Kosong atau bisa diisi field lain jika ada -->
            </div>
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control">
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PESANAN PELANGGAN</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga/Satuan</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Akan diisi otomatis oleh JS --}}
            </tbody>
        </table>

        <!-- Total dan Lain-lain -->
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
    // Data detail pesanan dari backend (controller harus mengirim $pesananDetails)
    let pesananDetails = @json($pesananDetails ?? []);
    let daftarProduk = [];

    document.getElementById('no_pesanan').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        document.getElementById('tanggal_pesanan').value = selected.getAttribute('data-tanggal') || '';
        document.getElementById('nama_pelanggan').value = selected.getAttribute('data-pelanggan') || '';
        document.getElementById('kode_pelanggan').value = selected.getAttribute('data-kodepelanggan') || '';

        // Ambil detail produk dari pesananDetails
        const noPesanan = this.value;
        daftarProduk = pesananDetails[noPesanan] || [];
        updateTabel();
    });

    function updateTabel() {
        const tbody = document.querySelector('#daftar-produk tbody');
        tbody.innerHTML = '';
        let totalHarga = 0;

        daftarProduk.forEach((item, index) => {
            // Pastikan subtotal selalu ada di array
            item.subtotal = item.jumlah * item.harga_satuan;
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

    function hapusBaris(index) {
        daftarProduk.splice(index, 1);
        updateTabel();
    }

    function hitungTotalLain() {
        let totalHarga = parseFloat(document.getElementById('total_harga').value) || 0;
        let diskon = parseFloat(document.getElementById('diskon').value) || 0;
        let totalJual = totalHarga - diskon;
        if (totalJual < 0) totalJual = 0;
        document.getElementById('total_jual').value = totalJual;

        let totalBayar = parseFloat(document.getElementById('total_bayar').value) || 0;
        let kembalian = 0, piutang = 0;

        if (totalBayar >= totalJual) {
            kembalian = totalBayar - totalJual;
            piutang = 0;
        } else {
            kembalian = 0;
            piutang = totalJual - totalBayar;
        }
        document.getElementById('kembalian').value = kembalian;
        document.getElementById('piutang').value = piutang;
    }

    document.getElementById('diskon').addEventListener('input', hitungTotalLain);
    document.getElementById('total_bayar').addEventListener('input', hitungTotalLain);

    document.querySelector('form').addEventListener('submit', function(e) {
        if (daftarProduk.length === 0) {
            alert('Minimal 1 produk harus ditambahkan!');
            e.preventDefault();
            return false;
        }
    });
</script>
@endsection