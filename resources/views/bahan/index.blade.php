@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Daftar Bahan</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('bahan.create') }}" class="btn btn-primary mb-3">Tambah Bahan</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Bahan</th>
                <th>Kode Kategori</th>
                <th>Nama Bahan</th>
                <th>Satuan</th>
                <th>Stok Minimal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bahan as $item)
                <tr>
                    <td>{{ $item->kode_bahan }}</td>
                    <td>{{ $item->kode_kategori }}</td>
                    <td>{{ $item->nama_bahan }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td>{{ $item->stokmin }}</td>
                    <td>
                        <a href="{{ route('bahan.edit', $item->kode_bahan) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('bahan.destroy', $item->kode_bahan) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
