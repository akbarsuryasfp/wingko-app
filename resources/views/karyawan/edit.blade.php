@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Edit Karyawan</h4>
    <form action="{{ route('karyawan.update', $karyawan->kode_karyawan) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-2">
            <label>Kode Karyawan</label>
            <input type="text" name="kode_karyawan" class="form-control" value="{{ $karyawan->kode_karyawan }}" readonly>
        </div>
        <div class="mb-2">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="{{ $karyawan->nama }}" required>
        </div>
        <div class="mb-2">
            <label>Jabatan</label>
            <input type="text" name="jabatan" class="form-control" value="{{ $karyawan->jabatan }}">
        </div>
        <div class="mb-2">
            <label>Departemen</label>
            <input type="text" name="departemen" class="form-control" value="{{ $karyawan->departemen }}">
        </div>
        <div class="mb-2">
            <label>Gaji</label>
            <input type="number" name="gaji" class="form-control" value="{{ $karyawan->gaji }}">
        </div>
        <div class="mb-2">
            <label>Tanggal Masuk</label>
            <input type="date" name="tanggal_masuk" class="form-control" value="{{ $karyawan->tanggal_masuk }}">
        </div>
        <div class="mb-2">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control">{{ $karyawan->alamat }}</textarea>
        </div>
        <div class="mb-2">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ $karyawan->email }}">
        </div>
        <div class="mb-2">
            <label>No Telepon</label>
            <input type="text" name="no_telepon" class="form-control" value="{{ $karyawan->no_telepon }}">
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('karyawan.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection