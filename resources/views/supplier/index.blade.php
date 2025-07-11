@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            {{-- Judul --}}
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">Daftar Supplier</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
                    {{-- Tombol Tambah Supplier --}}
                    <a href="{{ route('supplier.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Supplier
                    </a>
                </div>
            </div>

            {{-- Filter dan Search (samakan dengan produk) --}}
<div class="row align-items-center mb-3">
    <div class="col-md-12 text-md-end text-start">
        <form method="GET" action="{{ route('supplier.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
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
            {{-- Tabel --}}
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle table-sm">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center align-middle py-3" style="width:110px;">Kode Supplier</th>
                            <th class="text-center align-middle py-3" style="width:150px;">Nama Supplier</th>
                            <th class="text-center align-middle py-3" style="width:180px;">Alamat</th>
                            <th class="text-center align-middle py-3" style="width:120px;">No. Telp</th>
                            <th class="text-center align-middle py-3" style="width:180px;">No. Rekening</th>
                            <th class="text-center align-middle py-3" style="width:220px;">Keterangan</th>
                            <th class="text-center align-middle py-3" style="width:110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($supplier as $item)
                            <tr>
                                <td class="text-center py-3">{{ $item->kode_supplier }}</td>
                                <td class="text-start py-3">{{ $item->nama_supplier }}</td>
                                <td class="text-start py-3">{{ $item->alamat }}</td>
                                <td class="text-center py-3">{{ $item->no_telp }}</td>
                                <td class="text-center py-3">{{ $item->rekening }}</td>
                                <td class="text-start py-3">{{ $item->keterangan }}</td>
                                <td class="text-center py-3">
                                    <a href="{{ route('supplier.edit', $item->kode_supplier) }}" class="btn btn-warning btn-sm me-1" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('supplier.destroy', $item->kode_supplier) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-3">Data tidak tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection