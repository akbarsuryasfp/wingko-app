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
    <div class="row">
    </div>

    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
{{-- Judul dan Tombol Tambah --}}
<div class="row align-items-center mb-3">
    <div class="col-md-6 col-12 text-md-start text-center">
        <h4 class="mb-0 fw-semibold">Daftar Bahan</h4>
    </div>
    <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
        <a href="{{ route('bahan.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Data Bahan
        </a>
    </div>
</div>

{{-- Filter dan Search --}}
<div class="row align-items-center mb-3">
    <div class="col-md-6 col-12 mb-2 mb-md-0">
        <form method="GET" action="{{ route('bahan.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
            <label for="kode_kategori" class="mb-0">Filter Kategori:</label>
            <select name="kode_kategori" id="kode_kategori"
                    class="form-select form-select-sm"
                    style="width: 180px;"
                    onchange="this.form.submit()">
                <option value="">-- Semua Kategori --</option>
                @foreach($kategoriList as $kategori)
                    <option value="{{ $kategori->kode_kategori }}" {{ request('kode_kategori') == $kategori->kode_kategori ? 'selected' : '' }}>
                        {{ $kategori->jenis_kategori }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="col-md-6 col-12">
        <form method="GET" action="{{ route('bahan.index') }}" class="d-flex justify-content-md-end justify-content-start gap-2 flex-wrap">
            <input type="text" name="search"
                   class="form-control form-control-sm"
                   placeholder="Cari Nama Bahan..."
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
        <th style="width: 40px;">No</th>
        <th style="width: 100px;">Kode Bahan</th>
        <th style="width: 220px;">Nama Bahan</th>
        <th style="width: 90px;">Satuan</th>
        <th style="width: 110px;">Stok Minimal</th>
        <th style="width: 100px;">Aksi</th>
    </tr>
</thead>
<tbody>
    @forelse($bahan as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->kode_bahan }}</td>
            <td class="text-start">{{ $item->nama_bahan }}</td>
            <td>{{ $item->satuan }}</td>
            <td>{{ $item->stokmin }}</td>
            <td>
                <div class="d-flex justify-content-center gap-1">
                    <a href="{{ route('bahan.edit', $item->kode_bahan) }}" class="btn btn-sm btn-warning square-icon" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('bahan.destroy', $item->kode_bahan) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
            <td colspan="8" class="text-center">Data tidak tersedia.</td>
        </tr>
    @endforelse
</tbody>
    </table>
</div>

        </div>
    </div>
</div>
@endsection
