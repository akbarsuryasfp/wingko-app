@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Edit Consignee</h2>
    <form action="{{ route('consignee.update', $consignee->kode_consignee) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3 d-flex align-items-center">
            <label for="kode_consignee" class="form-label mb-0" style="width:150px;">Kode Consignee</label>
            <input type="text" class="form-control" id="kode_consignee" name="kode_consignee" value="{{ old('kode_consignee', $consignee->kode_consignee) }}" readonly style="width:300px;">
            @error('kode_consignee')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="nama_consignee" class="form-label mb-0" style="width:150px;">Nama Consignee</label>
            <input type="text" class="form-control" id="nama_consignee" name="nama_consignee" value="{{ old('nama_consignee', $consignee->nama_consignee) }}" required style="width:300px;">
            @error('nama_consignee')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="alamat" class="form-label mb-0" style="width:150px;">Alamat</label>
            <input type="text" class="form-control" id="alamat" name="alamat" value="{{ old('alamat', $consignee->alamat) }}" required style="width:300px;">
            @error('alamat')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="no_telp" class="form-label mb-0" style="width:150px;">No. Telepon</label>
            <input type="text" class="form-control" id="no_telp" name="no_telp" value="{{ old('no_telp', $consignee->no_telp) }}" required style="width:300px;">
            @error('no_telp')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="keterangan" class="form-label mb-0" style="width:150px;">Keterangan</label>
            <input type="text" class="form-control" id="keterangan" name="keterangan" value="{{ old('keterangan', $consignee->keterangan) }}" style="width:300px;">
            @error('keterangan')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('consignee.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection