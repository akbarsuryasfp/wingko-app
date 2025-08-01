@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4>DETAIL PIUTANG</h4>
            <table class="table">
                <tr>
                    <th style="width:220px;">No Piutang</th>
                    <td>{{ $piutang->no_piutang }}</td>
                </tr>
                <tr>
                    <th>No Jual</th>
                    <td>{{ $piutang->no_jual }}</td>
                </tr>
                <tr>
                    <th>Pelanggan</th>
                    <td>{{ $pelanggan ? $pelanggan->nama_pelanggan : '-' }}</td>
                </tr>
                <tr>
                    <th>Total Tagihan</th>
                    <td>Rp{{ number_format($piutang->total_tagihan,0,',','.') }}</td>
                </tr>
                <tr>
                    <th>Total Bayar</th>
                    <td>Rp{{ number_format($piutang->total_bayar,0,',','.') }}</td>
                </tr>
                <tr>
                    <th>Sisa Piutang</th>
                    <td>Rp{{ number_format($piutang->sisa_piutang,0,',','.') }}</td>
                </tr>
                <tr>
                    <th>Status Pembayaran</th>
                    <td>
                        @if($piutang->status_piutang == 'lunas')
                            Lunas
                        @else
                            Belum Lunas
                        @endif
                    </td>
                </tr>
            </table>

            <h5 class="text-center">DAFTAR PEMBAYARAN PIUTANG</h5>
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">No BKM</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Nominal</th>
                        <th class="text-center">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($pembayaran) && count($pembayaran) > 0)
                        @foreach($pembayaran as $i => $bayar)
                        <tr>
                            <td class="text-center">{{ $i+1 }}</td>
                            <td class="text-center">{{ $bayar->no_bkm }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($bayar->tanggal)->format('Y-m-d') }}</td>
                            <td class="text-center">Rp{{ number_format($bayar->nominal,0,',','.') }}</td>
                            <td class="text-center">{{ $bayar->keterangan }}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total Bayar</td>
                            <td class="fw-bold text-center">Rp{{ number_format($piutang->total_bayar,0,',','.') }}</td>
                            <td></td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="5" class="text-center">Belum ada pembayaran</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <a href="{{ route('piutang.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection