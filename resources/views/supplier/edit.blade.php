@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Edit Supplier</h2>
    <form action="{{ route('supplier.update', $supplier->kode_supplier) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="kode_supplier" class="form-label">Kode Supplier</label>
            <input type="text" class="form-control" id="kode_supplier" name="kode_supplier" value="{{ old('kode_supplier', $supplier->kode_supplier) }}" required readonly>
            @error('kode_supplier')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="nama_supplier" class="form-label">Nama Supplier</label>
            <input type="text" class="form-control" id="nama_supplier" name="nama_supplier" value="{{ old('nama_supplier', $supplier->nama_supplier) }}" required>
            @error('nama_supplier')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea class="form-control" id="alamat" name="alamat" required>{{ old('alamat', $supplier->alamat) }}</textarea>
            @error('alamat')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="no_telp" class="form-label">No. Telp</label>
            <input type="text" class="form-control" id="no_telp" name="no_telp" value="{{ old('no_telp', $supplier->no_telp) }}" required>
            @error('no_telp')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="no_rek" class="form-label">No. Rekening</label>
            <input type="text" class="form-control" id="no_rek" name="no_rek" value="{{ old('no_rek', $supplier->no_rek) }}">
            @error('no_rek')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <input type="text" class="form-control" id="keterangan" name="keterangan" value="{{ old('keterangan', $supplier->keterangan) }}">
            @error('keterangan')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('supplier.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection