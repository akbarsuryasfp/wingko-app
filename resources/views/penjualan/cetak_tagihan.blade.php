<!-- filepath: resources/views/penjualan/cetak_tagihan.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Tagihan - {{ $penjualan->no_jual }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .container { width: 700px; margin: 0 auto; border: 1px solid #333; padding: 24px 32px; }
        .nota-header { display: flex; align-items: center; justify-content: space-between; }
        .nota-logo { width: 80px; height: 80px; border: 1px solid #333; display: flex; align-items: center; justify-content: center; font-size: 12px; }
        .nota-title { text-align: left; flex: 1; margin-left: 24px; }
        .nota-title h2 { margin: 0 0 4px 0; font-size: 19px; }
        .nota-title .sub { font-size: 14px; }
        .nota-print { text-align: right; }
        .section-title { font-size: 15px; font-weight: bold; margin-top: 24px; margin-bottom: 8px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .table th, .table td { border: 1px solid #333; padding: 6px 8px; text-align: center; font-size: 13px; }
        .table th { background: #f2f2f2; font-size: 13.5px; }
        .summary-list { list-style: none; padding: 0; }
        .summary-list li { margin-bottom: 4px; }
        .check { color: green; font-weight: bold; }
        .bank-info { margin-top: 8px; }
        .footer { margin-top: 32px; font-size: 13px; }
        @media print {
            .no-print { display: none; }
            .container { border: none; }
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Kop Nota Tagihan -->
    <div class="nota-header" style="display: flex; align-items: center; justify-content: space-between;">
        <div class="nota-logo" style="width: 80px; height: 80px; border: 1px solid #333; display: flex; align-items: center; justify-content: center; font-size: 12px;">
            Logo
        </div>
        <div class="nota-title" style="text-align: left; flex: 1; margin-left: 24px;">
            <h2 style="margin: 0 0 4px 0; font-size: 20px;">WINGKO BABAT PRATAMA</h2>
            <div class="sub" style="font-size: 14px;">Nota Tagihan</div>
            <div class="sub" style="font-size: 14px;">Tanggal Tagihan: {{ $penjualan->tanggal_jual }}</div>
            <div class="sub" style="font-size: 14px;">Nama Pelanggan: {{ $penjualan->nama_pelanggan ?? ($penjualan->pelanggan->nama_pelanggan ?? '-') }}</div>
        </div>
        <div class="nota-print no-print" style="text-align: right;">
            <button onclick="window.print()" style="padding:4px 12px;">Print</button>
        </div>
    </div>
    <div class="subtitle" style="margin-top:8px;">Nomor Tagihan: {{ $penjualan->no_jual }}</div>

    <!-- Rincian Penjualan -->
    <div class="section-title">📦 Rincian Penjualan</div>
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $detail)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $detail->nama_produk ?? ($detail->produk->nama_produk ?? '-') }}</td>
                <td>{{ $detail->satuan ?? ($detail->produk->satuan ?? '-') }}</td>
                <td>{{ $detail->jumlah }}</td>
                <td>Rp{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td>Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Rincian Pembayaran -->
    <div class="section-title">💰 Rincian Pembayaran</div>
    <ul class="summary-list">
        <li>Total Penjualan : Rp{{ number_format($penjualan->total_harga, 0, ',', '.') }}</li>
        <li>Diskon : Rp{{ number_format($penjualan->diskon, 0, ',', '.') }}</li>
        <li>Pembayaran Sebelumnya : Rp{{ number_format($penjualan->total_bayar, 0, ',', '.') }}</li>
        <li>Metode Pembayaran : {{ ucfirst($penjualan->metode_pembayaran) }}</li>
        <li><b>Sisa Tagihan (Piutang) : Rp{{ number_format($penjualan->piutang, 0, ',', '.') }}</b></li>
    </ul>

    <!-- Instruksi Pembayaran -->
    <div class="section-title">🏦 Instruksi Pembayaran</div>
    <div>
        Silakan lakukan pelunasan sisa tagihan sebesar <b>Rp{{ number_format($penjualan->piutang, 0, ',', '.') }}</b> melalui:
        <ul>
            <li>
                <span class="check">&#x2611;</span> <b>Tunai</b><br>
                Datang langsung ke toko Wingko Babat Pratama.
            </li>
            <li style="margin-top:8px;">
                <span class="check">&#x2611;</span> <b>Non Tunai</b><br>
                Transfer ke:<br>
                <div class="bank-info">
                    <b>Bank Jateng</b><br>
                    No. Rekening: 123456789<br>
                    a.n. Wingko Babat Pratama
                </div>
            </li>
        </ul>
    </div>

    <div class="footer">
        Mohon lakukan pelunasan sebelum jatuh tempo untuk menjaga kelancaran transaksi Anda.<br>
        <br>
        Terima kasih atas kepercayaannya.<br>
        Hormat kami,<br>
        <b>Wingko Babat Pratama</b><br>
        (Yoko Setyo)
    </div>
</div>
</body>
</html>