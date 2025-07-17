@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>DETAIL KONSINYASI MASUK</h4>
    <table class="table">
        <tr>
            <th>No Konsinyasi Masuk</th>
            <td>{{ $konsinyasi->no_konsinyasimasuk }}</td>
        </tr>
        <tr>
            <th>No Surat Titip Jual</th>
            <td>{{ $konsinyasi->no_surattitipjual }}</td>
        </tr>
        <tr>
            <th>Tanggal Masuk</th>
            <td>{{ $konsinyasi->tanggal_masuk }}</td>
        </tr>
        <tr>
            <th>Nama Consignor (Pemilik Barang)</th>
            <td>{{ $konsinyasi->consignor->nama_consignor ?? '-' }}</td>
        </tr>
        <tr>
            <th>Total Titip</th>
            <td>Rp{{ number_format($konsinyasi->total_titip,0,',','.') }}</td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td>{{ $konsinyasi->keterangan ?? '-' }}</td>
        </tr>
    </table>

    <h5 class="text-center">DETAIL PRODUK KONSINYASI MASUK</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah Stok</th>
                <th>Harga Titip/Produk</th>
                <th>Harga Jual/Produk</th>
                <th>Subtotal Titip</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $d)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $d->nama_produk ?? '-' }}</td>
                <td>{{ $d->jumlah_stok }}</td>
                <td>Rp{{ number_format($d->harga_titip,0,',','.') }}</td>
                <td>Rp{{ number_format($d->harga_jual,0,',','.') }}</td>
                <td>Rp{{ number_format($d->subtotal,0,',','.') }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="5" class="text-end fw-bold">Total Titip</td>
                <td class="fw-bold">Rp{{ number_format($konsinyasi->total_titip,0,',','.') }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('konsinyasimasuk.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection