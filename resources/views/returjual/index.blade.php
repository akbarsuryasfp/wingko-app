@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>DAFTAR RETUR PENJUALAN PRODUK</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap">
        <!-- Filter Periode Tanggal + Urutkan -->
        <form method="GET" class="d-flex align-items-center gap-2 mb-0 flex-wrap">
            @foreach(request()->except(['tanggal_awal','tanggal_akhir','page','sort']) as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
            <span class="fw-semibold">Periode:</span>
            <input type="date" name="tanggal_awal" class="form-control form-control-sm w-auto" value="{{ request('tanggal_awal') }}">
            <span class="mx-1">s/d</span>
            <input type="date" name="tanggal_akhir" class="form-control form-control-sm w-auto" value="{{ request('tanggal_akhir') }}">
            <button type="submit" class="btn btn-secondary btn-sm">Terapkan</button>
            @php
                $sort = request('sort', 'asc');
                $nextSort = $sort === 'asc' ? 'desc' : 'asc';
                $icon = $sort === 'asc' ? '▲' : '▼';
            @endphp
            <a href="{{ route('returjual.index', array_merge(request()->except('page'), ['sort' => $nextSort])) }}"
               class="btn btn-outline-secondary btn-sm ms-2">
                Urutkan No Retur Jual {!! $icon !!}
            </a>
        </form>
        <div>
            <a href="{{ route('returjual.create') }}" class="btn btn-primary btn-sm" title="Tambah Retur Penjualan">
                Tambah Retur Penjualan
            </a>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No Retur Jual</th>
                <th>No Jual</th>
                <th>Tanggal Retur</th>
                <th>Pelanggan</th>
                <th>Jumlah Retur & Nama Produk</th>
                <th>Total Nilai Retur</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($returjual as $rj)
                <tr>
                    <td>{{ $rj->no_returjual }}</td>
                    <td>{{ $rj->no_jual }}</td>
                    <td>{{ $rj->tanggal_returjual }}</td>
                    <td>{{ $rj->nama_pelanggan ?? '-' }}</td>
                    <td>{{ $rj->produk_jumlah ?? '-' }}</td>
                    <td>{{ number_format($rj->total_nilai_retur, 0, ',', '.') }}</td>
                    <td>{{ $rj->keterangan }}</td>
                    <td>
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('returjual.show', $rj->no_returjual) }}" class="btn btn-info btn-sm" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('returjual.edit', $rj->no_returjual) }}" class="btn btn-warning btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('returjual.destroy', $rj->no_returjual) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <a href="{{ route('returjual.cetak', $rj->no_returjual) }}" class="btn btn-success btn-sm" title="Cetak" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection