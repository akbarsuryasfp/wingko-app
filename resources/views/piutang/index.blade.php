@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 mt-4" style="max-width:1300px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">DAFTAR PELUNASAN PIUTANG</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
                    <a href="{{ route('piutang.cetak_laporan') . '?' . http_build_query(request()->all()) }}" target="_blank" class="btn btn-sm btn-success btn-icon-square d-inline-flex align-items-center gap-2" style="width: 140px; justify-content: center;">
                        <i class="bi bi-printer"></i> Cetak Laporan
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
                        <a href="{{ route('piutang.index', array_merge(request()->except('page','sort'), ['sort' => $nextSort])) }}"
                           class="btn btn-sm btn-outline-secondary ms-2">
                            Urutkan No Piutang {!! $icon !!}
                        </a>
                    </form>
                </div>
                <div class="col-md-4 col-12 text-md-end text-start">
                    <form method="GET" action="{{ route('piutang.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        @foreach(request()->except(['search','page']) as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endforeach
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari No Piutang/No Jual..." value="{{ request('search') }}" style="max-width: 220px;" autocomplete="off">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>No Piutang</th>
                            <th>No Jual</th>
                            <th>Tanggal Jual</th>
                            {{-- <th>Pelanggan</th> --}}
                            <th>Total Tagihan</th>
                            <th>Total Bayar</th>
                            <th>Sisa Piutang</th>
                            <th>Tanggal Jatuh Tempo</th>
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
                            <td>
                                {{ isset($p->tanggal_jual) && $p->tanggal_jual ? \Carbon\Carbon::parse($p->tanggal_jual)->format('d-m-Y') : '-' }}
                            </td>
                            {{-- <td>{{ $p->kode_pelanggan }}</td> --}}
                            <td>Rp{{ number_format($p->total_tagihan,0,',','.') }}</td>
                            <td>Rp{{ number_format($p->total_bayar,0,',','.') }}</td>
                            <td>
                                <span class="{{ $p->sisa_piutang == 0 ? 'text-dark' : 'text-danger fw-bold' }}">
                                    Rp{{ number_format($p->sisa_piutang,0,',','.') }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $jatuhTempo = $p->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($p->tanggal_jatuh_tempo) : null;
                                    $sekarang = \Carbon\Carbon::now();
                                    $warning = false;
                                    if ($jatuhTempo && $p->sisa_piutang > 0) {
                                        $diff = $jatuhTempo->copy()->startOfDay()->diffInDays($sekarang->copy()->startOfDay(), false);
                                        // diff < 0 = belum jatuh tempo, diff == 0 = hari ini, diff > 0 = lewat jatuh tempo
                                        // warning jika hari ini >= (jatuh tempo - 1 hari)
                                        $warning = $sekarang->copy()->startOfDay()->gte($jatuhTempo->copy()->subDay()->startOfDay());
                                    }
                                @endphp
                                @if($jatuhTempo)
                                    <span class="{{ $warning ? 'fw-bold text-danger' : '' }}">
                                        {{ $jatuhTempo->format('d-m-Y') }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
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
                            <td colspan="9">Data piutang belum ada.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection