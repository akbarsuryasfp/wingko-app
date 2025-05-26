
@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Edit Produk</h2>
    <form action="{{ route('produk.update', $produk->kode_produk) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="kode_produk" class="form-label">Kode Produk</label>
            <input type="text" class="form-control" id="kode_produk" name="kode_produk" value="{{ old('kode_produk', $produk->kode_produk) }}" readonly>
            @error('kode_produk')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="kode_kategori" class="form-label">Kategori Produk</label>
            <select class="form-control" id="kode_kategori" name="kode_kategori" required>
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategori as $kat)
                    <option value="{{ $kat->kode_kategori }}" {{ old('kode_kategori', $produk->kode_kategori) == $kat->kode_kategori ? 'selected' : '' }}>
                        {{ $kat->jenis_kategori }}
                    </option>
                @endforeach
            </select>
            @error('kode_kategori')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="nama_produk" class="form-label">Nama Produk</label>
            <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="{{ old('nama_produk', $produk->nama_produk) }}" required>
            @error('nama_produk')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="satuan" class="form-label">Satuan</label>
            <input type="text" class="form-control" id="satuan" name="satuan" value="{{ old('satuan', $produk->satuan) }}" required>
            @error('satuan')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('produk.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection