@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>DETAIL RETUR PENJUALAN</h4>
    <table class="table">
        <tr>
            <th>No Retur Jual</th>
            <td>{{ $returjual->no_returjual }}</td>
        </tr>
        <tr>
            <th>No Penjualan</th>
            <td>{{ $returjual->no_jual }}</td>
        </tr>
        <tr>
            <th>Tanggal Retur</th>
            <td>{{ $returjual->tanggal_returjual }}</td>
        </tr>
        <tr>
            <th>Pelanggan</th>
            <td>{{ $returjual->nama_pelanggan ?? '-' }}</td>
        </tr>
        <tr>
            <th>Jenis Retur</th>
            <td>{{ $returjual->jenis_retur ?? '-' }}</td>
        </tr>
        <tr>
            <th>Total Retur</th>
            <td>Rp{{ number_format($returjual->total_nilai_retur,0,',','.') }}</td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td>{{ $returjual->keterangan ?? '-' }}</td>
        </tr>
    </table>

    <h5 class="text-center">DETAIL PRODUK RETUR PENJUALAN</h5>
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
            <tr>
                <td colspan="5" class="text-end fw-bold">Total Retur</td>
                <td class="fw-bold">Rp{{ number_format($returjual->total_nilai_retur,0,',','.') }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('returjual.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection