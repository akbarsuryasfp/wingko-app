<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Penjualan - {{ $penjualan->no_jual }}</title>
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
        <div class="nota-logo>
            Logo
        </div>
        <div class="nota-title">
            <h2>WINGKO BABAT PRATAMA</h2>
            <div class="sub">Nota Penjualan</div>
            <div class="sub">Tanggal Penjualan: {{ $penjualan->tanggal_jual }}</div>
            <div class="sub">Nama Pelanggan: {{ $penjualan->nama_pelanggan ?? '-' }}</div>
        </div>
        <div class="nota-print no-print">
            <button onclick="window.print()" style="padding:4px 12px;">Print</button>
        </div>
    </div>
    <div class="nota-info" style="margin-top:8px;">Nomor Penjualan: {{ $penjualan->no_jual }}</div>

    <table class="nota-table" style="margin-top: 18px;">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Kuantitas</th>
                <th>Harga Jual</th>
                <th>Sub Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $detail)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $detail->nama_produk }}</td>
                <td>{{ $detail->satuan ?? '-' }}</td>
                <td>{{ $detail->jumlah }}</td>
                <td>Rp{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td>Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="nota-summary" style="margin-left:0; width: 350px;">
        <tr>
            <td class="fw-bold" style="width:170px;">Total Harga</td>
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
            <td class="fw-bold">Total Penjualan</td>
            <td>: Rp{{ number_format($penjualan->total_jual, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Jenis Pembayaran</td>
            <td>: {{ ucfirst($penjualan->metode_pembayaran) }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Total Bayar</td>
            <td>:
                Rp{{ number_format(($penjualan->piutang == 0 ? $penjualan->total_jual : $penjualan->total_bayar), 0, ',', '.') }}
            </td>
        </tr>
        <tr>
            <td class="fw-bold">Kembalian</td>
            <td>: Rp{{ number_format($penjualan->kembalian, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>
</body>
</html>