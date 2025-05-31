@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Daftar Jadwal Produksi</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Kode Jadwal</th>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Detail Produk</th>
                <th>Aksi</th> <!-- Tambahkan kolom aksi -->
            </tr>
        </thead>
        <tbody>
            @foreach ($jadwal as $j)
                <tr>
                    <td>{{ $j->kode_jadwal }}</td>
                    <td>{{ $j->tanggal_jadwal }}</td>
                    <td>{{ $j->keterangan }}</td>
                    <td>
                        <a href="{{ route('jadwal.show', $j->kode_jadwal) }}" class="btn btn-sm btn-info">Detail</a>
                    </td>
                    <td>
                        <form action="{{ route('jadwal.destroy', $j->kode_jadwal) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
