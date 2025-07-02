<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pembelian Bahan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 4px 8px; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h3 style="text-align:center;">LAPORAN PEMBELIAN BAHAN</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Pembelian</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Total Pembelian</th>
                <th>Uang Muka</th>
                <th>Total Bayar</th>
                <th>Hutang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pembelian as $no => $p)
            <tr>
                <td>{{ $no+1 }}</td>
                <td>{{ $p->no_pembelian }}</td>
                <td>{{ $p->tanggal_pembelian }}</td>
                <td>{{ $p->nama_supplier }}</td>
                <td>{{ number_format($p->total_pembelian,0,',','.') }}</td>
                <td>{{ number_format($p->uang_muka ?? 0,0,',','.') }}</td>
                <td>{{ number_format($p->total_bayar,0,',','.') }}</td>
                <td>{{ number_format($p->hutang,0,',','.') }}</td>
                <td>
                    @if($p->hutang > 0)
                        Belum Lunas
                    @else
                        Lunas
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>