@extends('layouts.app')

@section('content')
<style>
    .table-fixed-height thead th,
    .table-fixed-height tbody td {
        height: 35px;
        vertical-align: middle;
        padding-top: 0;
        padding-bottom: 0;
    }
    .card {
        border-radius: 8px;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .action-buttons {
        display: flex;
        gap: 0.25rem;
        justify-content: center;
    }
</style>

<div class="container-fluid px-3">
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            @php
                use Carbon\Carbon;
                $now = Carbon::now();
                $tanggal_mulai = request('tanggal_mulai') ?? $now->copy()->startOfMonth()->format('Y-m-d');
                $tanggal_selesai = request('tanggal_selesai') ?? $now->copy()->endOfMonth()->format('Y-m-d');
            @endphp

            {{-- Header --}}
            <div class="row align-items-center mb-2">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">DAFTAR RETUR PEMBELIAN BAHAN</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
                    <div class="d-flex justify-content-md-end justify-content-center gap-2 flex-wrap">
                        <a href="{{ route('returbeli.laporan.pdf', [
                            'tanggal_mulai' => $tanggal_mulai,
                            'tanggal_selesai' => $tanggal_selesai,
                            'search' => request('search')
                        ]) }}"
                        class="btn btn-sm btn-success"
                        target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Cetak Laporan
                        </a>
                        <a href="{{ route('returbeli.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah Retur Pembelian
                        </a>
                    </div>
                </div>
            </div>

            {{-- Filter & Search --}}
            <div class="row align-items-end mb-3">
                <div class="col-md-8 col-12 mb-2 mb-md-0">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="small">Periode:</span>
                        <input type="date" name="tanggal_mulai"
                               value="{{ $tanggal_mulai }}"
                               class="form-control form-control-sm"
                               style="width: 140px;"
                               onchange="this.form.submit()">
                        <span class="small">s.d.</span>
                        <input type="date" name="tanggal_selesai"
                               value="{{ $tanggal_selesai }}"
                               class="form-control form-control-sm"
                               style="width: 140px;"
                               onchange="this.form.submit()">
                        <select name="status" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="menunggu_terima_barang" {{ request('status') == 'menunggu_terima_barang' ? 'selected' : '' }}>Menunggu Terima Barang</option>
                            <option value="menunggu_pengembalian" {{ request('status') == 'menunggu_pengembalian' ? 'selected' : '' }}>Menunggu Pengembalian</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </form>
                </div>
                <div class="col-md-4 col-12 text-md-end text-center">
                    <form method="GET" action="{{ route('returbeli.index') }}" class="d-inline-flex gap-2 flex-wrap justify-content-end w-100">
                        <input type="text" name="search"
                               class="form-control form-control-sm"
                               placeholder="Cari No. Retur / Supplier"
                               value="{{ request('search') }}"
                               style="max-width: 250px;">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                            </button>
                                            @if(request('search'))
        <a href="{{ route('returbeli.index', array_merge(request()->except('search'))) }}"
           class="btn btn-sm btn-outline-danger" title="Reset">
            <i class="bi bi-x"></i>
        </a>
        @endif
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px" class="text-center align-middle">No</th>
                            <th style="width: 110px" class="text-center align-middle">Kode Retur</th>
                            <th style="width: 110px" class="text-center align-middle">Kode Pembelian</th>
                            <th style="width: 110px" class="text-center align-middle">Tanggal Retur</th>
                            <th style="width: 200px" class="text-start align-middle">Supplier</th>
                            <th style="width: 300px" class="text-start align-middle">Keterangan</th>
                            <th style="width: 100px" class="text-center align-middle">Jenis Pengembalian</th>
                            <th style="width: 100px" class="text-center align-middle">Status</th>
                            <th style="width: 200px" class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($returList as $retur)
                            <tr>
                                <td class="align-middle">{{ $loop->iteration }}</td>
                                <td class="align-middle">{{ $retur->no_retur_beli }}</td>
                                <td class="align-middle">{{ $retur->no_pembelian }}</td>
                                <td class="align-middle">{{ $retur->tanggal_retur_beli }}</td>
                                <td class="text-start">{{ $retur->nama_supplier }}</td>
                                <td class="text-start">
                                    @foreach($retur->details as $detail)
                                        <div class="mb-1">
                                            <b>{{ $detail->nama_bahan }}</b> ({{ $detail->jumlah_retur }}) 
                                            @if($detail->alasan)
                                                {{ $detail->alasan }}
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                                <td class="align-middle">
                                    {{ ucfirst($retur->jenis_pengembalian ?? '-') }}
                                </td>
                                <td class="align-middle">
                                    <span class="badge bg-info text-dark">
                                        {{ ucfirst(str_replace('_', ' ', $retur->status ?? '-')) }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        @if($retur->status === 'selesai')
                                            <a href="{{ route('returbeli.show', $retur->no_retur_beli) }}" class="btn btn-info btn-sm" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @else
                                            <a href="{{ route('returbeli.cetak', $retur->no_retur_beli) }}" class="btn btn-success btn-sm" title="Cetak" target="_blank">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <a href="{{ route('returbeli.show', $retur->no_retur_beli) }}" class="btn btn-info btn-sm" title="Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('returbeli.edit', $retur->no_retur_beli) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('returbeli.destroy', $retur->no_retur_beli) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin hapus?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
@if($retur->jenis_pengembalian === 'barang' && $retur->status === 'menunggu_terima_barang')
    <a href="{{ route('returbeli.terimabarang', $retur->no_retur_beli) }}" class="btn btn-primary btn-sm">
        <i class="bi bi-box-seam"></i>
    </a>
@elseif($retur->jenis_pengembalian === 'uang' && $retur->status === 'menunggu_pengembalian')
    <form action="{{ route('returbeli.kasretur', $retur->no_retur_beli) }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-success btn-sm">
            <i class="bi bi-cash-coin"></i> 
        </button>
    </form>
@endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Belum ada data retur pembelian bahan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection