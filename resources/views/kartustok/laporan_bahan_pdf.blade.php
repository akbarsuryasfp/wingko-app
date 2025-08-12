<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Stok Bahan Baku</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h4 { text-align: center; font-size: 1.2rem; font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        thead th { background-color: #e9ecef; color: #212529; border-bottom: 2px solid #000; }
        tr:last-child td { border-bottom: 2px solid #000; }
        tr td:first-child, tr th:first-child { border-left: 2px solid #000; }
        tr td:last-child, tr th:last-child { border-right: 2px solid #000; }
        .text-success { color: #28a745; font-weight: bold; }
        .text-danger { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <h4>LAPORAN STOK BAHAN BAKU</h4>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Bahan</th>
                <th>Satuan</th>
                <th>Stok Min</th>
                <th>Stok Akhir</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bahanList as $i => $bahan)
            @php
                $totalStok = $bahan->stok_akhir->sum('stok');
                $status = $totalStok > $bahan->stokmin ? 'Aman' : 'Perlu Beli';
                $statusClass = $status == 'Aman' ? 'text-success' : 'text-danger';
            @endphp
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $bahan->kode_bahan }}</td>
                <td>{{ $bahan->nama_bahan }}</td>
                <td>{{ $bahan->satuan }}</td>
                <td>{{ $bahan->stokmin }}</td>
                <td>
                    @if($bahan->stok_akhir->count())
                        @foreach($bahan->stok_akhir as $stok)
                            <div>
                                <span style="font-weight: bold;">{{ number_format($stok->stok, 3) }}</span> {{ $bahan->satuan }}
                                @if(isset($stok->harga))
                                    @ <span style="font-weight: bold; color: #0d6efd;">Rp{{ number_format($stok->harga,0,',','.') }}</span>/{{ $bahan->satuan }}
                                @endif
                            </div>
                        @endforeach
                    @else
                        <span style="color: #dc3545;">Kosong</span>
                    @endif
                </td>
                <td><strong>{{ $totalStok }}</strong> {{ $bahan->satuan }}</td>
                <td><span class="{{ $statusClass }}">{{ $status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="text-align: right; margin-top: 10px;">
        <small>Terakhir diperbarui: {{ now()->format('d-m-Y H:i:s') }}</small>
    </div>
</body>
</html>