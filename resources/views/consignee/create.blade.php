@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Tambah Consignee</h2>
    <form action="{{ route('consignee.store') }}" method="POST">
        @csrf
        <div class="mb-3 d-flex align-items-center">
            <label for="kode_consignee" class="form-label mb-0" style="width:150px;">Kode Consignee</label>
            <input type="text" class="form-control" id="kode_consignee" name="kode_consignee" value="{{ $kode_consignee }}" readonly style="width:300px;">
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="nama_consignee" class="form-label mb-0" style="width:150px;">Nama Consignee</label>
            <input type="text" class="form-control" id="nama_consignee" name="nama_consignee" value="{{ old('nama_consignee') }}" required style="width:300px;">
            @error('nama_consignee')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="alamat" class="form-label mb-0" style="width:150px;">Alamat</label>
            <input type="text" class="form-control" id="alamat" name="alamat" value="{{ old('alamat') }}" required style="width:300px;">
            @error('alamat')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="no_telp" class="form-label mb-0" style="width:150px;">No. Telepon</label>
            <input type="text" class="form-control" id="no_telp" name="no_telp" value="{{ old('no_telp') }}" required style="width:300px;">
            @error('no_telp')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <a href="{{ route('consignee.index') }}" class="btn btn-secondary">Back</a>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success">Submit</button>
    </form>
</div>
@endsection