@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Laporan Stok Akhir Produk per {{ $tanggal }}</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Stok Gudang</th>
                <th>Stok Toko 1</th>
                <th>Stok Toko 2</th>
                <th>Stok Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($produkList as $i => $produk)
            @php
                $gudang = $produk->stok_akhir->where('lokasi', 'Gudang')->sum('stok');
                $toko1  = $produk->stok_akhir->where('lokasi', 'Toko 1')->sum('stok');
                $toko2  = $produk->stok_akhir->where('lokasi', 'Toko 2')->sum('stok');
                $totalStok = $gudang + $toko1 + $toko2;
            @endphp
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $produk->kode_produk }}</td>
                <td>{{ $produk->nama_produk }}</td>
                <td>{{ $produk->satuan }}</td>
                <td>{{ $gudang }}</td>
                <td>{{ $toko1 }}</td>
                <td>{{ $toko2 }}</td>
                <td>
                    @if($totalStok > 0)
                        <b>{{ $totalStok }}</b> {{ $produk->satuan }}
                    @else
                        <span class="text-danger">Kosong</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection