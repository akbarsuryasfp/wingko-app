
<!DOCTYPE html>
<html>
<head>
    <style>
        @page { size: A4; margin: 2cm; }
        body { font-family: sans-serif; font-size: 12pt; }
        .header { display: flex; align-items: center; margin-bottom: 20px; }
        .logo { width: 80px; height: 80px; border: 1px solid #000; text-align: center; padding: 10px; margin-right: 20px; }
        .title { font-weight: bold; font-size: 18pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 6px; text-align: center; }
        .info { margin-bottom: 10px; }
        .keterangan { margin-top: 25px; font-size: 12pt; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Logo</div>
        <div>
            <div class="title">WINGKO BABAT PRATAMA</div>
            <div>Nota Retur Pembelian Bahan</div>
            <div class="info">
                <div>No Retur: {{ $retur->no_retur_beli }}</div>
                <div>No Order Beli: {{ $retur->no_order_beli ?? '-' }}</div>
                <div>Tanggal Terima: {{ $retur->tanggal_terima ?? '-' }}</div>
                <div>Tanggal Retur: {{ $retur->tanggal_retur_beli }}</div>
                <div>Supplier: {{ $retur->nama_supplier }}</div>
                <div>Total Retur: Rp {{ number_format($retur->total_retur, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Bahan</th>
                <th>Harga Beli</th>
                <th>Jumlah Retur</th>
                <th>Subtotal</th>
                <th>Alasan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $d)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $d->nama_bahan }}</td>
                <td>Rp {{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                <td>{{ $d->jumlah_retur }}</td>
                <td>Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                <td>{{ $d->alasan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($retur->keterangan))
    <div class="keterangan">
        <strong>Keterangan:</strong><br>
        {{ $retur->keterangan }}
    </div>
    @endif
</body>
</html>
