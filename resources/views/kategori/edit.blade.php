@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Edit Kategori</h2>
    <form action="{{ route('kategori.update', $kategori->kode_kategori) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="kode_kategori" class="form-label">Kode Kategori</label>
            <input type="text" class="form-control" id="kode_kategori" name="kode_kategori" value="{{ old('kode_kategori', $kategori->kode_kategori) }}" required>
            @error('kode_kategori')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="jenis_kategori" class="form-label">Jenis Kategori</label>
            <input type="text" class="form-control" id="jenis_kategori" name="jenis_kategori" value="{{ old('jenis_kategori', $kategori->jenis_kategori) }}" required>
            @error('jenis_kategori')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('kategori.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection