<!DOCTYPE html>
<html>
<head>
    <title>Laporan HPP Bulan {{ $bulan }}/{{ $tahun }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 8px; }
        th { background: #eee; }
        .fw-bold { font-weight: bold; }
        .table-secondary { background: #f2f2f2; }
        .table-info { background: #d9edf7; }
        .table-primary { background: #cfe2ff; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <h3 style="text-align:center;">Laporan Harga Pokok Produksi (HPP)<br>Bulan {{ $bulan }}/{{ $tahun }}</h3>
    <table>
        <tr class="table-secondary">
            <th colspan="2">Bahan Baku Langsung</th>
        </tr>
        <tr>
            <td>+ Pembelian Bahan Baku</td>
            <td class="text-end">Rp {{ number_format($pembelian_bahan ?? 0,0,',','.') }}</td>
        </tr>
        <tr>
            <td>+ Persediaan Awal Bahan Baku</td>
            <td class="text-end">Rp {{ number_format($persediaan_awal ?? 0,0,',','.') }}</td>
        </tr>
        <tr>
            <td>- Persediaan Akhir Bahan Baku</td>
            <td class="text-end">Rp {{ number_format($persediaan_akhir ?? 0,0,',','.') }}</td>
        </tr>
        <tr class="fw-bold">
            <td>= Bahan Baku yang Digunakan</td>
            <td class="text-end">
                Rp {{ number_format($bahan_digunakan ?? 0,0,',','.') }}
            </td>
        </tr>
        <tr class="table-secondary">
            <th colspan="2">Tenaga Kerja Langsung</th>
        </tr>
        <tr>
            <td>Biaya Tenaga Kerja Langsung</td>
            <td class="text-end">Rp {{ number_format($total_tk ?? 0,0,',','.') }}</td>
        </tr>
        <tr class="table-secondary">
            <th colspan="2">Biaya Overhead Pabrik</th>
        </tr>
        <tr>
            <td>Biaya Overhead Pabrik</td>
            <td class="text-end">Rp {{ number_format($total_overhead ?? 0,0,',','.') }}</td>
        </tr>
        <tr class="fw-bold table-info">
            <td>= Total Biaya Produksi</td>
            <td class="text-end">
                Rp {{ number_format($total_biaya_produksi ?? 0,0,',','.') }}
            </td>
        </tr>
        <tr class="fw-bold table-primary">
            <td>= Harga Pokok Produksi</td>
            <td class="text-end">
                <b>Rp {{ number_format($total_biaya_produksi ?? 0,0,',','.') }}</b>
            </td>
        </tr>
    </table>
</body>
</html>