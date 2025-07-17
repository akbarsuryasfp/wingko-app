@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:1300px;">
    <h4 class="mb-4">DAFTAR PELUNASAN PIUTANG</h4>
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap">
        <!-- Filter Periode Tanggal Jatuh Tempo + Urutkan -->
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
            <a href="{{ route('piutang.index', array_merge(request()->except('page','sort'), ['sort' => $nextSort])) }}"
               class="btn btn-outline-secondary btn-sm ms-2">
                Urutkan No Piutang {!! $icon !!}
            </a>
        </form>
    </div>
    <table class="table table-bordered table-striped align-middle text-center">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>No Piutang</th>
                <th>No Jual</th>
                <th>Pelanggan</th>
                <th>Total Tagihan</th>
                <th>Total Bayar</th>
                <th>Sisa Piutang</th>
                <th>Jatuh Tempo</th>
                <th>Status Pembayaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($piutangs as $i => $p)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $p->no_piutang }}</td>
                <td>{{ $p->no_jual }}</td>
                <td>{{ $p->kode_pelanggan }}</td>
                <td>Rp{{ number_format($p->total_tagihan,0,',','.') }}</td>
                <td>Rp{{ number_format($p->total_bayar,0,',','.') }}</td>
                <td>
                    <span class="{{ $p->sisa_piutang == 0 ? 'text-dark' : 'text-danger fw-bold' }}">
                        Rp{{ number_format($p->sisa_piutang,0,',','.') }}
                    </span>
                </td>
                <td>{{ $p->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->format('d-m-Y') : '-' }}</td>
                <td>
                    @if($p->status_piutang == 'lunas')
                        <span class="badge bg-success">Lunas</span>
                    @else
                        <span class="badge bg-warning text-dark">Belum Lunas</span>
                    @endif
                </td>
                <td class="d-flex justify-content-center gap-1">
                    <a href="{{ route('piutang.show', $p->no_piutang) }}" class="btn btn-info btn-sm" title="Detail">
                        <i class="bi bi-eye"></i>
                    </a>
                    @if($p->status_piutang != 'lunas')
                    <a href="{{ route('piutang.bayar', $p->no_piutang) }}" class="btn btn-success btn-sm" title="Pembayaran">
                        <i class="bi bi-cash-coin"></i>
                    </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10">Data piutang belum ada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection