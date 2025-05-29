@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Tambah Consignor</h2>
    <form action="{{ route('consignor.store') }}" method="POST">
        @csrf
        <div class="mb-3 d-flex align-items-center">
            <label for="kode_consignor" class="form-label mb-0" style="width:150px;">Kode Consignor</label>
            <input type="text" class="form-control" id="kode_consignor" name="kode_consignor" value="{{ $kode_consignor }}" readonly style="width:300px;">
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="nama_consignor" class="form-label mb-0" style="width:150px;">Nama Consignor</label>
            <input type="text" class="form-control" id="nama_consignor" name="nama_consignor" value="{{ old('nama_consignor') }}" required style="width:300px;">
            @error('nama_consignor')
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
        <div class="mb-3 d-flex align-items-center">
            <label for="keterangan" class="form-label mb-0" style="width:150px;">Keterangan</label>
            <input type="text" class="form-control" id="keterangan" name="keterangan" value="{{ old('keterangan') }}" style="width:300px;">
            @error('keterangan')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('consignor.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection