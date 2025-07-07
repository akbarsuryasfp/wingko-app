{{-- filepath: resources/views/laporan/keuangan.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Laporan Keuangan</h3>
    <form method="get" action="{{ route('laporan.keuangan') }}" class="mb-3">
        <label>Pilih Periode:</label>
        <input type="month" name="periode" value="{{ $periode }}" required>
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
        <a href="{{ route('laporan.keuangan.cetak', ['periode' => $periode]) }}" target="_blank" class="btn btn-success btn-sm">Cetak</a>
    </form>

    <h5>Neraca</h5>
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

    <h5>Laba Rugi</h5>
    <table class="table table-bordered">
        <tr><td>Pendapatan Penjualan</td><td>Rp {{ number_format($saldo['penjualan'] ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td>Harga Pokok Penjualan</td><td>Rp {{ number_format($saldo['hpp'] ?? 0, 0, ',', '.') }}</td></tr>
        <tr><td>Beban Operasional</td><td>Rp {{ number_format($saldo['beban'] ?? 0, 0, ',', '.') }}</td></tr>
        <tr style="font-weight:bold;">
            <td>Laba Bersih</td>
            <td>
                Rp {{ number_format(($saldo['penjualan'] ?? 0) - ($saldo['hpp'] ?? 0) - ($saldo['beban'] ?? 0), 0, ',', '.') }}
            </td>
        </tr>
    </table>
</div>
@endsection