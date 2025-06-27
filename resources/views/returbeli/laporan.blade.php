<!DOCTYPE html>
<html>
<head>
    <title>Laporan Retur Pembelian</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 4px 8px; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h3 style="text-align:center;">LAPORAN RETUR PEMBELIAN</h3>
    <p style="text-align:center;">
        Periode: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M Y') }} s.d. {{ \Carbon\Carbon::parse($tanggal_selesai)->format('d M Y') }}
    </p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Retur</th>
                <th>Kode Pembelian</th>
                <th>Tanggal Retur</th>
                <th>Supplier</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse ($returList as $retur)
                @foreach($retur->details as $index => $detail)
                    <tr>
                        @if($index === 0)
                            <td rowspan="{{ $retur->details->count() }}">{{ $no++ }}</td>
                            <td rowspan="{{ $retur->details->count() }}">{{ $retur->no_retur_beli }}</td>
                            <td rowspan="{{ $retur->details->count() }}">{{ $retur->no_pembelian }}</td>
                            <td rowspan="{{ $retur->details->count() }}">{{ $retur->tanggal_retur_beli }}</td>
                            <td rowspan="{{ $retur->details->count() }}">{{ $retur->nama_supplier }}</td>
                        @endif
                        <td>
                            <b>{{ $detail->nama_bahan }}</b> ({{ $detail->jumlah_retur }}) {{ $detail->alasan }}
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="6">Tidak ada data retur pembelian pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>