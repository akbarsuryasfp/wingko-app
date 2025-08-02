@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4>DETAIL RETUR PENJUALAN</h4>
            <table class="table">
                <tr>
                    <th>No Retur Jual</th>
                    <td>{{ $returjual->no_returjual }}</td>
                </tr>
                <tr>
                    <th>No Jual</th>
                    <td>{{ $returjual->no_jual }}</td>
                </tr>
                <tr>
                    <th>Tanggal Retur</th>
                    <td>{{ $returjual->tanggal_returjual }}</td>
                </tr>
                <tr>
                    <th>Nama Pelanggan</th>
                    <td>{{ $returjual->nama_pelanggan ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Jenis Retur</th>
                    <td>{{ $returjual->jenis_retur ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Total Retur</th>
                    <td>Rp{{ number_format($returjual->total_nilai_retur,0,',','.') }}</td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td>{{ $returjual->keterangan ?? '-' }}</td>
                </tr>
            </table>

            <h5 class="text-center">DETAIL PRODUK RETUR PENJUALAN</h5>
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Nama Produk</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Jumlah Retur</th>
                        <th class="text-center">Harga/Satuan</th>
                        <th class="text-center">Alasan</th>
                        <th class="text-center">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $i => $d)
                    <tr>
                        <td class="text-center align-middle">{{ $i+1 }}</td>
                        <td class="text-center align-middle">{{ $d->nama_produk ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $d->satuan ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $d->jumlah_retur }}</td>
                        <td class="text-center align-middle">Rp{{ number_format($d->harga_satuan,0,',','.') }}</td>
                        <td class="text-center align-middle">{{ $d->alasan }}</td>
                        <td class="text-center align-middle">Rp{{ number_format($d->subtotal,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total Retur</td>
                        <td class="text-center fw-bold">Rp{{ number_format($returjual->total_nilai_retur,0,',','.') }}</td>
                    </tr>
                </tbody>
            </table>
            <a href="{{ route('returjual.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection