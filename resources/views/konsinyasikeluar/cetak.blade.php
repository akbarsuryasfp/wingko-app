<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pengiriman Produk Konsinyasi - {{ $header->no_konsinyasikeluar }}</title>
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
            <div class="sub fw-bold" style="margin-bottom:8px;">Surat Pengiriman Produk Konsinyasi</div>
            <div class="sub" style="margin-bottom:8px;">Tanggal Pengiriman: {{ $header->tanggal_setor }}</div>
            <div class="sub">Kepada Yth: {{ $header->consignee->nama_consignee ?? '-' }}</div>
        </div>
    <!-- print button removed for PDF output -->
    </div>
    <div class="nota-info" style="margin-top:8px;">No Surat Pengiriman: {{ $header->no_suratpengiriman ?? $header->no_konsinyasikeluar }}</div>
    <div class="nota-info" style="margin-top:2px;">No Konsinyasi Keluar: {{ $header->no_konsinyasikeluar }}</div>
    <div class="nota-info" style="margin-top:2px;">Alamat Mitra: {{ $header->consignee->alamat ?? '-' }}</div>
    <div class="nota-info" style="margin-top:2px;">Keterangan: {{ $header->keterangan ?? '-' }}</div>

    <table class="nota-table" style="margin-top: 18px;">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Jumlah Setor</th>
                <th>Harga Setor/Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($header->details as $i => $detail)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $detail->produk->nama_produk ?? $detail->nama_produk ?? '-' }}</td>
                <td>{{ $detail->satuan }}</td>
                <td>{{ $detail->jumlah_setor }}</td>
                <td>Rp{{ number_format($detail->harga_setor, 0, ',', '.') }}</td>
                <td>Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>


    <table class="nota-summary" style="margin-left:0; width: 100%; max-width: 500px;">
        <tr>
            <td class="fw-bold" style="width:170px;">Total Setor</td>
            <td style="width:10px;">:</td>
            <td>Rp{{ number_format($header->total_setor, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Keterangan</td>
            <td>:</td>
            <td>{{ $header->keterangan ?? '-' }}</td>
        </tr>
    </table>

    <div style="margin-top: 32px; font-size: 15px;">
        <p>
            Dengan ini, pihak <b>Wingko Babat Pratama</b> telah melakukan pengiriman produk konsinyasi kepada pihak Consignee (Mitra), yaitu <b>{{ $header->consignee->nama_consignee ?? '-' }}</b>, sesuai dengan rincian yang tertera pada surat ini. Seluruh proses pengiriman telah dilakukan secara resmi dan dapat dipertanggungjawabkan.
        </p>
    </div>
</div>
</body>
</html>
