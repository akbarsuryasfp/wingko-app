@extends('layouts.app')
@section('content')
<div class="container">
    <h3>Laporan Perubahan Ekuitas</h3>
    <form method="get" action="{{ route('laporan.perubahan_ekuitas') }}" class="mb-3">
        <label>Pilih Periode:</label>
        <input type="month" name="periode" value="{{ $periode }}" required>
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
    </form>
    <table class="table table-bordered">
        <tr><td>Modal Awal</td><td>Rp {{ number_format($modal_awal,0,',','.') }}</td></tr>
        <tr><td>Tambahan Modal</td><td>Rp {{ number_format($tambahan_modal,0,',','.') }}</td></tr>
        <tr><td>Laba Bersih</td><td>Rp {{ number_format($laba_bersih,0,',','.') }}</td></tr>
        <tr><td>Prive</td><td>Rp {{ number_format($prive,0,',','.') }}</td></tr>
        <tr style="font-weight:bold;"><td>Modal Akhir</td><td>Rp {{ number_format($modal_akhir,0,',','.') }}</td></tr>
    </table>
</div>
@endsection