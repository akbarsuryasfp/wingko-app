@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Daftar Jadwal Produksi</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
<a href="{{ route('jadwal_.create') }}" class="btn btn-primary mb-3 ms-2">
        + Buat Jadwal Produksi
    </a>
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>No Jadwal</th>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Status Bahan</th> <!-- Kolom baru -->
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jadwal as $j)
                <tr>
                    <td>{{ $j->no_jadwal }}</td>
                    <td>{{ $j->tanggal_jadwal }}</td>
                    <td>{{ $j->keterangan }}</td>
                    <td>
                        @if(!empty($j->ada_bahan_kurang))
                            <span class="badge bg-danger">Bahan Kurang</span>
                        @else
                            <span class="badge bg-success">Cukup</span>
                        @endif
                    </td>
                    <td>
                        @if($j->sudah_diproses)
                            <button class="btn btn-sm btn-secondary mt-1" disabled>Diproses</button>
                        @elseif(!empty($j->ada_bahan_kurang))
                            <button class="btn btn-sm btn-secondary mt-1" disabled style="pointer-events: none; opacity: 0.6;">Proses Produksi</button>
                        @else
                            <a href="{{ route('produksi.create', ['jadwal' => $j->no_jadwal]) }}" class="btn btn-sm btn-primary mt-1">Proses Produksi</a>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('jadwal.show', $j->no_jadwal) }}" class="btn btn-sm btn-info">Detail</a>
                        <form action="{{ route('jadwal.destroy', $j->no_jadwal) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
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
