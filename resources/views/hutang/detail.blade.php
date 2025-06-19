@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Detail Hutang</h3>
    <table class="table">
        <tr>
            <th>No Utang</th>
            <td>{{ $hutang->no_utang }}</td>
        </tr>
        <tr>
            <th>No Pembelian</th>
            <td>{{ $hutang->no_pembelian }}</td>
        </tr>
        <tr>
            <th>Supplier</th>
            <td>{{ $hutang->kode_supplier }}</td>
        </tr>
        <tr>
            <th>Total Tagihan</th>
            <td>Rp{{ number_format($hutang->total_tagihan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total Bayar</th>
            <td>Rp{{ number_format($hutang->total_bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Sisa Utang</th>
            <td>Rp{{ number_format($hutang->sisa_utang, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if ($hutang->sisa_utang == 0)
                    Lunas
                @else
                    Belum Lunas
                @endif
            </td>
        </tr>
    </table>
    <h5 class="mt-4">Daftar Pembayaran Utang</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>No BKK</th>
                <th>Tanggal</th>
                <th>Nominal</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $pembayaran = \DB::table('t_kaskeluar')
                    ->where('no_referensi', $hutang->no_utang)
                    ->orderBy('tanggal', 'asc')
                    ->get();
            @endphp
            @forelse($pembayaran as $key => $bayar)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $bayar->no_BKK }}</td>
                    <td>{{ $bayar->tanggal }}</td>
                    <td class="text-end">Rp{{ number_format($bayar->jumlah, 0, ',', '.') }}</td>
                    <td>{{ $bayar->keterangan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Belum ada pembayaran</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ route('hutang.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection