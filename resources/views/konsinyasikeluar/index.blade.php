@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">DAFTAR KONSINYASI KELUAR</h4>
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
            <a href="{{ route('konsinyasikeluar.index', array_merge(request()->except('page'), ['sort' => $nextSort])) }}"
               class="btn btn-outline-secondary btn-sm ms-2">
                Urutkan No Konsinyasi Keluar {!! $icon !!}
            </a>
        </form>
        <div>
            <a href="{{ route('konsinyasikeluar.create') }}" class="btn btn-primary">Tambah Data</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center align-middle" style="width: 5%; vertical-align: middle;">No</th>
                        <th class="text-center align-middle" style="width: 15%; vertical-align: middle;">No Konsinyasi Keluar</th>
                        <th class="text-center align-middle" style="width: 20%; vertical-align: middle;">No Surat Konsinyasi Keluar</th>
                        <th class="text-center align-middle" style="width: 15%; vertical-align: middle;">Tanggal Setor</th>
                        <th class="text-center align-middle" style="width: 250px; vertical-align: middle;">Nama Consignee (Mitra)</th>
                        <th class="text-center align-middle" style="width: 25%; vertical-align: middle;">Jumlah Setor & Nama Produk</th>
                        <th class="text-center align-middle" style="width: 10%; vertical-align: middle;">Total Setor</th>
                        <th class="text-center align-middle" style="width: 10%; vertical-align: middle;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($konsinyasiKeluarList as $i => $item)
                        <tr>
                            <td class="text-center align-middle">{{ $i + 1 }}</td>
                            <td class="text-center align-middle">{{ $item->no_konsinyasikeluar }}</td>
                            <td class="text-center align-middle">{{ $item->no_suratpengiriman ?? '-' }}</td>
                            <td class="text-center align-middle">{{ \Carbon\Carbon::parse($item->tanggal_setor)->format('d-m-Y') }}</td>
                            <td class="text-center align-middle">{{ $item->consignee->nama_consignee ?? '-' }}</td>
                            <td class="text-center align-middle">
                                @php
                                    $produkList = $item->details ?? [];
                                @endphp
                                @if(count($produkList))
                                    @foreach($produkList as $detail)
                                        <div>
                                            <b>{{ $detail->jumlah_setor }}</b> x {{ $detail->produk->nama_produk ?? $detail->nama_produk ?? '-' }}
                                        </div>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center align-middle">Rp{{ number_format($item->total_setor, 0, ',', '.') }}</td>
                            <td class="text-center align-middle">
                                <div class="d-flex flex-column align-items-center gap-2" style="min-width: 140px;">
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('konsinyasikeluar.show', $item->no_konsinyasikeluar) }}" class="btn btn-info btn-sm btn-icon-square" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('konsinyasikeluar.edit', $item->no_konsinyasikeluar) }}" class="btn btn-warning btn-sm btn-icon-square" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                    <div class="d-flex gap-1">
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Data tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
