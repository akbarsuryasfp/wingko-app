@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Scan Barcode Batch Produk</h3>
    <div id="reader" style="width:300px"></div>
    <div id="scan-result" class="mt-3"></div>
    <div id="batch-info" class="mt-3"></div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function showBatchInfo(data) {
        let html = `<table class="table table-bordered">
            <tr><th>No Transaksi (Batch)</th><td>${data.no_transaksi}</td></tr>
            <tr><th>Kode Produk</th><td>${data.kode_produk}</td></tr>
            <tr><th>Nama Produk</th><td>${data.nama_produk}</td></tr>
            <tr><th>Jumlah Masuk</th><td>${data.masuk}</td></tr>
            <tr><th>Jumlah Keluar</th><td>${data.keluar}</td></tr>
            <tr><th>Sisa Stok</th><td>${data.sisa}</td></tr>
            <tr><th>Tanggal Expired</th><td>${data.tanggal_expired ?? '-'}</td></tr>
        </table>`;
        document.getElementById('batch-info').innerHTML = html;
    }

    function fetchBatchInfo(batchCode) {
        document.getElementById('scan-result').innerHTML = "Memuat data batch...";
        fetch(`/barcode-batch-info?no_transaksi=${encodeURIComponent(batchCode)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showBatchInfo(data.batch);
                } else {
                    document.getElementById('batch-info').innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            });
    }

    let lastResult = null;
    const html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: 250 },
        qrCodeMessage => {
            if (qrCodeMessage !== lastResult) {
                lastResult = qrCodeMessage;
                document.getElementById('scan-result').innerHTML = `Barcode batch: <b>${qrCodeMessage}</b>`;
                fetchBatchInfo(qrCodeMessage);
            }
        }
    );
</script>
@endpush