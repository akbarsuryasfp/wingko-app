{{-- filepath: resources/views/konsinyasimasuk/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">DAFTAR KONSINYASI MASUK</h4>
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
            <a href="{{ route('konsinyasimasuk.index', array_merge(request()->except('page'), ['sort' => $nextSort])) }}"
               class="btn btn-outline-secondary btn-sm ms-2">
                Urutkan No Konsinyasi Masuk {!! $icon !!}
            </a>
        </form>
        <div>
            <a href="{{ route('konsinyasimasuk.create') }}" class="btn btn-primary btn-sm" title="Tambah Data">
                Tambah Data
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 20%;">No Konsinyasi Masuk</th>
                        <th style="width: 20%;">No Surat Titip Jual</th>
                        <th style="width: 20%;">Tanggal Masuk</th>
                        <th style="width: 25%;">Nama Consignor (Pemilik Barang)</th>
                        <th style="width: 10%;">Jumlah Stok</th>
                        <th style="width: 20%;">Total Titip Jual</th>
                        <th style="width: 15%;">Keterangan</th>
                        <th style="width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($konsinyasiMasukList as $i => $konsinyasi)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $konsinyasi->no_konsinyasimasuk ?? '-' }}</td>
                            <td>{{ $konsinyasi->no_surattitipjual ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($konsinyasi->tanggal_titip ?? $konsinyasi->tanggal_masuk)->format('d-m-Y') }}</td>
                            <td>{{ $konsinyasi->consignor->nama_consignor ?? '-' }}</td>
                            <td>
                                @php
                                    $jumlah_stok = $konsinyasi->details->sum('jumlah_stok');
                                @endphp
                                {{ $jumlah_stok }}
                            </td>
                            <td>
                                @php
                                    $total = $konsinyasi->details->sum(function($d) {
                                        return $d->jumlah_stok * $d->harga_titip;
                                    });
                                @endphp
                                Rp{{ number_format($total, 0, ',', '.') }}
                            </td>
                            <td>{{ $konsinyasi->keterangan ?? '-' }}</td>
                            <td>
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('konsinyasimasuk.show', $konsinyasi->no_konsinyasimasuk ?? $konsinyasi->no_surattitipjual) }}" class="btn btn-info btn-sm" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('konsinyasimasuk.edit', $konsinyasi->no_konsinyasimasuk ?? $konsinyasi->no_surattitipjual) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('konsinyasimasuk.destroy', $konsinyasi->no_konsinyasimasuk ?? $konsinyasi->no_surattitipjual) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    {{-- Jika ingin tombol cetak, aktifkan baris di bawah --}}
                                    {{-- <a href="#" class="btn btn-success btn-sm" title="Cetak" target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a> --}}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Data tidak ditemukan.</td>
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
.btn-icon-square {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.6em;
    padding: 0;
    margin: 0 0 12px 0;
    box-shadow: none;
}
.btn-info.btn-icon-square { background: #0fd3ff; color: #111; border: none; }
.btn-warning.btn-icon-square { background: #ffc107; color: #111; border: none; }
.btn-danger.btn-icon-square { background: #f44336; color: #fff; border: none; }
.btn-success.btn-icon-square { background: #219653; color: #fff; border: none; }
.btn-icon-square i { margin: 0; }
.btn-icon-square:focus { box-shadow: 0 0 0 2px #aaa; }
.d-flex.flex-column.align-items-center.gap-1 > * {
    margin-bottom: 12px !important;
}
.d-flex.flex-column.align-items-center.gap-1 > *:last-child {
    margin-bottom: 0 !important;
}
</style>
@endpush