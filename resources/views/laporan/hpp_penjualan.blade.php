{{-- resources/views/laporan/hpp_penjualan.blade.php --}}
@extends('layouts.app')

@section('title', 'Laporan Harga Pokok Penjualan')

@section('content')
<div class="container mt-4">
    <div class="text-center mb-4">
        <h4 class="fw-bold">LAPORAN HARGA POKOK PENJUALAN</h4>
        <h5>UMKM Manufaktur Kue Wingko</h5>
        <p>Periode: {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y') }}</p>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-primary">
                <tr>
                    <th>Keterangan</th>
                    <th class="text-end">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Persediaan Awal Barang Jadi</td>
                    <td class="text-end">{{ number_format($persediaan_awal_jadi, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>+ Harga Pokok Produksi</td>
                    <td class="text-end">{{ number_format($harga_pokok_produksi, 0, ',', '.') }}</td>
                </tr>
                <tr class="table-light fw-bold">
                    <td>= Barang Jadi Tersedia untuk Dijual</td>
                    <td class="text-end">
                        {{ number_format(($persediaan_awal_jadi ?? 0) + ($harga_pokok_produksi ?? 0), 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td>- Persediaan Akhir Barang Jadi</td>
                    <td class="text-end">{{ number_format($persediaan_akhir_jadi, 0, ',', '.') }}</td>
                </tr>
                <tr class="table-success fw-bold">
                    <td>= Harga Pokok Penjualan</td>
                    <td class="text-end">
                        {{ number_format($hpp_penjualan, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <p><strong>Dibuat oleh:</strong> {{ auth()->user()->name ?? 'Admin' }}</p>
        <p><small>Tanggal cetak: {{ now()->format('d/m/Y H:i') }}</small></p>
    </div>
</div>
@endsection
