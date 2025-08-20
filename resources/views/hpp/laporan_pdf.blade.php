<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan HPP</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #222; padding: 4px 8px; }
        .table-dark { background: #222; color: #fff; }
        .table-secondary { background: #eee; }
        .table-info { background: #d1ecf1; }
        .table-primary { background: #cfe2ff; }
    </style>
</head>
<body>
    <div class="text-center mb-4">
        <h4 class="mb-0 fw-bold">Wingko Babat Pratama</h4>
        <div class="mb-1">Laporan Harga Pokok Produksi</div>
        <div class="mb-2">
            Periode: {{ DateTime::createFromFormat('!m', $bulan)->format('F') }} {{ $tahun }}
        </div>
        <hr class="my-2">
    </div>
   
    <table>
        <thead class="table-dark">
            <tr>
                <th>Komponen</th>
                <th style="text-align:right">Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="table-secondary">
                <th colspan="2">Bahan Baku Langsung</th>
            </tr>
            <tr>
                <td>+ Pembelian Bahan Baku</td>
                <td style="text-align:right">Rp {{ number_format($pembelian_bahan ?? 0,0,',','.') }}</td>
            </tr>
            <tr>
                <td>+ Persediaan Awal Bahan Baku</td>
                <td style="text-align:right">Rp {{ number_format($persediaan_awal ?? 0,0,',','.') }}</td>
            </tr>
            <tr>
                <td>- Persediaan Akhir Bahan Baku</td>
                <td style="text-align:right">Rp {{ number_format($persediaan_akhir ?? 0,0,',','.') }}</td>
            </tr>
            <tr class="fw-bold">
                <td>= Bahan Baku yang Digunakan</td>
                <td style="text-align:right">
                    Rp {{ number_format(($pembelian_bahan ?? 0) + ($persediaan_awal ?? 0) - ($persediaan_akhir ?? 0),0,',','.') }}
                </td>
            </tr>
            <tr class="table-secondary">
                <th colspan="2">Tenaga Kerja Langsung</th>
            </tr>
            <tr>
                <td>Biaya Tenaga Kerja Langsung</td>
                <td style="text-align:right">Rp {{ number_format($total_tk ?? 0,0,',','.') }}</td>
            </tr>
            <tr class="table-secondary">
                <th colspan="2">Biaya Overhead Pabrik</th>
            </tr>
            <tr>
                <td>Biaya Overhead Pabrik</td>
                <td style="text-align:right">Rp {{ number_format($total_overhead ?? 0,0,',','.') }}</td>
            </tr>
            <tr class="fw-bold table-info">
                <td>= Total Biaya Produksi</td>
                <td style="text-align:right">
                    Rp {{ number_format(
                        (($pembelian_bahan ?? 0) + ($persediaan_awal ?? 0) - ($persediaan_akhir ?? 0)) + ($total_tk ?? 0) + ($total_overhead ?? 0)
                    ,0,',','.') }}
                </td>
            </tr>
            <tr class="fw-bold table-primary">
                <td>= Harga Pokok Produksi</td>
                <td style="text-align:right">
                    <b>
                    Rp {{ number_format(
                        (($pembelian_bahan ?? 0) + ($persediaan_awal ?? 0) - ($persediaan_akhir ?? 0)) + ($total_tk ?? 0) + ($total_overhead ?? 0)
                    ,0,',','.') }}
                    </b>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>