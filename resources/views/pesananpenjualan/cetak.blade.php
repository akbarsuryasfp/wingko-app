
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Pesanan - {{ $pesanan->no_pesanan }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .nota-container { width: 700px; margin: 0 auto; border: 1px solid #333; padding: 24px 32px; }
        .nota-header { display: flex; align-items: center; justify-content: space-between; }
        .nota-logo { width: 80px; height: 80px; border: 1px solid #333; display: flex; align-items: center; justify-content: center; font-size: 12px; }
        .nota-title { text-align: left; flex: 1; margin-left: 24px; }
        .nota-title h2 { margin: 0 0 4px 0; font-size: 20px; }
        .nota-title .sub { font-size: 14px; }
        .nota-print { text-align: right; }
        .nota-info { margin: 18px 0 10px 0; }
        .nota-info td { padding: 2px 8px 2px 0; }
        .nota-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .nota-table th, .nota-table td { border: 1px solid #333; padding: 6px 8px; text-align: center; }
        .nota-table th { background: #f2f2f2; }
        .nota-summary { margin-left: 0; }
        .nota-summary td { padding: 4px 8px; }
        .fw-bold { font-weight: bold; }
        @media print {
            .no-print { display: none; }
            .nota-container { border: none; }
        }
    </style>
</head>
<body>
<div class="nota-container">

    <div class="nota-header">
        <div class="nota-title" style="margin-left:0;">
            <h2 style="margin-bottom:12px;">WINGKO BABAT PRATAMA</h2>
            <div class="sub fw-bold" style="margin-bottom:8px;">Nota Pesanan</div>
            <div class="sub">Tanggal Pesanan: {{ $pesanan->tanggal_pesanan ? date('d-m-Y', strtotime($pesanan->tanggal_pesanan)) : '-' }}</div>
            <div class="sub">Nama Pelanggan: {{ $pesanan->nama_pelanggan ?? '-' }}</div>
        </div>
        <div class="nota-print no-print">
            <button onclick="window.print()" style="padding:4px 12px;">Print</button>
        </div>
    </div>
    <div class="nota-info" style="margin-top:8px;">Nomor Pesanan: {{ $pesanan->no_pesanan }}</div>

    <table class="nota-table" style="margin-top: 18px;">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Jumlah</th>
                <th>Harga/Satuan</th>
                <th>Diskon/Satuan</th>
                <th>Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($details as $i => $d)
                @php $total += $d->subtotal; @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $d->nama_produk }}</td>
                    <td>{{ isset($d->satuan) && $d->satuan !== '' ? $d->satuan : '-' }}</td>
                    <td>{{ $d->jumlah }}</td>
                    <td>Rp{{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($d->diskon_produk ?? $d->diskon_satuan ?? 0, 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($d->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="nota-summary" style="margin-left:0; width: 350px;">
        <tr>
            <td class="fw-bold" style="width:170px;">Total Pesanan</td>
            <td>: Rp{{ number_format($total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Uang Muka (DP)</td>
            <td>: Rp{{ number_format($pesanan->uang_muka ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Sisa Tagihan</td>
            <td>: Rp{{ number_format($pesanan->sisa_tagihan ?? 0, 0, ',', '.') }}</td>
        </tr>
    </table>
    <div style="margin-top:18px;">
        <small class="text-muted">Nota ini digunakan untuk melakukan pembayaran dan pengambilan produk.<br>Harap membawa nota ini saat melakukan pembayaran dan pengambilan produk.</small>
    </div>
</div>
</body>
</html>
