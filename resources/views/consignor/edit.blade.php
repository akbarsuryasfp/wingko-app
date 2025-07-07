@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Edit Consignor</h2>
    <form action="{{ route('consignor.update', $consignor->kode_consignor) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3 d-flex align-items-center">
            <label for="kode_consignor" class="form-label mb-0" style="width:150px;">Kode Consignor</label>
            <input type="text" class="form-control" id="kode_consignor" name="kode_consignor" value="{{ old('kode_consignor', $consignor->kode_consignor) }}" readonly style="width:300px;">
            @error('kode_consignor')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="nama_consignor" class="form-label mb-0" style="width:150px;">Nama Consignor</label>
            <input type="text" class="form-control" id="nama_consignor" name="nama_consignor" value="{{ old('nama_consignor', $consignor->nama_consignor) }}" required style="width:300px;">
            @error('nama_consignor')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="alamat" class="form-label mb-0" style="width:150px;">Alamat</label>
            <input type="text" class="form-control" id="alamat" name="alamat" value="{{ old('alamat', $consignor->alamat) }}" required style="width:300px;">
            @error('alamat')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="no_telp" class="form-label mb-0" style="width:150px;">No. Telepon</label>
            <input type="text" class="form-control" id="no_telp" name="no_telp" value="{{ old('no_telp', $consignor->no_telp) }}" required style="width:300px;">
            @error('no_telp')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label class="form-label mb-0" style="width:150px;">Rekening</label>
            <span>Jenis Bank&nbsp;</span>
            <input type="text" class="form-control mx-2" style="width:120px;" id="bank" name="bank" value="{{ old('bank', $bank) }}" required>
            <span>&nbsp;No Rekening&nbsp;</span>
            <input type="text" class="form-control mx-2" style="width:150px;" id="rekening" name="rekening" value="{{ old('rekening', $no_rekening) }}" required>
            @error('bank')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
            @error('rekening')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('consignor.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div>
@endsection