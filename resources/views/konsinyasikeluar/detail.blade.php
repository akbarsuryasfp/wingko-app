@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>DETAIL KONSINYASI KELUAR</h4>
    <table class="table">
        <tr>
            <th>No Konsinyasi Keluar</th>
            <td>{{ $header->kode_setor }}</td>
        </tr>
        <tr>
            <th>Tanggal Setor</th>
            <td>{{ \Carbon\Carbon::parse($header->tanggal_setor)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>Nama Consignee (Mitra)</th>
            <td>{{ $header->consignee->nama_consignee ?? '-' }}</td>
        </tr>
        <tr>
            <th>Total Setor</th>
            <td>Rp{{ number_format($header->total_setor,0,',','.') }}</td>
        </tr>
    </table>

    <h5 class="text-center">DETAIL PRODUK KONSINYASI KELUAR</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah Setor</th>
                <th>Satuan</th>
                <th>Harga Setor</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($header->details as $i => $d)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $d->produk->nama_produk ?? '-' }}</td>
                <td>{{ $d->jumlah_setor }}</td>
                <td>{{ $d->satuan }}</td>
                <td>{{ number_format($d->harga_setor,0,',','.') }}</td>
                <td>{{ number_format($d->subtotal,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('konsinyasikeluar.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
