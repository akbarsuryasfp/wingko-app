
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Retur Penjualan - {{ $returjual->no_returjual }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .nota-container { width: 540px; margin: 0 auto; border: 1px solid #333; padding: 16px 18px; text-align: left; }
        .nota-header { display: flex; align-items: flex-start; justify-content: space-between; }
        .nota-title { text-align: left; flex: 1; margin-left: 0; }
        .nota-title h2 { margin: 0 0 4px 0; font-size: 16px; }
        .nota-title .sub { font-size: 12px; }
        .nota-print { text-align: right; }
        .nota-info { margin: 18px 0 10px 0; }
        .nota-info td { padding: 2px 8px 2px 0; }
        .nota-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .nota-table th, .nota-table td { border: 1px solid #333; padding: 4px 6px; text-align: center; }
        .nota-table th { background: #f2f2f2; }
        .nota-summary { margin-left: auto; margin-right: auto; }
        .nota-summary td { padding: 3px 6px; }
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
        <div class="nota-title">
            <h2 style="margin-bottom:12px;">WINGKO BABAT PRATAMA</h2>
            <div class="sub fw-bold" style="margin-bottom:8px;">Nota Retur Penjualan</div>
            <div class="sub">Tanggal Retur: {{ $returjual->tanggal_returjual }}</div>
            <div class="sub">Nama Pelanggan: {{ $returjual->pelanggan->nama_pelanggan ?? $returjual->nama_pelanggan ?? '-' }}</div>
        </div>
        <!-- print button removed -->
    </div>
    <div class="nota-info" style="margin-top:8px;">Nomor Retur Penjualan: {{ $returjual->no_returjual }}</div>
    <div class="nota-info">Nomor Penjualan: {{ $returjual->no_jual }}</div>

    <table class="nota-table" style="margin-top: 18px;">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Jumlah</th>
                <th>Harga/Satuan</th>
                <th>Alasan</th>
                <th>Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $detail)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $detail->nama_produk }}</td>
                <td>{{ $detail->satuan ?? '-' }}</td>
                <td>{{ $detail->jumlah_retur }}</td>
                <td>Rp{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td>{{ $detail->alasan }}</td>
                <td>Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="nota-summary" style="margin-left:0; width: 100%; max-width: 700px;">
        <tr>
            <td class="fw-bold" style="width:170px; white-space:nowrap;">Total Retur</td>
            <td style="white-space:nowrap;">: Rp{{ number_format($returjual->total_nilai_retur, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold" style="white-space:nowrap;">Jenis Retur</td>
            <td style="white-space:nowrap;">: {{ $returjual->jenis_retur ?? '-' }}</td>
        </tr>
        <tr>
            <td class="fw-bold" style="white-space:nowrap;">Keterangan</td>
            <td style="white-space:nowrap;">: {{ str_replace(["\r\n", "\r", "\n"], ' ', $returjual->keterangan ?? '-') }}</td>
        </tr>
    </table>
</div>
</body>
</html>