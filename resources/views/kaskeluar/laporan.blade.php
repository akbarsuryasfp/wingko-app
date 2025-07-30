<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran Kas Lain-lain</title>
    <style>
        @page { size: A4 portrait; margin: 1.5cm; }
        body { font-family: Cambria, Helvetica, sans-serif; font-size: 12px; }
        .title { 
            text-align: center; 
            font-weight: bold; 
            font-size: 16px; 
            margin-bottom: 10px; 
        }
        .periode {
            text-align: center;
            margin-bottom: 15px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 10px; 
        }
        th, td { 
            border: 1px solid #000; 
            padding: 5px 7px; 
            font-size: 11px; 
        }
        th { 
            background: #f0f0f0; 
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .text-danger { color: #dc3545; }
    </style>
</head>
<body>
    <div class="title">LAPORAN PENGELUARAN KAS LAIN-LAIN</div>
    <div class="periode">
        Periode: {{ date('d/m/Y', strtotime($start_date)) }} s/d {{ date('d/m/Y', strtotime($end_date)) }}
    </div>
    
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tanggal</th>
                <th width="15%">No Bukti</th>
                <th width="20%">Penerima</th>
                <th width="15%">Jumlah</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_pengeluaran = 0;
            @endphp
            
            @foreach($kaskeluar as $i => $item)
            <tr>
                <td class="text-center">{{ $i+1 }}</td>
                <td class="text-center">{{ date('d/m/Y', strtotime($item->tanggal)) }}</td>
                <td class="text-center">{{ $item->nomor_bukti }}</td>
                <td class="text-start">{{ $item->penerima !== '-' ? $item->penerima : '-' }}</td>
                <td class="text-end">
                    Rp{{ number_format(floatval($item->jumlah_rupiah), 0, ',', '.') }}
                </td>
                <td class="text-start">{{ $item->keterangan_teks }}</td>
            </tr>
            @php
                $total_pengeluaran += floatval($item->jumlah_rupiah);
            @endphp
            @endforeach
            
            <tr>
                <th colspan="4" class="text-end">TOTAL PENGELUARAN</th>
                <th class="text-end">Rp{{ number_format(floatval($total_pengeluaran), 0, ',', '.') }}</th>
                <th></th>
            </tr>
        </tbody>
    </table>
</body>
</html>