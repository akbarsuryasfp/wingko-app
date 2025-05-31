
@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Detail Penerimaan Bahan</h4>
    <table class="table">
        <tr>
            <th>No Terima Bahan</th>
            <td>{{ $terima->no_terima_bahan }}</td>
        </tr>
        <tr>
            <th>Tanggal Terima</th>
            <td>{{ $terima->tanggal_terima }}</td>
        </tr>
        <tr>
            <th>No Order Beli</th>
            <td>{{ $terima->no_order_beli }}</td>
        </tr>
        <tr>
            <th>Supplier</th>
            <td>{{ $terima->nama_supplier ?? '-' }}</td>
        </tr>
    </table>

    <h5>Daftar Bahan Diterima</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Bahan</th>
                <th>Nama Bahan</th>
                <th>Jumlah Diterima</th>
                <th>Harga Beli</th>
                <th>Total</th>
                <th>Tanggal Exp</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $d)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $d->kode_bahan }}</td>
                <td>{{ $d->nama_bahan ?? '-' }}</td>
                <td>{{ $d->bahan_masuk }}</td>
                <td>{{ number_format($d->harga_beli,0,',','.') }}</td>
                <td>{{ number_format($d->total,0,',','.') }}</td>
                <td>{{ $d->tanggal_exp }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('terimabahan.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection