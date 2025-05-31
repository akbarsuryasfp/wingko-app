@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Daftar Produk</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('produk.create') }}" class="btn btn-primary mb-3">Tambah Produk</a>

    <form method="GET" action="{{ route('produk.index') }}" class="mb-3 d-flex align-items-center gap-2">
        <label for="kode_kategori" class="mb-0">Filter Berdasarkan Kategori Produk:</label>
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
                <th>Kode Produk</th>
                <th>Kategori Produk</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produk as $item)
                <tr>
                <td>{{ $item->kode_produk }}</td>    
                <td>{{ $item->kode_kategori }}</td>
                    <td>{{ $item->nama_produk }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td>
                        <a href="{{ route('produk.edit', $item->kode_produk) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('produk.destroy', $item->kode_produk) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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