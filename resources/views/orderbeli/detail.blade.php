<table class="table">
    <tr><th>Kode Order</th><td>{{ $order->no_order_beli }}</td></tr>
    <tr><th>Tanggal</th><td>{{ $order->tanggal_order }}</td></tr>
    <tr><th>Supplier</th><td>{{ $order->supplier->nama_supplier ?? '-' }}</td></tr>
    <tr><th>Jumlah Item</th><td>{{ $details->count() }}</td></tr>
    <tr><th>Total Order</th><td>Rp {{ number_format($order->total_order,0,',','.') }}</td></tr>
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
        @php $grandTotal = 0; @endphp
        @foreach($details as $i => $d)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $d->nama_bahan ?? '-' }}</td>
            <td>{{ $d->satuan ?? '-' }}</td>
            <td>{{ $d->jumlah_beli }}</td>
            <td>Rp {{ number_format($d->harga_beli,0,',','.') }}</td>
            <td>Rp {{ number_format($d->total,0,',','.') }}</td>
        </tr>
        @php $grandTotal += $d->total; @endphp
        @endforeach
        <tr>
            <td colspan="5" class="text-end fw-bold">Grand Total</td>
            <td class="fw-bold">Rp {{ number_format($grandTotal,0,',','.') }}</td>
        </tr>
    </tbody>
</table>

{{-- Form pengaturan uang muka & metode bayar --}}
@if($order->status !== 'Disetujui')
<form action="{{ route('orderbeli.updatePembayaran', $order->no_order_beli) }}" method="POST" class="mt-3" onsubmit="return validateUangMuka();">
    @csrf
    <div class="mb-3 d-flex align-items-center">
        <label for="uang_muka" class="form-label mb-0" style="width:150px;">Uang Muka</label>
        <input type="number" class="form-control" id="uang_muka" name="uang_muka" value="{{ old('uang_muka', $order->uang_muka) }}" style="width:300px;">
    </div>
    <div class="mb-3 d-flex align-items-center">
        <label for="metode_bayar" class="form-label mb-0" style="width:150px;">Metode Bayar</label>
        <select class="form-control" id="metode_bayar" name="metode_bayar" style="width:300px;">
            <option value="">-- Pilih Metode --</option>
            <option value="Transfer" {{ old('metode_bayar', $order->metode_bayar) == 'Transfer' ? 'selected' : '' }}>Transfer</option>
            <option value="Tunai" {{ old('metode_bayar', $order->metode_bayar) == 'Tunai' ? 'selected' : '' }}>Tunai</option>
        </select>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-success">Update Pembayaran</button>
        <a href="{{ route('orderbeli.index') }}" class="btn btn-secondary">Batal</a>
    </div>
</form>
<script>
    function validateUangMuka() {
        var uangMuka = parseFloat(document.getElementById('uang_muka').value) || 0;
        var grandTotal = {{ $grandTotal }};
        if(uangMuka > grandTotal) {
            alert('Uang muka tidak boleh melebihi total order!');
            return false;
        }
        return true;
    }
</script>
@else
<div class="alert alert-success mt-3">Order sudah disetujui.</div>
@endif