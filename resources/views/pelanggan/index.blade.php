@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Daftar Pelanggan</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('pelanggan.create') }}" class="btn btn-primary mb-3">Tambah Pelanggan</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Alamat</th>
                <th>No. Telepon</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelanggan as $item)
                <tr>
                    <td>{{ $item->kode_pelanggan }}</td>
                    <td>{{ $item->nama_pelanggan }}</td>
                    <td>{{ $item->alamat }}</td>
                    <td>{{ $item->no_telp }}</td>
                    <td>
                        <a href="{{ route('pelanggan.edit', $item->kode_pelanggan) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('pelanggan.destroy', $item->kode_pelanggan) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection