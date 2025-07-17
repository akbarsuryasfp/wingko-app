@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>DETAIL PENERIMAAN KONSINYASI</h4>
    <table class="table">
        <tr>
            <th>No Penerimaan Konsinyasi</th>
            <td>{{ $header->no_penerimaankonsinyasi ?? '-' }}</td>
        </tr>
        <tr>
            <th>No Konsinyasi Keluar</th>
            <td>{{ $header->no_konsinyasikeluar ?? '-' }}</td>
        </tr>
        <tr>
            <th>Nama Consignee (Mitra)</th>
            <td>{{ $header->consignee->nama_consignee ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tanggal Terima</th>
            <td>{{ $header->tanggal_terima ? \Carbon\Carbon::parse($header->tanggal_terima)->format('d-m-Y') : '-' }}</td>
        </tr>
        <tr>
            <th>Metode Pembayaran</th>
            <td>{{ $header->metode_pembayaran ?? '-' }}</td>
        </tr>
        <tr>
            <th>Total Terima</th>
            <td>Rp{{ number_format($header->total_terima,0,',','.') }}</td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td>{{ $header->keterangan ?? '-' }}</td>
        </tr>
    </table>

    <h5 class="text-center">DETAIL PRODUK PENERIMAAN KONSINYASI</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah Setor</th>
                <th>Jumlah Terjual</th>
                <th>Satuan</th>
                <th>Harga/Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($header->details as $i => $d)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $d->produk->nama_produk ?? $d->nama_produk ?? '-' }}</td>
                <td>{{ $d->jumlah_setor }}</td>
                <td>{{ $d->jumlah_terjual }}</td>
                <td>{{ $d->satuan }}</td>
                <td>Rp{{ number_format($d->harga_satuan,0,',','.') }}</td>
                <td>Rp{{ number_format($d->subtotal,0,',','.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada detail produk</td>
            </tr>
            @endforelse
            <tr>
                <td colspan="6" class="text-end fw-bold">Total Terima</td>
                <td class="fw-bold">Rp{{ number_format($header->total_terima,0,',','.') }}</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('penerimaankonsinyasi.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
