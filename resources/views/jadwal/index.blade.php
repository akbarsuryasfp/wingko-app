@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">Daftar Jadwal Produksi</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('jadwal.create') }}" class="btn btn-primary mb-3">
        + Buat Jadwal Produksi
    </a>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('jadwal.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Cari no jadwal/keterangan..." value="{{ request('search') }}">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label mb-0">Dari Tanggal</label>
                    <input type="date" name="tanggal_awal" class="form-control" value="{{ request('tanggal_awal') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-0">Sampai Tanggal</label>
                    <input type="date" name="tanggal_akhir" class="form-control" value="{{ request('tanggal_akhir') }}">
                </div>
                <div class="col-md-2">
                    <input type="hidden" name="sort" value="{{ $sort ?? 'desc' }}">
                    <button class="btn btn-outline-dark" type="submit">
                        <i class="bi bi-search" style="color:black;"></i>
                    </button>
                </div>
                
                <div class="col-md-1">
                    <button type="submit" name="sort" value="{{ ($sort ?? 'desc') == 'desc' ? 'asc' : 'desc' }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-sort-alpha-down"></i>
                        {{ ($sort ?? 'desc') == 'desc' ? 'ASC' : 'DESC' }}
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No Jadwal</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Status Bahan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jadwal as $j)
                            <tr>
                                <td>{{ $j->no_jadwal }}</td>
                                <td>{{ \Carbon\Carbon::parse($j->tanggal_jadwal)->format('d-m-Y') }}</td>
                                <td>{{ $j->keterangan }}</td>
                                <td>
                                    @if(!empty($j->ada_bahan_kurang))
                                        <span class="badge bg-danger">Bahan Kurang</span>
                                    @else
                                        <span class="badge bg-success">Cukup</span>
                                    @endif
                                </td>
                                <td>
                                    @if($j->sudah_diproses)
                                        <button class="btn btn-sm btn-secondary mt-1" disabled>Diproses</button>
                                    @elseif(!empty($j->ada_bahan_kurang))
                                        <button class="btn btn-sm btn-secondary mt-1" disabled style="pointer-events: none; opacity: 0.6;">Proses Produksi</button>
                                    @else
                                        <a href="{{ route('produksi.create', ['jadwal' => $j->no_jadwal]) }}" class="btn btn-sm btn-primary mt-1">Proses Produksi</a>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('jadwal.show', $j->no_jadwal) }}" 
                                       class="btn btn-sm btn-info bi bi-book" 
                                       data-bs-toggle="tooltip" 
                                       title="Lihat Detail Jadwal">
                                    </a>
                                    <form action="{{ route('jadwal.destroy', $j->no_jadwal) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        @if($j->sudah_diproses)
                                            <span data-bs-toggle="tooltip" title="Sudah diproses, batalkan produksi terlebih dahulu">
                                                <button 
                                                    class="btn btn-sm btn-danger bi bi-trash" 
                                                    type="button" disabled
                                                    style="pointer-events: auto;">
                                                </button>
                                            </span>
                                        @else
                                            <button 
                                                class="btn btn-sm btn-danger bi bi-trash" 
                                                type="submit"
                                                data-bs-toggle="tooltip"
                                                title="Hapus Jadwal">
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada jadwal produksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span class="text-muted">
                        Menampilkan {{ $jadwal->firstItem() ?? 0 }} - {{ $jadwal->lastItem() ?? 0 }} dari {{ $jadwal->total() }} data
                    </span>
                </div>
                <div>
                    {{ $jadwal->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
