@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Daftar Transfer Produk</h4>
        <div>
            <input type="date" id="start_date" class="form-control d-inline-block" style="width: 150px;" 
                   value="{{ request('start_date', date('Y-m-01')) }}">
            <span class="mx-2">s/d</span>
            <input type="date" id="end_date" class="form-control d-inline-block" style="width: 150px;" 
                   value="{{ request('end_date', date('Y-m-d')) }}">
<button id="reset_period" class="btn btn-outline-secondary" type="button">
    <i class="fas fa-sync-alt me-1"></i> Reset
</button>
            <a href="{{ route('transferproduk.create') }}" class="btn btn-primary ms-3">Tambah Transfer</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No Transaksi</th>
                            <th>Tanggal</th>
                            <th>Asal</th>
                            <th>Tujuan</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $transfer)
                            <tr>
                                <td>{{ ($transfers->currentPage() - 1) * $transfers->perPage() + $loop->iteration }}</td>
                                <td>{{ $transfer->no_transaksi }}</td>
                                <td>{{ date('d-m-Y', strtotime($transfer->tanggal)) }}</td>
                                <td>{{ $transfer->lokasi_asal }}</td>
                                <td>{{ $transfer->lokasi_tujuan }}</td>
                                <td>
                                    @foreach($transfer->details as $detail)
                                        <div>{{ $detail->nama_produk }} {{ $detail->jumlah }}</div>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
    <a href="{{ route('transferproduk.edit', $transfer->no_transaksi) }}" 
       class="btn btn-sm btn-warning" title="Edit">
        <i class="bi bi-pencil-square"></i>
    </a>
    <form action="{{ route('transferproduk.destroy', $transfer->no_transaksi) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" 
                onclick="return confirm('Apakah Anda yakin ingin menghapus transfer ini?')"
                title="Hapus">
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
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');
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
    
    startDate.addEventListener('change', filterByDate);
    endDate.addEventListener('change', filterByDate);
    resetBtn.addEventListener('click', resetPeriod);
});
</script>
@endsection