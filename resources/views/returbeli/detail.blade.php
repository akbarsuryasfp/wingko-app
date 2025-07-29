@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Detail Retur Pembelian</h4>
        </div>
        
        <div class="card-body">
            <table class="table table-borderless">
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

            <h5 class="mt-4">Detail Barang Retur</h5>
            <div class="table-responsive">
<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th class="text-center align-middle" style="width: 50px;">No</th>
            <th class="text-center align-middle">Nama Bahan</th>
            <th class="text-center align-middle" style="width: 180px;">Harga Beli</th>
            <th class="text-center align-middle" style="width: 130px;">Jumlah Retur</th>
            <th class="text-center align-middle" style="width: 180px;">Subtotal</th>
            <th class="text-center align-middle">Alasan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($details as $index => $d)
        <tr>
            <td class="text-center">{{ $index + 1 }}</td>
            <td>{{ $d->nama_bahan }}</td>
            <td class="text-end">Rp {{ number_format($d->harga_beli, 0, ',', '.') }}</td>
            <td class="text-center">{{ $d->jumlah_retur }}</td>
            <td class="text-end">Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
            <td>{{ $d->alasan }}</td>
         </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="text-end fw-bold">Total Retur</td>
            <td></td>
            <td class="text-end fw-bold">Rp {{ number_format($retur->total_retur, 0, ',', '.') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

            <div class="mt-4">
                <a href="{{ route('returbeli.index') }}" class="btn btn-secondary">← Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection