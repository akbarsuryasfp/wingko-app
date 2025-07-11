@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Edit Produk</h2>
    <form action="{{ route('produk.update', $produk->kode_produk) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3 d-flex align-items-center">
            <label for="kode_produk" class="form-label mb-0" style="width:150px;">Kode Produk</label>
            <input type="text" class="form-control" id="kode_produk" name="kode_produk" value="{{ $produk->kode_produk }}" readonly style="width:300px;">
        </div>
    
        <div class="mb-3 d-flex align-items-center">
            <label for="nama_produk" class="form-label mb-0" style="width:150px;">Nama Produk</label>
            <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="{{ old('nama_produk', $produk->nama_produk) }}" required style="width:300px;">
            @error('nama_produk')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="satuan" class="form-label mb-0" style="width:150px;">Satuan</label>
            <input type="text" class="form-control" id="satuan" name="satuan" value="{{ old('satuan', $produk->satuan) }}" required style="width:300px;">
            @error('satuan')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="stokmin" class="form-label mb-0" style="width:150px;">Stok Minimal</label>
            <input type="number" class="form-control" id="stokmin" name="stokmin" value="{{ old('stokmin', $produk->stokmin) }}" required min="0" style="width:300px;">
            @error('stokmin')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="harga_jual" class="form-label mb-0" style="width:150px;">Harga Jual</label>
            <input type="number" class="form-control" id="harga_jual" name="harga_jual" value="{{ old('harga_jual', $produk->harga_jual) }}" min="0" step="0.01" style="width:300px;">
            @error('harga_jual')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('produk.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection