@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Data Kas Keluar</h4>
    <a href="{{ route('kaskeluar.create') }}" class="btn btn-primary mb-3">
        + Tambah Pengeluaran
    </a>

    <table class="table table-bordered table-striped align-middle text-center">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No Bukti</th>
                <th>Jumlah</th>
                <th>Penerima</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kaskeluar as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}</td>
                <td>{{ $item->nomor_bukti }}</td>
                <td>Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                <td>{{ $item->nama_penerima }}</td>
                <td>{{ $item->keterangan_teks }}</td>
                <td>
                    <a href="{{ route('kaskeluar.edit', $item->no_jurnal) }}" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <form action="{{ route('kaskeluar.destroy', $item->no_jurnal) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">Tidak ada data kas keluar.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
