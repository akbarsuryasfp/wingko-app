@extends('layouts.app')
<style>
    .table-fixed-height thead th,
    .table-fixed-height tbody td {
        height: 35px; /* Sesuaikan tinggi sesuai kebutuhan */
        vertical-align: middle;
        padding-top: 0;
        padding-bottom: 1px;
    }
</style>
@section('content')
<div class="container-fluid px-3">
    {{-- Notifikasi sukses --}}
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    {{-- Card Utama --}}
    <div class="card shadow-sm">
        <div class="card-body">
            {{-- Judul dan Tombol Tambah --}}
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">Daftar Produk</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
                    <a href="{{ route('produk.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Produk
                    </a>
                </div>
            </div>

            {{-- Search --}}
            <div class="row align-items-center mb-3">
                <div class="col-12">
                    <form method="GET" action="{{ route('produk.index') }}" class="d-flex justify-content-md-end justify-content-start gap-2 flex-wrap">
                        <input type="text" name="search"
                               class="form-control form-control-sm"
                               placeholder="Cari Nama Produk..."
                               value="{{ request('search') }}"
                               style="max-width: 200px;">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="table-responsive">
               <table class="table table-bordered table-sm text-center align-middle mb-0 table-fixed-height">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">Kode Produk</th>
                            <th style="width: 220px;">Nama Produk</th>
                            <th style="width: 90px;">Satuan</th>
                            <th style="width: 90px;">Stok Minimal</th> <!-- Tambah ini -->
                            <th style="width: 120px;">Harga Jual</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produk as $item)
                            <tr>
                                <td>{{ $item->kode_produk }}</td>
                                <td class="text-start">{{ $item->nama_produk }}</td>
                                <td>{{ $item->satuan }}</td>
                                <td>{{ $item->stokmin }}</td> <!-- Tambah ini -->
         <td>
    <div class="d-flex justify-content-center">
        <span>Rp</span>
        <span class="ms-1">{{ number_format($item->harga_jual ?? 0, 0, ',', '.') }}</span>
    </div>
</td>                           <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('produk.edit', $item->kode_produk) }}" class="btn btn-sm btn-warning square-icon" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('produk.destroy', $item->kode_produk) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')" style="display:inline;">
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
                                <td colspan="6" class="text-center">Data tidak tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection