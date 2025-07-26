@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">Daftar Retur Penjualan </h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0 d-flex justify-content-md-end justify-content-center gap-2">
                    <a href="{{ route('returjual.cetak_laporan') . '?' . http_build_query(request()->all()) }}" target="_blank" class="btn btn-sm btn-success d-flex align-items-center gap-2">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </a>
                    <a href="{{ route('returjual.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle"></i> Tambah Retur Penjualan
                    </a>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-md-8 col-12 text-md-start text-start mb-2 mb-md-0">
                    <div class="d-flex gap-2 flex-wrap align-items-center w-100 mt-1 justify-content-start">
                        <form id="filterTanggalReturJual" method="GET" class="d-flex align-items-center gap-2 flex-wrap mb-0">
                            <span class="fw-semibold">Periode:</span>
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm w-auto" value="{{ request('tanggal_awal') }}">
                            <span class="mx-1">s/d</span>
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm w-auto" value="{{ request('tanggal_akhir') }}">
                            <button type="submit" class="btn btn-sm btn-outline-secondary ms-2">
                                <i class="bi bi-funnel"></i> Terapkan
                            </button>
                        </form>
                        <form id="filterJenisReturJual" method="GET" class="d-flex align-items-center gap-2 flex-wrap mb-0 ms-3">
                            <span class="fw-semibold">Filter:</span>
                            <select name="jenis_retur" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                <option value="">Semua Jenis</option>
                                @foreach($jenisList as $jenis)
                                    <option value="{{ $jenis }}" {{ request('jenis_retur') === (string)$jenis ? 'selected' : '' }}>{{ ucfirst($jenis) }}</option>
                                @endforeach
                            </select>
                        </form>
                        <a href="{{ route('returjual.index', array_merge(request()->except('page'), ['sort' => request('sort', 'asc') === 'asc' ? 'desc' : 'asc'])) }}"
                           class="btn btn-sm btn-outline-secondary ms-2">
                            Urutkan No Retur Jual {!! request('sort', 'asc') === 'asc' ? '▲' : '▼' !!}
                        </a>
                    </div>
                </div>
                <div class="col-md-4 col-12 text-md-end text-start">
                    <form method="GET" action="{{ route('returjual.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari No Retur Jual/Nama Pelanggan..." value="{{ request('search') }}" style="max-width: 220px;" autocomplete="off">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle table-sm">
        <thead class="table-light">
            <tr>
                <th class="text-center align-middle" style="width:40px;">No</th>
                <th class="text-center align-middle" style="width:120px;">No Retur Jual</th>
                <th class="text-center align-middle" style="width:110px;">No Jual</th>
                <th class="text-center align-middle" style="width:140px;">Tanggal Retur</th>
                <th class="text-center align-middle" style="width:140px;">Nama Pelanggan</th>
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
                    <td class="text-center align-middle">{{ $rj->tanggal_returjual ? \Carbon\Carbon::parse($rj->tanggal_returjual)->format('d-m-Y') : '-' }}</td>
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
