@extends('layouts.app')

@section('content')

<div class="mb-4">
    <h4>Detail Retur Pembelian</h4>
    <table class="table table-borderless" style="max-width: 600px;">
        <tr>
            <th style="width: 200px;">No Retur</th>
            <td>: {{ $retur->no_retur_beli }}</td>
        </tr>
        <tr>
            <th>No Pembelian</th>
            <td>: {{ $retur->no_pembelian }}</td>
        </tr>
        <tr>
            <th>Tanggal Retur</th>
            <td>: {{ \Carbon\Carbon::parse($retur->tanggal_retur_beli)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>Supplier</th>
            <td>: {{ $retur->nama_supplier }}</td>
        </tr>
        <tr>
            <th>Total Retur</th>
            <td>: Rp {{ number_format($retur->total_retur, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Jenis Pengembalian</th>
            <td>: {{ $retur->jenis_pengembalian === 'barang' ? 'Barang Pengganti' : 'Pengembalian Uang' }}</td>
        </tr>
<tr>
    <th>Status</th>
    <td>:
        <span class="badge 
            @if($retur->status == 'pending') bg-warning text-dark 
            @elseif($retur->status == 'selesai') bg-success 
            @else bg-secondary 
            @endif">
            {{ ucfirst($retur->status) }}
        </span>
    </td>
</tr>
        @if(!empty($retur->keterangan))
        <tr>
            <th>Keterangan</th>
            <td>: {{ $retur->keterangan }}</td>
        </tr>
        @endif
    </table>
</div>


    <h5>Detail Barang Retur</h5>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Nama Bahan</th>
                <th>Harga Beli</th>
                <th>Jumlah Retur</th>
                <th>Subtotal</th>
                <th>Alasan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $d)
            <tr>
                <td>{{ $d->nama_bahan }}</td>
                <td>Rp {{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                <td>{{ $d->jumlah_retur }}</td>
                <td>Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
                <td>{{ $d->alasan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        <a href="{{ route('returbeli.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>
@endsection
