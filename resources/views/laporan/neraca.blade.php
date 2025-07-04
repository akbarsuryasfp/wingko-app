{{-- filepath: resources/views/laporan/neraca.blade.php --}}
@extends('layouts.app')
@section('content')
<div class="container">
    <h3>Laporan Neraca</h3>
    <form method="get" action="{{ route('laporan.neraca') }}" class="mb-3">
        <label>Pilih Periode:</label>
        <input type="month" name="periode" value="{{ $periode }}" required>
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
    </form>
    <table class="table table-bordered">
        <tr><th colspan="2">Aset</th></tr>
        <tr><td>Kas</td><td>Rp {{ number_format($saldo['kas'] ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td>Piutang Usaha</td><td>Rp {{ number_format($saldo['piutang'] ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td>Persediaan Barang Jadi</td><td>Rp {{ number_format($saldo['persediaan'] ?? 0, 0, ',', '.') }}</td></tr>
        <tr><th colspan="2">Kewajiban</th></tr>
        <tr><td>Utang Usaha</td><td>Rp {{ number_format($saldo['utang'] ?? 0, 0, ',', '.') }}</td></tr>
        <tr><th colspan="2">Ekuitas</th></tr>
        <tr><td>Modal Pemilik</td><td>Rp {{ number_format($saldo['modal'] ?? 0, 0, ',', '.') }}</td></tr>
    </table>
</div>
@endsection