<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Order Pembelian - {{ $order->no_order_beli }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 0.5cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            font-size: 20px;
        }
        .company-address {
            font-size: 12px;
            margin: 2px 0;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            text-decoration: underline;
        }
        hr {
            border: 1px solid #000;
            margin: 10px 0;
        }
        .info {
            margin-bottom: 15px;
        }
        .info div {
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 5px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .summary {
            margin-top: 15px;
        }
        .summary div {
            margin: 5px 0;
        }
        .bold {
            font-weight: bold;
        }
        @media print {
            body {
                padding: 0;
                font-size: 13px;
            }
            .document-title {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>WINGKO BABAT PRATAMA</h1>
        <div class="company-address">
            Jl. Tumpang XIV, RT.02/RW.09, Gajahmungkur,
        </div>
        <div class="company-address">
            Kec. Gajahmungkur, Kota Semarang, Jawa Tengah
        </div>
        <hr>
        <div class="title">SURAT ORDER PEMBELIAN</div>
    </div>

    <div class="info">
        <div>Tanggal Order: {{ $order->tanggal_order }}</div>
        <div>Supplier: {{ $order->supplier->nama_supplier ?? '-' }}</div>
        <div>Nomor Order: {{ $order->no_order_beli }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:35%">Nama Bahan</th>
                <th style="width:10%">Satuan</th>
                <th style="width:15%">Jumlah</th>
                <th style="width:15%">Harga/Satuan</th>
                <th style="width:20%">Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($details as $i => $d)
                @php 
                    $subTotal = $d->jumlah_beli * $d->harga_beli;
                    $grandTotal += $subTotal; 
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $d->nama_bahan }}</td>
                    <td>{{ $d->satuan }}</td>
                    <td>{{ number_format($d->jumlah_beli, 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($subTotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="bold">Total Harga: Rp{{ number_format($grandTotal, 0, ',', '.') }}</div>
        <div class="bold">Uang Muka: Rp{{ $order->uang_muka ? number_format($order->uang_muka, 0, ',', '.') : '0' }}</div>
        <div class="bold">Sisa Pembayaran: Rp{{ number_format($grandTotal - ($order->uang_muka ?? 0), 0, ',', '.') }}</div>
    </div>
</body>
</html>