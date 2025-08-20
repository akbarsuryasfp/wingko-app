<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Laba Rugi</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .container { margin: 0 auto; padding: 20px; }
        .text-center { text-align: center; }
        .mb-4 { margin-bottom: 1.5rem; }
        .fw-bold { font-weight: bold; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #333; padding: 6px 10px; }
        .table th { background: #eee; }
        .text-end { text-align: right; }
        .bg-light { background: #f8f9fa; }
        .bg-success { background: #d4edda; }
        .mt-4 { margin-top: 1.5rem; }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="text-center mb-4">
        <h4 class="fw-bold">LAPORAN LABA RUGI</h4>
        <h5>Wingko Pratama</h5>
        <p>Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}</p>
    </div>
    <table class="table">
        <tr class="fw-bold bg-light"><td colspan="2">Pendapatan Usaha</td></tr>
        <tr>
            <td>Pendapatan Penjualan</td>
            <td class="text-end">Rp {{ number_format($penjualan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pendapatan Lain-lain</td>
            <td class="text-end">Rp {{ number_format($pendapatan_lain, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Retur Penjualan</td>
            <td class="text-end">(Rp {{ number_format($retur_penjualan, 0, ',', '.') }})</td>
        </tr>
        <tr>
            <td>Diskon Penjualan</td>
            <td class="text-end">(Rp {{ number_format($diskon_penjualan, 0, ',', '.') }})</td>
        </tr>
        <tr class="fw-bold">
            <td>Pendapatan Bersih</td>
            <td class="text-end">Rp {{ number_format($pendapatan_bersih, 0, ',', '.') }}</td>
        </tr>
        <tr class="fw-bold bg-light"><td colspan="2">Harga Pokok Penjualan</td></tr>
        <tr>
            <td>Harga Pokok Penjualan</td>
            <td class="text-end">(Rp {{ number_format($hpp, 0, ',', '.') }})</td>
        </tr>
        <tr class="fw-bold">
            <td>Laba Kotor</td>
            <td class="text-end">Rp {{ number_format($laba_kotor, 0, ',', '.') }}</td>
        </tr>
        <tr class="fw-bold bg-light"><td colspan="2">Beban Operasional</td></tr>
        @foreach($list_beban as $beban)
        <tr>
            <td>{{ $beban['nama'] }}</td>
            <td class="text-end">(Rp {{ number_format($beban['saldo'], 0, ',', '.') }})</td>
        </tr>
        @endforeach
        <tr class="fw-bold">
            <td>Total Beban Operasional</td>
            <td class="text-end">(Rp {{ number_format($beban_operasional, 0, ',', '.') }})</td>
        </tr>
        <tr class="fw-bold">
            <td>Laba Usaha</td>
            <td class="text-end">Rp {{ number_format($laba_usaha, 0, ',', '.') }}</td>
        </tr>
        <tr class="fw-bold bg-success">
            <td>Laba Bersih</td>
            <td class="text-end">Rp {{ number_format($laba_bersih, 0, ',', '.') }}</td>
        </tr>
    </table>
    <div class="mt-4">
        <p><strong>Dibuat oleh:</strong> {{ auth()->user()->name ?? 'Admin' }}</p>
        <p><small>Tanggal cetak: {{ now()->format('d/m/Y H:i') }}</small></p>
    </div>
</div>
</body>
</html>