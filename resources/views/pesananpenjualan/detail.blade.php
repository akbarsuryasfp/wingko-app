@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>DETAIL PESANAN PENJUALAN</h4>
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
            <th>Tanggal Pengiriman</th>
            <td>{{ $pesanan->tanggal_pengiriman ?? '-' }}</td>
        </tr>
        <tr>
            <th>Pelanggan</th>
            <td>{{ $pesanan->nama_pelanggan ?? ($pesanan->pelanggan->nama_pelanggan ?? '-') }}</td>
        </tr>
        <tr>
            <th>Total Pesanan</th>
            <td>Rp{{ number_format($pesanan->total_pesanan,0,',','.') }}</td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td>{{ $pesanan->keterangan ?? '-' }}</td>
        </tr>
    </table>

    <h5 class="text-center">DETAIL PRODUK PESANAN</h5>
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
                <td>{{ $d->nama_produk ?? ($d->produk->nama_produk ?? '-') }}</td>
                <td>{{ $d->jumlah }}</td>
                <td>Rp{{ number_format($d->harga_satuan,0,',','.') }}</td>
                <td>Rp{{ number_format($d->subtotal,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('pesananpenjualan.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection