@extends('layouts.app')

@section('content')
<style>
    .table-fixed-height thead th,
    .table-fixed-height tbody td {
        height: 35px;
        vertical-align: middle;
        padding-top: 0;
        padding-bottom: 1px;
        font-size: 1rem; /* Consistent font size *//* Consistent font family */
    }
    .square-icon {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
    .square-icon i {
        font-size: 0.9rem;
    }
    .table-fixed-height tbody td {
        font-weight: 400; /* Normal weight for data */
    }
    .table-fixed-height thead th {
        font-weight: 600; /* Semi-bold for headers */
    }
    .keterangan-cell {
        font-size: 0.8125rem; /* Slightly smaller for keterangan */
        line-height: 1.3; /* Better line spacing */
    }
</style>

<div class="container-fluid px-3">
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            {{-- Header --}}
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">Daftar Supplier</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
                    <a href="{{ route('supplier.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Supplier
                    </a>
                </div>
            </div>

            {{-- Search --}}
            <div class="row align-items-center mb-3">
                <div class="col-12">
                    <form method="GET" action="{{ route('supplier.index') }}" class="d-flex justify-content-md-end justify-content-start gap-2 flex-wrap">
                        <input type="text" name="search"
                               class="form-control form-control-sm"
                               placeholder="Cari Nama Supplier..."
                               value="{{ request('search') }}"
                               style="max-width: 200px;">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center align-middle mb-0 table-fixed-height">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10px;">No</th>
                            <th style="width: 110px;">Kode Supplier</th>
                            <th style="width: 150px;">Nama Supplier</th>
                            <th style="width: 210px;">Alamat</th>
                            <th style="width: 110px;">No. Telp</th>
                            <th style="width: 170px;">No. Rekening</th>
                            <th style="width: 240px;">Keterangan</th>
                            <th style="width: 70px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->kode_supplier }}</td>
                                <td class="text-start">{{ $item->nama_supplier }}</td>
                                <td class="text-start">{{ $item->alamat }}</td>
                                <td>{{ $item->no_telp }}</td>
                                <td>{{ $item->rekening }}</td>
                                <td class="text-start small">{{ $item->keterangan }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('supplier.edit', $item->kode_supplier) }}" class="btn btn-sm btn-warning square-icon" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('supplier.destroy', $item->kode_supplier) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger square-icon" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Data tidak tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection