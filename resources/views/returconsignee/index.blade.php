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
                    <h4 class="mb-0 fw-semibold">Daftar Retur Consignee (Mitra)</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0 d-flex justify-content-md-end justify-content-center gap-2">
                    <a href="{{ route('returconsignee.cetak_laporan') . '?' . http_build_query(request()->all()) }}" target="_blank" class="btn btn-sm btn-success d-flex align-items-center gap-2">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </a>
                    <a href="{{ route('returconsignee.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle"></i> Tambah Retur Consignee
                    </a>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-md-8 col-12 text-md-start text-start mb-2 mb-md-0">
                    <form id="filterReturConsignee" method="GET" class="d-flex align-items-center gap-2 flex-wrap w-100 mt-1 justify-content-start">
                        <span class="fw-semibold">Periode:</span>
                        <input type="date" name="tanggal_awal" class="form-control form-control-sm w-auto" value="{{ request('tanggal_awal') }}">
                        <span class="mx-1">s/d</span>
                        <input type="date" name="tanggal_akhir" class="form-control form-control-sm w-auto" value="{{ request('tanggal_akhir') }}">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-funnel"></i> Terapkan
                        </button>
                        <a href="{{ route('returconsignee.index', array_merge(request()->except('page'), ['sort' => request('sort', 'asc') === 'asc' ? 'desc' : 'asc'])) }}"
                           class="btn btn-sm btn-outline-secondary ms-2">
                            Urutkan No Retur Consignee {!! request('sort', 'asc') === 'asc' ? '▲' : '▼' !!}
                        </a>
                    </form>
                </div>
                <div class="col-md-4 col-12 text-md-end text-start">
                    <form method="GET" action="{{ route('returconsignee.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        <input type="text" name="search" id="searchReturConsignee" class="form-control form-control-sm" placeholder="Cari No Retur/Nama Consignee..." value="{{ request('search') }}" style="max-width: 220px;" autocomplete="off">
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
                            <th class="text-center align-middle py-3" style="width:50px;">No</th>
                            <th class="text-center align-middle py-3" style="width:200px;">No Retur Consignee</th>
                            <th class="text-center align-middle py-3" style="width:200px;">No Konsinyasi Keluar</th>
                            <th class="text-center align-middle py-3" style="width:160px;">Tanggal Retur</th>
                            <th class="text-center align-middle py-3" style="width:250px;">Nama Consignee (Mitra)</th>
                            <th class="text-center align-middle py-3" style="min-width:250px;">Jumlah Retur & Nama Produk</th>
                            <th class="text-center align-middle py-3" style="width:140px;">Total Retur</th>
                            <th class="text-center align-middle py-3" style="width:120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returconsignees as $idx => $rc)
                            <tr>
                                <td class="text-center py-3">{{ $idx + 1 }}</td>
                                <td class="text-center py-3">{{ $rc->no_returconsignee }}</td>
                                <td class="text-center py-3">{{ $rc->konsinyasikeluar->no_konsinyasikeluar ?? '-' }}</td>
                                <td class="text-center py-3">{{ $rc->tanggal_returconsignee ? \Carbon\Carbon::parse($rc->tanggal_returconsignee)->format('d-m-Y') : '-' }}</td>
                                <td class="text-center py-3">{{ $rc->consignee->nama_consignee ?? '-' }}</td>
                                <td class="text-center py-3">
                                    <ul class="list-unstyled mb-0">
                                        @if($rc->details && count($rc->details))
                                            @foreach($rc->details as $detail)
                                                <li>
                                                    <span class="fw-bold">{{ $detail->jumlah_retur }}</span> x {{ $detail->produk->nama_produk ?? '-' }}
                                                    @if(!empty($detail->alasan))
                                                        ({{ $detail->alasan }})
                                                    @endif
                                                </li>
                                            @endforeach
                                        @else
                                            <li>-</li>
                                        @endif
                                    </ul>
                                </td>
                                <td class="text-center py-3">Rp{{ number_format($rc->total_nilai_retur, 0, ',', '.') }}</td>
                                <td class="text-center py-3">
                                    <div class="d-flex justify-content-center gap-1" style="min-width: 140px;">
                                        <a href="{{ route('returconsignee.show', $rc->no_returconsignee) }}" class="btn btn-info btn-sm btn-icon-square" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @php
                                            $adaTidakTerjual = false;
                                            if ($rc->details && count($rc->details)) {
                                                foreach ($rc->details as $detail) {
                                                    if (strtolower(trim($detail->alasan)) === 'tidak terjual') {
                                                        $adaTidakTerjual = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        @if(!$adaTidakTerjual)
                                            <a href="{{ route('returconsignee.edit', $rc->no_returconsignee) }}" class="btn btn-warning btn-sm btn-icon-square" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('returconsignee.destroy', $rc->no_returconsignee) }}" method="POST" style="display:inline-block; margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm btn-icon-square" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-3">Data tidak tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
