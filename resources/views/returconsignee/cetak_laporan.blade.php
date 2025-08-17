<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Retur Consignee</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; background: #fafafa; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 8px 10px; text-align: center; }
        th { background: #e9ecef; font-weight: bold; }
        h3 { margin-bottom: 0; }
        .table-title { margin-top: 8px; font-size: 15px; font-weight: 600; }
        tr:nth-child(even) { background: #f6f6f6; }
        ul { list-style: none; padding: 0; margin: 0; }
    </style>
</head>
<body>
    <h3 style="text-align:center;">LAPORAN RETUR CONSIGNEE (MITRA)</h3>
    <div class="table-title" style="text-align:center;">Periode: {{ request('tanggal_awal') ? \Carbon\Carbon::parse(request('tanggal_awal'))->format('d F Y') : '-' }} s/d {{ request('tanggal_akhir') ? \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d F Y') : '-' }}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Retur Consignee</th>
                <th>No Konsinyasi Keluar</th>
                <th>Tanggal Retur</th>
                <th>Nama Consignee (Mitra)</th>
                <th>Jumlah Retur & Nama Produk</th>
                <th>Satuan</th>
                <th>Harga/Satuan</th>
                <th>Alasan Retur</th>
                <th>Subtotal</th>
                <th>Total Retur</th>
            </tr>
        </thead>
        <tbody>
            @php $grand_total = 0; @endphp
            @foreach($returconsignees as $idx => $rc)
                @php $rowspan = isset($rc->details) && count($rc->details) ? count($rc->details) : 1; @endphp
                @if($rc->details && count($rc->details))
                    @foreach($rc->details as $didx => $detail)
                        <tr>
                            @if($didx == 0)
                                <td rowspan="{{ $rowspan }}">{{ $idx + 1 }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $rc->no_returconsignee }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $rc->konsinyasikeluar->no_konsinyasikeluar ?? '-' }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $rc->tanggal_returconsignee ? \Carbon\Carbon::parse($rc->tanggal_returconsignee)->format('d F Y') : '-' }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $rc->consignee->nama_consignee ?? '-' }}</td>
                            @endif
                            <td>
                                <b>{{ $detail->jumlah_retur }}</b> x {{ $detail->produk->nama_produk ?? '-' }}
                            </td>
                            <td>{{ $detail->produk->satuan ?? '-' }}</td>
                            <td>Rp{{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</td>
                            <td>{{ $detail->alasan ?? '-' }}</td>
                            <td>Rp{{ number_format($detail->subtotal ?? (($detail->harga_satuan ?? 0) * ($detail->jumlah_retur ?? 0)), 0, ',', '.') }}</td>
                            @if($didx == 0)
                                <td rowspan="{{ $rowspan }}">Rp{{ number_format($rc->total_nilai_retur, 0, ',', '.') }}</td>
                            @endif
                        </tr>
                    @endforeach
                @php $grand_total += $rc->total_nilai_retur; @endphp
                @else
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $rc->no_returconsignee }}</td>
                        <td>{{ $rc->konsinyasikeluar->no_konsinyasikeluar ?? '-' }}</td>
                        <td>{{ $rc->tanggal_returconsignee ? \Carbon\Carbon::parse($rc->tanggal_returconsignee)->format('d F Y') : '-' }}</td>
                        <td>{{ $rc->consignee->nama_consignee ?? '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>Rp{{ number_format($rc->total_nilai_retur, 0, ',', '.') }}</td>
                    </tr>
                @php $grand_total += $rc->total_nilai_retur; @endphp
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="10" style="text-align:right;">GRAND TOTAL</th>
                <th>Rp{{ number_format($grand_total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
    <div class="footer" style="margin-top:30px;text-align:right;font-size:12px;">
        Dicetak pada: {{ date('d-m-Y H:i') }}
    </div>
</body>
</html>
