@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-body">
<style>
    table th, table td {
        vertical-align: middle !important;
        height: 40px; /* atau sesuaikan */
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
</style>
            <!-- Judul dan Tombol Tambah -->
       
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
    <h4 class="mb-0">Data Pengeluaran Kas Lain Lain</h4>
    <div class="d-flex gap-2">

        <a href="{{ route('kaskeluar.laporan', [
            'start_date' => request('tanggal_awal', date('Y-m-01')),
            'end_date' => request('tanggal_akhir', date('Y-m-d')),
            'filter_penerima' => request('filter_penerima'),
            'search' => request('search')
        ]) }}" target="_blank" class="btn btn-success btn-sm mb-2">
            <i class="bi bi-file-earmark-pdf"></i> Cetak Laporan
        </a>
        <a href="{{ route('kaskeluar.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Pengeluaran    
        </a>

    </div>
</div>

<form method="GET" action="{{ route('kaskeluar.index') }}" id="filterForm" class="d-flex flex-wrap align-items-center gap-3 mb-3">
    <div class="d-flex align-items-center gap-2">
        <span class="fw-medium">Periode:</span>

        <input type="date" name="tanggal_awal" class="form-control form-control-sm"
            value="{{ request('tanggal_awal', date('Y-m-01')) }}"
            style="width: 140px;" onchange="document.getElementById('filterForm').submit();">

        <span class="fw-medium">s.d.</span>

        <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
            value="{{ request('tanggal_akhir', date('Y-m-d')) }}"
            style="width: 140px;" onchange="document.getElementById('filterForm').submit();">
    </div>
    <select name="per_page" class="form-select form-select-sm" style="width: 110px;" onchange="document.getElementById('filterForm').submit();">
        <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10 / page</option>
        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20 / page</option>
        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 / page</option>
        <option value="40" {{ request('per_page') == 40 ? 'selected' : '' }}>40 / page</option>
        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
        <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
    </select>

                    <div class="ms-auto d-flex align-items-center gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari keterangan atau penerima..." style="width: 250px;" value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-secondary">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <a href="{{ route('kaskeluar.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
</form>

            <!-- Tabel -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center" style="vertical-align: middle;">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>No Bukti</th>
                            <th>Jumlah</th>
                            <th>Penerima</th>
                            <th>Keterangan</th>
                            <th>Bukti Nota</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
  $no = (method_exists($kaskeluar, 'currentPage') ? ($kaskeluar->currentPage() - 1) * $kaskeluar->perPage() + 1 : 1);
@endphp
                        @forelse ($kaskeluar as $item)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}</td>
                            <td>{{ $item->nomor_bukti }}</td>
                            <td class="text-end">{{ $item->jumlah_rupiah }}</td>
                            <td>
                                @if($item->penerima !== '-')
                                    {{ $item->penerima }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-start">{{ $item->keterangan_teks }}</td>
<td>
    @if($item->bukti_nota)
        <button type="button" class="btn btn-sm btn-outline-primary"
            onclick="showNotaModal('{{ asset('storage/' . $item->bukti_nota) }}')">
            Lihat Nota
        </button>
    @else
        <span class="text-muted">-</span>
    @endif
</td>
                            <td class="text-nowrap">
    @unless(auth()->user()->role == 'gudang')
    <a href="{{ route('kaskeluar.edit', $item->no_jurnal) }}" class="btn btn-sm btn-warning me-1" data-bs-toggle="tooltip" title="Edit Data">
        <i class="bi bi-pencil"></i>
    </a>
    @endunless
                                <form action="{{ route('kaskeluar.destroy', $item->no_jurnal) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus Data">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada data kas keluar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(request('per_page') != 'all')
            <div class="mt-3 d-flex justify-content-center">
    {{ $kaskeluar->withQueryString()->links() }}
</div>
@endif

        </div>
    </div>
</div>

<!-- Modal Bukti Nota -->
<div class="modal fade" id="notaModal" tabindex="-1" aria-labelledby="notaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notaModalLabel">Bukti Nota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center" id="notaModalBody">
                <!-- Konten akan dimuat dinamis di sini -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>
function showNotaModal(url) {
    document.getElementById('notaModalBody').innerHTML = '';
    let ext = url.split('.').pop().toLowerCase();
    let html = '';
    if(['jpg','jpeg','png','gif','bmp','webp'].includes(ext)) {
        html = `<img src="${url}" alt="Bukti Nota" class="img-fluid" style="max-height: 70vh;">`;
    } else if(ext === 'pdf') {
        html = `<embed src="${url}" type="application/pdf" width="100%" height="600px" />`;
    } else {
        html = `<div class="alert alert-info">File tidak dapat ditampilkan. <a href="${url}" target="_blank" class="btn btn-primary mt-2">Download File</a></div>`;
    }
    document.getElementById('notaModalBody').innerHTML = html;
    if (typeof bootstrap !== 'undefined') {
        var modal = new bootstrap.Modal(document.getElementById('notaModal'));
        modal.show();
    } else if (window.$ && $('#notaModal').modal) {
        $('#notaModal').modal('show');
    } else {
        alert('Bootstrap JS tidak ditemukan!');
    }
}
</script>
@endsection
