@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h4 class="mb-3">DAFTAR PEMBELIAN BAHAN</h4>

    @php
        use Carbon\Carbon;
        $now = Carbon::now();
        $tanggal_mulai = request('tanggal_mulai') ?? $now->copy()->startOfMonth()->format('Y-m-d');
        $tanggal_selesai = request('tanggal_selesai') ?? $now->copy()->endOfMonth()->format('Y-m-d');
    @endphp

    {{-- Baris 1: Filter Jenis --}}
    <form method="GET" class="mb-2 d-flex align-items-center gap-2 flex-wrap">
        <label class="mb-0">Filter:</label>
        <select name="jenis_pembelian" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="">Semua Jenis</option>
            <option value="pembelian langsung" {{ request('jenis_pembelian') == 'pembelian langsung' ? 'selected' : '' }}>Pembelian Langsung</option>
            <option value="pembelian berdasarkan order" {{ request('jenis_pembelian') == 'pembelian berdasarkan order' ? 'selected' : '' }}>Pembelian Berdasarkan Order</option>
        </select>
    </form>

    {{-- Baris 2: Periode & Tombol --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            {{-- Hidden untuk mempertahankan jenis_pembelian saat submit --}}
            <input type="hidden" name="jenis_pembelian" value="{{ request('jenis_pembelian') }}">

            <label class="mb-0">Periode:</label>
            <input type="date" name="tanggal_mulai" value="{{ $tanggal_mulai }}" class="form-control form-control-sm w-auto" onchange="this.form.submit()">
            <span class="mb-0">s.d.</span>
            <input type="date" name="tanggal_selesai" value="{{ $tanggal_selesai }}" class="form-control form-control-sm w-auto" onchange="this.form.submit()">

            <a href="{{ route('pembelian.laporan.pdf', request()->all()) }}"
               class="btn btn-success btn-sm d-flex align-items-center ms-2"
               target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Cetak Laporan
            </a>
        </form>

        {{-- Tombol Aksi --}}
        <div class="d-flex gap-2">
            <a href="{{ route('pembelian.langsung') }}" class="btn btn-primary btn-sm">Pembelian Langsung</a>
            <a href="{{ route('pembelian.create') }}" class="btn btn-primary btn-sm">Pembelian Berdasarkan Order</a>
        </div>
    </div>
</div>


    <table class="table table-bordered text-center">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>No Pembelian</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Total Pembelian</th>
                <th>Uang Muka</th>
                <th>Total Bayar</th>
                <th>Hutang</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pembelian as $no => $p)
            <tr>
                <td>{{ $no+1 }}</td>
                <td>{{ $p->no_pembelian }}</td>
                <td>{{ $p->tanggal_pembelian }}</td>
                <td>{{ $p->nama_supplier }}</td>
                <td>{{ number_format($p->total_pembelian,0,',','.') }}</td>
                <td>{{ number_format($p->uang_muka ?? 0,0,',','.') }}</td>
                <td>{{ number_format($p->total_bayar,0,',','.') }}</td>
                <td>{{ number_format($p->hutang,0,',','.') }}</td>
                <td>
                    @if($p->hutang > 0)
                        <span class="badge bg-warning text-dark">Belum Lunas</span>
                    @else
                        <span class="badge bg-success">Lunas</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('pembelian.show', $p->no_pembelian) }}" class="btn btn-secondary btn-sm" title="Detail">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('pembelian.edit', $p->no_pembelian) }}" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('pembelian.destroy', $p->no_pembelian) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection