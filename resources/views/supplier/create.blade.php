@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Input Data Supplier</h2>
    <form action="{{ route('supplier.store') }}" method="POST">
        @csrf
        <div class="mb-3 d-flex align-items-center">
            <label for="kode_supplier" class="form-label mb-0" style="width:150px;">Kode Supplier</label>
            <input type="text" class="form-control" id="kode_supplier" name="kode_supplier" value="{{ $kode_supplier }}" readonly style="width:300px;">
            @error('kode_supplier')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="nama_supplier" class="form-label mb-0" style="width:150px;">Nama</label>
            <input type="text" class="form-control" id="nama_supplier" name="nama_supplier" value="{{ old('nama_supplier') }}" required style="width:300px;">
            @error('nama_supplier')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="alamat" class="form-label mb-0" style="width:150px;">Alamat</label>
            <textarea class="form-control" id="alamat" name="alamat" required style="width:300px;">{{ old('alamat') }}</textarea>
            @error('alamat')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="no_telp" class="form-label mb-0" style="width:150px;">No. Telp</label>
            <input type="text" class="form-control" id="no_telp" name="no_telp" value="{{ old('no_telp') }}" required style="width:300px;">
            @error('no_telp')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label class="form-label mb-0" style="width:150px;">Rekening</label>
            <span>Jenis Bank&nbsp;</span>
            <input type="text" class="form-control mx-2" style="width:120px;" id="jenis_bank" name="jenis_bank" value="{{ old('jenis_bank') }}" required>
            <span>&nbsp;No Rekening&nbsp;</span>
            <input type="text" class="form-control mx-2" style="width:150px;" id="no_rek" name="no_rek" value="{{ old('no_rek') }}" required>
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label class="form-label mb-0" style="width:150px;">Keterangan</label>
            <span>Jarak Kirim ke Gudang&nbsp;</span>
            <input type="number" step="0.01" class="form-control mx-2" style="width:80px;" id="jarak_kirim" name="jarak_kirim" value="{{ old('jarak_kirim') }}" required>
            <span>&nbsp;km</span>
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label class="form-label mb-0" style="width:150px;"></label>
            <span>Waktu Kirim&nbsp;</span>
            <input type="number" class="form-control mx-2" style="width:80px;" id="waktu_kirim" name="waktu_kirim" value="{{ old('waktu_kirim') }}" required>
            <span>&nbsp;hari setelah pesan</span>
            @error('keterangan')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <a href="{{ route('supplier.index') }}" class="btn btn-secondary">Back</a>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success">Submit</button>
    </form>
</div>
@endsection