@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">Daftar Konsinyasi Keluar</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0 d-flex justify-content-md-end justify-content-center gap-2">
                    <a href="{{ route('konsinyasikeluar.cetak_laporan') . '?' . http_build_query(request()->all()) }}" target="_blank" class="btn btn-sm btn-success d-flex align-items-center gap-2">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </a>
                    <a href="{{ route('konsinyasikeluar.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle"></i> Tambah Konsinyasi
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
                        <a href="{{ route('konsinyasikeluar.index', array_merge(request()->except('page','sort'), ['sort' => $nextSort])) }}"
                           class="btn btn-sm btn-outline-secondary ms-2">
                            Urutkan No Konsinyasi Keluar {!! $icon !!}
                        </a>
                    </form>
                </div>
                <div class="col-md-4 col-12 text-md-end text-start">
                    <form method="GET" action="{{ route('konsinyasikeluar.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        <input type="text" name="search" id="searchKonsinyasiKeluar" class="form-control form-control-sm" placeholder="Cari No Konsinyasi/Nama Consignee..." value="{{ request('search') }}" style="max-width: 220px;" autocomplete="off">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center align-middle text-nowrap" style="width: 5%; min-width: 50px;">No</th>
                                <th class="text-center align-middle text-nowrap" style="width: 18%; min-width: 180px;">No Konsinyasi Keluar</th>
                                <th class="text-center align-middle text-nowrap" style="width: 18%; min-width: 180px;">No Surat Konsinyasi Keluar</th>
                                <th class="text-center align-middle text-nowrap" style="width: 13%; min-width: 130px;">Tanggal Setor</th>
                                <th class="text-center align-middle text-nowrap" style="width: 8%; min-width: 70px;">
                                    <div class="w-100 text-center">Nama Consignee<br>(Mitra)</div>
                                </th>
                                <th class="text-center align-middle text-nowrap" style="width: 18%; min-width: 180px;">Jumlah Setor & Nama Produk</th>
                                <th class="text-center align-middle text-nowrap" style="width: 13%; min-width: 130px;">Total Setor</th>
                                <th class="text-center align-middle text-nowrap" style="width: 10%; min-width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($konsinyasiKeluarList as $i => $item)
                                <tr>
                                    <td class="text-center align-middle">{{ $i + 1 }}</td>
                                    <td class="text-center align-middle">{{ $item->no_konsinyasikeluar }}</td>
                                    <td class="text-center align-middle">{{ $item->no_suratpengiriman ?? '-' }}</td>
                                    <td class="text-center align-middle">{{ $item->tanggal_setor ? \Carbon\Carbon::parse($item->tanggal_setor)->format('d-m-Y') : '-' }}</td>
                                    <td class="text-center align-middle" style="max-width:70px; word-break:break-word;">
                                        {{ $item->consignee->nama_consignee ?? '-' }}
                                    </td>
                                    <td class="text-center align-middle text-nowrap" style="white-space:nowrap;">
                                        @php
                                            $produkList = $item->details ?? [];
                                        @endphp
                                        @if(count($produkList))
                                            <div class="d-flex flex-column align-items-center justify-content-center w-100">
                                            @foreach($produkList as $detail)
                                                <div class="text-center">
                                                    <b>{{ $detail->jumlah_setor }}</b> x {{ $detail->produk->nama_produk ?? $detail->nama_produk ?? '-' }}
                                                </div>
                                            @endforeach
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">Rp{{ number_format($item->total_setor, 0, ',', '.') }}</td>
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center gap-1" style="min-width: 180px;">
                                            <a href="{{ route('konsinyasikeluar.show', $item->no_konsinyasikeluar) }}" class="btn btn-info btn-sm btn-icon-square" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('konsinyasikeluar.edit', $item->no_konsinyasikeluar) }}" class="btn btn-warning btn-sm btn-icon-square" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('konsinyasikeluar.destroy', $item->no_konsinyasikeluar) }}" method="POST" style="display:inline-block; margin:0;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-icon-square" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('konsinyasikeluar.cetak', $item->no_konsinyasikeluar) }}" class="btn btn-envelope btn-sm btn-icon-square" title="Cetak Surat Pengiriman Produk" target="_blank">
                                                <i class="bi bi-envelope-fill"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Data tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-icon-square {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 1.1em;
    padding: 0;
    margin: 0;
    box-shadow: none;
}
.btn-sm.btn-icon-square {
    width: 32px;
    height: 32px;
    font-size: 1em;
    border-radius: 7px;
}
.btn-info.btn-icon-square { background: #0fd3ff; color: #111; border: none; }
.btn-warning.btn-icon-square { background: #ffc107; color: #111; border: none; }
.btn-danger.btn-icon-square { background: #f44336; color: #fff; border: none; }
.btn-success.btn-icon-square { background: #219653; color: #fff; border: none; }
.btn.btn-envelope.btn-icon-square {
    background: #219653 !important;
    background-color: #219653 !important;
    color: #fff !important;
    border: none !important;
    filter: drop-shadow(0 2px 6px rgba(33,150,83,0.15)) !important;
}
.btn-icon-square i { margin: 0; }
.btn-icon-square:focus { box-shadow: 0 0 0 2px #aaa; }
</style>
@endpush
