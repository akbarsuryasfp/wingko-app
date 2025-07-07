@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="mb-4">Buku Besar</h3>
    <form method="get" class="row g-3 mb-3 align-items-center">
        <div class="col-md-4">
            <label for="kode_akun" class="form-label mb-0">Pilih Akun:</label>
            <select name="kode_akun" id="kode_akun" class="form-select" onchange="this.form.submit()">
                <option value="">-- Pilih Akun --</option>
                @foreach($akuns as $akun)
                    <option value="{{ $akun->kode_akun }}" {{ $kode_akun == $akun->kode_akun ? 'selected' : '' }}>
                        {{ $akun->kode_akun }} - {{ $akun->nama_akun }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6 ms-auto d-flex justify-content-end">
            <input type="text" name="q" class="form-control me-2" style="max-width:260px" placeholder="Cari keterangan / no jurnal" value="{{ request('q') }}">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filter</button>
        </div>
    </form>
    @if($kode_akun)
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>No Jurnal</th>
                    <th>Keterangan</th>
                    <th>Debit</th>
                    <th>Kredit</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                @php $saldo = 0; @endphp
                @foreach($mutasi as $row)
                    @php
                        $saldo += $row->debit - $row->kredit;
                    @endphp
                    <tr>
                        <td>{{ $row->jurnalUmum->tanggal ?? '' }}</td>
                        <td>{{ $row->no_jurnal }}</td>
                        <td>{{ $row->jurnalUmum->keterangan ?? '' }}</td>
                        <td>{{ number_format($row->debit, 2) }}</td>
                        <td>{{ number_format($row->kredit, 2) }}</td>
                        <td>{{ number_format($saldo, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection