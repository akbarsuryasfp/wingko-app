<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Transfer Produk</title>
    <style>
        @page { size: A4 portrait; margin: 1.5cm; }
        body { font-family: Cambria, Helvetica, sans-serif; font-size: 12px; }
        .title { text-align: center; font-weight: bold; font-size: 16px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 5px 7px; font-size: 11px; }
        th { background: #f0f0f0; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
    </style>
</head>
<body>
    <div class="title">LAPORAN TRANSFER PRODUK</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Transaksi</th>
                <th>Tanggal</th>
                <th>Asal</th>
                <th>Tujuan</th>
                <th>Detail Produk</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfers as $i => $transfer)
            <tr>
                <td class="text-center">{{ $i+1 }}</td>
                <td class="text-center">{{ $transfer->no_transaksi }}</td>
                <td class="text-center">{{ date('d-m-Y', strtotime($transfer->tanggal)) }}</td>
                <td class="text-center">{{ $transfer->lokasi_asal }}</td>
                <td class="text-center">{{ $transfer->lokasi_tujuan }}</td>
                <td class="text-start">
                    @foreach($transfer->details as $detail)
                        <div>{{ $detail->nama_produk }} = {{ $detail->jumlah }} {{ $detail->satuan }}</div>
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>