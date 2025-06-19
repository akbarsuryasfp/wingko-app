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
                <th>Status</th> <!-- Gabungan status -->
                <th>Uang Muka</th>
                <th>Metode Bayar</th>
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
                    <td>
                        {{ $order->status ?? $order->status_penerimaan ?? '-' }}
                    </td>
                    <td>{{ $order->uang_muka ? number_format($order->uang_muka, 0, ',', '.') : '-' }}</td>
                    <td>{{ $order->metode_bayar ?? '-' }}</td>
                    <td>
                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal{{ $order->no_order_beli }}">Detail</button>
                        @if(($order->status_penerimaan === 'Disetujui' || $order->status_penerimaan === 'Diterima Sebagian') && $order->status !== 'Diterima Sepenuhnya')
                            <a href="{{ route('terimabahan.create') }}?order={{ $order->no_order_beli }}" class="btn btn-success btn-sm">Terima Bahan</a>
                        @endif
                        @if($order->status !== 'Disetujui' && $order->status !== 'Diterima Sebagian' && $order->status !== 'Diterima Sepenuhnya')
                            <a href="{{ route('orderbeli.edit', $order->no_order_beli) }}" class="btn btn-warning btn-sm">Edit</a>
                        @endif
                        @if($order->status_penerimaan !== 'Diterima Sebagian' && $order->status_penerimaan !== 'Diterima Sepenuhnya')
                            <a href="{{ route('orderbeli.cetak', $order->no_order_beli) }}" target="_blank" class="btn btn-secondary btn-sm">Cetak</a>
                            <form action="{{ route('orderbeli.destroy', $order->no_order_beli) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        @endif
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
                @php
                    $grandTotal = $order->details->sum('total');
                @endphp
                <tr>
                    <td colspan="5" class="text-end fw-bold">Grand Total</td>
                    <td class="fw-bold">{{ number_format($grandTotal,0,',','.') }}</td>
                </tr>
            </tbody>
        </table>

        @if($order->status !== 'Disetujui')
            <form action="{{ route('orderbeli.setujui', $order->no_order_beli) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-success">Setujui</button>
            </form>
        @else
            <form action="{{ route('orderbeli.uangmuka', $order->no_order_beli) }}" method="POST" class="mt-3" onsubmit="return validateUangMuka{{ $order->no_order_beli }}();">
                @csrf
                <div class="mb-3 d-flex align-items-center">
                    <label for="uang_muka{{ $order->no_order_beli }}" class="form-label mb-0" style="width:150px;">Uang Muka</label>
                    <input type="number" class="form-control" id="uang_muka{{ $order->no_order_beli }}" name="uang_muka" value="{{ old('uang_muka', $order->uang_muka) }}" style="width:300px;" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label for="metode_bayar{{ $order->no_order_beli }}" class="form-label mb-0" style="width:150px;">Metode Bayar</label>
                    <select class="form-control" id="metode_bayar{{ $order->no_order_beli }}" name="metode_bayar" style="width:300px;" required>
                        <option value="">-- Pilih Metode --</option>
                        <option value="Transfer" {{ old('metode_bayar', $order->metode_bayar) == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                        <option value="Tunai" {{ old('metode_bayar', $order->metode_bayar) == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        {{ ($order->uang_muka && $order->metode_bayar) ? 'Update Pembayaran' : 'Simpan Pembayaran' }}
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </form>
            <script>
            function validateUangMuka{{ $order->no_order_beli }}() {
                var uangMuka = parseFloat(document.getElementById('uang_muka{{ $order->no_order_beli }}').value);
                var grandTotal = {{ $grandTotal }};
                if(uangMuka > grandTotal) {
                    alert('Uang muka tidak boleh melebihi Grand Total!');
                    return false;
                }
                return true;
            }
            </script>
        @endif

      </div>
    </div>
  </div>
</div>
@endforeach