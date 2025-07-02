@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>DAFTAR PESANAN PELANGGAN</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-2 d-flex justify-content-between align-items-center flex-wrap">
        <!-- Filter Periode Tanggal + Urutkan -->
        <form method="GET" class="d-flex align-items-center gap-2 mb-0 flex-wrap">
            @foreach(request()->except(['tanggal_awal','tanggal_akhir','page','sort']) as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
            <span class="fw-semibold">Periode:</span>
            <input type="date" name="tanggal_awal" class="form-control form-control-sm w-auto" value="{{ request('tanggal_awal') }}">
            <span class="mx-1">s/d</span>
            <input type="date" name="tanggal_akhir" class="form-control form-control-sm w-auto" value="{{ request('tanggal_akhir') }}">
            <button type="submit" class="btn btn-secondary btn-sm">Terapkan</button>
            @php
                $sort = request('sort', 'asc');
                $nextSort = $sort === 'asc' ? 'desc' : 'asc';
                $icon = $sort === 'asc' ? '▲' : '▼';
            @endphp
            <a href="{{ route('pesananpenjualan.index', array_merge(request()->except('page'), ['sort' => $nextSort])) }}"
               class="btn btn-outline-secondary btn-sm ms-2">
                Urutkan No Pesanan {{ $icon }}
            </a>
        </form>
        <div>
            <a href="{{ route('pesananpenjualan.create') }}" class="btn btn-primary btn-sm" title="Tambah Pesanan">
                Tambah Pesanan
            </a>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>No Pesanan</th>
                <th>Tanggal Pesanan</th>
                <th>Pelanggan</th>
                <th>Total Pesanan</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesanan as $psn)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $psn->no_pesanan }}</td>
                    <td>{{ $psn->tanggal_pesanan }}</td>
                    <td>{{ $psn->nama_pelanggan ?? '-' }}</td>
                    <td>{{ number_format($psn->total_pesanan, 0, ',', '.') }}</td>
                    <td>{{ $psn->keterangan ?? '-' }}</td>
                    <td>
                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal{{ $psn->no_pesanan }}" title="Detail">
                            <i class="bi bi-eye"></i>
                        </button>
                        <a href="{{ route('pesananpenjualan.edit', $psn->no_pesanan) }}" class="btn btn-warning btn-sm" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('pesananpenjualan.destroy', $psn->no_pesanan) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
                    <td colspan="7" class="text-center">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@foreach ($pesanan as $psn)
<!-- Modal Detail Pesanan -->
<div class="modal fade" id="detailModal{{ $psn->no_pesanan }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $psn->no_pesanan }}" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel{{ $psn->no_pesanan }}">Detail Pesanan: {{ $psn->no_pesanan }}</h5>
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
                @foreach($psn->details as $i => $detail)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>
                        {{ $detail->nama_produk ?? ($detail->produk->nama_produk ?? '-') }}
                    </td>
                    <td>{{ $detail->jumlah }}</td>
                    <td>{{ number_format($detail->harga_satuan,0,',','.') }}</td>
                    <td>{{ number_format($detail->subtotal,0,',','.') }}</td>
                </tr>
                @endforeach
                @php
                    $grandTotal = $psn->details->sum('subtotal');
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