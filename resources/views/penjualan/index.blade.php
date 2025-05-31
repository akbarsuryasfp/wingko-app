@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Daftar Penjualan</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('penjualan.create') }}" class="btn btn-primary mb-3">Tambah Penjualan</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>No Jual</th>
                <th>Tanggal Jual</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Metode Pembayaran</th>
                <th>Status Pembayaran</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penjualan as $jual)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $jual->no_jual }}</td>
                    <td>{{ $jual->tanggal_jual }}</td>
                    <td>{{ $jual->pelanggan->nama_pelanggan ?? '-' }}</td>
                    <td>{{ number_format($jual->total, 0, ',', '.') }}</td>
                    <td>{{ ucfirst($jual->metode_pembayaran) }}</td>
                    <td>
                        @if($jual->status_pembayaran == 'lunas')
                            <span class="badge bg-success">Lunas</span>
                        @else
                            <span class="badge bg-warning text-dark">Belum Lunas</span>
                        @endif
                    </td>
                    <td>{{ $jual->keterangan }}</td>
                    <td>
                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal{{ $jual->no_jual }}">Detail</button>
                        <a href="{{ route('penjualan.edit', $jual->no_jual) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('penjualan.destroy', $jual->no_jual) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                        <a href="{{ route('penjualan.cetak', $jual->no_jual) }}" target="_blank" class="btn btn-success btn-sm mt-1">Cetak</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@foreach ($penjualan as $jual)
<!-- Modal Detail Penjualan -->
<div class="modal fade" id="detailModal{{ $jual->no_jual }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $jual->no_jual }}" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel{{ $jual->no_jual }}">Detail Penjualan: {{ $jual->no_jual }}</h5>
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
                @foreach($jual->details as $i => $detail)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $detail->nama_produk ?? ($detail->produk->nama_produk ?? '-') }}</td>
                    <td>{{ $detail->jumlah }}</td>
                    <td>{{ number_format($detail->harga_satuan,0,',','.') }}</td>
                    <td>{{ number_format($detail->subtotal,0,',','.') }}</td>
                </tr>
                @endforeach
                @php
                    $grandTotal = $jual->details->sum('subtotal');
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