@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Daftar Bahan</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('bahan.create') }}" class="btn btn-primary mb-3">Tambah Bahan</a>

    <form method="GET" action="{{ route('bahan.index') }}" class="mb-3 d-flex align-items-center gap-2">
        <label for="kode_kategori" class="mb-0">Filter Berdasarkan Kategori Bahan:</label>
        <select name="kode_kategori" id="kode_kategori" class="form-control" style="width:200px;">
            <option value="">-- Semua Kategori --</option>
            @foreach($kategoriList as $kategori)
                <option value="{{ $kategori->kode_kategori }}" {{ request('kode_kategori') == $kategori->kode_kategori ? 'selected' : '' }}>
                    {{ $kategori->jenis_kategori }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-info btn-sm">Filter</button>
    </form>

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
