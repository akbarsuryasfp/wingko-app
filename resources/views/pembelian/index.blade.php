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
                $now = Carbon\Carbon::now();
                $tanggal_mulai = request('tanggal_mulai') ?? $now->copy()->startOfMonth()->format('Y-m-d');
                $tanggal_selesai = request('tanggal_selesai') ?? $now->copy()->endOfMonth()->format('Y-m-d');
            @endphp

            {{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
    <div class="mb-1 mb-md-0">
        <h4 class="mb-0 fw-semibold">Daftar Pembelian Bahan</h4>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('pembelian.laporan.pdf', request()->all()) }}"
           class="btn btn-sm btn-success"
           target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> Cetak Laporan
        </a>
        <a href="{{ route('pembelian.langsung', ['jenis' => 'langsung']) }}" class="btn btn-sm btn-warning">
            <i class="bi bi-cart-plus"></i> Pembelian Langsung
        </a>
        <a href="{{ route('pembelian.create', ['jenis' => 'order']) }}" class="btn btn-sm btn-info">
            <i class="bi bi-clipboard-check"></i> Pembelian Berdasarkan Order
        </a>
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
                        <select name="jenis_pembelian" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
                            <option value="">Semua Jenis</option>
                            <option value="pembelian langsung" {{ request('jenis_pembelian') == 'pembelian langsung' ? 'selected' : '' }}>Pembelian Langsung</option>
                            <option value="pembelian berdasarkan order" {{ request('jenis_pembelian') == 'pembelian berdasarkan order' ? 'selected' : '' }}>Pembelian Berdasarkan Order</option>
                        </select>
                        <select name="status_lunas" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="lunas" {{ request('status_lunas') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="belum" {{ request('status_lunas') == 'belum' ? 'selected' : '' }}>Belum Lunas</option>
                        </select>
                    </form>
                </div>
                <div class="col-md-4 col-12 text-md-end text-center">
                    <form method="GET" action="{{ route('pembelian.index') }}" class="d-inline-flex gap-2 flex-wrap justify-content-end w-100">
                        <input type="text" name="search"
                               class="form-control form-control-sm"
                               placeholder="Cari No. Pembelian / Supplier"
                               value="{{ request('search') }}"
                               style="max-width: 250px;">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;" class="text-center align-middle">No</th>
                            <th style="width: 120px;" class="text-center align-middle">No Pembelian</th>
                            <th style="width: 100px;" class="text-center align-middle">Tanggal</th>
                            <th style="width: 220px;" class="text-center align-middle">Nama Supplier</th>
                            <th style="width: 120px;" class="text-center align-middle">Total Pembelian</th>
                            <th style="width: 120px;" class="text-center align-middle">Uang Muka</th>
                            <th style="width: 120px;" class="text-center align-middle">Total Bayar</th>
                            <th style="width: 120px;" class="text-center align-middle">Hutang</th>
                            <th style="width: 90px;" class="text-center align-middle">Status</th>
                            <th style="width: 130px;" class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @forelse($pembelian as $p)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $p->no_pembelian }}</td>
                                <td>{{ $p->tanggal_pembelian }}</td>
                                <td class="text-start">{{ $p->nama_supplier }}</td>
                                <td>
                                    <span class="float-start">Rp</span>
                                    <span class="float-end">{{ number_format($p->total_pembelian,0,',','.') }}</span>
                                </td>
                                <td>
                                    <span class="float-start">Rp</span>
                                    <span class="float-end">{{ number_format($p->uang_muka ?? 0,0,',','.') }}</span>
                                </td>
                                <td>
                                    <span class="float-start">Rp</span>
                                    <span class="float-end">{{ number_format($p->total_bayar,0,',','.') }}</span>
                                </td>
                                <td>
                                    <span class="float-start">Rp</span>
                                    <span class="float-end">{{ number_format($p->hutang,0,',','.') }}</span>
                                </td>
                                <td>
                                    @if (($p->hutang ?? 0) <= 0)
                                        <span class="badge bg-success">Lunas</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Belum Lunas</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('pembelian.show', $p->no_pembelian) }}" class="btn btn-info btn-sm" title="Detail">
                                            <i class="bi bi-info-circle"></i>
                                        </a>
                                        <a href="{{ route('pembelian.edit', $p->no_pembelian) }}" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('pembelian.destroy', $p->no_pembelian) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Data tidak tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection