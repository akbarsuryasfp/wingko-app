@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4>DETAIL RETUR CONSIGNEE (MITRA)</h4>
            <table class="table">
                <tr>
                    <th>No Retur Consignee</th>
                    <td>{{ $returconsignee->no_returconsignee }}</td>
                </tr>
                <tr>
                    <th>No Konsinyasi Keluar</th>
                    <td>{{ $returconsignee->konsinyasikeluar->no_konsinyasikeluar ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Tanggal Retur</th>
                    <td>{{ $returconsignee->tanggal_returconsignee }}</td>
                </tr>
                <tr>
                    <th>Nama Consignee (Mitra)</th>
                    <td>{{ $returconsignee->consignee->nama_consignee ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Total Retur</th>
                    <td>Rp{{ number_format($returconsignee->total_nilai_retur,0,',','.') }}</td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td>{{ $returconsignee->keterangan ?? '-' }}</td>
                </tr>
            </table>
            <h5 class="text-center">DETAIL PRODUK RETUR CONSIGNEE (MITRA)</h5>
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Nama Produk</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Jumlah Retur</th>
                        <th class="text-center">Harga/Satuan</th>
                        <th class="text-center">Alasan</th>
                        <th class="text-center">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $i => $d)
                    <tr>
                        <td class="text-center">{{ $i+1 }}</td>
                        <td class="text-center">{{ $d->nama_produk ?? '-' }}</td>
                        <td class="text-center">{{ $d->satuan ?? '-' }}</td>
                        <td class="text-center">{{ $d->jumlah_retur }}</td>
                        <td class="text-center">Rp{{ number_format($d->harga_satuan,0,',','.') }}</td>
                        <td class="text-center">{{ $d->alasan }}</td>
                        <td class="text-center">Rp{{ number_format($d->subtotal,0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Total Retur</th>
                        <th class="text-center">Rp{{ number_format($returconsignee->total_nilai_retur,0,',','.') }}</th>
                    </tr>
                </tfoot>
            </table>
            <a href="{{ route('returconsignee.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
