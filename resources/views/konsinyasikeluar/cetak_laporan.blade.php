<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Konsinyasi Keluar</title>
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
    <h3 style="text-align:center;">LAPORAN KONSINYASI KELUAR</h3>
    <div class="table-title" style="text-align:center;">Periode: {{ request('tanggal_awal') ? \Carbon\Carbon::parse(request('tanggal_awal'))->format('d F Y') : '-' }} s/d {{ request('tanggal_akhir') ? \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d F Y') : '-' }}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Konsinyasi Keluar</th>
                <th>No Surat Konsinyasi Keluar</th>
                <th>Tanggal Setor</th>
                <th>Nama Consignee (Mitra)</th>
                <th>Jumlah Setor & Nama Produk</th>
                <th>Satuan</th>
                <th>Harga Setor/Satuan</th>
                <th>Total Setor</th>
            </tr>
        </thead>
        <tbody>
            @php $grand_total = 0; @endphp
            @foreach($konsinyasiKeluarList as $i => $konsinyasi)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $konsinyasi->no_konsinyasikeluar ?? '-' }}</td>
                <td>{{ $konsinyasi->no_suratpengiriman ?? '-' }}</td>
                <td>{{ $konsinyasi->tanggal_setor ? \Carbon\Carbon::parse($konsinyasi->tanggal_setor)->format('d F Y') : '-' }}</td>
                <td>{{ $konsinyasi->consignee->nama_consignee ?? '-' }}</td>
                <td>
                    @php $produkList = $konsinyasi->details ?? []; @endphp
                    @if(count($produkList))
                        <div class="d-flex flex-column align-items-center justify-content-center w-100">
                        @foreach($produkList as $detail)
                            <div class="text-center">
                                <b>{{ $detail->jumlah_setor }}</b> x {{ $detail->produk->nama_produk ?? $detail->nama_produk ?? '-' }}
                            </div>
                        @endforeach
                        </div>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if(count($produkList))
                        <div class="d-flex flex-column align-items-center justify-content-center w-100">
                        @foreach($produkList as $detail)
                            <div class="text-center">
                                {{ $detail->satuan ?? ($detail->produk->satuan ?? '-') }}
                            </div>
                        @endforeach
                        </div>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if(count($produkList))
                        <div class="d-flex flex-column align-items-center justify-content-center w-100">
                        @foreach($produkList as $detail)
                            <div class="text-center">
                                Rp{{ number_format($detail->harga_setor ?? 0, 0, ',', '.') }}
                            </div>
                        @endforeach
                        </div>
                    @else
                        -
                    @endif
                </td>
                <td>Rp{{ number_format($konsinyasi->total_setor, 0, ',', '.') }}</td>
            </tr>
            @php $grand_total += $konsinyasi->total_setor; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" style="text-align:right;">GRAND TOTAL</th>
                <th>Rp{{ number_format($grand_total, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
    <div class="footer" style="margin-top:30px;text-align:right;font-size:12px;">
        Dicetak pada: {{ date('d-m-Y H:i') }}
    </div>
</body>
</html>
