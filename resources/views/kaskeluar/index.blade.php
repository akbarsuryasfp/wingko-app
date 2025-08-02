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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kaskeluar as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
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
                            <td class="text-nowrap">
                                <a href="{{ route('kaskeluar.edit', $item->no_jurnal) }}" class="btn btn-sm btn-warning me-1" data-bs-toggle="tooltip" title="Edit Data">
                                    <i class="bi bi-pencil"></i>
                                </a>
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

        </div>
    </div>
</div>
@endsection
