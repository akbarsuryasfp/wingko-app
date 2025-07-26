@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4>DETAIL KONSINYASI KELUAR</h4>
            <table class="table">
        <tr>
            <th>No Konsinyasi Keluar</th>
            <td>{{ $header->no_konsinyasikeluar ?? $header->kode_setor }}</td>
        </tr>
        <tr>
            <th>No Surat Konsinyasi Keluar</th>
            <td>{{ $header->no_suratpengiriman ?? '-' }}</td>
        </tr>
        <tr>
            <th>Nama Consignee (Mitra)</th>
            <td>{{ $header->consignee->nama_consignee ?? '-' }}</td>
        </tr>
        <tr>
            <th>Tanggal Setor</th>
            <td>{{ \Carbon\Carbon::parse($header->tanggal_setor)->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <th>Total Setor</th>
            <td>Rp{{ number_format($header->total_setor,0,',','.') }}</td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td>{{ $header->keterangan ?? '-' }}</td>
        </tr>
    </table>

            <h5 class="text-center">DETAIL PRODUK KONSINYASI KELUAR</h5>
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Satuan</th>
                        <th>Jumlah Setor</th>
                        <th>Harga Setor/Satuan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($header->details as $i => $d)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $d->produk->nama_produk ?? $d->nama_produk ?? '-' }}</td>
                        <td>{{ $d->satuan }}</td>
                        <td>{{ $d->jumlah_setor }}</td>
                        <td>Rp{{ number_format($d->harga_setor,0,',','.') }}</td>
                        <td>Rp{{ number_format($d->subtotal,0,',','.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada detail produk</td>
                    </tr>
                    @endforelse
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Total Setor</td>
                        <td class="fw-bold">Rp{{ number_format($header->total_setor,0,',','.') }}</td>
                    </tr>
                </tbody>
            </table>
            <a href="{{ route('konsinyasikeluar.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
