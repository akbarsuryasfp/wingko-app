@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h4 class="mb-4 fw-semibold text-center">Edit Consignor</h4>
            <form action="{{ route('consignor.update', $consignor->kode_consignor) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <label for="kode_consignor" class="col-sm-4 col-form-label text-start">Kode Consignor</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="kode_consignor" name="kode_consignor" value="{{ old('kode_consignor', $consignor->kode_consignor) }}" readonly tabindex="-1" style="pointer-events: none; background-color: #e9ecef;">
                        @error('kode_consignor')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="nama_consignor" class="col-sm-4 col-form-label text-start">Nama Consignor</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="nama_consignor" name="nama_consignor" value="{{ old('nama_consignor', $consignor->nama_consignor) }}" required>
                        @error('nama_consignor')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="alamat" class="col-sm-4 col-form-label text-start">Alamat</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="alamat" name="alamat" value="{{ old('alamat', $consignor->alamat) }}" required>
                        @error('alamat')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="no_telp" class="col-sm-4 col-form-label text-start">No. Telepon</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="no_telp" name="no_telp" value="{{ old('no_telp', $consignor->no_telp) }}" required>
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
                            <input type="text" class="form-control" id="bank" name="bank" value="{{ old('bank', $bank) }}" required>
                            @error('bank')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label for="rekening" class="form-label mb-1">No Rekening</label>
                            <input type="text" class="form-control" id="rekening" name="rekening" value="{{ old('rekening', $no_rekening) }}" required>
                            @error('rekening')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-2">
                    <a href="{{ route('consignor.index') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection