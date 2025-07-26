<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; background: #fafafa; }
        table { border-collapse: collapse; width: 100%; margin-top: 16px; }
        th, td { border: 1px solid #333; padding: 8px 10px; text-align: center; }
        th { background: #e9ecef; font-weight: bold; }
        h3 { margin-bottom: 0; }
        .table-title { margin-top: 8px; font-size: 15px; font-weight: 600; }
        tr:nth-child(even) { background: #f6f6f6; }
    </style>
</head>
<body>
    <h3 style="text-align:center;">
        @if(request('jenis_penjualan') == 'langsung')
            LAPORAN PENJUALAN LANGSUNG
        @elseif(request('jenis_penjualan') == 'pesanan')
            LAPORAN PENJUALAN PESANAN
        @else
            LAPORAN PENJUALAN
        @endif
    </h3>
    <div class="table-title" style="text-align:center;">Periode: {{ request('tanggal_awal') ? \Carbon\Carbon::parse(request('tanggal_awal'))->format('d F Y') : '-' }} s/d {{ request('tanggal_akhir') ? \Carbon\Carbon::parse(request('tanggal_akhir'))->format('d F Y') : '-' }}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Jual</th>
                <th>Tanggal Jual</th>
                <th>Pelanggan</th>
                <th>Total Harga</th>
                <th>Diskon</th>
                <th>Total Jual</th>
                <th>Piutang</th>
                <th>Metode</th>

            </tr>
        </thead>
        <tbody>
            @forelse($penjualan as $i => $p)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $p->no_jual }}</td>
                <td>{{ $p->tanggal_jual ? \Carbon\Carbon::parse($p->tanggal_jual)->format('d F Y') : '-' }}</td>
                <td>{{ $p->nama_pelanggan ?? '-' }}</td>
                <td>Rp{{ number_format($p->total_harga,0,',','.') }}</td>
                <td>
                    @if(isset($p->tipe_diskon) && $p->tipe_diskon == 'persen')
                        {{ $p->diskon }}%
                    @else
                        Rp{{ number_format($p->diskon,0,',','.') }}
                    @endif
                </td>
                <td>Rp{{ number_format($p->total_jual,0,',','.') }}</td>
                <td>
                    @if($p->status_pembayaran == 'belum lunas')
                        <span style="color:#d90429; font-weight:bold;">
                            Rp{{ number_format($p->piutang,0,',','.') }}
                        </span>
                    @else
                        Rp{{ number_format($p->piutang,0,',','.') }}
                    @endif
                </td>
                <td>{{ ucfirst($p->metode_pembayaran) }}</td>

            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-3">Data penjualan belum ada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
