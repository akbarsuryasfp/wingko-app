{{-- 
    // filepath: c:\Users\ACER\wingko-app\resources\views\penjualan\detail.blade.php
--}}
@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4>DETAIL PENJUALAN</h4>
            <table class="table">
        <tr>
            <th style="width:220px;">No Jual</th>
            <td>{{ $penjualan->no_jual ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tanggal Jual</th>
            <td>{{ $penjualan->tanggal_jual ?? '-' }}</td>
        </tr>
        <tr>
            <th>Nama Pelanggan</th>
            <td>{{ $penjualan->nama_pelanggan ?? ($penjualan->pelanggan->nama_pelanggan ?? '-') }}</td>
        </tr>
        <tr>
            <th>Metode Pembayaran</th>
            <td>{{ ucfirst($penjualan->metode_pembayaran ?? '-') }}</td>
        </tr>
        <tr>
            <th>Status Pembayaran</th>
            <td>
                @if(($penjualan->status_pembayaran ?? '') == 'lunas')
                    <span class="badge bg-success">Lunas</span>
                @else
                    <span class="badge bg-warning text-dark">Belum Lunas</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Total Harga</th>
            <td>Rp{{ number_format($penjualan->total_harga ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Diskon</th>
            <td>
                @if(isset($penjualan->tipe_diskon) && $penjualan->tipe_diskon == 'persen')
                    ({{ $penjualan->diskon }}%)
                @else
                    (Rp{{ number_format($penjualan->diskon ?? 0, 0, ',', '.') }})
                @endif
            </td>
        </tr>
        <tr>
            <th>Total Jual</th>
            <td>Rp{{ number_format($penjualan->total_jual ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total Bayar</th>
            <td>Rp{{ number_format($penjualan->total_bayar ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Kembalian</th>
            <td>Rp{{ number_format($penjualan->kembalian ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Piutang</th>
            <td>Rp{{ number_format($penjualan->piutang ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td>{{ $penjualan->keterangan ?? '-' }}</td>
        </tr>
    </table>

    <h5 class="text-center">DETAIL PRODUK TERJUAL</h5>
    <table class="table table-bordered text-center align-middle">
        <thead>
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Nama Produk</th>
                <th class="text-center align-middle">Satuan</th>
                <th class="text-center align-middle">Jumlah</th>
                <th class="text-center align-middle">Harga/Satuan</th>
                <th class="text-center align-middle">Diskon/Satuan</th>
                <th class="text-center align-middle">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($details as $i => $d)
            <tr>
                <td class="text-center align-middle">{{ $i+1 }}</td>
                <td class="text-center align-middle">{{ $d->nama_produk ?? ($d->produk->nama_produk ?? '-') }}</td>
                <td class="text-center align-middle">{{ $d->satuan ?? '-' }}</td>
                <td class="text-center align-middle">{{ $d->jumlah }}</td>
                <td class="text-center align-middle">Rp{{ number_format($d->harga_satuan,0,',','.') }}</td>
                <td class="text-center align-middle">{{ isset($d->diskon_satuan) ? '(Rp'.number_format($d->diskon_satuan,0,',','.') .')' : (isset($d->diskon_produk) ? '(Rp'.number_format($d->diskon_produk,0,',','.') .')' : '(Rp0)') }}</td>
                <td class="text-center align-middle">Rp{{ number_format($d->subtotal,0,',','.') }}</td>
            </tr>
            @php $grandTotal += $d->subtotal; @endphp
            @endforeach
            <tr>
                <td colspan="6" class="text-end fw-bold">Total Harga</td>
                <td class="fw-bold text-center align-middle">Rp{{ number_format($grandTotal,0,',','.') }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('penjualan.index') }}" class="btn btn-secondary mt-2">Back</a>
        </div>
    </div>
</div>
@endsection