{{-- filepath: resources/views/konsinyasimasuk/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">DAFTAR KONSINYASI MASUK</h4>
    <div class="mb-3">
        <a href="{{ route('konsinyasimasuk.create') }}" class="btn btn-primary">Tambah Data</a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 20%;">Kode Titip Jual</th>
                        <th style="width: 20%;">Tanggal Masuk</th>
                        <th style="width: 25%;">Nama Consignor (Pemilik Barang)</th>
                        <th style="width: 20%;">Total Titip Jual</th>
                        <th style="width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($konsinyasiMasukList as $i => $konsinyasi)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $konsinyasi->no_surattitipjual }}</td>
                            <td>{{ \Carbon\Carbon::parse($konsinyasi->tanggal_masuk)->format('d-m-Y') }}</td>
                            <td>{{ $konsinyasi->consignor->nama_consignor ?? '-' }}</td>
                            <td>
                                @php
                                    $total = $konsinyasi->details->sum(function($d) {
                                        return $d->jumlah_stok * $d->harga_titip;
                                    });
                                @endphp
                                Rp{{ number_format($total, 0, ',', '.') }}
                            </td>
                            <td>
                                <a href="{{ route('konsinyasimasuk.show', $konsinyasi->no_surattitipjual) }}" class="btn btn-info btn-sm">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Data tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection