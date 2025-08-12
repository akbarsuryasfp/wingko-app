<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Hutang</title>
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
    <div class="title">LAPORAN HUTANG</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Utang</th>
                <th>No Pembelian</th>
                <th>Supplier</th>
                <th>Total Tagihan</th>
                <th>Total Bayar</th>
                <th>Sisa Hutang</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_tagihan = 0;
                $total_bayar = 0;
                $total_sisa = 0;
            @endphp
            @foreach($hutangs as $i => $hutang)
            @php
                $total_tagihan += $hutang->total_tagihan;
                $total_bayar += $hutang->total_bayar ?? 0;
                $total_sisa += $hutang->sisa_utang;
            @endphp
            <tr>
                <td class="text-center">{{ $i+1 }}</td>
                <td class="text-center">{{ $hutang->no_utang }}</td>
                <td class="text-center">{{ $hutang->no_pembelian }}</td>
                <td class="text-start">{{ $hutang->nama_supplier }}</td>
                <td class="text-end">Rp{{ number_format($hutang->total_tagihan, 0, ',', '.') }}</td>
                <td class="text-end">Rp{{ number_format($hutang->total_bayar ?? 0, 0, ',', '.') }}</td>
                <td class="text-end">
                    @if ($hutang->sisa_utang == 0)
                        <span>Rp0</span>
                    @else
                        <span class="text-danger">Rp{{ number_format($hutang->sisa_utang, 0, ',', '.') }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
            <tr>
                <th colspan="4" class="text-end">TOTAL</th>
                <th class="text-end">Rp{{ number_format($total_tagihan, 0, ',', '.') }}</th>
                <th class="text-end">Rp{{ number_format($total_bayar, 0, ',', '.') }}</th>
                <th class="text-end">Rp{{ number_format($total_sisa, 0, ',', '.') }}</th>
            </tr>
        </tbody>
    </table>
</body>
</html>