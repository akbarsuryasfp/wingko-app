<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Neraca</title>
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
        .mt-4 { margin-top: 1.5rem; }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="text-center mb-4">
        <h4 class="fw-bold">LAPORAN NERACA</h4>
        <h5>Wingko Pratama</h5>
        <p>Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}</p>
    </div>
    <table class="table">
        <tr>
            <th colspan="2" class="text-center">Aset Lancar</th>
        </tr>
        <tr>
            <td style="padding-left:30px;">Kas di Bank</td>
            <td class="text-end">Rp {{ number_format(($saldo['kas_bank_1000'] ?? 0) + ($saldo['kas_bank_1010'] ?? 0), 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:30px;">Kas Kecil</td>
            <td class="text-end">Rp {{ number_format($saldo['kas_kecil_1011'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:30px;">Piutang Usaha</td>
            <td class="text-end">Rp {{ number_format($saldo['piutang_1020'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:30px;">Uang Muka Pembelian</td>
            <td class="text-end">Rp {{ number_format($saldo['uang_muka_1025'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:30px;">Persediaan Bahan</td>
            <td class="text-end">Rp {{ number_format($saldo['persediaan_bahan_1030'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:30px;">Persediaan Barang Jadi</td>
            <td class="text-end">Rp {{ number_format($saldo['persediaan_jadi_1040'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th colspan="2" class="text-center">Aset Tetap</th>
        </tr>
        <tr>
            <td style="padding-left:30px;">Tanah</td>
            <td class="text-end">Rp {{ number_format($saldo['tanah_1110'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:30px;">Bangunan</td>
            <td class="text-end">Rp {{ number_format($saldo['bangunan_1120'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:30px;">Mesin</td>
            <td class="text-end">Rp {{ number_format($saldo['mesin_1130'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:30px;">Akumulasi Penyusutan</td>
            <td class="text-end">Rp {{ number_format($saldo['akumulasi_penyusutan_1140'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th class="text-end">Total Aset</th>
            <th class="text-end">
                Rp {{ number_format(
                    ($saldo['kas_bank_1000'] ?? 0) + ($saldo['kas_bank_1010'] ?? 0) +
                    ($saldo['kas_kecil_1011'] ?? 0) + ($saldo['piutang_1020'] ?? 0) +
                    ($saldo['uang_muka_1025'] ?? 0) + ($saldo['persediaan_bahan_1030'] ?? 0) +
                    ($saldo['persediaan_jadi_1040'] ?? 0) + ($saldo['tanah_1110'] ?? 0) +
                    ($saldo['bangunan_1120'] ?? 0) + ($saldo['mesin_1130'] ?? 0) +
                    ($saldo['akumulasi_penyusutan_1140'] ?? 0)
                , 0, ',', '.') }}
            </th>
        </tr>
        <tr>
            <th colspan="2" class="text-center">Kewajiban</th>
        </tr>
        <tr>
            <td style="padding-left:30px;">Utang Usaha</td>
            <td class="text-end">Rp {{ number_format($saldo['utang_usaha_2000'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:30px;">Utang Bank</td>
            <td class="text-end">Rp {{ number_format($saldo['utang_bank_2010'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:30px;">Utang Pajak</td>
            <td class="text-end">Rp {{ number_format($saldo['utang_pajak_2020'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th colspan="2" class="text-center">Ekuitas</th>
        </tr>
        <tr>
            <td style="padding-left:30px;">Modal Pemilik</td>
            <td class="text-end">Rp {{ number_format($saldo['modal_pemilik_3000'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td style="padding-left:30px;">Prive</td>
            <td class="text-end">Rp {{ number_format($saldo['prive_3010'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th class="text-end">Total Kewajiban & Ekuitas</th>
            <th class="text-end">
                Rp {{ number_format(
                    ($saldo['utang_usaha_2000'] ?? 0) + ($saldo['utang_bank_2010'] ?? 0) +
                    ($saldo['utang_pajak_2020'] ?? 0) + ($saldo['modal_pemilik_3000'] ?? 0) +
                    ($saldo['prive_3010'] ?? 0)
                , 0, ',', '.') }}
            </th>
        </tr>
    </table>
    <div class="mt-4">
        <p><strong>Dibuat oleh:</strong> {{ auth()->user()->name ?? 'Admin' }}</p>
        <p><small>Tanggal cetak: {{ now()->format('d/m/Y H:i') }}</small></p>
    </div>
</div>
</body>
</html>