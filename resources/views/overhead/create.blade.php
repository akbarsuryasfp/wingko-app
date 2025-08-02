@extends('layouts.app')
@section('content')
<div class="container py-3">
    <h4 class="mb-3">Input Overhead Aktual Bulanan</h4>
    @if(session('alert'))
        <div class="alert alert-warning">
            {{ session('alert') }}
        </div>
    @endif
    @if($errors->has('periode'))
        <div class="alert alert-danger">
            {{ $errors->first('periode') }}
        </div>
    @endif
    <form action="{{ route('overhead.store') }}" method="POST">
        @csrf
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Bulan</label>
                <div class="d-flex gap-2">
                    <select name="bulan" id="bulan" class="form-control" style="width:auto;">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ sprintf('%02d', $m) }}"
                                {{ $bulan == sprintf('%02d', $m) ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                    <select name="tahun" id="tahun" class="form-control" style="width:auto;">
                        @for ($y = date('Y')-5; $y <= date('Y')+5; $y++)
                            <option value="{{ $y }}"
                                {{ $tahun == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <input type="hidden" name="periode" value="{{ $tahun }}-{{ $bulan }}">
            </div>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Jenis Overhead</th>
                    <th>Jumlah (Rp)</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bopList as $i => $o)
                <tr>
                    <td>
                        {{ $o['nama_bop'] }}
                        <input type="hidden" name="bop[{{ $i }}][kode_bop]" value="{{ $o['kode_bop'] }}">
                    </td>
                    <td>
                        <input type="number" class="form-control" name="bop[{{ $i }}][jumlah]" min="0" step="0.01" value="{{ $o['jumlah'] ?? '' }}">
                    </td>
                    <td>
                        <input type="text" class="form-control" name="bop[{{ $i }}][keterangan]" value="{{ $o['keterangan'] ?? '' }}">
                    </td>
                </tr>
                @endforeach
               
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Simpan Overhead Aktual</button>
    </form>
</div>

@endsection