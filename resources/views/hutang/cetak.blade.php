<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cetak Detail Hutang</title>
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

        .hutang-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .hutang-table th,
        .hutang-table td {
            border: 1px solid #000;
            padding: 6px 7px;
            font-size: 11px;
        }

        .hutang-table th {
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

    <div class="title">DETAIL HUTANG PEMBELIAN</div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong>No Hutang</strong> : {{ $hutang->no_utang }}<br>
                <strong>No Pembelian</strong> : {{ $hutang->no_pembelian }}<br>
                <strong>Tanggal Pembelian</strong> : {{ $hutang->tanggal ?? '-' }}
            </td>
            <td>
                <strong>Supplier</strong> : {{ $nama_supplier }}<br>
                <strong>Status</strong> : {{ $hutang->status }}<br>
                <strong>Jatuh Tempo</strong> : {{ $hutang->jatuh_tempo ?? '-' }}
            </td>
        </tr>
    </table>

    <table class="hutang-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Bayar</th>
                <th>No Bukti</th>
                <th>Keterangan</th>
                <th>Jumlah Bayar</th>
                <th>Akun Kas/Bank</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pembayarans as $i => $bayar)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td class="text-center">{{ $bayar->tanggal }}</td>
                <td class="text-center">{{ $bayar->nomor_bukti }}</td>
                <td>{{ $bayar->keterangan }}</td>
                <td class="text-right">Rp {{ number_format($bayar->jumlah, 0, ',', '.') }}</td>
                <td class="text-center">{{ $bayar->kode_akun }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td><strong>Total Tagihan</strong></td>
            <td class="text-right"><strong>Rp {{ number_format($hutang->total_tagihan, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td><strong>Total Bayar</strong></td>
            <td class="text-right"><strong>Rp {{ number_format($hutang->total_bayar, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td><strong>Sisa Hutang</strong></td>
            <td class="text-right"><strong>Rp {{ number_format($hutang->sisa_utang, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <div class="clear"></div>

    @if(!empty($hutang->keterangan))
    <div class="note">
        <strong>Keterangan:</strong><br>
        {{ $hutang->keterangan }}
    </div>
    @endif

</body>
</html>