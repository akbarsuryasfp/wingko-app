@extends('layouts.app')
@section('content')
<div class="container-fluid px-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">Daftar Penjualan Konsinyasi (Per Produk)</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
                    <a href="{{ route('jualkonsinyasimasuk.cetak_laporan_pdf') . '?' . http_build_query(request()->all()) }}" target="_blank" class="btn btn-sm btn-success btn-icon-square d-inline-flex align-items-center gap-2" style="width: 140px; justify-content: center;">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </a>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-md-8 col-12 text-md-start text-start mb-2 mb-md-0">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap w-100 mt-1 justify-content-start">
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
                        <a href="{{ route('jualkonsinyasimasuk.index', array_merge(request()->except('page'), ['sort' => $nextSort])) }}"
                           class="btn btn-sm btn-outline-secondary ms-2">
                            Urutkan No Jual {!! $icon !!}
                        </a>
                    </form>
                </div>
                <div class="col-md-4 col-12 text-md-end mb-2 mb-md-0">
                    <form method="GET" action="{{ route('jualkonsinyasimasuk.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari No Jual/Nama Pelanggan..." value="{{ request('search') }}" style="max-width: 220px;" autocomplete="off">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle table-sm konsinyasi-table">
                <thead class="table-light">
                    <tr>
                        <th class="text-center align-middle py-3" style="width:28px;">No</th>
                        <th class="text-center align-middle py-3" style="width:160px;">No Jual</th>
                        <th class="text-center align-middle py-3" style="width:160px;">Tanggal Jual</th>
                        <th class="text-center align-middle py-3" style="width:220px;">Nama Pelanggan</th>
                        <th class="text-center align-middle py-3" style="width:120px;">Kode Produk</th>
                        <th class="text-center align-middle py-3" style="min-width:160px;">Nama Produk</th>
                        <th class="text-center align-middle py-3" style="width:80px;">Satuan</th>
                        <th class="text-center align-middle py-3" style="width:90px;">Jumlah</th>
                        <th class="text-center align-middle py-3" style="width:140px;">Harga/Satuan</th>
                        <th class="text-center align-middle py-3" style="width:160px;">Subtotal Jual</th>
                        <th class="text-center align-middle py-3" style="width:140px;">Komisi/Satuan</th>
                        <th class="text-center align-middle py-3" style="width:160px;">Subtotal Komisi</th>
                    </tr>
                </thead>
                <tbody>
                @php $rowNo = 1; @endphp
                @forelse($penjualanKonsinyasi as $penjualan)
                    @foreach($penjualan->details as $detail)
                        @php
                            $namaProdukKonsinyasi = null;
                            if(Str::startsWith($detail->kode_produk, 'PKM')) {
                                $produkKonsinyasi = \App\Models\ProdukKonsinyasi::where('kode_produk', $detail->kode_produk)->first();
                                $namaProdukKonsinyasi = $produkKonsinyasi ? $produkKonsinyasi->nama_produk : $detail->nama_produk;
                            }
                        @endphp
                        @if(Str::startsWith($detail->kode_produk, 'PKM'))
                        <tr>
                            <td class="text-center align-middle py-3">{{ $rowNo++ }}</td>
                            <td class="text-center align-middle py-3">{{ $penjualan->no_jual }}</td>
                            <td class="text-center align-middle py-3">{{ \Carbon\Carbon::parse($penjualan->tanggal_jual)->format('d-m-Y') }}</td>
                            <td class="text-center align-middle py-3">{{ $penjualan->pelanggan->nama_pelanggan ?? '-' }}</td>
                            <td class="text-center align-middle py-3">{{ $detail->kode_produk }}</td>
                            <td class="text-center align-middle py-3">{{ $namaProdukKonsinyasi }}</td>
                            <td class="text-center align-middle py-3">
                                @php
                                    $satuan = null;
                                    if(isset($produkKonsinyasi)) {
                                        $satuan = $produkKonsinyasi->satuan ?? null;
                                    }
                                @endphp
                                {{ $satuan ?? '-' }}
                            </td>
                            <td class="text-center align-middle py-3">{{ number_format($detail->jumlah) }}</td>
                            <td class="text-end align-middle py-3">
                                <span style="display: inline-flex; gap: 2px; align-items: center; justify-content: flex-end; width: 100%;">
                                    <span>Rp</span><span>{{ number_format($detail->harga_satuan, 0, ',', '.') }}</span>
                                </span>
                            </td>
                            <td class="text-center align-middle py-3">
                                <span style="display: inline-flex; gap: 2px; align-items: center; justify-content: center; width: 100%;">
                                    <span>Rp</span><span>{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                </span>
                            </td>
                            <td class="text-end align-middle py-3">
                                @php
                                    // Ambil komisi/unit dari t_konsinyasimasuk_detail
                                    $komisi = null;
                                    $komisiRow = \DB::table('t_konsinyasimasuk_detail')
                                        ->where('kode_produk', $detail->kode_produk)
                                        ->whereNotNull('komisi')
                                        ->orderByDesc('no_detailkonsinyasimasuk')
                                        ->value('komisi');
                                    $komisi = $komisiRow ?? 0;
                                @endphp
                                <span>Rp{{ number_format($komisi, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-center align-middle py-3">
                                @php
                                    $subtotal_komisi = ($komisi ?? 0) * ($detail->jumlah ?? 0);
                                @endphp
                                <span>Rp{{ number_format($subtotal_komisi, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-3">Tidak ada data penjualan konsinyasi.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-dyZtM4Q1Q6l0e6QF6UVx/FuWRz5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    .konsinyasi-table th, .konsinyasi-table td {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }
    .table td.text-end {
        font-variant-numeric: tabular-nums;
        padding-right: 1rem !important;
        vertical-align: middle;
    }
    .konsinyasi-table td.text-center, .konsinyasi-table td.text-center.align-middle {
        vertical-align: middle;
    }
</style>
@endpush
