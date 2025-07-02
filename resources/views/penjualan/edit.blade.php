@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>EDIT PENJUALAN</h3>
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
        <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-md-6">
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Kode Jual</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" value="{{ $penjualan->no_jual }}" readonly>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Tanggal Jual</label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control" name="tanggal_jual" value="{{ $penjualan->tanggal_jual }}" required>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Pelanggan</label>
                    <div class="col-sm-8">
                        <select class="form-control" name="kode_pelanggan" required>
                            <option value="">---Pilih Pelanggan---</option>
                            @foreach($pelanggan as $p)
                                <option value="{{ $p->kode_pelanggan }}" {{ $penjualan->kode_pelanggan == $p->kode_pelanggan ? 'selected' : '' }}>{{ $p->nama_pelanggan }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Metode Pembayaran</label>
                    <div class="col-sm-8">
                        <select class="form-control" name="metode_pembayaran" id="metode_pembayaran" required>
                            <option value="tunai" {{ $penjualan->metode_pembayaran == 'tunai' ? 'selected' : '' }}>Tunai</option>
                            <option value="non tunai" {{ $penjualan->metode_pembayaran == 'non tunai' ? 'selected' : '' }}>Non Tunai</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Keterangan</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="keterangan" value="{{ $penjualan->keterangan }}">
                    </div>
                </div>
            </div>
            <!-- Kolom Kanan -->
            <div class="col-md-6">
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Total Harga</label>
                    <div class="col-sm-8">
                        <input type="number" class="form-control" id="total_harga" name="total_harga" value="{{ $penjualan->total_harga }}" readonly>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Diskon</label>
                    <div class="col-sm-8">
                        <input type="number" class="form-control" id="diskon" name="diskon" value="{{ $penjualan->diskon }}" oninput="hitungTotal()">
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Total Jual</label>
                    <div class="col-sm-8">
                        <input type="number" class="form-control" id="total_jual" name="total_jual" value="{{ $penjualan->total_jual }}" readonly>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Total Bayar</label>
                    <div class="col-sm-8">
                        <input type="number" class="form-control" id="total_bayar" name="total_bayar" value="{{ $penjualan->total_bayar }}" oninput="hitungTotal()">
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Kembalian</label>
                    <div class="col-sm-8">
                        <input type="number" class="form-control" id="kembalian" name="kembalian" value="{{ $penjualan->kembalian }}" readonly>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Piutang</label>
                    <div class="col-sm-8">
                        <input type="number" class="form-control" id="piutang" name="piutang" value="{{ $penjualan->piutang }}" readonly>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Status Pembayaran</label>
                    <div class="col-sm-8">
                        <select class="form-control" name="status_pembayaran" id="status_pembayaran" required readonly>
                            <option value="lunas" {{ $penjualan->status_pembayaran == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum lunas" {{ $penjualan->status_pembayaran == 'belum lunas' ? 'selected' : '' }}>Belum Lunas</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-4 text-center">DAFTAR PRODUK TERJUAL</h5>
        <div id="detail_produk">
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
                    @foreach($details as $i => $detail)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $detail['nama_produk'] }}</td>
                        <td>{{ $detail['jumlah'] }}</td>
                        <td>{{ number_format($detail['harga_satuan'],0,',','.') }}</td>
                        <td>{{ number_format($detail['subtotal'],0,',','.') }}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris({{ $i }})" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <input type="hidden" name="detail_json" id="detail_json">

        <div class="d-flex justify-content-between mt-4">
            <div>
                <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning ms-2">Reset</button>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
        </div>
    </form>
</div>

<style>
    .row.mb-3 { margin-bottom: 0.5rem !important; }
    .form-label, .form-control { margin-bottom: 2px !important; }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let daftarProduk = @json($details);

    function tambahProduk() {
        const kode_produk = $('#kode_produk').val();
        const nama_produk = $('#kode_produk option:selected').data('nama');
        const jumlah = parseInt($('#jumlah').val());
        const harga_satuan = parseInt($('#harga_satuan').val());

        if (!kode_produk || !jumlah || !harga_satuan || jumlah <= 0 || harga_satuan <= 0) {
            alert('Lengkapi data produk dengan benar!');
            return;
        }

        const subtotal = jumlah * harga_satuan;
        daftarProduk.push({ kode_produk, nama_produk, jumlah, harga_satuan, subtotal });
        updateTabel();
        $('#kode_produk').val('');
        $('#jumlah').val('');
        $('#harga_satuan').val('');
    }

    function hapusBaris(index) {
        daftarProduk.splice(index, 1);
        updateTabel();
    }

    function updateTabel() {
        let html = `<table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>`;
        let totalHarga = 0;
        daftarProduk.forEach((item, i) => {
            html += `<tr>
                <td>${item.nama_produk}</td>
                <td>${item.jumlah}</td>
                <td>${item.harga_satuan.toLocaleString('id-ID')}</td>
                <td>${item.subtotal.toLocaleString('id-ID')}</td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(${i})">Hapus</button></td>
            </tr>`;
            totalHarga += item.subtotal;
        });
        html += `</tbody></table>`;
        $('#detail_produk').html(html);
        $('#total_harga').val(totalHarga);
        hitungTotal();
        $('#detail_json').val(JSON.stringify(daftarProduk));
    }

function hitungTotal() {
    let totalHarga = parseInt($('#total_harga').val()) || 0;
    let diskon = parseInt($('#diskon').val()) || 0;
    let totalJual = totalHarga - diskon;
    if (totalJual < 0) totalJual = 0;
    $('#total_jual').val(totalJual);

    let totalBayar = parseInt($('#total_bayar').val()) || 0;
    let kembalian = 0, piutang = 0;

    if ($('#metode_pembayaran').val() === 'tunai') {
        kembalian = totalBayar > totalJual ? totalBayar - totalJual : 0;
        piutang = 0;
    } else {
        kembalian = 0;
        piutang = totalJual - totalBayar > 0 ? totalJual - totalBayar : 0;
    }
    $('#kembalian').val(kembalian);
    $('#piutang').val(piutang);

    // Status pembayaran otomatis
    let status = 'belum lunas';
    if (totalBayar === totalJual && totalJual > 0) {
        status = 'lunas';
    }
    $('#status_pembayaran').val(status);
}
    $(document).ready(function() {
        let produkOptions = `<option value="">---Pilih Produk---</option>
            @foreach($produk as $p)
                <option value="{{ $p->kode_produk }}" data-nama="{{ $p->nama_produk }}">{{ $p->kode_produk }} - {{ $p->nama_produk }}</option>
            @endforeach`;
        $('#kode_produk').html(produkOptions);
    });
</script>
@endsection