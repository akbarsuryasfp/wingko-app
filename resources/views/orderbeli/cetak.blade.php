<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A5 landscape;
            margin: 1.5cm;
        }

        body {
            font-family: Cambria, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #d9d2cb;
            margin: 0 auto 10px auto;
        }

        .company-name {
            font-weight: bold;
            font-size: 16px;
        }

        .company-address {
            font-size: 11px;
            margin-top: 4px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            margin: 20px 0 10px;
            font-size: 14px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 12px;
        }

        .info-table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .order-table th,
        .order-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 11px;
        }

        .order-table th {
            background-color: #f0f0f0;
        }

        .summary-table {
            width: 40%;
            border-collapse: collapse;
            float: right;
            margin-top: 10px;
        }

        .summary-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 11px;
        }

        .clear { clear: both; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo"></div>
        <div class="company-name">WINGKO BABAT PRATAMA</div>
        <div class="company-address">
            Jl. Tumpang XIV, RT.02/RW.09, Gajahmungkur, Kec. Gajahmungkur, Kota Semarang, Jawa Tengah
        </div>
        <hr style="border: 1px solid #000; margin-top: 10px; margin-bottom: 20px;">
    </div>

    <div class="title">SURAT ORDER PEMBELIAN</div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong>Kode Order</strong> : {{ $order->no_order_beli }}<br>
                <strong>Tanggal Order</strong> : {{ $order->tanggal_order }}
            </td>
            <td>
                <strong>Supplier</strong> : {{ $order->supplier->nama_supplier ?? '-' }}<br>
                {{ $order->supplier->alamat ?? '-' }}
            </td>
        </tr>
    </table>

    <table class="order-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Harga</th>
                <th>Jumlah Order</th>
                <th>Satuan</th>
                <th>Sub Total</th>
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
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td>{{ $d->nama_bahan }}</td>
                    <td style="text-align:right">Rp {{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                    <td style="text-align:center">{{ $d->jumlah_beli }}</td>
                    <td style="text-align:center">{{ $d->satuan }}</td>
                    <td style="text-align:right">Rp {{ number_format($subTotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td><strong>Total</strong></td>
            <td style="text-align:right"><strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Uang Muka</td>
            <td style="text-align:right">Rp {{ $order->uang_muka ? number_format($order->uang_muka, 0, ',', '.') : '-' }}</td>
        </tr>
        <tr>
            <td>Sisa</td>
            <td style="text-align:right">Rp {{ number_format($grandTotal - ($order->uang_muka ?? 0), 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="clear"></div>

</body>
</html>
