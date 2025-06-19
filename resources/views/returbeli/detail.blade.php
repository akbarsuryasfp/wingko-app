@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Detail Retur Pembelian</h4>
    <div class="mb-3">
        <strong>No Retur:</strong> {{ $retur->no_retur_beli }}<br>
        <strong>No Pembelian:</strong> {{ $retur->no_pembelian }}<br>
        <strong>Tanggal Retur:</strong> {{ $retur->tanggal_retur_beli }}<br>
        <strong>Supplier:</strong> {{ $retur->nama_supplier }}<br>
        <strong>Total Retur:</strong> {{ number_format($retur->total_retur, 0, ',', '.') }}<br>
        @if(!empty($retur->keterangan))
            <strong>Keterangan:</strong> {{ $retur->keterangan }}
        @endif
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Bahan</th>
                <th>Harga Beli</th>
                <th>Jumlah Retur</th>
                <th>Subtotal</th>
                <th>Alasan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $d)
            <tr>
                <td>{{ $d->nama_bahan }}</td>
                <td>{{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                <td>{{ $d->jumlah_retur }}</td>
                <td>{{ number_format($d->subtotal, 0, ',', '.') }}</td>
                <td>{{ $d->alasan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('returbeli.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection