<!-- filepath: resources/views/penjualan/cetak_tagihan.blade.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Tagihan - {{ $penjualan->no_jual }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .nota-container { width: 700px; margin: 0 auto; border: 1px solid #333; padding: 24px 32px; }
        .nota-header { display: flex; align-items: center; justify-content: space-between; }
        .nota-title { text-align: left; flex: 1; }
        .nota-title h2 { margin: 0 0 4px 0; font-size: 20px; }
        .nota-title .sub { font-size: 14px; }
        .nota-print { text-align: right; }
        .nota-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .nota-table th, .nota-table td { border: 1px solid #333; padding: 6px 8px; text-align: center; }
        .nota-table th { background: #f2f2f2; }
        .nota-summary { margin-left: 0; }
        .nota-summary td { padding: 4px 8px; }
        .fw-bold { font-weight: bold; }
        .section-title { font-size: 15px; font-weight: bold; margin-top: 24px; margin-bottom: 8px; }
        .footer { margin-top: 32px; font-size: 13px; }
        @media print {
            .no-print { display: none; }
            .nota-container { border: none; }
        }
    </style>
</head>
<body>
<div class="nota-container">
    <div class="nota-header">
        <div class="nota-title">
            <h2>WINGKO BABAT PRATAMA</h2>
            <div class="sub">Nota Tagihan</div>
            <div class="sub">Tanggal Tagihan: {{ $penjualan->tanggal_jual }}</div>
            <div class="sub">Nama Pelanggan: {{ $penjualan->nama_pelanggan ?? ($penjualan->pelanggan->nama_pelanggan ?? '-') }}</div>
        </div>
        <div class="nota-print no-print">
            <button onclick="window.print()" style="padding:4px 12px;">Print</button>
        </div>
    </div>
    <div class="nota-info" style="margin-top:8px;">Nomor Tagihan: {{ $penjualan->no_jual }}</div>

    <table class="nota-table" style="margin-top: 18px;">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $detail)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $detail->nama_produk ?? ($detail->produk->nama_produk ?? '-') }}</td>
                <td>{{ $detail->satuan ?? ($detail->produk->satuan ?? '-') }}</td>
                <td>{{ $detail->jumlah }}</td>
                <td>Rp{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td>Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="nota-summary" style="margin-left:0; width: 350px;">
        <tr>
            <td class="fw-bold" style="width:170px;">Total Penjualan</td>
            <td>: Rp{{ number_format($penjualan->total_harga, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Diskon</td>
            <td>:
                @if(isset($penjualan->tipe_diskon) && $penjualan->tipe_diskon == 'persen')
                    {{ $penjualan->diskon }}%
                @else
                    Rp{{ number_format($penjualan->diskon, 0, ',', '.') }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="fw-bold">Pembayaran Sebelumnya</td>
            <td>: Rp{{ number_format($penjualan->total_bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Metode Pembayaran</td>
            <td>: {{ ucfirst($penjualan->metode_pembayaran) }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Sisa Tagihan (Piutang)</td>
            <td>: <b>Rp{{ number_format($penjualan->piutang, 0, ',', '.') }}</b></td>
        </tr>
    </table>

    <div class="section-title">Instruksi Pembayaran</div>
    <div style="font-size:13px;">
        Silakan lakukan pelunasan sisa tagihan sebesar <b>Rp{{ number_format($penjualan->piutang, 0, ',', '.') }}</b> melalui:
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

    <div class="footer">
        Mohon lakukan pelunasan sebelum jatuh tempo untuk menjaga kelancaran transaksi Anda.<br>
        Terima kasih atas kepercayaannya.<br>
        Hormat kami,<br>
        <b>Wingko Babat Pratama</b><br>
        (Yoko Setyo)
    </div>
</div>
</body>
</html>