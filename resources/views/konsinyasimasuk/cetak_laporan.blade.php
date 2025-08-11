<?php header('Content-Type: text/html; charset=utf-8'); ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Konsinyasi Masuk</title>
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
    <h3 style="text-align:center;">LAPORAN KONSINYASI MASUK</h3>
    <div class="table-title" style="text-align:center;">Periode: {{ request('tanggal_awal') ? \Carbon\Carbon::parse(request('tanggal_awal'))->format('d F Y') : '-' }} s/d {{ request('tanggal_akhir') ? \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d F Y') : '-' }}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Konsinyasi Masuk</th>
                <th>No Surat Titip Jual</th>
                <th>Tanggal Masuk</th>
                <th>Nama Consignor</th>
                <th>Jumlah Stok & Nama Produk</th>
                <th>Satuan</th>
                <th>Harga Titip/Satuan</th>
                <th>Total Titip</th>
                <th>Harga Jual/Satuan</th>
                <th>Komisi/Satuan</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grand_total_titip = 0;
                $grand_total_jual = 0;
                $grand_total_komisi = 0;
            @endphp
            @foreach($konsinyasiMasukList as $idx => $konsinyasi)
                @php
                    $produkList = $konsinyasi->details;
                    $rowspan = $produkList && count($produkList) ? count($produkList) : 1;
                    $grand_total_titip += $konsinyasi->total_titip ?? 0;
                    $total_jual = 0;
                    $total_komisi = 0;
                @endphp
                @if($produkList && count($produkList))
                    @foreach($produkList as $pidx => $produk)
                        <tr>
                            @if($pidx == 0)
                                <td rowspan="{{ $rowspan }}">{{ $idx + 1 }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $konsinyasi->no_konsinyasimasuk ?? '-' }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $konsinyasi->no_surattitipjual ?? '-' }}</td>
                                <td rowspan="{{ $rowspan }}">{{ \Carbon\Carbon::parse($konsinyasi->tanggal_titip ?? $konsinyasi->tanggal_masuk)->format('d F Y') }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $konsinyasi->consignor->nama_consignor ?? '-' }}</td>
                            @endif
                            <td><b>{{ $produk->jumlah_stok }}</b> x {{ $produk->produk->nama_produk ?? $produk->nama_produk ?? '-' }}</td>
                            <td>{{ $produk->satuan ?? ($produk->produk->satuan ?? '-') }}</td>
                            <td>Rp{{ number_format($produk->harga_titip, 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($produk->subtotal ?? ($produk->harga_titip * $produk->jumlah_stok), 0, ',', '.') }}</td>
                            <td>Rp{{ number_format($produk->harga_jual, 0, ',', '.') }}</td>
                            <td>Rp{{ number_format(($produk->harga_jual ?? 0) - ($produk->harga_titip ?? 0), 0, ',', '.') }}</td>
                            @if($pidx == 0)
                                <td rowspan="{{ $rowspan }}">{{ $konsinyasi->keterangan ?? '-' }}</td>
                            @endif
                        </tr>
                        @php
                            $total_jual += ($produk->harga_jual ?? 0) * ($produk->jumlah_stok ?? 1);
                            $total_komisi += (($produk->harga_jual ?? 0) - ($produk->harga_titip ?? 0)) * ($produk->jumlah_stok ?? 1);
                        @endphp
                    @endforeach
                    @php
                        $grand_total_jual += $total_jual;
                        $grand_total_komisi += $total_komisi;
                    @endphp
                @else
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $konsinyasi->no_konsinyasimasuk ?? '-' }}</td>
                        <td>{{ $konsinyasi->no_surattitipjual ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($konsinyasi->tanggal_titip ?? $konsinyasi->tanggal_masuk)->format('d F Y') }}</td>
                        <td>{{ $konsinyasi->consignor->nama_consignor ?? '-' }}</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>Rp{{ number_format($konsinyasi->total_titip ?? 0, 0, ',', '.') }}</td>
                        <td>-</td>
                        <td>-</td>
                        <td>{{ $konsinyasi->keterangan ?? '-' }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" style="text-align:right;">GRAND TOTAL</th>
                <th>Rp{{ number_format($grand_total_titip, 0, ',', '.') }}</th>
                <th>Rp{{ number_format($grand_total_jual, 0, ',', '.') }}</th>
                <th>Rp{{ number_format($grand_total_komisi, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
    <div class="footer" style="margin-top:30px;text-align:right;font-size:12px;">
        Dicetak pada: {{ date('d-m-Y H:i') }}
    </div>
</body>
</html>
