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
                    <h4 class="mb-0 fw-semibold">Daftar Pelanggan</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
                    <a href="{{ route('pelanggan.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Pelanggan
                    </a>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-md-12 text-md-end text-start">
                    <form method="GET" action="{{ route('pelanggan.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        <input type="text" name="search" id="searchPelanggan" class="form-control form-control-sm" placeholder="Cari Nama Pelanggan..." value="{{ request('search') }}" style="max-width: 200px;" autocomplete="off">
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
                            <th class="text-center align-middle py-3" style="width:110px;">Kode Pelanggan</th>
                            <th class="text-center align-middle py-3" style="width:150px;">Nama Pelanggan</th>
                            <th class="text-center align-middle py-3" style="width:180px;">Alamat</th>
                            <th class="text-center align-middle py-3" style="width:120px;">No. Telepon</th>
                            <th class="text-center align-middle py-3" style="width:110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pelanggan as $item)
                            <tr>
                                <td class="text-center py-3">{{ $item->kode_pelanggan }}</td>
                                <td class="text-center py-3">{{ $item->nama_pelanggan }}</td>
                                <td class="text-start py-3">{{ $item->alamat }}</td>
                                <td class="text-center py-3">{{ $item->no_telp }}</td>
                                <td class="text-center py-3">
                                    <a href="{{ route('pelanggan.edit', $item->kode_pelanggan) }}" class="btn btn-warning btn-sm me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('pelanggan.destroy', $item->kode_pelanggan) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
                                <td colspan="5" class="text-center py-3">Data tidak tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection