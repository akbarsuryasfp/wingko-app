<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Retur Penjualan - {{ $returjual->no_returjual }}</title>
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
            <div class="sub fw-bold" style="margin-bottom:8px;">Nota Retur Penjualan</div>
            <div class="sub">Tanggal Retur: {{ $returjual->tanggal_returjual }}</div>
            <div class="sub">Nama Pelanggan: {{ $returjual->pelanggan->nama_pelanggan ?? $returjual->nama_pelanggan ?? '-' }}</div>
        </div>
        <div class="nota-print no-print">
            <button onclick="window.print()" style="padding:4px 12px;">Print</button>
        </div>
    </div>
    <div class="nota-info">Nomor Retur Penjualan: {{ $returjual->no_returjual }}</div>
    <div class="nota-info">Nomor Penjualan: {{ $returjual->no_jual }}</div>

    <table class="nota-table" style="margin-top: 18px;">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah Retur</th>
                <th>Harga Satuan</th>
                <th>Alasan</th>
                <th>Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $detail)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $detail->nama_produk }}</td>
                <td>{{ $detail->jumlah_retur }}</td>
                <td>Rp{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td>{{ $detail->alasan }}</td>
                <td>Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="nota-summary" style="margin-left:0; width: 100%; max-width: 500px;">
        <tr>
            <td class="fw-bold" style="width:170px;">Total Nilai Retur</td>
            <td style="width:10px;">:</td>
            <td>Rp{{ number_format($returjual->total_nilai_retur, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Jenis Retur</td>
            <td>:</td>
            <td>{{ $returjual->jenis_retur ?? '-' }}</td>
        </tr>
        <tr>
            <td class="fw-bold" style="vertical-align: top;">Keterangan</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">{{ $returjual->keterangan ?? '-' }}</td>
        </tr>
    </table>
</div>
</body>
</html>