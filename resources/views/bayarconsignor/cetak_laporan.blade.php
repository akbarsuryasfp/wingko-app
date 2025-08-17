<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Pembayaran Consignor</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; background: #fafafa; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 8px 10px; text-align: center; }
        th { background: #e9ecef; font-weight: bold; }
        h3 { margin-bottom: 0; }
        .table-title { margin-top: 8px; font-size: 15px; font-weight: 600; }
        tr:nth-child(even) { background: #f6f6f6; }
        @page { margin: 30px 25px 30px 25px; }
        body { margin: 0; }
    </style>
</head>
<body>
    <h3 style="text-align:center;">LAPORAN PEMBAYARAN CONSIGNOR</h3>
    <div class="table-title" style="text-align:center;">Periode: {{ request('tanggal_awal') ? \Carbon\Carbon::parse(request('tanggal_awal'))->format('d F Y') : '-' }} s/d {{ request('tanggal_akhir') ? \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d F Y') : '-' }}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Bayar Consignor</th>
                <th>Tanggal Bayar</th>
                <th>Nama Consignor (Pemilik Barang)</th>
                <th>Jumlah Terjual & Nama Produk</th>
                <th>Satuan</th>
                <th>Harga/Satuan</th>
                <th>Total Bayar</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $grand_total = 0; @endphp
            @foreach($list as $i => $row)
            @php $grand_total += $row->total_bayar; @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row->no_bayarconsignor ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($row->tanggal_bayar)->format('d F Y') }}</td>
                <td>{{ $row->consignor->nama_consignor ?? '-' }}</td>
                <td>
                    @foreach($row->details as $detail)
                        <div><b>{{ $detail->jumlah_terjual }}</b> x {{ $detail->produk->nama_produk ?? '-' }}</div>
                    @endforeach
                </td>
                <td>
                    @foreach($row->details as $detail)
                        <div>{{ $detail->produk->satuan ?? '-' }}</div>
                    @endforeach
                </td>
                <td>
                    @foreach($row->details as $detail)
                        <div>Rp{{ number_format($detail->harga_satuan,0,',','.') }}</div>
                    @endforeach
                </td>
                <td>Rp{{ number_format($row->total_bayar,0,',','.') }}</td>
                <td>{{ $row->keterangan ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" style="text-align:right;">GRAND TOTAL</th>
                <th style="text-align:center;">Rp{{ number_format($grand_total, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
    <div class="footer" style="margin-top:30px;text-align:right;font-size:12px;">
        Dicetak pada: {{ date('d-m-Y H:i') }}
    </div>
</body>
</html>
