@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Daftar Produksi</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('produksi.index') }}" class="row g-2 mb-3 align-items-end">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Cari no produksi/keterangan..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <input type="hidden" name="sort" value="{{ $sort ?? 'desc' }}">
            <button type="submit" class="btn btn-outline-secondary bi bi-search"></button>
        </div>
         <div class="col-md-6 text-end">
            <button type="submit" name="sort" value="{{ ($sort ?? 'desc') == 'desc' ? 'asc' : 'desc' }}" class="btn btn-outline-primary">
                Urutkan: {{ ($sort ?? 'desc') == 'desc' ? 'Terlama' : 'Terbaru' }}
            </button>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No Produksi</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Detail Produk</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($produksi as $p)
                            <tr>
                                <td>{{ $p->no_produksi }}</td>
                                <td>{{ \Carbon\Carbon::parse($p->tanggal_produksi)->format('d-m-Y') }}</td>
                                <td>{{ $p->keterangan }}</td>
                                <td>
                                    <a href="{{ route('produksi.show', $p->no_produksi) }}"
                                       class="btn btn-sm btn-info mt-1">
                                        Detail Lengkap
                                    </a>
                                </td>
                                <td>
                                  <form action="{{ route('produksi.destroy', $p->no_produksi) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger mt-1" onclick="return confirm('Apakah Anda yakin ingin membatalkan produksi ini?')">Batalkan</button>
                                    </form>
                                    <a href="{{ route('hpp.index', ['search' => $p->no_produksi]) }}" class="btn btn-sm btn-success mt-1">
                                        Isi HPP
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada data produksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span class="text-muted">
                        Menampilkan {{ $produksi->firstItem() ?? 0 }} - {{ $produksi->lastItem() ?? 0 }} dari {{ $produksi->total() }} data
                    </span>
                </div>
                <div>
                    {{ $produksi->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
