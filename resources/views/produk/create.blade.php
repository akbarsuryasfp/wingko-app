@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Input Data Produk</h2>
    <form action="{{ route('produk.store') }}" method="POST">
        @csrf
        <div class="mb-3 d-flex align-items-center">
            <label for="kode_produk" class="form-label mb-0" style="width:150px;">Kode Produk</label>
            <input type="text" class="form-control" id="kode_produk" name="kode_produk" value="{{ $kode_produk }}" readonly style="width:300px;">
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="kode_kategori" class="form-label mb-0" style="width:150px;">Kategori Produk</label>
            <select class="form-control" id="kode_kategori" name="kode_kategori" required style="width:300px;">
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategori as $kat)
                    @if(Str::startsWith($kat->kode_kategori, 'P'))
                        <option value="{{ $kat->kode_kategori }}" {{ old('kode_kategori') == $kat->kode_kategori ? 'selected' : '' }}>
                            {{ $kat->jenis_kategori }}
                        </option>
                    @endif
                @endforeach
            </select>
            @error('kode_kategori')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="nama_produk" class="form-label mb-0" style="width:150px;">Nama Produk</label>
            <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="{{ old('nama_produk') }}" required style="width:300px;">
            @error('nama_produk')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="satuan" class="form-label mb-0" style="width:150px;">Satuan</label>
            <input type="text" class="form-control" id="satuan" name="satuan" value="{{ old('satuan') }}" required style="width:300px;">
            @error('satuan')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="stokmin" class="form-label mb-0" style="width:150px;">Stok Minimal</label>
            <input type="number" class="form-control" id="stokmin" name="stokmin" value="{{ old('stokmin') }}" required min="0" style="width:300px;">
            @error('stokmin')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <a href="{{ route('produk.index') }}" class="btn btn-secondary">Back</a>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success">Submit</button>
    </form>
</div>
@endsection