<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Stok Akhir Produk</title>
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
    <h4>LAPORAN STOK AKHIR PRODUK</h4>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Stok Gudang</th>
                <th>Stok Toko 1</th>
                <th>Stok Toko 2</th>
                <th>Total Stok</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produkList as $i => $produk)
            @php
                $gudangList = $produk->stok_akhir->where('lokasi', 'Gudang');
                $toko1List  = $produk->stok_akhir->where('lokasi', 'Toko 1');
                $toko2List  = $produk->stok_akhir->where('lokasi', 'Toko 2');
                $totalStok = $produk->stok_akhir->sum('stok');
                $stokmin = $produk->stokmin ?? 0;
                $status = ($totalStok <= $stokmin) ? 'Perlu Produksi' : 'Aman';
                $statusClass = $status == 'Aman' ? 'text-success' : 'text-danger';
            @endphp
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $produk->kode_produk }}</td>
                <td>{{ $produk->nama_produk }}</td>
                <td>{{ $produk->satuan }}</td>
                <td>
                    @if($gudangList->count())
                        @foreach($gudangList as $stok)
                            <div>
                                <span style="font-weight: bold;">{{ number_format($stok->stok, 3) }}</span> {{ $produk->satuan }}
                                @if(isset($stok->hpp))
                                    @ <span style="font-weight: bold; color: #0d6efd;">Rp{{ number_format($stok->hpp,0,',','.') }}</span>/{{ $produk->satuan }}
                                @endif
                            </div>
                        @endforeach
                    @else
                        <span style="color: #dc3545;">Kosong</span>
                    @endif
                </td>
                <td>
                    @if($toko1List->count())
                        @foreach($toko1List as $stok)
                            <div>
                                <span style="font-weight: bold;">{{ number_format($stok->stok, 3) }}</span> {{ $produk->satuan }}
                                @if(isset($stok->hpp))
                                    @ <span style="font-weight: bold; color: #0d6efd;">Rp{{ number_format($stok->hpp,0,',','.') }}</span>/{{ $produk->satuan }}
                                @endif
                            </div>
                        @endforeach
                    @else
                        <span style="color: #dc3545;">Kosong</span>
                    @endif
                </td>
                <td>
                    @if($toko2List->count())
                        @foreach($toko2List as $stok)
                            <div>
                                <span style="font-weight: bold;">{{ number_format($stok->stok, 3) }}</span> {{ $produk->satuan }}
                                @if(isset($stok->hpp))
                                    @ <span style="font-weight: bold; color: #0d6efd;">Rp{{ number_format($stok->hpp,0,',','.') }}</span>/{{ $produk->satuan }}
                                @endif
                            </div>
                        @endforeach
                    @else
                        <span style="color: #dc3545;">Kosong</span>
                    @endif
                </td>
                <td><strong>{{ number_format($totalStok, 3) }}</strong> {{ $produk->satuan }}</td>
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