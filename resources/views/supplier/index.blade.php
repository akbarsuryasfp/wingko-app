@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center">Daftar Supplier</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('supplier.create') }}" class="btn btn-primary mb-3">Tambah Supplier</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="text-center align-middle" style="width:110px;">Kode Supplier</th>
                <th class="text-center align-middle" style="width:150px;">Nama Supplier</th>
                <th class="text-center align-middle" style="width:180px;">Alamat</th>
                <th class="text-center align-middle" style="width:120px;">No. Telp</th>
                <th class="text-center align-middle" style="width:180px;">No. Rekening</th>
                <th class="text-center align-middle" style="width:220px;">Keterangan</th>
                <th class="text-center align-middle" style="width:110px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($supplier as $item)
                <tr>
                    <td class="text-center">{{ $item->kode_supplier }}</td>
                    <td>{{ $item->nama_supplier }}</td>
                    <td>{{ $item->alamat }}</td>
                    <td class="text-center">{{ $item->no_telp }}</td>
                    <td>{{ $item->rekening }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td class="text-center">
                        <a href="{{ route('supplier.edit', $item->kode_supplier) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('supplier.destroy', $item->kode_supplier) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection