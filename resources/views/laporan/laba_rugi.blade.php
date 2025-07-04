{{-- filepath: resources/views/laporan/laba_rugi.blade.php --}}
@extends('layouts.app')
@section('content')
<div class="container">
    <h3>Laporan Laba Rugi</h3>
    <form method="get" action="{{ route('laporan.laba_rugi') }}" class="mb-3">
        <label>Pilih Periode:</label>
        <input type="month" name="periode" value="{{ $periode }}" required>
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
    </form>
    <table class="table table-bordered">
        <tr><td>Pendapatan Penjualan</td><td>Rp {{ number_format($saldo['penjualan'] ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td>Harga Pokok Penjualan</td><td>Rp {{ number_format($saldo['hpp'] ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td>Beban Operasional</td><td>Rp {{ number_format($saldo['beban'] ?? 0, 0, ',', '.') }}</td></tr>
        <tr style="font-weight:bold;">
            <td>Laba Bersih</td>
            <td>Rp {{ number_format($laba_bersih, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>
@endsection