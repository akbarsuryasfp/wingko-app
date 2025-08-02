<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penerimaan Konsinyasi</title>
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
    <h3 style="text-align:center;">LAPORAN PENERIMAAN KONSINYASI</h3>
    <div class="table-title" style="text-align:center;">Periode: {{ request('tanggal_awal') ? \Carbon\Carbon::parse(request('tanggal_awal'))->format('d F Y') : '-' }} s/d {{ request('tanggal_akhir') ? \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d F Y') : '-' }}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Penerimaan Konsinyasi</th>
                <th>No Konsinyasi Keluar</th>
                <th>Tanggal Terima</th>
                <th>Nama Consignee (Mitra)</th>
                <th>Jumlah Terjual & Nama Produk</th>
                <th>Satuan</th>
                <th>Harga/Satuan</th>
                <th>Total Terima</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penerimaanKonsinyasiList as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->no_penerimaankonsinyasi }}</td>
                <td>{{ $item->no_konsinyasikeluar ?? '-' }}</td>
                <td>{{ $item->tanggal_terima ? \Carbon\Carbon::parse($item->tanggal_terima)->format('d F Y') : '-' }}</td>
                <td>{{ $item->consignee->nama_consignee ?? '-' }}</td>
                <td>
                    @if($item->details && count($item->details))
                        <div class="d-flex flex-column align-items-center justify-content-center w-100">
                        @foreach($item->details as $detail)
                            <div class="text-center">
                                <b>{{ $detail->jumlah_terjual ?? 0 }}</b> x {{ $detail->produk->nama_produk ?? '-' }}
                            </div>
                        @endforeach
                        </div>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($item->details && count($item->details))
                        <div class="d-flex flex-column align-items-center justify-content-center w-100">
                        @foreach($item->details as $detail)
                            <div class="text-center">
                                {{ $detail->satuan ?? '-' }}
                            </div>
                        @endforeach
                        </div>
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($item->details && count($item->details))
                        <div class="d-flex flex-column align-items-center justify-content-center w-100">
                        @foreach($item->details as $detail)
                            <div class="text-center">
                                Rp{{ number_format($detail->harga_satuan ?? 0, 0, ',', '.') }}
                            </div>
                        @endforeach
                        </div>
                    @else
                        -
                    @endif
                </td>
                <td>Rp{{ number_format($item->total_terima, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
