@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-sm" style="max-width: 100%;">
                <div class="card-body">
                    <style>
input[readonly] {
    background-color: #e9ecef;
    color: #495057;
}
                    </style>

                    <h4 class="mb-4 text-center">Tambah Data Produk</h4>

                    <form action="{{ route('produk.store') }}" method="POST">
                        @csrf

                        {{-- Kode Produk --}}
                        <div class="row mb-3">
                            <label for="kode_produk" class="col-sm-4 col-form-label">Kode Produk</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control" 
                                       id="kode_produk" name="kode_produk" value="{{ $kode_produk }}">
                            </div>
                        </div>

                        {{-- Nama Produk --}}
                        <div class="row mb-3 align-items-center">
                            <label for="nama_produk" class="col-sm-4 col-form-label">Nama Produk</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                                       value="{{ old('nama_produk') }}" required>
                                @error('nama_produk')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Satuan --}}
                        <div class="row mb-3 align-items-center">
                            <label for="satuan" class="col-sm-4 col-form-label">Satuan</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="satuan" name="satuan" 
                                       value="{{ old('satuan') }}" required>
                                @error('satuan')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Stok Minimal --}}
                        <div class="row mb-3 align-items-center">
                            <label for="stokmin" class="col-sm-4 col-form-label">Stok Minimal</label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control" id="stokmin" name="stokmin" 
                                       value="{{ old('stokmin') }}" required min="0">
                                @error('stokmin')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Harga Jual --}}
                        <div class="row mb-3 align-items-center">
                            <label for="harga_jual" class="col-sm-4 col-form-label">Harga Jual</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-text px-3">Rp</span>
                                    <input type="number" class="form-control text-end" id="harga_jual" name="harga_jual" 
                                           value="{{ old('harga_jual') }}" min="0" step="0.01">
                                </div>
                                @error('harga_jual')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="row mt-4">
                            <div class="col-6">
                                <a href="{{ route('produk.index') }}" class="btn btn-secondary me-2">
                                ← Kembali
                                </a>
                                <button type="reset" class="btn btn-warning">
                                    Reset
                                </button>
                            </div>
                            <div class="col-6 text-end">
                                <button type="submit" class="btn btn-success">
                                     Simpan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
