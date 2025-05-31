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
            <div>Pesanan Penjualan</div>
            <div>Tanggal Pesanan: {{ $pesanan->tanggal_pesanan }}</div>
            <div>Pelanggan: {{ $pesanan->nama_pelanggan ?? '-' }}</div>
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td><b>No Pesanan</b></td>
            <td>: {{ $pesanan->no_pesanan }}</td>
        </tr>
        <tr>
            <td><b>Status Pembayaran</b></td>
            <td>: {{ ucfirst($pesanan->status_pembayaran) }}</td>
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
                <td><b>{{ number_format($pesanan->total,0,',','.') }}</b></td>
            </tr>
        </tbody>
    </table>

    {{-- Bagian ini agar cetak pesanan bisa langsung masuk ke laporan penjualan --}}
    @if(isset($laporan) && $laporan)
    <hr>
    <h4>Laporan Penjualan (Termasuk Pesanan Penjualan)</h4>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Transaksi</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Total</th>
                <th>Status Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $i => $row)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $row->no_transaksi }}</td>
                <td>{{ $row->tanggal }}</td>
                <td>{{ $row->nama_pelanggan ?? '-' }}</td>
                <td>{{ number_format($row->total,0,',','.') }}</td>
                <td>{{ ucfirst($row->status_pembayaran) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>