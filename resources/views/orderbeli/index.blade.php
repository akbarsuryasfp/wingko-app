@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Daftar Order Pembelian</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('orderbeli.create') }}" class="btn btn-primary mb-3">Tambah Order Pembelian</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Order</th>
                <th>Tanggal</th>
                <th>Nama Supplier</th>
                <th>Total Order</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $order->no_order_beli }}</td>
                    <td>{{ $order->tanggal_order }}</td>
                    <td>{{ $order->supplier->nama_supplier ?? '-' }}</td>
                    <td>{{ number_format($order->total_order, 0, ',', '.') }}</td>
                    <td>{{ $order->status ?? '-' }}</td>
                    <td>
                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal{{ $order->no_order_beli }}">Detail</button>
                        @if($order->status !== 'Disetujui')
                            <a href="{{ route('orderbeli.edit', $order->no_order_beli) }}" class="btn btn-warning btn-sm">Edit</a>
                        @else
                            <a href="{{ route('orderbeli.cetak', $order->no_order_beli) }}" target="_blank" class="btn btn-secondary btn-sm">Cetak</a>
                        @endif
                        <form action="{{ route('orderbeli.destroy', $order->no_order_beli) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
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


@foreach ($orders as $order)
<!-- Modal -->
<div class="modal fade" id="detailModal{{ $order->no_order_beli }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $order->no_order_beli }}" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel{{ $order->no_order_beli }}">Detail Order: {{ $order->no_order_beli }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered text-center">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Jumlah</th>
                    <th>Harga/Satuan</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->details as $i => $detail)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $detail->nama_bahan }}</td>
                    <td>{{ $detail->satuan }}</td>
                    <td>{{ $detail->jumlah_beli }}</td>
                    <td>{{ number_format($detail->harga_beli,0,',','.') }}</td>
                    <td>{{ number_format($detail->total,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <form action="{{ route('orderbeli.setujui', $order->no_order_beli) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-success">Setujui</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endforeach
