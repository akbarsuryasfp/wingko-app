
@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Daftar Consignee (Mitra)</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('consignee.create') }}" class="btn btn-primary mb-3">Tambah Consignee (Mitra)</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Consignee</th>
                <th>Nama Consignee</th>
                <th>Alamat</th>
                <th>No. Telepon</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consignee as $item)
                <tr>
                    <td>{{ $item->kode_consignee }}</td>
                    <td>{{ $item->nama_consignee }}</td>
                    <td>{{ $item->alamat }}</td>
                    <td>{{ $item->no_telp }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td>
                        <a href="{{ route('consignee.edit', $item->kode_consignee) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('consignee.destroy', $item->kode_consignee) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection