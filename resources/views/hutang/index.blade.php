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
            {{-- Header --}}
            <div class="row align-items-end mb-3">
    <div class="col-md-6 col-12 text-md-start text-center mb-2 mb-md-0">
        <h4 class="mb-0 fw-semibold">📋 Daftar Hutang</h4>
    </div>
    <div class="col-md-6 col-12 text-md-end text-center">
        <form method="GET" action="{{ route('hutang.index') }}" class="d-inline-flex gap-2 flex-wrap justify-content-end w-100">
            <input type="text" name="search"
                   class="form-control form-control-sm"
                   placeholder="Cari No. Utang/Supplier"
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
                            <th style="width: 50px">No</th>
                            <th style="width: 120px">No Utang</th>
                            <th style="width: 120px">No Pembelian</th>
                            <th style="width: 200px">Supplier</th>
                            <th style="width: 150px">Total Tagihan</th>
                            <th style="width: 150px">Sisa Utang</th>
                            <th style="width: 120px">Status</th>
                            <th style="width: 150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hutangs as $hutang)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $hutang->no_utang }}</td>
                                <td>{{ $hutang->no_pembelian }}</td>
                                <td class="text-start">
                                    @php
                                        $nama_supplier = \DB::table('t_supplier')->where('kode_supplier', $hutang->kode_supplier)->value('nama_supplier');
                                        echo $nama_supplier ?? $hutang->kode_supplier;
                                    @endphp
                                </td>
                                <td class="text-end">Rp{{ number_format($hutang->total_tagihan, 0, ',', '.') }}</td>
                                <td class="text-end">
                                    @if ($hutang->sisa_utang == 0)
                                        <span>Rp0</span>
                                    @else
                                        <span class="text-danger">Rp{{ number_format($hutang->sisa_utang, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($hutang->sisa_utang == 0)
                                        <span class="badge bg-success"><i class="fas fa-check-circle"></i> Lunas</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle"></i> Belum Lunas</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('hutang.detail', $hutang->no_utang) }}" class="btn btn-info btn-sm" title="Detail">
                                            <i class="bi bi-info-circle"></i>
                                        </a>
                                        @if ($hutang->sisa_utang > 0)
                                            <a href="{{ route('hutang.bayar', $hutang->no_utang) }}" class="btn btn-success btn-sm" title="Pembayaran">
                                                <i class="bi bi-cash-coin"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada data hutang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection