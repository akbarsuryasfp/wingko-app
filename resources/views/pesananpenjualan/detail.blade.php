@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Detail Pesanan Penjualan</h4>
    <table class="table">
        <tr>
            <th>No Pesanan</th>
            <td>{{ $pesanan->no_pesanan }}</td>
        </tr>
        <tr>
            <th>Tanggal Pesanan</th>
            <td>{{ $pesanan->tanggal_pesanan }}</td>
        </tr>
        <tr>
            <th>Pelanggan</th>
            <td>{{ $pesanan->nama_pelanggan ?? '-' }}</td>
        </tr>
        <tr>
            <th>Total Pesanan</th>
            <td>{{ number_format($pesanan->total,0,',','.') }}</td>
        </tr>
        <tr>
            <th>Status Pembayaran</th>
            <td>
                @if($pesanan->status_pembayaran == 'lunas')
                    <span class="badge bg-success">Lunas</span>
                @else
                    <span class="badge bg-warning text-dark">Belum Lunas</span>
                @endif
            </td>
        </tr>
    </table>

    <h5>Detail Produk Pesanan</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $d)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $d->nama_produk ?? '-' }}</td>
                <td>{{ $d->jumlah }}</td>
                <td>{{ number_format($d->harga_satuan,0,',','.') }}</td>
                <td>{{ number_format($d->subtotal,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('pesananpenjualan.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection