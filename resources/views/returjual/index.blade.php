@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>DAFTAR RETUR PENJUALAN PRODUK</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-2 w-100">
        <div class="d-flex flex-wrap justify-content-between align-items-center w-100 mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold">Filter:</span>
                <select name="jenis_retur" class="form-select form-select-sm w-auto" onchange="this.form.submit()" form="filterReturJual">
                    <option value="">Semua Jenis</option>
                    @foreach($jenisList as $jenis)
                        <option value="{{ $jenis }}" {{ request('jenis_retur') === (string)$jenis ? 'selected' : '' }}>{{ ucfirst($jenis) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <!-- Baris 2: Filter Periode dan Urutkan di kiri, Tambah di kanan -->
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap w-100">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <form id="filterReturJual" method="GET" class="d-flex align-items-center gap-2 mb-0 flex-wrap">
                    <span class="fw-semibold">Periode:</span>
                    <input type="date" name="tanggal_awal" class="form-control form-control-sm w-auto" value="{{ request('tanggal_awal') }}">
                    <span class="mx-1">s/d</span>
                    <input type="date" name="tanggal_akhir" class="form-control form-control-sm w-auto" value="{{ request('tanggal_akhir') }}">
                    <button type="submit" class="btn btn-secondary btn-sm">Terapkan</button>
                </form>
                <a href="{{ route('returjual.index', array_merge(request()->except('page'), ['sort' => request('sort', 'asc') === 'asc' ? 'desc' : 'asc'])) }}"
                   class="btn btn-outline-secondary btn-sm">
                    Urutkan No Retur Jual {!! request('sort', 'asc') === 'asc' ? '▲' : '▼' !!}
                </a>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('returjual.create') }}" class="btn btn-primary btn-sm" title="Tambah Retur Penjualan">
                    Tambah Retur Penjualan
                </a>
            </div>
        </div>
    </div>

    <div class="table-responsive">
    <table class="table table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th class="text-center align-middle" style="width:40px;">No</th>
                <th class="text-center align-middle" style="width:120px;">No Retur Jual</th>
                <th class="text-center align-middle" style="width:110px;">No Jual</th>
                <th class="text-center align-middle" style="width:140px;">Tanggal Retur</th>
                <th class="text-center align-middle" style="width:140px;">Pelanggan</th>
                <th class="text-center align-middle" style="width:120px;">Jenis Retur</th>
                <th class="text-center align-middle" style="min-width:100px;">Jumlah Retur & Nama Produk</th>
                <th class="text-center align-middle" style="width:150px;">Total Retur</th>
                <th class="text-center align-middle" style="width:110px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($returjual as $idx => $rj)
                <tr>
                    <td class="text-center align-middle">{{ $idx + 1 }}</td>
                    <td class="text-center align-middle">{{ $rj->no_returjual }}</td>
                    <td class="text-center align-middle">{{ $rj->no_jual }}</td>
                    <td class="text-center align-middle">{{ $rj->tanggal_returjual }}</td>
                    <td class="text-center align-middle">{{ $rj->nama_pelanggan ?? '-' }}</td>
                    <td class="text-center align-middle">{{ $rj->jenis_retur ?? '-' }}</td>
                    <td class="text-center align-middle">{!! $rj->produk_jumlah ?? '-' !!}</td>
                    <td class="text-center align-middle">Rp{{ number_format($rj->total_nilai_retur, 0, ',', '.') }}</td>
                    
                    <td>
                        <div class="d-flex gap-1 flex-wrap align-items-center justify-content-center">
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
</div>
@endsection
