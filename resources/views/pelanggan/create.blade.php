@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h4 class="mb-4 fw-semibold text-center">Tambah Pelanggan</h4>
            <form action="{{ route('pelanggan.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <label for="kode_pelanggan" class="col-sm-4 col-form-label">Kode Pelanggan</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="kode_pelanggan" name="kode_pelanggan" value="{{ $kode_pelanggan }}" readonly tabindex="-1" style="pointer-events: none; background-color: #e9ecef;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="nama_pelanggan" class="col-sm-4 col-form-label">Nama Pelanggan</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="nama_pelanggan" name="nama_pelanggan" value="{{ old('nama_pelanggan') }}" required>
                        @error('nama_pelanggan')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="alamat" class="col-sm-4 col-form-label">Alamat</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="alamat" name="alamat" value="{{ old('alamat') }}" required>
                        @error('alamat')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-4">
                    <label for="no_telp" class="col-sm-4 col-form-label">No. Telepon</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="no_telp" name="no_telp" value="{{ old('no_telp') }}" required>
                        @error('no_telp')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-2">
                    <div>
                        <a href="{{ route('pelanggan.index') }}" class="btn btn-secondary me-2">Back</a>
                        <button type="reset" class="btn btn-warning">Reset</button>
                    </div>
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection