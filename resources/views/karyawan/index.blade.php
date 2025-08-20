@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Data Karyawan</h4>
        <a href="{{ route('karyawan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Karyawan
        </a>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <form method="GET" action="{{ route('karyawan.index') }}" class="row g-2">
                    <div class="col-auto">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama/kode karyawan..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-secondary">Cari</button>
                    </div>
                    <input type="hidden" name="sort" value="{{ $sort ?? 'desc' }}">
                </form>
                <form method="GET" action="{{ route('karyawan.index') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort" value="{{ ($sort ?? 'desc') == 'desc' ? 'asc' : 'desc' }}">
                    <button type="submit" class="btn btn-outline-primary">
                        Urutkan: {{ ($sort ?? 'desc') == 'desc' ? 'Terbaru' : 'Terlama' }}
                    </button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Departemen</th>
                            <th>Gaji</th>
                            <th>Tanggal Masuk</th>
                            <th>No Telepon</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawan as $k)
                        <tr>
                            <td><span class="badge bg-info">{{ $k->kode_karyawan }}</span></td>
                            <td>{{ $k->nama }}</td>
                            <td>{{ $k->jabatan }}</td>
                            <td>{{ $k->departemen }}</td>
                            <td>Rp {{ number_format($k->gaji,0,',','.') }}</td>
                            <td>{{ \Carbon\Carbon::parse($k->tanggal_masuk)->format('d-m-Y') }}</td>
                            <td>{{ $k->no_telepon }}</td>
                            <td class="text-center">
                                <a href="{{ route('karyawan.edit', $k->kode_karyawan) }}" class="btn btn-sm btn-warning me-1">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                {{-- Tambahkan tombol hapus jika ingin --}}
                                {{-- <form action="{{ route('karyawan.destroy', $k->kode_karyawan) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form> --}}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Data karyawan belum tersedia.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $karyawan->links() }}
            </div>
        </div>
    </div>
</div>
@endsection