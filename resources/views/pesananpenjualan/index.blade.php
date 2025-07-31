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
                    <h4 class="mb-0 fw-semibold">Daftar Pesanan</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
                    <a href="{{ route('pesananpenjualan.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Pesanan
                    </a>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-md-8 col-12 text-md-start text-start mb-2 mb-md-0">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap w-100 mt-1 justify-content-start">
                        @foreach(request()->except(['tanggal_awal','tanggal_akhir','page','sort','search']) as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endforeach
                        <span class="fw-semibold">Periode:</span>
                        <input type="date" name="tanggal_awal" class="form-control form-control-sm w-auto" value="{{ request('tanggal_awal') }}">
                        <span class="mx-1">s/d</span>
                        <input type="date" name="tanggal_akhir" class="form-control form-control-sm w-auto" value="{{ request('tanggal_akhir') }}">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-funnel"></i> Terapkan
                        </button>
                        @php
                            $sort = request('sort', 'asc');
                            $nextSort = $sort === 'asc' ? 'desc' : 'asc';
                            $icon = $sort === 'asc' ? '▲' : '▼';
                        @endphp
                        <a href="{{ route('pesananpenjualan.index', array_merge(request()->except('page','sort'), ['sort' => $nextSort])) }}"
                           class="btn btn-sm btn-outline-secondary ms-2">
                            Urutkan No Pesanan {!! $icon !!}
                        </a>
                    </form>
                </div>
                <div class="col-md-4 col-12 text-md-end text-start">
                    <form method="GET" action="{{ route('pesananpenjualan.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        @foreach(request()->except(['search','page']) as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endforeach
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari No Pesanan/Nama Pelanggan..." value="{{ request('search') }}" style="max-width: 220px;" autocomplete="off">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle table-sm" style="font-size:15px;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center align-middle py-1" style="width:40px;">No</th>
                            <th class="text-center align-middle py-1" style="width:120px;">No Pesanan</th>
                            <th class="text-center align-middle py-1" style="width:120px;">Tanggal Pesanan</th>
                            <th class="text-center align-middle py-1" style="width:120px;">Tanggal Pengiriman</th>
                            <th class="text-center align-middle py-1" style="width:160px;">Pelanggan</th>
                            <th class="text-center align-middle py-1" style="width:120px;">Total Pesanan</th>
                            <th class="text-center align-middle py-1" style="width:120px;">Uang Muka (DP)</th>
                            <th class="text-center align-middle py-1" style="width:120px;">Sisa Tagihan</th>
                            <th class="text-center align-middle py-1" style="width:90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pesanan as $idx => $psn)
                            <tr>
                                <td class="text-center py-1">{{ $idx + 1 }}</td>
                                <td class="text-center py-1">{{ $psn->no_pesanan }}</td>
                                <td class="text-center py-1">{{ $psn->tanggal_pesanan ? \Carbon\Carbon::parse($psn->tanggal_pesanan)->format('d-m-Y') : '-' }}</td>
                                <td class="text-center py-1">{{ $psn->tanggal_pengiriman ? \Carbon\Carbon::parse($psn->tanggal_pengiriman)->format('d-m-Y') : '-' }}</td>
                                <td class="text-center py-1">{{ $psn->nama_pelanggan ?? '-' }}</td>
                                <td class="text-center py-1">Rp{{ number_format($psn->total_pesanan, 0, '.', '.') }}</td>
                                <td class="text-center py-1">Rp{{ number_format($psn->uang_muka ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center py-1">Rp{{ number_format($psn->sisa_tagihan ?? 0, 0, ',', '.') }}</td>
                                <td class="text-center py-1">
                                    <div class="d-flex justify-content-center gap-1" style="min-width: 120px;">
                                        <a href="{{ route('pesananpenjualan.show', $psn->no_pesanan) }}" class="btn btn-info btn-sm btn-icon-square" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(empty($psn->sudah_terjual) || !$psn->sudah_terjual)
                                        <a href="{{ route('pesananpenjualan.edit', $psn->no_pesanan) }}" class="btn btn-warning btn-sm btn-icon-square ms-1" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('pesananpenjualan.destroy', $psn->no_pesanan) }}" method="POST" style="display:inline-block; margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm btn-icon-square ms-1" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <a href="{{ route('pesananpenjualan.cetak', $psn->no_pesanan) }}" class="btn btn-success btn-sm btn-icon-square ms-1" title="Cetak Nota Pesanan" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-1">Data tidak tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection