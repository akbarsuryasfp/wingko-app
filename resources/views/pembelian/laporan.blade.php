<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pembelian Bahan</title>
    <style>
        @page { size: A4 landscape; margin: 1.5cm; }
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 4px 8px; }
        th { background: #eee; font-weight: bold; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        h3 { margin-bottom: 5px; }
    </style>
</head>
<body>
    <h3 style="text-align:center;">LAPORAN PEMBELIAN BAHAN</h3>
    <p style="text-align:center; margin-top:0;">Periode: {{ is_array($periode) ? implode(' - ', $periode) : ($periode ?? 'Semua Data') }}</p>
    
    <table>
        <thead>
            <tr class="text-center">
            <th width="5%">No</th>
            <th width="10%">No Pembelian</th>
            <th width="10%">Tanggal</th>
            <th width="25%">Supplier</th>
            <th width="13%" >Total Pembelian</th>
            <th width="12%" >Uang Muka</th>
            <th width="13%" >Total Bayar</th>
            <th width="10%" >Hutang</th>
            <th width="12%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Inisialisasi variabel total
                $total_pembelian = 0;
                $total_uang_muka = 0;
                $total_bayar = 0;
                $total_hutang = 0;
            @endphp
            
            @foreach($pembelian as $no => $p)
            <tr>
                <td class="text-center">{{ $no+1 }}</td>
                <td class="text-center">{{ $p->no_pembelian }}</td>
                <td class="text-center">{{ date('d/m/Y', strtotime(is_array($p->tanggal_pembelian) ? $p->tanggal_pembelian[0] : $p->tanggal_pembelian)) }}</td>
                <td>{{ is_array($p->nama_supplier) ? implode(', ', $p->nama_supplier) : $p->nama_supplier }}</td>
                <td class="text-end">Rp{{ number_format(is_array($p->total_pembelian) ? array_sum($p->total_pembelian) : $p->total_pembelian, 0, ',', '.') }}</td>
                <td class="text-end">Rp{{ number_format(is_array($p->uang_muka) ? array_sum($p->uang_muka) : ($p->uang_muka ?? 0), 0, ',', '.') }}</td>
                <td class="text-end">Rp{{ number_format(is_array($p->total_bayar) ? array_sum($p->total_bayar) : $p->total_bayar, 0, ',', '.') }}</td>
                <td class="text-end">Rp{{ number_format(is_array($p->hutang) ? array_sum($p->hutang) : $p->hutang, 0, ',', '.') }}</td>
                <td class="text-center">
                    @php
                        $hutang = is_array($p->hutang) ? array_sum($p->hutang) : $p->hutang;
                    @endphp
                    @if($hutang > 0)
                        Belum Lunas
                    @else
                        Lunas
                    @endif
                </td>
            </tr>
            @php
                // Akumulasi total
                $total_pembelian += is_array($p->total_pembelian) ? array_sum($p->total_pembelian) : $p->total_pembelian;
                $total_uang_muka += is_array($p->uang_muka) ? array_sum($p->uang_muka) : ($p->uang_muka ?? 0);
                $total_bayar += is_array($p->total_bayar) ? array_sum($p->total_bayar) : $p->total_bayar;
                $total_hutang += is_array($p->hutang) ? array_sum($p->hutang) : $p->hutang;
            @endphp
            @endforeach
            
            <tr>
                <th colspan="4" class="text-end">TOTAL</th>
                <th class="text-end">Rp{{ number_format($total_pembelian, 0, ',', '.') }}</th>
                <th class="text-end">Rp{{ number_format($total_uang_muka, 0, ',', '.') }}</th>
                <th class="text-end">Rp{{ number_format($total_bayar, 0, ',', '.') }}</th>
                <th class="text-end">Rp{{ number_format($total_hutang, 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tbody>
    </table>
</body>
</html>