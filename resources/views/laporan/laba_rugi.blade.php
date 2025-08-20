@extends('layouts.app')
@section('content')
<div class="container">
    <h3>Laporan Laba Rugi</h3>
    <form method="get" action="{{ route('laporan.laba_rugi') }}" class="mb-3">
        <label>Pilih Periode:</label>
        <input type="month" name="periode" value="{{ $periode }}" required>
        <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
        <a href="{{ route('laporan.laba_rugi.pdf', ['periode' => $periode]) }}" target="_blank" class="btn btn-success btn-sm">Cetak</a>

    </form>
    <div class="text-center mb-4">
        <h4 class="fw-bold">LAPORAN LABA RUGI</h4>
        <h5>Wingko Pratama</h5>
        <p>Periode: {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}</p>
    </div>
    <table class="table table-bordered">
        <tr class="fw-bold bg-light"><td colspan="2">Pendapatan Usaha</td></tr>
        <tr>
            <td>Pendapatan Penjualan</td>
            <td>Rp {{ number_format($penjualan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Pendapatan Lain-lain</td>
            <td>Rp {{ number_format($pendapatan_lain, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Retur Penjualan</td>
            <td>(Rp {{ number_format($retur_penjualan, 0, ',', '.') }})</td>
        </tr>
        <tr>
            <td>Diskon Penjualan</td>
            <td>(Rp {{ number_format($diskon_penjualan, 0, ',', '.') }})</td>
        </tr>
        <tr class="fw-bold">
            <td>Pendapatan Bersih</td>
            <td>Rp {{ number_format($pendapatan_bersih, 0, ',', '.') }}</td>
        </tr>
        <tr class="fw-bold bg-light"><td colspan="2">Harga Pokok Penjualan</td></tr>
        <tr>
            <td>Harga Pokok Penjualan</td>
            <td>(Rp {{ number_format($hpp, 0, ',', '.') }})</td>
        </tr>
        <tr class="fw-bold">
            <td>Laba Kotor</td>
            <td>Rp {{ number_format($laba_kotor, 0, ',', '.') }}</td>
        </tr>
        <tr class="fw-bold bg-light"><td colspan="2">Beban Operasional</td></tr>
        @foreach($list_beban as $beban)
        <tr>
            <td>{{ $beban['nama'] }}</td>
            <td>(Rp {{ number_format($beban['saldo'], 0, ',', '.') }})</td>
        </tr>
        @endforeach
        <tr class="fw-bold">
            <td>Total Beban Operasional</td>
            <td>(Rp {{ number_format($beban_operasional, 0, ',', '.') }})</td>
        </tr>
        <tr class="fw-bold">
            <td>Laba Usaha</td>
            <td>Rp {{ number_format($laba_usaha, 0, ',', '.') }}</td>
        </tr>
        <tr class="fw-bold bg-success text-white">
            <td>Laba Bersih</td>
            <td>Rp {{ number_format($laba_bersih, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>
@endsection