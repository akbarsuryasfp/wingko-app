@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h4>DAFTAR PENJUALAN</h4>
    <div class="mb-2 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center flex-wrap">
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Label Filter di kiri -->
                <span class="me-2 mb-0 fw-semibold">Filter:</span>
                <!-- Filter Jenis Penjualan -->
                <select name="jenis_penjualan" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    <option value="">Semua Jenis</option>
                    <option value="langsung" {{ request('jenis_penjualan') == 'langsung' ? 'selected' : '' }}>Langsung</option>
                    <option value="pesanan" {{ request('jenis_penjualan') == 'pesanan' ? 'selected' : '' }}>Pesanan</option>
                </select>
                <!-- Filter Status Pembayaran -->
                <select name="status_pembayaran" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="lunas" {{ request('status_pembayaran') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="belum lunas" {{ request('status_pembayaran') == 'belum lunas' ? 'selected' : '' }}>Belum Lunas</option>
                </select>
            </form>
        </div>
    </div>
    <!-- Filter Periode Tanggal + Button Urutkan + Button Penjualan -->
    <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <form method="GET" class="d-flex align-items-center gap-2 mb-0">
                @foreach(request()->except(['tanggal_awal','tanggal_akhir','page']) as $key => $val)
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endforeach
                <span class="fw-semibold">Periode:</span>
                <input type="date" name="tanggal_awal" class="form-control form-control-sm w-auto" value="{{ request('tanggal_awal') }}">
                <span class="mx-1">s/d</span>
                <input type="date" name="tanggal_akhir" class="form-control form-control-sm w-auto" value="{{ request('tanggal_akhir') }}">
                <button type="submit" class="btn btn-secondary btn-sm">Terapkan</button>
            </form>
            @php
                $sort = request('sort', 'asc');
                $nextSort = $sort === 'asc' ? 'desc' : 'asc';
                $icon = $sort === 'asc' ? '▲' : '▼';
            @endphp
            <a href="{{ route('penjualan.index', array_merge(request()->except('page'), ['sort' => $nextSort])) }}"
               class="btn btn-outline-secondary btn-sm">
                Urutkan No Jual {{ $icon }}
            </a>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('penjualan.create', ['jenis_penjualan' => 'langsung']) }}" class="btn btn-primary btn-sm" title="Penjualan Langsung">
                Penjualan Langsung
            </a>
            <a href="{{ route('penjualan.createPesanan', ['jenis_penjualan' => 'pesanan']) }}" class="btn btn-primary btn-sm" title="Pesanan Penjualan">
                Pesanan Penjualan
            </a>
        </div>
    </div>
    <table class="table table-bordered text-center">
        <thead class="table-light">
    <!-- Filter Periode Tanggal -->
    <form method="GET" class="d-flex align-items-center gap-2 mb-2">
        @foreach(request()->except(['tanggal_awal','tanggal_akhir','page']) as $key => $val)
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
        @endforeach
        <span class="fw-semibold">Periode:</span>
        <input type="date" name="tanggal_awal" class="form-control form-control-sm w-auto" value="{{ request('tanggal_awal') }}">
        <span class="mx-1">s/d</span>
        <input type="date" name="tanggal_akhir" class="form-control form-control-sm w-auto" value="{{ request('tanggal_akhir') }}">
        <button type="submit" class="btn btn-secondary btn-sm">Terapkan</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>No Jual</th>
                <th>Tanggal Jual</th>
                <th>Pelanggan</th>
                <th>Total Harga</th>
                <th>Diskon (Rp)</th>
                <th>Total Jual</th>
                <th>Piutang</th>
                <th>Metode Pembayaran</th>
                <th>Status Pembayaran</th>
                <th>Diskon</th>
                <th>Metode</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualan as $i => $jual)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $jual->no_jual }}</td>
                <td>{{ $jual->tanggal_jual }}</td>
                <td>{{ $jual->pelanggan->nama_pelanggan ?? '-' }}</td>
                <td class="text-end">
                    Rp {{ number_format($jual->details->sum('subtotal'), 0, ',', '.') }}
                </td>
                <td class="text-end">
                    Rp {{ number_format($jual->total, 0, ',', '.') }}
                </td>
                <td class="text-end">
                    Rp {{ number_format($jual->diskon ?? 0, 0, ',', '.') }}
                </td>
                <td>{{ ucfirst($jual->metode_pembayaran) }}</td>
                <td>
                    <span class="badge bg-{{ $jual->status_pembayaran == 'lunas' ? 'success' : 'warning' }}">
                        {{ ucfirst($jual->status_pembayaran) }}
                    </span>
                </td>
                <td>{{ ucfirst($p->metode_pembayaran) }}</td>
                <td>
                    @if($p->status_pembayaran == 'lunas')
                        <span class="badge bg-success">Lunas</span>
                    @else
                        <span class="badge bg-warning text-dark">Belum Lunas</span>
                    @endif
                </td>
                <td class="d-flex flex-wrap gap-1 justify-content-center align-items-center">
                    <!-- Detail -->
                    <a href="{{ route('penjualan.show', $p->no_jual) }}" class="btn btn-info btn-sm" title="Detail">
                        <i class="bi bi-eye"></i>
                    </a>
                    <!-- Edit (hanya jika belum lunas) -->
                    @if($p->status_pembayaran != 'lunas')
                        <a href="{{ route('penjualan.edit', $p->no_jual) }}" class="btn btn-warning btn-sm" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                    @endif
                    <!-- Cetak Tagihan dan Bayar (jika belum lunas) -->
                    @if($p->status_pembayaran == 'belum lunas')
                        <a href="{{ route('penjualan.cetak_tagihan', $p->no_jual) }}" target="_blank" class="btn btn-dark btn-sm" title="Cetak Nota Tagihan">
                            <i class="bi bi-receipt"></i>
                        </a>
                        @php
                            $piutang = \App\Models\Piutang::where('no_jual', $p->no_jual)->first();
                        @endphp
                        @if($piutang)
                            <a href="{{ route('piutang.bayar', $piutang->no_piutang) }}" class="btn btn-primary btn-sm" title="Pembayaran">
                                <i class="bi bi-cash-coin"></i>
                            </a>
                        @endif
                    @endif
                    <!-- Cetak Nota Penjualan (hanya jika lunas) -->
                    @if($p->status_pembayaran == 'lunas')
                        <a href="{{ route('penjualan.cetak', $p->no_jual) }}" target="_blank" class="btn btn-success btn-sm" title="Cetak Nota Penjualan">
                            <i class="bi bi-printer"></i>
                        </a>
                    @endif
                    <!-- Hapus -->
                    <form action="{{ route('penjualan.destroy', $p->no_jual) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                <td>
                    <a href="{{ route('penjualan.show', $jual->no_jual) }}" class="btn btn-info btn-sm">Lihat</a>
                    <a href="{{ route('penjualan.edit', $jual->no_jual) }}" class="btn btn-success btn-sm">Edit</a>
                    <form action="{{ route('penjualan.destroy', $jual->no_jual) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus penjualan ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection