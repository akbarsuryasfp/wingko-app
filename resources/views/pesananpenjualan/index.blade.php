@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Daftar Pesanan Penjualan</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('pesananpenjualan.create') }}" class="btn btn-primary mb-3">Tambah Pesanan</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>No Pesanan</th>
                <th>Tanggal Pesanan</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Status Pembayaran</th>
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
                    <td>{{ number_format($psn->total, 0, ',', '.') }}</td>
                    <td>
                        @if($psn->status_pembayaran == 'lunas')
                            <span class="badge bg-success">Lunas</span>
                        @else
                            <span class="badge bg-warning text-dark">Belum Lunas</span>
                        @endif
                    </td>
                    <td>{{ $psn->keterangan ?? '-' }}</td>
                    <td>
                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal{{ $psn->no_pesanan }}">Detail</button>
                        <a href="{{ route('pesananpenjualan.edit', $psn->no_pesanan) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('pesananpenjualan.destroy', $psn->no_pesanan) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                        <a href="{{ route('pesananpenjualan.cetak', $psn->no_pesanan) }}" target="_blank" class="btn btn-success btn-sm mt-1">Cetak</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Data tidak tersedia.</td>
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
                    <td>{{ $detail->nama_produk ?? ($detail->produk->nama_produk ?? '-') }}</td>
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