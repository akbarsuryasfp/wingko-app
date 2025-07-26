<!DOCTYPE html>
<html>
<head>
    <title>Laporan Piutang</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; background: #fafafa; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 8px 10px; text-align: center; }
        th { background: #e9ecef; font-weight: bold; }
        h3 { margin-bottom: 0; }
        .table-title { margin-top: 8px; font-size: 15px; font-weight: 600; }
        tr:nth-child(even) { background: #f6f6f6; }
    </style>
</head>
<body>
    <h3 style="text-align:center;">LAPORAN PELUNASAN PIUTANG</h3>
    <div class="table-title" style="text-align:center;">Periode: {{ request('tanggal_awal') ? \Carbon\Carbon::parse(request('tanggal_awal'))->format('d F Y') : '-' }} s/d {{ request('tanggal_akhir') ? \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d F Y') : '-' }}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Piutang</th>
                <th>No Jual</th>
                <th>Tanggal Jual</th>
                <th>Total Tagihan</th>
                <th>Total Bayar</th>
                <th>Sisa Piutang</th>
                <th>Tanggal Jatuh Tempo</th>
                
            </tr>
        </thead>
        <tbody>
            @forelse($piutangs as $i => $p)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $p->no_piutang }}</td>
                <td>{{ $p->no_jual }}</td>
                <td>{{ isset($p->tanggal_jual) && $p->tanggal_jual ? \Carbon\Carbon::parse($p->tanggal_jual)->format('d F Y') : '-' }}</td>
                <td>Rp{{ number_format($p->total_tagihan,0,',','.') }}</td>
                <td>Rp{{ number_format($p->total_bayar,0,',','.') }}</td>
                <td>
                    <span class="{{ $p->sisa_piutang == 0 ? 'text-dark' : 'text-danger fw-bold' }}">
                        Rp{{ number_format($p->sisa_piutang,0,',','.') }}
                    </span>
                </td>
                <td>{{ $p->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($p->tanggal_jatuh_tempo)->format('d F Y') : '-' }}</td>

            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-3">Data piutang belum ada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
