
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Penerimaan Bahan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px;}
        th, td { border: 1px solid #333; padding: 4px 8px; }
        th { background: #eee; }
        h4, h5 { margin: 0; }
    </style>
</head>
<body>
    <h4 style="text-align:center;">Laporan Penerimaan Bahan</h4>
    <p>Periode: {{ $tanggal_mulai }} s/d {{ $tanggal_selesai }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Terima</th>
                <th>Tanggal Terima</th>
                <th>Kode Supplier</th>
                <th>Nama Supplier</th>
                <th>Nama Bahan</th>
                <th>Total Terima</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($terimabahan as $item)
                @php
                    $key = trim((string)$item->no_terima_bahan);
                    $detailList = $details[$key] ?? collect();
                @endphp
                @foreach($detailList as $d)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->no_terima_bahan }}</td>
                    <td>{{ $item->tanggal_terima }}</td>
                    <td>{{ $item->kode_supplier ?? '-' }}</td>
                    <td>{{ $item->nama_supplier ?? '-' }}</td>
                    <td>{{ $d->nama_bahan }}</td>
                    <td>{{ number_format($d->bahan_masuk, 2) }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>