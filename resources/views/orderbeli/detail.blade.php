<table class="table">
    <tr><th>Kode Order</th><td>{{ $order->no_order_beli }}</td></tr>
    <tr><th>Tanggal</th><td>{{ $order->tanggal_order }}</td></tr>
    <tr><th>Supplier</th><td>{{ $order->supplier->nama_supplier ?? '-' }}</td></tr>
    <tr><th>Total Order</th><td>{{ number_format($order->total_order,0,',','.') }}</td></tr>
</table>

<h5>Detail Bahan Dibeli</h5>
<table class="table table-bordered">
    <thead>
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
        @foreach($details as $i => $d)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $detail->nama_bahan ?? '-' }}</td>
            <td>{{ $detail->satuan ?? '-' }}</td>
            <td>{{ $d->jumlah_beli }}</td>
            <td>{{ number_format($d->harga_beli,0,',','.') }}</td>
            <td>{{ number_format($d->total,0,',','.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-end gap-2">
    <form action="{{ route('orderbeli.setujui', $order->no_order_beli) }}" method="POST" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-success">Setujui</button>
    </form>
</div>