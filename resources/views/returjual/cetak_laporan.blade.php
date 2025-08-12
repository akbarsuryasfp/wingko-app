
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Retur Penjualan</title>
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
    <h3 style="text-align:center;">LAPORAN RETUR PENJUALAN</h3>
    <div class="table-title" style="text-align:center;">Periode: {{ request('tanggal_awal') ? \Carbon\Carbon::parse(request('tanggal_awal'))->format('d F Y') : '-' }} s/d {{ request('tanggal_akhir') ? \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d F Y') : '-' }}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Retur Jual</th>
                <th>No Jual</th>
                <th>Tanggal Retur</th>
                <th>Nama Pelanggan</th>
                <th>Jenis Retur</th>
                <th>Jumlah Retur & Nama Produk</th>
                <th>Satuan</th>
                <th>Harga/Satuan</th>
                <th>Alasan Retur</th>
                <th>Subtotal</th>
                <th>Total Retur</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grand_total = 0;
            @endphp
            @foreach($returjual as $idx => $rj)
                @php
                    $details = \DB::table('t_returjual_detail')
                        ->leftJoin('t_produk', 't_returjual_detail.kode_produk', '=', 't_produk.kode_produk')
                        ->leftJoin('t_produk_konsinyasi', 't_returjual_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
                        ->where('t_returjual_detail.no_returjual', $rj->no_returjual)
                        ->select(
                            't_returjual_detail.*',
                            \DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                            \DB::raw('COALESCE(t_produk.satuan, t_produk_konsinyasi.satuan) as satuan')
                        )
                        ->get();
                    $rowspan = $details->count() ?: 1;
                    $grand_total += $rj->total_nilai_retur;
                @endphp
                @if($details->count())
                    @foreach($details as $didx => $detail)
                        <tr>
                            @if($didx == 0)
                                <td rowspan="{{ $rowspan }}">{{ $idx + 1 }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $rj->no_returjual }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $rj->no_jual }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $rj->tanggal_returjual ? \Carbon\Carbon::parse($rj->tanggal_returjual)->format('d F Y') : '-' }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $rj->nama_pelanggan ?? '-' }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $rj->jenis_retur ?? '-' }}</td>
                            @endif
                            <td><b>{{ $detail->jumlah_retur }}</b> x {{ $detail->nama_produk ?? '-' }}</td>
                            <td>{{ $detail->satuan ?? '-' }}</td>
                            <td>Rp{{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}</td>
                            <td>{{ $detail->alasan ?? '-' }}</td>
                            <td>Rp{{ number_format($detail->subtotal ?? (($detail->harga_satuan ?? 0) * ($detail->jumlah_retur ?? 0)), 0, ',', '.') }}</td>
                            @if($didx == 0)
                                <td rowspan="{{ $rowspan }}">Rp{{ number_format($rj->total_nilai_retur, 0, ',', '.') }}</td>
                            @endif
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $rj->no_returjual }}</td>
                        <td>{{ $rj->no_jual }}</td>
                        <td>{{ $rj->tanggal_returjual ? \Carbon\Carbon::parse($rj->tanggal_returjual)->format('d F Y') : '-' }}</td>
                        <td>{{ $rj->nama_pelanggan ?? '-' }}</td>
                        <td>{{ $rj->jenis_retur ?? '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>Rp{{ number_format($rj->total_nilai_retur, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($rj->total_nilai_retur, 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="11" style="text-align:right;">GRAND TOTAL</th>
                <th>Rp{{ number_format($grand_total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
    <div class="footer" style="margin-top:30px;text-align:right;font-size:12px;">
        Dicetak pada: {{ date('d-m-Y H:i') }}
    </div>
</body>
</html>
