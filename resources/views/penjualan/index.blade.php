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
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penjualan as $no => $p)
            <tr>
                <td>{{ $no+1 }}</td>
                <td>{{ $p->no_jual }}</td>
                <td>{{ $p->tanggal_jual }}</td>
                <td>{{ $p->pelanggan->nama_pelanggan ?? '-' }}</td>
                <td>Rp{{ number_format($p->total_harga,0,',','.') }}</td>
                <td>
                    @if(isset($p->tipe_diskon) && $p->tipe_diskon == 'persen')
                        {{ $p->diskon }}%
                    @else
                        Rp{{ number_format($p->diskon,0,',','.') }}
                    @endif
                </td>
                <td>Rp{{ number_format($p->total_jual,0,',','.') }}</td>
                <td>
                    @if($p->status_pembayaran == 'belum lunas')
                        <span style="color:#d90429; font-weight:bold;">
                            Rp{{ number_format($p->piutang,0,',','.') }}
                        </span>
                    @else
                        Rp{{ number_format($p->piutang,0,',','.') }}
                    @endif
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
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">Data tidak tersedia.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection