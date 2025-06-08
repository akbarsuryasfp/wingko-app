@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Data Karyawan</h4>
    <a href="{{ route('karyawan.create') }}" class="btn btn-primary mb-3">Tambah Karyawan</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Departemen</th>
                <th>Gaji</th>
                <th>Tanggal Masuk</th>
                <th>Email</th>
                <th>No Telepon</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($karyawan as $k)
            <tr>
                <td>{{ $k->kode_karyawan }}</td>
                <td>{{ $k->nama }}</td>
                <td>{{ $k->jabatan }}</td>
                <td>{{ $k->departemen }}</td>
                <td>{{ number_format($k->gaji,0,',','.') }}</td>
                <td>{{ $k->tanggal_masuk }}</td>
                <td>{{ $k->email }}</td>
                <td>{{ $k->no_telepon }}</td>
                <td>
                    <a href="{{ route('karyawan.edit', $k->kode_karyawan) }}" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection