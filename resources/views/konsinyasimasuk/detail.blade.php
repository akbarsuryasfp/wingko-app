@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4>DETAIL KONSINYASI MASUK</h4>
            <table class="table">
        <tr>
            <th>No Konsinyasi Masuk</th>
            <td>{{ $konsinyasi->no_konsinyasimasuk }}</td>
        </tr>
        <tr>
            <th>No Surat Titip Jual</th>
            <td>{{ $konsinyasi->no_surattitipjual }}</td>
        </tr>
        <tr>
            <th>Tanggal Masuk</th>
            <td>{{ $konsinyasi->tanggal_masuk }}</td>
        </tr>
        <tr>
            <th>Nama Consignor (Pemilik Barang)</th>
            <td>{{ $konsinyasi->consignor->nama_consignor ?? '-' }}</td>
        </tr>
        <tr>
            <th>Total Titip</th>
            <td>Rp{{ number_format($konsinyasi->total_titip,0,',','.') }}</td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td>{{ $konsinyasi->keterangan ?? '-' }}</td>
        </tr>
    </table>

            <h5 class="text-center">DETAIL PRODUK KONSINYASI MASUK</h5>
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Nama Produk</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Jumlah Stok</th>
                        <th class="text-center">Harga Titip/Satuan</th>
                        <th class="text-center">Harga Jual/Satuan</th>
                        <th class="text-center">Subtotal Titip</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $i => $d)
                    <tr>
                        <td class="text-center">{{ $i+1 }}</td>
                        <td class="text-center">{{ $d->nama_produk ?? '-' }}</td>
                        <td class="text-center">{{ $d->satuan ?? '-' }}</td>
                        <td class="text-center">{{ $d->jumlah_stok }}</td>
                        <td class="text-center">Rp{{ number_format($d->harga_titip,0,',','.') }}</td>
                        <td class="text-center">Rp{{ number_format($d->harga_jual,0,',','.') }}</td>
                        <td class="text-center">Rp{{ number_format($d->subtotal,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Total Titip</td>
                        <td class="fw-bold text-center">Rp{{ number_format($konsinyasi->total_titip,0,',','.') }}</td>
                    </tr>
                </tbody>
            </table>
            <a href="{{ route('konsinyasimasuk.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection