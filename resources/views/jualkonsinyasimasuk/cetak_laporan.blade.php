<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan Konsinyasi Masuk</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; background: #fafafa; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 8px 10px; text-align: center; }
        th { background: #e9ecef; font-weight: bold; }
        h3 { margin-bottom: 0; }
        .table-title { margin-top: 8px; font-size: 15px; font-weight: 600; }
        tr:nth-child(even) { background: #f6f6f6; }
        /* btn-cetak removed as requested */
    </style>
</head>
<body>
    <h3 style="text-align:center;">LAPORAN PENJUALAN KONSINYASI MASUK</h3>
    <div class="table-title" style="text-align:center;">Periode: {{ request('tanggal_awal') ? \Carbon\Carbon::parse(request('tanggal_awal'))->format('d F Y') : '-' }} s/d {{ request('tanggal_akhir') ? \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d F Y') : '-' }}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Jual</th>
                <th>Tanggal Jual</th>
                <th>Nama Pelanggan</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Jumlah</th>
                <th>Harga/Satuan</th>
                <th>Subtotal Jual</th>
                <th>Komisi/Satuan</th>
                <th>Subtotal Komisi</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $rowNo = 1; 
                $grand_total_jual = 0;
                $grand_total_komisi = 0;
            @endphp
            @forelse($penjualanKonsinyasi as $penjualan)
                @foreach($penjualan->details as $detail)
                    @php
                        $namaProdukKonsinyasi = null;
                        if(Str::startsWith($detail->kode_produk, 'PKM')) {
                            $produkKonsinyasi = \App\Models\ProdukKonsinyasi::where('kode_produk', $detail->kode_produk)->first();
                            $namaProdukKonsinyasi = $produkKonsinyasi ? $produkKonsinyasi->nama_produk : $detail->nama_produk;
                        }
                        // Ambil komisi/unit dari t_konsinyasimasuk_detail
                        $komisi = null;
                        $komisiRow = \DB::table('t_konsinyasimasuk_detail')
                            ->where('kode_produk', $detail->kode_produk)
                            ->whereNotNull('komisi')
                            ->orderByDesc('no_detailkonsinyasimasuk')
                            ->value('komisi');
                        $komisi = $komisiRow ?? 0;
                        $subtotal_komisi = ($komisi ?? 0) * ($detail->jumlah ?? 0);
                    @endphp
                    @if(Str::startsWith($detail->kode_produk, 'PKM'))
                    <tr>
                        <td>{{ $rowNo++ }}</td>
                        <td>{{ $penjualan->no_jual }}</td>
                        <td>{{ \Carbon\Carbon::parse($penjualan->tanggal_jual)->format('d F Y') }}</td>
                        <td>{{ $penjualan->pelanggan->nama_pelanggan ?? '-' }}</td>
                        <td>{{ $detail->kode_produk }}</td>
                        <td>{{ $namaProdukKonsinyasi }}</td>
                        <td>
                            @php
                                $satuan = null;
                                if(isset($produkKonsinyasi)) {
                                    $satuan = $produkKonsinyasi->satuan ?? null;
                                }
                            @endphp
                            {{ $satuan ?? '-' }}
                        </td>
                        <td>{{ number_format($detail->jumlah) }}</td>
                        <td>Rp{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($komisi, 0, ',', '.') }}</td>
                        <td>Rp{{ number_format($subtotal_komisi, 0, ',', '.') }}</td>
                    </tr>
                    @php
                        $grand_total_jual += $detail->subtotal ?? 0;
                        $grand_total_komisi += $subtotal_komisi;
                    @endphp
                    @endif
                @endforeach
            @empty
                <tr>
                    <td colspan="12" class="text-center py-3">Tidak ada data penjualan konsinyasi.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="9" style="text-align:right;">GRAND TOTAL</th>
                <th>Rp{{ number_format($grand_total_jual, 0, ',', '.') }}</th>
                <th></th>
                <th>Rp{{ number_format($grand_total_komisi, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
    <div class="footer" style="margin-top:30px;text-align:right;font-size:12px;">
        Dicetak pada: {{ date('d-m-Y H:i') }}
    </div>
</body>
</html>
