<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Retur Consignee - {{ $returconsignee->no_returconsignee }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        .nota-container { width: 700px; margin: 0 auto; border: 1px solid #333; padding: 24px 32px; }
        .nota-header { display: flex; align-items: center; justify-content: space-between; }
        .nota-title { text-align: left; flex: 1; margin-left: 0; }
        .nota-title h2 { margin: 0 0 4px 0; font-size: 20px; }
        .nota-title .sub { font-size: 14px; }
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
        <div class="nota-title">
            <h2 style="margin-bottom:12px;">WINGKO BABAT PRATAMA</h2>
            <div class="sub fw-bold" style="margin-bottom:8px;">Nota Retur Consignee (Mitra)</div>
            <div class="sub">Tanggal Retur: {{ $returconsignee->tanggal_returconsignee }}</div>
            <div class="sub">Nama Consignee: {{ $returconsignee->consignee->nama_consignee ?? '-' }}</div>
        </div>
    </div>
    <div class="nota-info">Nomor Retur Consignee: {{ $returconsignee->no_returconsignee }}</div>
    <div class="nota-info">Nomor Konsinyasi Keluar: {{ $returconsignee->konsinyasikeluar->no_konsinyasikeluar ?? '-' }}</div>

    <table class="nota-table" style="margin-top: 18px;">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Jumlah Retur</th>
                <th>Harga/Satuan</th>
                <th>Alasan</th>
                <th>Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($details as $i => $detail)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $detail->nama_produk ?? ($detail->produk->nama_produk ?? '-') }}</td>
                <td>{{ $detail->satuan ?? ($detail->produk->satuan ?? '-') }}</td>
                <td>{{ $detail->jumlah_retur }}</td>
                <td>Rp{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td>{{ $detail->alasan }}</td>
                <td>Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @php $total += $detail->subtotal; @endphp
            @endforeach
        </tbody>
    </table>

    <table class="nota-summary" style="margin-left:0; width: 100%; max-width: 500px;">
        <tr>
            <td class="fw-bold" style="width:170px;">Total Retur</td>
            <td style="width:10px;">:</td>
            <td>Rp{{ number_format($returconsignee->total_nilai_retur, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Keterangan</td>
            <td>:</td>
            <td>{{ $returconsignee->keterangan ?? '-' }}</td>
        </tr>
    </table>
    <div style="margin-top: 32px; font-size: 15px;">
        <p>
            Dengan ini, pihak <b>{{ $returconsignee->consignee->nama_consignee ?? '-' }}</b> selaku Consignee telah melakukan retur barang kepada pihak <b>Wingko Babat Pratama</b> selaku pemilik produk (Consignor), sesuai dengan rincian yang tertera pada nota ini. Seluruh proses retur telah dilaksanakan secara resmi dan dapat dipertanggungjawabkan.
        </p>
    </div>
</div>
</body>
</html>
