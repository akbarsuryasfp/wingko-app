@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>DETAIL PENJUALAN KONSINYASI MASUK</h4>
    <table class="table">
        <tr>
            <th>No Jual Konsinyasi</th>
            <td>{{ $jual->no_jualkonsinyasi }}</td>
        </tr>
        <tr>
            <th>No Konsinyasi Masuk</th>
            <td>{{ $jual->no_konsinyasimasuk }}</td>
        </tr>
        <tr>
            <th>Tanggal Jual</th>
            <td>{{ $jual->tanggal_jual }}</td>
        </tr>
        <tr>
            <th>Total Jual</th>
            <td>{{ number_format($jual->total_jual,0,',','.') }}</td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td>{{ $jual->keterangan ?? '-' }}</td>
        </tr>
    </table>
    <h5 class="text-center">DETAIL PRODUK TERJUAL</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah</th>
                <th>Harga Jual</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $d)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $d->nama_produk ?? '-' }}</td>
                <td>{{ $d->jumlah }}</td>
                <td>{{ number_format($d->harga_jual,0,',','.') }}</td>
                <td>{{ number_format($d->subtotal,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('jualkonsinyasimasuk.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
