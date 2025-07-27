
@extends('layouts.app')

@section('content')
<div class="container mt-4">
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
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah Retur</th>
                <th>Harga Satuan</th>
                <th>Alasan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $d)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $d->nama_produk ?? '-' }}</td>
                <td>{{ $d->jumlah_retur }}</td>
                <td>Rp{{ number_format($d->harga_satuan,0,',','.') }}</td>
                <td>{{ $d->alasan }}</td>
                <td>Rp{{ number_format($d->subtotal,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('returconsignor.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
