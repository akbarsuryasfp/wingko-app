@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Tambah Karyawan</h4>
    <form action="{{ route('karyawan.store') }}" method="POST">
        @csrf
        <div class="mb-2">
            <label>Kode Karyawan</label>
            <input type="text" name="kode_karyawan" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Jabatan</label>
            <input type="text" name="jabatan" class="form-control">
        </div>
        <div class="mb-2">
            <label>Departemen</label>
            <input type="text" name="departemen" class="form-control">
        </div>
        <div class="mb-2">
            <label>Gaji</label>
            <input type="number" name="gaji" class="form-control">
        </div>
        <div class="mb-2">
            <label>Tanggal Masuk</label>
            <input type="date" name="tanggal_masuk" class="form-control">
        </div>
        <div class="mb-2">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control"></textarea>
        </div>
        <div class="mb-2">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-2">
            <label>No Telepon</label>
            <input type="text" name="no_telepon" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('karyawan.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection