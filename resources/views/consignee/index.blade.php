@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center">DAFTAR CONSIGNEE (MITRA)</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('consignee.create') }}" class="btn btn-primary mb-3">Tambah Consignee</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Consignee</th>
                <th>Nama Consignee</th>
                <th>Alamat</th>
                <th>No. Telepon</th>
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
                    <td>
                        <a href="{{ route('consignee.edit', $item->kode_consignee) }}" class="btn btn-warning btn-sm" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('consignee.destroy', $item->kode_consignee) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
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