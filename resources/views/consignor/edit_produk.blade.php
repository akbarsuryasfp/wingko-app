@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Edit Produk Konsinyasi</h4>
    <form action="{{ route('produk-konsinyasi.update', $produk->kode_produk) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Kode Produk</label>
            <input type="text" name="kode_produk" class="form-control" value="{{ $produk->kode_produk }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="nama_produk" class="form-control" value="{{ $produk->nama_produk }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Satuan</label>
            <input type="text" name="satuan" class="form-control" value="{{ $produk->satuan }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Keterangan</label>
            <input type="text" name="keterangan" class="form-control" value="{{ $produk->keterangan }}">
        </div>
        <input type="hidden" name="kode_consignor" value="{{ $produk->kode_consignor }}">
        <div class="d-flex gap-2">
            <a href="{{ route('consignor.index') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-success">Update</button>
        </div>
    </form>
</div>
@endsection