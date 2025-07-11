@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Laporan Stok Akhir Bahan per {{ $tanggal }}</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Bahan</th>
                <th>Nama Bahan</th>
                <th>Satuan</th>
                <th>Stok Minimum</th>
                <th>Stok Akhir</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bahanList as $i => $bahan)
            @php
                // Hitung total stok akhir semua harga
                $totalStok = $bahan->stok_akhir->sum('stok');
                $status = $totalStok > $bahan->stokmin ? 'Aman' : 'Perlu Beli';
            @endphp
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $bahan->kode_bahan }}</td>
                <td>{{ $bahan->nama_bahan }}</td>
                <td>{{ $bahan->satuan }}</td>
                <td>{{ $bahan->stokmin }}</td>
                <td>
                    @if($bahan->stok_akhir->count())
                        @foreach($bahan->stok_akhir as $stok)
                            <div>
                                <b>{{ $stok->stok }}</b> {{ $bahan->satuan }} 
                                @if(isset($stok->harga))
                                    @ <b>Rp{{ number_format($stok->harga,0,',','.') }}</b>/{{ $bahan->satuan }}
                                @endif
                            </div>
                        @endforeach
                    @else
                        <span class="text-danger">Kosong</span>
                    @endif
                </td>
                <td>
                    <b>{{ $totalStok }}</b> {{ $bahan->satuan }}
                </td>
                <td>
                    @if($status == 'Aman')
                        <span class="badge bg-success">Aman</span>
                    @else
                        <span class="badge bg-danger">Perlu Beli</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection