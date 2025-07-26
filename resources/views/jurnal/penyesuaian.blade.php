@extends('layouts.app')
@section('content')
<div class="container">
    <h3 class="mb-4">Jurnal Penyesuaian</h3>
    <form method="get" class="row g-3 mb-3">
        <div class="col-md-2">
            <input type="date" name="tanggal_awal" class="form-control" value="{{ request('tanggal_awal') }}">
        </div>
        <div class="col-auto d-flex align-items-center">
            <span class>s/d</span>
        </div>
        <div class="col-md-2">
            <input type="date" name="tanggal_akhir" class="form-control" value="{{ request('tanggal_akhir') }}">
        </div>
        <div class="col-md-6 ms-auto">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Cari keterangan / no jurnal" value="{{ request('q') }}">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>No Jurnal</th>
                    <th>Keterangan</th>
                    <th>Nomor Bukti</th>
                    <th>Akun</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jurnals as $jurnal)
                    @foreach($jurnal->details as $detail)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d-m-Y') }}</td>
                        <td>{{ $jurnal->no_jurnal }}</td>
                        <td>{{ $jurnal->keterangan }}</td>
                        <td>{{ $jurnal->nomor_bukti }}</td>
                        <td>{{ $detail->akun->nama_akun ?? '-' }}</td>
                        <td class="text-end">{{ number_format($detail->debit, 2) }}</td>
                        <td class="text-end">{{ number_format($detail->kredit, 2) }}</td>
                    </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Data tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection