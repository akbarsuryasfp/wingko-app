<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kartu Persediaan Konsinyasi</title>
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
    <h3 style="text-align:center;">LAPORAN KARTU PERSEDIAAN KONSINYASI</h3>
    <div class="table-title" style="text-align:center;">Periode: {{ request('tanggal_awal') ? \Carbon\Carbon::parse(request('tanggal_awal'))->format('d F Y') : '-' }} s/d {{ request('tanggal_akhir') ? \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d F Y') : '-' }}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>No Transaksi</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Stok Akhir</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $grand_total_masuk = 0; $grand_total_keluar = 0; $grand_total_stok = 0; @endphp
            @foreach($kartuperskonsinyasi as $idx => $row)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d F Y') : '-' }}</td>
                    <td>{{ $row->no_transaksi ?? '-' }}</td>
                    <td>{{ $row->produk->nama_produk ?? '-' }}</td>
                    <td>{{ $row->produk->satuan ?? '-' }}</td>
                    <td>{{ $row->masuk ?? 0 }}</td>
                    <td>{{ $row->keluar ?? 0 }}</td>
                    <td>{{ $row->sisa ?? 0 }}</td>
                    <td>{{ $row->keterangan ?? '-' }}</td>
                </tr>
                @php 
                    $grand_total_masuk += $row->masuk ?? 0;
                    $grand_total_keluar += $row->keluar ?? 0;
                    $grand_total_stok += $row->sisa ?? 0;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" style="text-align:right;">GRAND TOTAL</th>
                <th>{{ $grand_total_masuk }}</th>
                <th>{{ $grand_total_keluar }}</th>
                <th>{{ $grand_total_stok }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
    <div class="footer" style="margin-top:30px;text-align:right;font-size:12px;">
        Dicetak pada: {{ date('d-m-Y H:i') }}
    </div>
</body>
</html>
