<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cetak Retur Pembelian</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 1.5cm;
        }

        body {
            font-family: Cambria, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0 auto;
            width: 100%;
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
            margin: 0 auto 8px auto;
        }

        .company-name {
            font-weight: bold;
            font-size: 16px;
        }

        .company-address {
            font-size: 11px;
            margin-top: 2px;
        }

        hr {
            border: 1px solid #000;
            margin: 10px 0 16px;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 12px;
        }

        .info-table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .retur-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .retur-table th,
        .retur-table td {
            border: 1px solid #000;
            padding: 6px 7px;
            font-size: 11px;
        }

        .retur-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .summary-table {
            width: 45%;
            float: right;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .summary-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 11px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .note {
            font-size: 11px;
            margin-top: 20px;
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
        <hr>
    </div>

    <div class="title">NOTA RETUR PEMBELIAN BAHAN</div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong>No Retur</strong> : {{ $retur->no_retur_beli }}<br>
                <strong>Tanggal Retur</strong> : {{ $retur->tanggal_retur_beli }}<br>
                <strong>Tanggal Terima</strong> : {{ $retur->tanggal_terima ?? '-' }}
            </td>
            <td>
                <strong>Supplier</strong> : {{ $retur->nama_supplier }}<br>
                <strong>No Order</strong> : {{ $retur->no_order_beli ?? '-' }}
            </td>
        </tr>
    </table>

    <table class="retur-table">
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
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $d->nama_bahan }}</td>
                <td class="text-right">Rp {{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                <td class="text-center">{{ $d->jumlah_retur }}</td>
                <td class="text-right">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                <td>{{ $d->alasan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td><strong>Total Retur</strong></td>
            <td class="text-right"><strong>Rp {{ number_format($retur->total_retur, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Jenis Pengembalian</td>
            <td class="text-right">{{ ucfirst($retur->jenis_pengembalian ?? '-') }}</td>
        </tr>
    </table>

    <div class="clear"></div>

    @if(!empty($retur->keterangan))
    <div class="note">
        <strong>Keterangan:</strong><br>
        {{ $retur->keterangan }}
    </div>
    @endif

    
</body>
</html>


