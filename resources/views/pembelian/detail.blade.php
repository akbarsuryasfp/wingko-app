@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Detail Pembelian</h3>
    <div class="row">
        <!-- Informasi Pembelian -->
        <div class="col-md-6">
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Kode Pembelian</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->no_pembelian }}" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Tanggal Pembelian</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->tanggal_pembelian }}" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">No Nota</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->no_nota }}" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Kode Terima Bahan</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->no_terima_bahan }}" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Nama Supplier</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->nama_supplier }}" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Kode Supplier</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->kode_supplier }}" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Metode Bayar</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->metode_bayar }}" readonly>
                </div>
            </div>
        </div>
        <!-- Informasi Harga -->
        <div class="col-md-6">
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Total Harga</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->total_harga }}" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Diskon</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->diskon }}" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Ongkos Kirim</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->ongkir }}" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Total Pembelian</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->total_pembelian }}" readonly>
                </div>
            </div>
                        <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Uang Muka</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->uang_muka }}" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Total Bayar</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->total_bayar }}" readonly>
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-sm-4 col-form-label">Kurang Bayar</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" value="{{ $pembelian->hutang }}" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Bahan -->
    <h5 class="mt-4">Detail Bahan</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Bahan</th>
                <th>Satuan</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $detail)
            <tr>
                <td>{{ $detail->nama_bahan }}</td>
                <td>{{ $detail->satuan }}</td>
                <td>{{ $detail->bahan_masuk }}</td>
                <td>{{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                <td>{{ number_format($detail->subtotal ?? $detail->bahan_masuk * $detail->harga_beli, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-3">
        <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection