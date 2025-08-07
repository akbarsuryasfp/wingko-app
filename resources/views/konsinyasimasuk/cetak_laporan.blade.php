<!DOCTYPE html>
<html>
<head>
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
                <th>Nama Consignor (Pemilik Barang)</th>
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
            @foreach($konsinyasiMasukList as $i => $konsinyasi)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $konsinyasi->no_konsinyasimasuk ?? '-' }}</td>
                <td>{{ $konsinyasi->no_surattitipjual ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($konsinyasi->tanggal_titip ?? $konsinyasi->tanggal_masuk)->format('d F Y') }}</td>
                <td>{{ $konsinyasi->consignor->nama_consignor ?? '-' }}</td>
                <td>
                    @php $produkList = $konsinyasi->details; @endphp
                    @if($produkList && count($produkList))
                        @foreach($produkList as $produk)
                            <div>
                                <b>{{ $produk->jumlah_stok }}</b> x {{ $produk->produk->nama_produk ?? $produk->nama_produk ?? '-' }}
                            </div>
                        @endforeach
                    @else - @endif
                </td>
                <td>
                    @if($produkList && count($produkList))
                        @foreach($produkList as $produk)
                            <div>
                                {{ $produk->satuan ?? ($produk->produk->satuan ?? '-') }}
                            </div>
                        @endforeach
                    @else - @endif
                </td>
                <td>
                    @if($produkList && count($produkList))
                        @foreach($produkList as $produk)
                            <div>
                                Rp{{ number_format($produk->harga_titip, 0, ',', '.') }}
                            </div>
                        @endforeach
                    @else - @endif
                </td>
                <td>
                    @if($produkList && count($produkList))
                        @foreach($produkList as $produk)
                            <div>
                                Rp{{ number_format($produk->subtotal ?? ($produk->harga_titip * $produk->jumlah_stok), 0, ',', '.') }}
                            </div>
                        @endforeach
                    @else - @endif
                </td>
                <td>
                    @if($produkList && count($produkList))
                        @foreach($produkList as $produk)
                            <div>
                                Rp{{ number_format($produk->harga_jual, 0, ',', '.') }}
                            </div>
                        @endforeach
                    @else - @endif
                </td>
                <td>
                    @if($produkList && count($produkList))
                        @foreach($produkList as $produk)
                            <div>
                                Rp{{ number_format(($produk->harga_jual ?? 0) - ($produk->harga_titip ?? 0), 0, ',', '.') }}
                            </div>
                        @endforeach
                    @else - @endif
                </td>
                <td>{{ $konsinyasi->keterangan ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
