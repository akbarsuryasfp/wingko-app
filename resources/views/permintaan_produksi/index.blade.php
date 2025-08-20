@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Daftar Permintaan Produksi</h2>

    <a href="{{ route('permintaan_produksi.create') }}" class="btn btn-success mb-3">
        + Tambah Permintaan Produksi
    </a>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('permintaan_produksi.index') }}" class="row g-2 align-items-end mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Cari kode/keterangan..." value="{{ request('search') }}">
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
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Detail</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permintaanProduksi as $item)
                            <tr>
                                <td>{{ ($permintaanProduksi->firstItem() ?? 0) + $loop->index }}</td>
                                <td>{{ $item->no_permintaan_produksi }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $item->keterangan }}</td>
                                <td>
                                    @if ($item->status === 'Menunggu')
                                        <span class="badge bg-warning text-dark">{{ $item->status }}</span>
                                    @elseif ($item->status === 'Diproses')
                                        <span class="badge bg-primary">{{ $item->status }}</span>
                                    @elseif ($item->status === 'Selesai')
                                        <span class="badge bg-success">{{ $item->status }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $item->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-toggle-detail" 
                                            type="button"
                                            data-detail="{{ $item->no_permintaan_produksi }}">
                                        Lihat
                                    </button>
                                </td>
                                <td>
                                    <form action="{{ route('permintaan_produksi.destroy', $item->no_permintaan_produksi) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus permintaan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <tr class="detail-row" id="detail-{{ $item->no_permintaan_produksi }}" style="display: none;">
                                <td colspan="7" class="p-0">
                                    <div class="p-3">
                                        <table class="table table-sm table-striped mb-0">
                                            <thead>
                                                <tr class="table-secondary">
                                                    <th>Kode Produk</th>
                                                    <th>Nama Produk</th>
                                                    <th>Unit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($item->details as $detail)
                                                    <tr>
                                                        <td>{{ $detail->kode_produk }}</td>
                                                        <td>{{ $detail->produk->nama_produk ?? '-' }}</td>
                                                        <td>{{ $detail->unit }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center">Tidak ada detail permintaan</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada permintaan produksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span class="text-muted">
                        Menampilkan {{ $permintaanProduksi->firstItem() ?? 0 }} - {{ $permintaanProduksi->lastItem() ?? 0 }} dari {{ $permintaanProduksi->total() }} data
                    </span>
                </div>
                <div>
                    {{ $permintaanProduksi->links() }}
                </div>
            </div>
           
            <div class="mt-4 text-end">
                <a href="{{ route('jadwal.index') }}" class="btn btn-success">
                    Lanjut ke Jadwal Produksi <i class="bi bi-arrow-right-circle"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleDetailButtons = document.querySelectorAll('.btn-toggle-detail');
        toggleDetailButtons.forEach(button => {
            button.addEventListener('click', function () {
                const detailId = this.getAttribute('data-detail');
                const detailRow = document.getElementById('detail-' + detailId);
                if (detailRow.style.display === 'none' || detailRow.style.display === '') {
                    detailRow.style.display = 'table-row';
                } else {
                    detailRow.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush