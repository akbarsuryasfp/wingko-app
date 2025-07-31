
@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4>DETAIL RETUR CONSIGNOR</h4>
            <table class="table">
                <tr>
                    <th>No Retur Consignor</th>
                    <td>{{ $returconsignor->no_returconsignor }}</td>
                </tr>
                <tr>
                    <th>No Konsinyasi Masuk</th>
                    <td>{{ $returconsignor->konsinyasimasuk->no_konsinyasimasuk ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Tanggal Retur</th>
                    <td>{{ $returconsignor->tanggal_returconsignor }}</td>
                </tr>
                <tr>
                    <th>Consignor</th>
                    <td>{{ $returconsignor->consignor->nama_consignor ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Total Nilai Retur</th>
                    <td>Rp{{ number_format($returconsignor->total_nilai_retur,0,',','.') }}</td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td>{{ $returconsignor->keterangan ?? '-' }}</td>
                </tr>
            </table>
            <h5 class="text-center">DETAIL PRODUK RETUR CONSIGNOR</h5>
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
                    @php $total = 0; @endphp
                    @foreach($details as $i => $d)
                    <tr>
                        <td class="text-center align-middle">{{ $i+1 }}</td>
                        <td class="text-center align-middle">{{ $d->nama_produk ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $d->satuan ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $d->jumlah_retur }}</td>
                        <td class="text-center align-middle">Rp{{ number_format($d->harga_satuan,0,',','.') }}</td>
                        <td class="text-center align-middle">{{ $d->alasan }}</td>
                        <td class="text-center align-middle">Rp{{ number_format($d->subtotal,0,',','.') }}</td>
                    </tr>
                    @php $total += $d->subtotal; @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Total Retur</th>
                        <th class="text-center">Rp{{ number_format($total,0,',','.') }}</th>
                    </tr>
                </tfoot>
            </table>
            <a href="{{ route('returconsignor.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
