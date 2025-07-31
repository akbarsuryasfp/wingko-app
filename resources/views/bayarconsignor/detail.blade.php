@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4>DETAIL PEMBAYARAN CONSIGNOR (PEMILIK BARANG)</h4>
            <table class="table">
                <tr>
                    <th>No Bayar Consignor</th>
                    <td>{{ $header->no_bayarconsignor }}</td>
                </tr>
                <tr>
                    <th>Tanggal Bayar</th>
                    <td>{{ $header->tanggal_bayar }}</td>
                </tr>
                <tr>
                    <th>Nama Consignor (Pemilik Barang)</th>
                    <td>{{ $header->consignor->nama_consignor ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Metode Pembayaran</th>
                    <td>{{ $header->metode_pembayaran ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Total Bayar</th>
                    <td>Rp{{ number_format($header->total_bayar,0,',','.') }}</td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td>{{ $header->keterangan ?? '-' }}</td>
                </tr>
            </table>
            <h5 class="text-center">DETAIL PRODUK PEMBAYARAN CONSIGNOR (PEMILIK BARANG)</h5>
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Kode Produk</th>
                        <th class="text-center">Nama Produk</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Jumlah Terjual</th>
                        <th class="text-center">Harga/Satuan</th>
                        <th class="text-center">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @foreach($header->details as $i => $d)
                    <tr>
                        <td class="text-center">{{ $i+1 }}</td>
                        <td class="text-center">{{ $d->kode_produk ?? '-' }}</td>
                        <td class="text-center">{{ $d->produk->nama_produk ?? '-' }}</td>
                        <td class="text-center">{{ $d->produk->satuan ?? '-' }}</td>
                        <td class="text-center">{{ $d->jumlah_terjual }}</td>
                        <td class="text-center">Rp{{ number_format($d->harga_satuan,0,',','.') }}</td>
                        <td class="text-center">Rp{{ number_format($d->subtotal,0,',','.') }}</td>
                    </tr>
                    @php $total += $d->subtotal; @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Total Bayar</th>
                        <th class="text-center">Rp{{ number_format($total,0,',','.') }}</th>
                    </tr>
                </tfoot>
            </table>
            <a href="{{ route('bayarconsignor.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
