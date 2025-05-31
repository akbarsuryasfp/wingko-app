<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { display: flex; align-items: center; margin-bottom: 20px; }
        .logo { width: 80px; height: 80px; border: 1px solid #000; text-align: center; padding: 10px; margin-right: 20px; }
        .title { font-weight: bold; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 6px; text-align: center; }
        .info-table { border: none; margin-bottom: 0; }
        .info-table td { border: none; text-align: left; padding: 2px 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Logo</div>
        <div>
            <div class="title">WINGKO BABAT PRATAMA</div>
            <div>Penjualan Produk</div>
            <div>Tanggal Jual: {{ $penjualan->tanggal_jual }}</div>
            <div>Pelanggan: {{ $penjualan->nama_pelanggan ?? '-' }}</div>
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td><b>No Penjualan</b></td>
            <td>: {{ $penjualan->no_jual }}</td>
        </tr>
        <tr>
            <td><b>Metode Pembayaran</b></td>
            <td>: {{ ucfirst($penjualan->metode_pembayaran) }}</td>
        </tr>
        <tr>
            <td><b>Status Pembayaran</b></td>
            <td>: {{ ucfirst($penjualan->status_pembayaran) }}</td>
        </tr>
        <tr>
            <td><b>Keterangan</b></td>
            <td>: {{ $penjualan->keterangan ?? '-' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $d)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $d->nama_produk ?? '-' }}</td>
                <td>{{ $d->jumlah }}</td>
                <td>{{ number_format($d->harga_satuan,0,',','.') }}</td>
                <td>{{ number_format($d->subtotal,0,',','.') }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="4" style="text-align:right;"><b>Total</b></td>
                <td><b>{{ number_format($penjualan->total,0,',','.') }}</b></td>
            </tr>
        </tbody>
    </table>
</body>
</html>