@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Edit Bahan</h2>
    <form action="{{ route('bahan.update', $bahan->kode_bahan) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="kode_kategori" class="form-label">Kode Kategori</label>
            <input type="text" class="form-control" id="kode_kategori" name="kode_kategori" value="{{ old('kode_kategori', $bahan->kode_kategori) }}" required>
            @error('kode_kategori')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="kode_bahan" class="form-label">Kode Bahan</label>
            <input type="text" class="form-control" id="kode_bahan" name="kode_bahan" value="{{ old('kode_bahan', $bahan->kode_bahan) }}" required readonly>
            @error('kode_bahan')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="nama_bahan" class="form-label">Nama Bahan</label>
            <input type="text" class="form-control" id="nama_bahan" name="nama_bahan" value="{{ old('nama_bahan', $bahan->nama_bahan) }}" required>
            @error('nama_bahan')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="satuan" class="form-label">Satuan</label>
            <input type="text" class="form-control" id="satuan" name="satuan" value="{{ old('satuan', $bahan->satuan) }}" required>
            @error('satuan')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="stokmin" class="form-label">Stok Minimal</label>
            <input type="number" class="form-control" id="stokmin" name="stokmin" value="{{ old('stokmin', $bahan->stokmin) }}" required>
            @error('stokmin')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('bahan.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection