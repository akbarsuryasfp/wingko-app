<!-- filepath: resources/views/penjualan/cetak_tagihan.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Tagihan - {{ $penjualan->no_jual }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        .nota-container { width: 100%; max-width: 420px; min-width: 320px; margin: 0 auto; border: 1px solid #333; padding: 10px 12px; text-align: left; }
        .nota-header-table { width: 100%; margin-bottom: 4px; }
        .nota-header-table td { vertical-align: top; }
        .nota-title { text-align: left; }
        .nota-title h2 { margin: 0 0 2px 0; font-size: 13px; }
        .nota-title .sub { font-size: 10px; }
        .nota-print { text-align: right; }
        .nota-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10px; }
        .nota-table th, .nota-table td { border: 1px solid #333; padding: 2px 3px; text-align: center; }
        .nota-table th { background: #f2f2f2; }
        .nota-summary { text-align: left; font-size: 10px; margin-top: 6px; width: 100%; }
        .nota-summary td { padding: 2px 4px; }
        .fw-bold { font-weight: bold; }
        .section-title { font-size: 11px; font-weight: bold; margin-top: 12px; margin-bottom: 4px; text-align: left; }
        .footer { margin-top: 14px; font-size: 10px; text-align: left; }
        @media print {
            .no-print { display: none; }
            .nota-container { border: none; }
        }
    </style>
</head>
<body>
<div class="nota-container">
    <table class="nota-header-table">
        <tr>
            <td style="width:100%;">
                <div class="nota-title">
                    <h2 style="margin-bottom:4px;">WINGKO BABAT PRATAMA</h2>
                    <div class="sub fw-bold" style="margin-bottom:3px;">Nota Tagihan</div>
                    <div class="sub">Tanggal Tagihan: {{ $penjualan->tanggal_jual }}</div>
                    <div class="sub">Nama Pelanggan: {{ $penjualan->nama_pelanggan ?? ($penjualan->pelanggan->nama_pelanggan ?? '-') }}</div>
                    <div class="sub">Nomor Tagihan: {{ $penjualan->no_jual }}</div>
                </div>
            </td>
        </tr>
    </table>


    <table class="nota-table" style="margin-top: 18px;">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Jumlah</th>
                <th>Harga/Satuan</th>
                <th>Diskon/Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPenjualan = 0; @endphp
                @php
                    $totalPenjualan = 0;
                @endphp
                @php
                    if (!function_exists('getval')) {
                        function getval($item, $key, $default = '-') {
                            if (is_array($item) && isset($item[$key])) return $item[$key];
                            if (is_object($item) && isset($item->$key)) return $item->$key;
                            return $default;
                        }
                    }
                @endphp
                @foreach($details as $i => $detail)
                @php
                    $hargaSatuan = (float) getval($detail, 'harga_satuan', 0);
                    $diskonSatuan = (float) (getval($detail, 'diskon_produk', getval($detail, 'diskon_satuan', 0)));
                    $jumlah = (float) getval($detail, 'jumlah', 0);
                    $subtotal = ($hargaSatuan - $diskonSatuan) * $jumlah;
                    $totalPenjualan += $subtotal;
            @endphp
            <tr>
                <td>{{ $i+1 }}</td>
                <td>
                    {{ getval($detail, 'nama_produk', '-') }}
                </td>
                <td>
                    {{ getval($detail, 'satuan', '-') }}
                </td>
                <td>{{ $jumlah }}</td>
                <td>Rp{{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                <td>(Rp{{ number_format($diskonSatuan, 0, ',', '.') }})</td>
                <td>Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @php
        // Ambil data piutang dari relasi jika ada
        $piutangRow = null;
        if (isset($penjualan->no_jual)) {
            $piutangRow = DB::table('t_piutang')->where('no_jual', $penjualan->no_jual)->first();
        }
        $total_bayar = $piutangRow && isset($piutangRow->total_bayar) ? (float)$piutangRow->total_bayar : (float)$penjualan->total_bayar;
        $sisa_piutang = $piutangRow && isset($piutangRow->sisa_piutang) ? (float)$piutangRow->sisa_piutang : null;
        $piutang = $sisa_piutang !== null ? $sisa_piutang : ($totalPenjualan - (float)$penjualan->diskon - $total_bayar);
        if ($piutang < 0) $piutang = 0;
    @endphp
    <table class="nota-summary" style="margin-left:0; width: 350px;">
        <tr>
            <td class="fw-bold" style="width:170px;">Total Penjualan</td>
            <td>: Rp{{ number_format($totalPenjualan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Diskon</td>
            <td>:
                @if(isset($penjualan->tipe_diskon) && $penjualan->tipe_diskon == 'persen')
                    ({{ $penjualan->diskon }}%)
                @else
                    (Rp{{ number_format($penjualan->diskon, 0, ',', '.') }})
                @endif
            </td>
        </tr>
        <tr>
            <td class="fw-bold">Pembayaran Sebelumnya</td>
            <td>: Rp{{ number_format($total_bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Metode Pembayaran</td>
            <td>: {{ ucfirst($penjualan->metode_pembayaran) }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Sisa Tagihan (Piutang)</td>
            <td>: <b>Rp{{ number_format($piutang, 0, ',', '.') }}</b></td>
        </tr>
        <tr>
            <td class="fw-bold">Tanggal Jatuh Tempo</td>
            <td>:
                @php
                    // Ambil tanggal jatuh tempo dari penjualan atau piutang
                    $tanggalJatuhTempo = null;
                    if (isset($penjualan->tanggal_jatuh_tempo) && $penjualan->tanggal_jatuh_tempo) {
                        $tanggalJatuhTempo = $penjualan->tanggal_jatuh_tempo;
                    } elseif (isset($piutangRow->tanggal_jatuh_tempo) && $piutangRow->tanggal_jatuh_tempo) {
                        $tanggalJatuhTempo = $piutangRow->tanggal_jatuh_tempo;
                    }
                @endphp
                @if($tanggalJatuhTempo)
                    {{ \Carbon\Carbon::parse($tanggalJatuhTempo)->format('d-m-Y') }}
                @else
                    -
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title" style="text-align:left;">Instruksi Pembayaran</div>
    <div style="font-size:13px; text-align:left;">
        Silakan lakukan pelunasan sisa tagihan sebesar <b>Rp{{ number_format($piutang, 0, ',', '.') }}</b> melalui:
        <ul style="margin-top:8px;">
            <li><b>Tunai</b><br>
                Datang langsung ke toko Wingko Babat Pratama.</li>
            <li style="margin-top:8px;"><b>Non Tunai</b><br>
                Transfer ke:<br>
                <div style="margin-left:12px;">
                    <b>Bank Jateng</b><br>
                    No. Rekening: 123456789<br>
                    a.n. Wingko Babat Pratama
                </div>
            </li>
        </ul>
    </div>

    <div class="footer" style="text-align:left;">
        Mohon lakukan pelunasan sebelum jatuh tempo untuk menjaga kelancaran transaksi Anda.<br>
        Terima kasih atas kepercayaannya.<br>
        Hormat kami,<br>
        <b>Wingko Babat Pratama</b><br>
        (Yoko Setyo)
    </div>
</div>
</body>
</html>