@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="mb-3">DETAIL PESANAN</h4>
            <table class="table">
                <tr>
                    <th style="width:200px;">No Pesanan</th>
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
                    <th>Nama Pelanggan</th>
                    <td>{{ $pesanan->nama_pelanggan ?? ($pesanan->pelanggan->nama_pelanggan ?? '-') }}</td>
                </tr>
                <tr>
                    <th>Total Pesanan</th>
                    <td>Rp{{ number_format($pesanan->total_pesanan,0,',','.') }}</td>
                </tr>
                <tr>
                    <th>Uang Muka (DP)</th>
                    <td>Rp{{ number_format($pesanan->uang_muka ?? 0,0,',','.') }}</td>
                </tr>
                <tr>
                    <th>Sisa Tagihan</th>
                    <td>Rp{{ number_format($pesanan->sisa_tagihan ?? 0,0,',','.') }}</td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td>{{ $pesanan->keterangan ?? '-' }}</td>
                </tr>
            </table>

            <h5 class="text-center">DETAIL PRODUK PESANAN</h5>
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Nama Produk</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-center">Harga/Satuan</th>
                        <th class="text-center">Diskon/Satuan</th>
                        <th class="text-center">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $i => $d)
                    <tr>
                        <td class="text-center">{{ $i+1 }}</td>
                        <td class="text-center">{{ $d->nama_produk ?? ($d->produk->nama_produk ?? '-') }}</td>
                        <td class="text-center">{{ $d->satuan ?? ($d->produk->satuan ?? '-') }}</td>
                        <td class="text-center">{{ $d->jumlah }}</td>
                        <td class="text-center">Rp{{ number_format($d->harga_satuan,0,',','.') }}</td>
                        <td class="text-center">Rp{{ number_format(isset($d->diskon_produk) ? $d->diskon_produk : 0,0,',','.') }}</td>
                        <td class="text-center">Rp{{ number_format($d->subtotal,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total Pesanan</td>
                        <td class="fw-bold text-center">Rp{{ number_format($pesanan->total_pesanan,0,',','.') }}</td>
                    </tr>
                </tbody>
            </table>
            <a href="{{ route('pesananpenjualan.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection