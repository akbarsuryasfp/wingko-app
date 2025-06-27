@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Detail Produksi</h3>

    <p><strong>No Produksi:</strong> {{ $produksi->no_produksi }}</p>
    <p><strong>Tanggal:</strong> {{ $produksi->tanggal_produksi }}</p>
    <p><strong>Keterangan:</strong> {{ $produksi->keterangan }}</p>

    <hr>
    <h5>Produk yang Diproduksi</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Produk</th>
                <th>Jumlah Aktual</th>
                <th>Tanggal Expired</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produksi->details as $d)
                <tr>
                    <td>{{ $d->produk->nama_produk ?? $d->kode_produk }}</td>
                    <td>{{ $d->jumlah_unit }}</td>
                    <td>{{ $d->tanggal_expired }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>
    @foreach ($bahanPakaiPerProduk as $produk)
        <h5 class="mt-4">Bahan Baku untuk Produk: <strong>{{ $produk['nama_produk'] }}</strong></h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Bahan</th>
                    <th>Jumlah Pakai</th>
                    <th>Satuan</th>
                    <th>Harga per Batch</th>
                    <th>Total Harga</th>
                    <th>Batch/No Terima</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($produk['detail'] as $b)
                    <tr>
                        <td>{{ $b->nama_bahan }}</td>
                        <td>{{ number_format($b->jumlah, 2) }}</td>
                        <td>{{ $b->satuan }}</td>
                        <td>{{ number_format($b->harga, 2) }}</td>
                        <td>{{ number_format($b->total_harga, 2) }}</td>
                        <td>{{ $b->batch }}</td>
                        <td>{{ $b->keterangan ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
</div>
@endsection
