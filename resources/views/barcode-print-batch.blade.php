@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Cetak Barcode Batch Produk</h3>
    <button onclick="printBarcodes()" class="btn btn-primary mb-3">Print</button>
    <div id="print-area">
        <div class="row">
            @foreach($batches as $batch)
            <div class="col-md-4 mb-4">
                <div class="card p-2 text-center">
                    <strong>{{ $batch->nama_produk }}</strong><br>
                    Batch: {{ $batch->no_transaksi }}<br>
                    Exp: {{ $batch->tanggal_expired ?? '-' }}<br>
                    <img src="{{ route('barcode.image', ['code' => $batch->no_transaksi]) }}" alt="barcode" style="max-width:100%;height:60px;">
                    <div style="font-size:12px;">{{ $batch->no_transaksi }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function printBarcodes() {
    var printContents = document.getElementById('print-area').innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}
</script>
@endpush

<style>
@media print {
    body * { visibility: hidden !important; }
    #print-area, #print-area * { visibility: visible !important; }
    #print-area { position: absolute; left: 0; top: 0; width: 100%; }
    button, .btn { display: none !important; }
}
</style>