@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:700px;">
    <h5 class="fw-bold mb-4">
        DETAIL PIUTANG
    </h5>
    <div class="border rounded p-4 bg-white">
        <table class="table table-borderless mb-0">
            <tbody>
                <tr>
                    <th style="width:180px;">No Piutang</th>
                    <td>{{ $piutang->no_piutang }}</td>
                </tr>
                <tr>
                    <th>No Penjualan</th>
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
                    <th>Status</th>
                    <td>
                        @if($piutang->status_piutang == 'lunas')
                            Lunas
                        @else
                            Belum Lunas
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Daftar Pembayaran Piutang --}}
        @if(isset($pembayaran) && count($pembayaran) > 0)
        <div class="mt-4">
            <strong>Daftar Pembayaran Piutang:</strong>
            <table class="table table-sm mt-2">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No BKM</th>
                        <th>Tanggal</th>
                        <th>Nominal</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pembayaran as $i => $bayar)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td>{{ $bayar->no_bkm }}</td>
                        <td>{{ \Carbon\Carbon::parse($bayar->tanggal)->format('Y-m-d') }}</td>
                        <td>Rp{{ number_format($bayar->nominal,0,',','.') }}</td>
                        <td>{{ $bayar->keterangan }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('piutang.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection