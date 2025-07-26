@extends('layouts.app')
@section('content')
<div class="container">
    <h4 class="mb-4">DAFTAR PENJUALAN KONSINYASI (PER PRODUK)</h4>
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap">
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
            <a href="{{ route('jualkonsinyasimasuk.index', array_merge(request()->except('page'), ['sort' => $nextSort])) }}"
               class="btn btn-outline-secondary btn-sm ms-2">
                Urutkan No Jual {!! $icon !!}
            </a>
        </form>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center align-middle">No</th>
                        <th class="text-center align-middle">No Jual</th>
                        <th class="text-center align-middle">Tanggal Jual</th>
                        <th class="text-center align-middle">Pelanggan</th>
                        <th class="text-center align-middle">Kode Produk</th>
                        <th class="text-center align-middle">Nama Produk</th>
                        <th class="text-center align-middle">Jumlah</th>
                        <th class="text-center align-middle">Harga/Satuan</th>
                        <th class="text-center align-middle">Subtotal</th>
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
                            <td class="text-center align-middle">{{ $rowNo++ }}</td>
                            <td class="text-center align-middle">{{ $penjualan->no_jual }}</td>
                            <td class="text-center align-middle">{{ \Carbon\Carbon::parse($penjualan->tanggal_jual)->format('d-m-Y') }}</td>
                            <td class="text-center align-middle">{{ $penjualan->pelanggan->nama_pelanggan ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $detail->kode_produk }}</td>
                            <td class="text-center align-middle">{{ $namaProdukKonsinyasi }}</td>
                            <td class="text-center align-middle">{{ number_format($detail->jumlah) }}</td>
                            <td class="text-end align-middle">
                                <span style="display: inline-flex; gap: 2px; align-items: center; justify-content: flex-end; width: 100%;">
                                    <span>Rp</span><span>{{ number_format($detail->harga_satuan, 0, ',', '.') }}</span>
                                </span>
                            </td>
                            <td class="text-end align-middle">
                                <span style="display: inline-flex; gap: 2px; align-items: center; justify-content: flex-end; width: 100%;">
                                    <span>Rp</span><span>{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                </span>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                @empty
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data penjualan konsinyasi.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-dyZtM4Q1Q6l0e6QF6UVx/FuWRz5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q5Q0Q==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    .table td.text-end {
        font-variant-numeric: tabular-nums;
        padding-right: 1rem !important;
        vertical-align: middle;
    }
</style>
@endpush
