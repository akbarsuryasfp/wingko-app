<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pembayaran Consignor - {{ $header->no_bayarconsignor }}</title>
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
            <h2 style="margin-bottom:12px;">WINGKO BABAT PRATAMA</h2>
            <div class="sub fw-bold" style="margin-bottom:8px;">Bukti Pembayaran Consignor (Pemilik Barang)</div>
            <div class="sub" style="margin-bottom:8px;">Tanggal Pembayaran: {{ $header->tanggal_bayar }}</div>
            <div class="sub">Kepada Yth: {{ $header->consignor->nama_consignor ?? '-' }}</div>
        </div>
        <div class="nota-print no-print">
            <button onclick="window.print()" style="padding:4px 12px;">Print</button>
        </div>
    </div>
    <div class="nota-info" style="margin-top:8px;">No Bukti Pembayaran: {{ $header->no_bayarconsignor }}</div>
    <div class="nota-info" style="margin-top:2px;">Alamat: {{ $header->consignor->alamat ?? '-' }}</div>
    <div class="nota-info" style="margin-top:2px;">Rekening: {{ $header->consignor->rekening ?? '-' }}</div>
    <div class="nota-info" style="margin-top:12px;">Metode Pembayaran: {{ $header->metode_pembayaran ?? '-' }}</div>
    <div class="nota-info" style="margin-top:2px;">Keterangan: {{ $header->keterangan ?? '-' }}</div>

    <table class="nota-table" style="margin-top: 18px;">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Stok</th>
                <th>Jumlah Terjual</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($header->details as $i => $d)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $d->produk->nama_produk ?? '-' }}</td>
                <td>{{ $d->jumlah_stok ?? ($d->produk->jumlah_stok ?? '-') }}</td>
                <td>{{ $d->jumlah_terjual }}</td>
                <td>Rp{{ number_format($d->harga_satuan,0,',','.') }}</td>
                <td>Rp{{ number_format($d->subtotal,0,',','.') }}</td>
            </tr>
            @php $total += $d->subtotal; @endphp
            @endforeach
        </tbody>
    </table>

    <table class="nota-summary" style="margin-left:0; width: 350px;">
        <tr>
            <td class="fw-bold" style="width:170px;">Total Bayar</td>
            <td>: Rp{{ number_format($total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="section-title">Catatan</div>
    <div style="font-size:13px;">
        Bukti pembayaran ini diberikan kepada consignor sebagai tanda pembayaran atas produk yang telah terjual secara konsinyasi.
    </div>

    <div class="footer">
        Hormat kami,<br>
        <b>Wingko Babat Pratama</b><br>
        (Yoko Setyo)
    </div>
</div>
</body>
</html>
