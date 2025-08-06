
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Piutang</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; background: #fafafa; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 8px 10px; text-align: center; }
        th { background: #e9ecef; font-weight: bold; }
        h3 { margin-bottom: 0; }
        .table-title { margin-top: 8px; font-size: 15px; font-weight: 600; }
        tr:nth-child(even) { background: #f6f6f6; }
        .footer { margin-top: 30px; text-align: right; font-size: 12px; }
    </style>
</head>
<body>
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <h3 style="text-align:center;flex:1;">LAPORAN PIUTANG</h3>
    </div>
    <div class="table-title" style="text-align:center;">
        Periode: {{ request('tanggal_awal') ? \Carbon\Carbon::parse(request('tanggal_awal'))->format('d F Y') : '-' }} s/d {{ request('tanggal_akhir') ? \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d F Y') : '-' }}
    </div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Piutang</th>
                <th>No Jual</th>
                <th>Tanggal Jual</th>
                <th>Tanggal Jatuh Tempo</th>
                <th>Total Tagihan</th>
                <th>Total Bayar</th>
                <th>Sisa Piutang</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_tagihan = 0;
                $total_bayar = 0;
                $total_sisa = 0;
            @endphp
            @forelse($piutangs as $i => $p)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $p->no_piutang }}</td>
                <td>{{ $p->no_jual }}</td>
                <td>{{ $p->tanggal_jual ? \Carbon\Carbon::parse($p->tanggal_jual)->format('d F Y') : '-' }}</td>
                <td>{{ $p->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->format('d F Y') : '-' }}</td>
                <td>Rp{{ number_format($p->total_tagihan,0,',','.') }}</td>
                <td>Rp{{ number_format($p->total_bayar,0,',','.') }}</td>
                <td>Rp{{ number_format($p->sisa_piutang,0,',','.') }}</td>
            </tr>
            @php
                $total_tagihan += $p->total_tagihan;
                $total_bayar += $p->total_bayar;
                $total_sisa += $p->sisa_piutang;
            @endphp
            @empty
            <tr>
                <td colspan="8" class="text-center py-3">Data piutang belum ada.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">TOTAL</th>
                <th>Rp{{ number_format($total_tagihan,0,',','.') }}</th>
                <th>Rp{{ number_format($total_bayar,0,',','.') }}</th>
                <th>Rp{{ number_format($total_sisa,0,',','.') }}</th>
            </tr>
        </tfoot>
    </table>
    <div class="footer">
        Dicetak pada: {{ date('d-m-Y H:i') }}
    </div>
</body>
</html>
