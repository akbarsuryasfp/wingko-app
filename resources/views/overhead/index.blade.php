
@extends('layouts.app')
@section('content')
<div class="container py-3">
    <h4 class="mb-3">Daftar Overhead (BOP) Aktual</h4>
    <form method="get" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-auto">
                <label>Bulan</label>
                <input type="month" name="periode" class="form-control" value="{{ $periode }}">
            </div>
            <div class="col-auto">
                <button class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Jenis Overhead</th>
                <th>Jumlah (Rp)</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($overheads as $o)
            <tr>
                <td>{{ \Carbon\Carbon::parse($o->periode)->format('F Y') }}</td>
                <td>{{ $o->bop->nama_bop ?? $o->kode_bop }}</td>
                <td class="text-end">{{ number_format($o->jumlah,0) }}</td>
                <td>{{ $o->keterangan }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Belum ada data overhead.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
  
</div>
@endsection