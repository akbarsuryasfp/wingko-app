@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">EDIT PENJUALAN KONSINYASI MASUK</h3>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('jualkonsinyasimasuk.update', $jual->no_jualkonsinyasi) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>No Jual Konsinyasi</label>
            <input type="text" name="no_jualkonsinyasi" class="form-control" value="{{ $jual->no_jualkonsinyasi }}" readonly>
        </div>
        <div class="mb-3">
            <label>No Konsinyasi Masuk</label>
            <select name="no_konsinyasimasuk" class="form-control" required>
                <option value="">---Pilih Konsinyasi Masuk---</option>
                @foreach($konsinyasiMasukList as $k)
                    <option value="{{ $k->no_konsinyasimasuk }}" {{ $jual->no_konsinyasimasuk == $k->no_konsinyasimasuk ? 'selected' : '' }}>{{ $k->no_konsinyasimasuk }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Tanggal Jual</label>
            <input type="date" name="tanggal_jual" class="form-control" value="{{ $jual->tanggal_jual }}" required>
        </div>
        <div class="mb-3">
            <label>Keterangan</label>
            <input type="text" name="keterangan" class="form-control" value="{{ $jual->keterangan }}">
        </div>
        <hr>
        <h4 class="text-center">DETAIL PRODUK TERJUAL</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk-jual">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga Jual</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <div class="d-flex justify-content-end mb-3">
            <label class="me-2">Total</label>
            <input type="text" id="total_jual_view" readonly class="form-control" style="width: 160px;">
            <input type="hidden" id="total_jual" name="total_jual" value="{{ $jual->total_jual }}">
        </div>
        <input type="hidden" name="detail_json" id="detail_json">
        <button type="submit" class="btn btn-success">Update</button>
    </form>
</div>
<script>
// Dummy produk, ganti dengan AJAX jika perlu
let produkList = [];
let detailJual = @json($details);
function tambahProdukJual() {
    // Implementasi JS untuk tambah produk ke detailJual
}
function hapusProdukJual(idx) {
    // Implementasi JS untuk hapus produk dari detailJual
}
function updateTabelJual() {
    // Implementasi JS untuk render tabel detailJual
}
</script>
@endsection
