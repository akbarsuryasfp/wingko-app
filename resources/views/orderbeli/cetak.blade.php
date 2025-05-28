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
        .btn { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Logo</div>
        <div>
            <div class="title">WINGKO BABAT PRATAMA</div>
            <div>Order Pembelian Bahan</div>
            <div>Tanggal Order: {{ $order->tanggal_order }}</div>
            <div>Nama Supplier: {{ $order->supplier->nama_supplier ?? '-' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Bahan</th>
                <th>Kuantitas</th>
                <th>Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $d)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $d->nama_bahan }}</td>
                <td>{{ $d->jumlah_beli }}</td>
                <td>{{ $d->satuan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
