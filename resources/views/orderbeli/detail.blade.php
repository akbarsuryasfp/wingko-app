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

{{-- Tombol Setujui hanya muncul jika status belum Disetujui --}}
@if($order->status !== 'Disetujui')
    <div class="d-flex justify-content-end gap-2">
        <form action="{{ route('orderbeli.setujui', $order->no_order_beli) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-success">Setujui</button>
        </form>
    </div>
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
            <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
            <a href="{{ route('orderbeli.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
    <script>
        function validateUangMuka{{ $order->no_order_beli }}() {
            var uangMuka = parseFloat(document.getElementById('uang_muka{{ $order->no_order_beli }}').value);
            var grandTotal = {{ $grandTotal }};
            if(uangMuka > grandTotal) {
                alert('Uang muka tidak boleh melebihi total order!');
                return false;
            }
            return true;
        }
    </script>
@endif