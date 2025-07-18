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
            @php
                $sort = request('sort', 'asc');
                $nextSort = $sort === 'asc' ? 'desc' : 'asc';
                $icon = $sort === 'asc' ? '▲' : '▼';
            @endphp
            <a href="{{ route('penjualan.index', array_merge(request()->except('page'), ['sort' => $nextSort])) }}"
               class="btn btn-outline-secondary btn-sm ms-2">
                Urutkan No Jual {{ $icon }}
            </a>
        </div>
        <div>
            <a href="{{ route('penjualan.create', ['jenis_penjualan' => 'langsung']) }}" class="btn btn-primary btn-sm" title="Penjualan Langsung">
                Penjualan Langsung
            </a>
            <a href="{{ route('penjualan.createPesanan', ['jenis_penjualan' => 'pesanan']) }}" class="btn btn-primary btn-sm ms-2" title="Pesanan Penjualan">
                Pesanan Penjualan
            </a>
        </div>
    </div>
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
                <th>Total Jual</th>
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

@foreach ($penjualan as $p)
<!-- Modal Detail Penjualan -->
<div class="modal fade" id="detailModal{{ $p->no_jual }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $p->no_jual }}" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel{{ $p->no_jual }}">Detail Penjualan: {{ $p->no_jual }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered text-center">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($p->details as $i => $detail)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $detail->nama_produk ?? ($detail->produk->nama_produk ?? '-') }}</td>
                    <td>{{ $detail->jumlah }}</td>
                    <td>{{ number_format($detail->harga_satuan,0,',','.') }}</td>
                    <td>{{ number_format($detail->subtotal,0,',','.') }}</td>
                </tr>
                @endforeach
                @php
                    $grandTotal = $p->details->sum('subtotal');
                @endphp
                <tr>
                    <td colspan="4" class="text-end fw-bold">Grand Total</td>
                    <td class="fw-bold">{{ number_format($grandTotal,0,',','.') }}</td>
                </tr>
            </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endforeach