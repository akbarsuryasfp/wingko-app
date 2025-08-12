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
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <h4 style="text-align:center; margin-bottom:5px;">LAPORAN PENERIMAAN BAHAN</h4>
    <p style="text-align:center; margin-top:0;">Periode: {{ $tanggal_mulai }} s/d {{ $tanggal_selesai }}</p>
    
    <table border="1" cellspacing="0" cellpadding="5" width="100%" style="border-collapse: collapse;">
        <thead>
            <tr>
                <th style="text-align:center">No</th>
                <th style="text-align:center">Kode Terima</th>
                <th style="text-align:center">Tanggal Terima</th>
                <th>Nama Supplier</th>
                <th>Nama Bahan</th>
                <th style="text-align:center">Satuan</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:center">Harga Beli</th>
                <th style="text-align:center">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $no = 1;
                $grandTotal = 0;
                $sortedTerimaBahan = $terimabahan->sortBy([
                    ['tanggal_terima', 'asc'],
                    ['no_terima_bahan', 'asc']
                ]);
            @endphp
            
            @foreach($sortedTerimaBahan as $item)
                @php
                    $key = trim((string)$item->no_terima_bahan);
                    $detailList = isset($details[$key]) ? $details[$key]->sortBy('nama_bahan') : collect();
                    $rowCount = count($detailList);
                    $subTotal = 0;
                    $namaBahanList = [];
                    $satuanList = [];
                    $qtyList = [];
                    $hargaList = [];
                    foreach($detailList as $d) {
                        $subTotal += $d->bahan_masuk * ($d->harga_beli ?? 0);
                        $namaBahanList[] = $d->nama_bahan;
                        $satuanList[] = $d->satuan ?? '-';
                        $qtyList[] = number_format($d->bahan_masuk, 0);
                        $hargaList[] = 'Rp ' . number_format($d->harga_beli ?? 0, 0, ',', '.');
                    }
                    $grandTotal += $subTotal;
                @endphp

                <tr>
                    <td style="text-align:center; vertical-align:middle">{{ $no++ }}</td>
                    <td style="text-align:center; vertical-align:middle">{{ $item->no_terima_bahan }}</td>
                    <td style="text-align:center; vertical-align:middle">{{ $item->tanggal_terima }}</td>
                    <td style="vertical-align:middle">{{ $item->nama_supplier ?? '-' }}</td>
                    <td style="vertical-align:middle">
                        {!! implode('<br>', $namaBahanList) !!}
                    </td>
                    <td style="text-align:center; vertical-align:middle">
                        {!! implode('<br>', $satuanList) !!}
                    </td>
                    <td style="text-align:right; vertical-align:middle">
                        {!! implode('<br>', $qtyList) !!}
                    </td>
                    <td style="padding-right:10px; text-align:right; vertical-align:middle; white-space:nowrap;">
                        {!! implode('<br>', $hargaList) !!}
                    </td>
                    <td style="vertical-align:middle; width:150px; padding:0 10px; text-align:right;">
                        <div style="display:flex; justify-content:flex-end; gap:2px;">
                            <span style="text-align:left;">Rp</span>
                            <span style="text-align:right; letter-spacing:0.5px;">
                                {{ number_format($subTotal, 0, ',', '.') }}
                            </span>
                        </div>
                    </td>
                </tr>
            @endforeach
            
            <tr style="font-weight:bold; background-color:#e6e6e6; border-top:2px solid #000">
                <td colspan="8" style="text-align:right">GRAND TOTAL:</td>
                <td style="padding-right:10px; text-align:right">
                    <span style="float:left">Rp</span>
                    <span style="float:right">{{ number_format($grandTotal, 0) }}</span>
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>