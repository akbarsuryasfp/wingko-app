@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>DAFTAR PESANAN PELANGGAN</h3>

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
            <a href="{{ route('pesananpenjualan.index', array_merge(request()->except('page'), ['sort' => $nextSort])) }}"
               class="btn btn-outline-secondary btn-sm ms-2">
                Urutkan No Pesanan {{ $icon }}
            </a>
        </form>
        <div>
            <a href="{{ route('pesananpenjualan.create') }}" class="btn btn-primary btn-sm" title="Tambah Pesanan">
                Tambah Pesanan
            </a>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>No Pesanan</th>
                <th>Tanggal Pesanan</th>
                <th>Tanggal Pengiriman</th>
                <th>Pelanggan</th>
                <th>Total Pesanan</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesanan as $psn)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $psn->no_pesanan }}</td>
                    <td>{{ $psn->tanggal_pesanan }}</td>
                    <td>{{ $psn->tanggal_pengiriman ?? '-' }}</td>
                    <td>{{ $psn->nama_pelanggan ?? '-' }}</td>
                    <td>Rp {{ number_format($psn->total_pesanan, 0, ',', '.') }}</td>
                    <td>{{ $psn->keterangan ?? '-' }}</td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap align-items-center">
                            <a href="{{ route('pesananpenjualan.show', $psn->no_pesanan) }}" class="btn btn-info btn-sm" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('pesananpenjualan.edit', $psn->no_pesanan) }}" class="btn btn-warning btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('pesananpenjualan.destroy', $psn->no_pesanan) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
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

{{-- Hapus/komentari modal detail pesanan jika tidak dipakai lagi --}}