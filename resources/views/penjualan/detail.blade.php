{{-- 
    // filepath: c:\Users\ACER\wingko-app\resources\views\penjualan\detail.blade.php
--}}
@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">DETAIL PENJUALAN</h4>
    <table class="table table-borderless">
        <tr>
            <th style="width:220px;">No Jual</th>
            <td>{{ $penjualan->no_jual ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tanggal Jual</th>
            <td>{{ $penjualan->tanggal_jual ?? '-' }}</td>
        </tr>
        <tr>
            <th>Pelanggan</th>
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
            <td>{{ number_format($penjualan->total_harga ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Diskon</th>
            <td>{{ number_format($penjualan->diskon ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total Jual</th>
            <td>{{ number_format($penjualan->total_jual ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total Bayar</th>
            <td>{{ number_format($penjualan->total_bayar ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Kembalian</th>
            <td>{{ number_format($penjualan->kembalian ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Piutang</th>
            <td>{{ number_format($penjualan->piutang ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td>{{ $penjualan->keterangan ?? '-' }}</td>
        </tr>
    </table>

    <h5 class="text-center mb-3">DAFTAR PRODUK TERJUAL</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($details as $i => $detail)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $detail->nama_produk ?? ($detail->produk->nama_produk ?? '-') }}</td>
                <td>{{ $detail->jumlah }}</td>
                <td>{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td>{{ $detail->satuan ?? ($detail->produk->satuan ?? '-') }}</td>
                <td>{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @php $grandTotal += $detail->subtotal; @endphp
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada produk.</td>
            </tr>
            @endforelse
            <tr>
                <td colspan="5" class="text-end fw-bold">Grand Total</td>
                <td class="fw-bold">{{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('penjualan.index') }}" class="btn btn-secondary mt-2">Back</a>
</div>
@endsection