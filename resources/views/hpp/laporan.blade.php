@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Laporan Harga Pokok Produksi (HPP) Bulanan</h3>
    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <select name="bulan" class="form-select">
                @foreach(range(1,12) as $b)
                    <option value="{{ sprintf('%02d',$b) }}" @selected($bulan == sprintf('%02d',$b))>{{ DateTime::createFromFormat('!m', $b)->format('F') }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="tahun" class="form-select">
                @for($y = date('Y')-3; $y <= date('Y'); $y++)
                    <option value="{{ $y }}" @selected($tahun == $y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary">Tampilkan</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Komponen</th>
                <th class="text-end">Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="table-secondary">
                <th colspan="2">Bahan Baku Langsung</th>
            </tr>
            <tr>
                <td>+ Pembelian Bahan Baku</td>
                <td class="text-end">Rp {{ number_format($pembelian_bahan ?? 0,0,',','.') }}</td>
            </tr>
            <tr>
                <td>+ Persediaan Awal Bahan Baku</td>
                <td class="text-end">Rp {{ number_format($persediaan_awal ?? 0,0,',','.') }}</td>
            </tr>   
            <tr>
                <td>- Persediaan Akhir Bahan Baku</td>
                <td class="text-end">Rp {{ number_format($persediaan_akhir ?? 0,0,',','.') }}</td>
            </tr>
            <tr class="fw-bold">
                <td>= Bahan Baku yang Digunakan</td>
                <td class="text-end">
                    Rp {{ number_format(($pembelian_bahan ?? 0) + ($persediaan_awal ?? 0) - ($persediaan_akhir ?? 0),0,',','.') }}
                </td>
            </tr>
            <tr class="table-secondary">
                <th colspan="2">Tenaga Kerja Langsung</th>
            </tr>
            <tr>
                <td>Biaya Tenaga Kerja Langsung</td>
                <td class="text-end">Rp {{ number_format($total_tk ?? 0,0,',','.') }}</td>
            </tr>
            <tr class="table-secondary">
                <th colspan="2">Biaya Overhead Pabrik</th>
            </tr>
            <tr>
                <td>Biaya Overhead Pabrik</td>
                <td class="text-end">Rp {{ number_format($total_overhead ?? 0,0,',','.') }}</td>
            </tr>
            <tr class="fw-bold table-info">
                <td>= Total Biaya Produksi</td>
                <td class="text-end">
                    Rp {{ number_format(
                        (($pembelian_bahan ?? 0) + ($persediaan_awal ?? 0) - ($persediaan_akhir ?? 0)) + ($total_tk ?? 0) + ($total_overhead ?? 0)
                    ,0,',','.') }}
                </td>
            </tr>
            {{-- Jika ada persediaan awal/akhir barang dalam proses, tambahkan di sini --}}
            {{-- 
            <tr>
                <td>+ Persediaan Awal Barang Dalam Proses</td>
                <td class="text-end">Rp {{ number_format($persediaan_awal_proses ?? 0,0,',','.') }}</td>
            </tr>
            <tr>
                <td>- Persediaan Akhir Barang Dalam Proses</td>
                <td class="text-end">Rp {{ number_format($persediaan_akhir_proses ?? 0,0,',','.') }}</td>
            </tr>
            --}}
            <tr class="fw-bold table-primary">
                <td>= Harga Pokok Produksi</td>
                <td class="text-end">
                    <b>
                    Rp {{ number_format(
                        (($pembelian_bahan ?? 0) + ($persediaan_awal ?? 0) - ($persediaan_akhir ?? 0)) + ($total_tk ?? 0) + ($total_overhead ?? 0)
                    ,0,',','.') }}
                    </b>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection