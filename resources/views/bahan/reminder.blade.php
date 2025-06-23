@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>🔔 Reminder Bahan Kadaluarsa</h4>
    <table class="table table-bordered mt-3">
        <thead class="table-warning">
            <tr>
                <th>No</th>
                <th>Nama Bahan</th>
                <th>Tanggal Expired</th>
                <th>Jumlah Sisa</th>
                <th>Harga Beli</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        @forelse($data as $i => $row)
            @php
                $keluar = \DB::table('t_kartupersbahan')
                    ->where('kode_bahan', $row->kode_bahan)
                    ->where('harga', $row->harga_beli)
                    ->where('tanggal_exp', $row->tanggal_exp ?? null)
                    ->sum('keluar');
                $sisa = $row->bahan_masuk - $keluar;
                $exp = \Carbon\Carbon::parse($row->tanggal_exp);
                $today = \Carbon\Carbon::today();
                $diff = $today->diffInDays($exp, false);
            @endphp
            <tr class="{{ $diff <= 0 ? 'table-danger' : ($diff <= 7 ? 'table-warning' : '') }}">
                <td>{{ $i+1 }}</td>
                <td>{{ $row->nama_bahan }}</td>
                <td>{{ $exp->format('d M Y') }}</td>
                <td>{{ $sisa }}</td>
                <td>Rp{{ number_format($row->harga_beli,0,',','.') }}</td>
                <td>
                    @if($diff < 0)
                        <span class="badge bg-danger">Kadaluarsa</span>
                    @elseif($diff == 0)
                        <span class="badge bg-warning text-dark">Expired Hari Ini</span>
                    @elseif($diff <= 7)
                        <span class="badge bg-warning text-dark">H-{{ $diff }}</span>
                    @else
                        <span class="badge bg-success">Aman</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">Tidak ada bahan kadaluarsa atau mendekati expired.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection