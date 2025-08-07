@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h4 class="mb-4 fw-semibold text-center">Tambah Consignor</h4>
            <form action="{{ route('consignor.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <label for="kode_consignor" class="col-sm-4 col-form-label text-start">Kode Consignor</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="kode_consignor" name="kode_consignor" value="{{ $kode_consignor ?? old('kode_consignor') }}" readonly tabindex="-1" style="pointer-events: none; background-color: #e9ecef;">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="nama_consignor" class="col-sm-4 col-form-label text-start">Nama Consignor</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="nama_consignor" name="nama_consignor" value="{{ old('nama_consignor') }}" required>
                        @error('nama_consignor')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="alamat" class="col-sm-4 col-form-label text-start">Alamat</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="alamat" name="alamat" value="{{ old('alamat') }}" required>
                        @error('alamat')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="no_telp" class="col-sm-4 col-form-label text-start">No. Telepon</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="no_telp" name="no_telp" value="{{ old('no_telp') }}" required>
                        @error('no_telp')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-4">
                    <label class="col-sm-4 col-form-label text-start">Rekening</label>
                    <div class="col-sm-8">
                        <div class="mb-2">
                            <label for="bank" class="form-label mb-1">Jenis Bank</label>
                            <input type="text" class="form-control" id="bank" name="bank" value="{{ old('bank') }}" required>
                            @error('bank')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="rekening" class="form-label mb-1">No Rekening</label>
                            <input type="text" class="form-control" id="rekening" name="rekening" value="{{ old('rekening') }}" required>
                            @error('rekening')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-2">
                    <div>
                        <a href="{{ route('consignor.index') }}" class="btn btn-secondary me-2">Back</a>
                        <button type="reset" class="btn btn-warning">Reset</button>
                    </div>
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection