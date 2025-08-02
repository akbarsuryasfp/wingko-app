@extends('layouts.app')

@section('content')
<style>
    .table-fixed-height thead th,
    .table-fixed-height tbody td {
        height: 35px;
        vertical-align: middle;
        padding-top: 0;
        padding-bottom: 0;
    }
    .card {
        border-radius: 8px;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .action-buttons {
        display: flex;
        gap: 0.25rem;
        justify-content: center;
    }
</style>

<div class="container-fluid px-3">
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif
    <div class="card shadow-sm">
        <div class="card-body">

            <div class="row align-items-center mb-2">
                <div class="col-md-6 col-12 mb-2 mb-md-0">
                    <h4 class="mb-0 fw-semibold">Daftar Transfer Produk</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-start">
                    <div class="d-inline-flex gap-2">
                        <a href="{{ route('transferproduk.laporan.pdf', request()->all()) }}" class="btn btn-sm btn-success" target="_blank">
                            <i class="bi bi-file-earmark-pdf"></i> Cetak Laporan
                        </a>
                        <a href="{{ route('transferproduk.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Tambah Transfer
                        </a>
                    </div>
                </div>
            </div>

            <div class="row align-items-end mb-3">
                <div class="col-md-8 col-12 mb-2 mb-md-0">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="small">Periode:</span>
                        <input type="date" name="start_date"
                               value="{{ request('start_date', date('Y-m-01')) }}"
                               class="form-control form-control-sm"
                               style="width: 140px;">
                        <span class="small">s.d.</span>
                        <input type="date" name="end_date"
                               value="{{ request('end_date', date('Y-m-d')) }}"
                               class="form-control form-control-sm"
                               style="width: 140px;">
                        <select name="lokasi_tujuan" class="form-select form-select-sm" style="width: 160px;">
                            <option value="">Semua Tujuan</option>
                            @foreach($listLokasi as $lokasi)
                                <option value="{{ $lokasi }}" {{ request('lokasi_tujuan') == $lokasi ? 'selected' : '' }}>{{ $lokasi }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                    </form>
                </div>
                <div class="col-md-4 col-12 text-md-end text-center">
                    <form method="GET" class="d-inline-flex gap-2 flex-wrap justify-content-end w-100">
                        <input type="text" name="search"
                               class="form-control form-control-sm"
                               placeholder="Cari No. Transaksi / Lokasi"
                               value="{{ request('search') }}"
                               style="max-width: 250px;">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                            </button>
                                                               @if(request('search'))
        <a href="{{ route('transferproduk.index', array_merge(request()->except('search'))) }}"
           class="btn btn-sm btn-outline-danger" title="Reset">
            <i class="bi bi-x"></i>
        </a>
        @endif                   </form>
                </div>
            </div>


            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center align-middle mb-0 table-fixed-height">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="width: 120px;">No Transaksi</th>
                            <th style="width: 100px;">Tanggal</th>
                            <th style="width: 120px;">Asal</th>
                            <th style="width: 120px;">Tujuan</th>
                            <th style="width: 220px;">Detail Produk</th>
                            <th style="width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @forelse($transfers as $transfer)
                            <tr>
<<<<<<< Updated upstream
                                <td>{{ $no++ }}</td>
=======
                                <td>{{ $loop->iteration + ($transfers->currentPage() - 1) * $transfers->perPage() }}</td>
>>>>>>> Stashed changes
                                <td>{{ $transfer->no_transaksi }}</td>
                                <td>{{ date('d-m-Y', strtotime($transfer->tanggal)) }}</td>
                                <td>{{ $transfer->lokasi_asal }}</td>
                                <td>{{ $transfer->lokasi_tujuan }}</td>
                                <td class="text-start">
                                    @foreach($transfer->details as $detail)
                                        <div>{{ $detail->nama_produk }} = {{ $detail->jumlah }} {{ $detail->satuan }}</div>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('transferproduk.edit', $transfer->no_transaksi) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('transferproduk.destroy', $transfer->no_transaksi) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transfer ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada data transfer</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $transfers->appends([
                    'start_date' => request('start_date'),
                    'end_date' => request('end_date')
                ])->links() }}
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.querySelector('input[name="start_date"]');
    const endDate = document.querySelector('input[name="end_date"]');
    const resetBtn = document.getElementById('reset_period');
    
    function filterByDate() {
        const params = new URLSearchParams(window.location.search);
        params.set('start_date', startDate.value);
        params.set('end_date', endDate.value);
        window.location.href = `${window.location.pathname}?${params.toString()}`;
    }
    
    function resetPeriod() {
        const today = new Date().toISOString().split('T')[0];
        const firstDay = today.substring(0, 8) + '01';
        
        startDate.value = firstDay;
        endDate.value = today;
        filterByDate();
    }
    
    if(startDate) startDate.addEventListener('change', filterByDate);
    if(endDate) endDate.addEventListener('change', filterByDate);
    if(resetBtn) resetBtn.addEventListener('click', resetPeriod);
});
</script>
@endsection