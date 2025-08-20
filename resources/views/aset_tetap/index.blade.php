@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Daftar Aset Tetap</h4>
        <a href="{{ route('aset-tetap.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Aset Tetap
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
                <form method="GET" action="{{ route('aset-tetap.index') }}" class="row g-2">
                    <div class="col-auto">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama/kode aset..." value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-outline-secondary">Cari</button>
                    </div>
                    <input type="hidden" name="sort" value="{{ $sort }}">
                </form>
                <form method="GET" action="{{ route('aset-tetap.index') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="sort" value="{{ $sort == 'desc' ? 'asc' : 'desc' }}">
                    <button type="submit" class="btn btn-outline-primary">
                        Urutkan: {{ $sort == 'desc' ? 'Terbaru' : 'Terlama' }}
                    </button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Tanggal Beli</th>
                            <th>Harga Perolehan</th>
                            <th>Umur Ekonomis</th>
                            <th>Nilai Sisa</th>
                            <th>Keterangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $aset)
                        <tr>
                            <td><span class="badge bg-info">{{ $aset->kode_aset_tetap }}</span></td>
                            <td>{{ $aset->nama_aset }}</td>
                            <td>{{ \Carbon\Carbon::parse($aset->tanggal_beli)->format('d-m-Y') }}</td>
                            <td>Rp {{ number_format($aset->harga_perolehan,0,',','.') }}</td>
                            <td>{{ $aset->umur_ekonomis }} tahun</td>
                            <td>Rp {{ number_format($aset->nilai_sisa,0,',','.') }}</td>
                            <td>{{ $aset->keterangan }}</td>
                            <td class="text-center">
                                <a href="{{ route('aset-tetap.edit', $aset->getKey()) }}" class="btn btn-sm btn-warning me-1">
                                    <i class="bi bi-pencil-square"></i> 
                                </a>
                                <form action="{{ route('aset-tetap.destroy', $aset->getKey()) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data?')">
                                        <i class="bi bi-trash"></i> 
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Data aset tetap belum tersedia.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $data->links() }}
            </div>
        </div>
    </div>
</div>
@endsection