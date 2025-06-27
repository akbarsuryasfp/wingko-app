@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Selamat Datang</h4>
    <div class="alert alert-info mt-3">
        Selamat datang di Sistem Informasi Akuntansi Pratama.<br>
        Silakan gunakan menu di samping untuk mengelola data.
    </div>

@php
    $kadaluarsa = collect($reminder)->filter(fn($r) => \Carbon\Carbon::parse($r->tanggal_exp)->isPast());
    $hampir = collect($reminder)->filter(function ($r) {
        $diff = \Carbon\Carbon::today()->diffInDays(\Carbon\Carbon::parse($r->tanggal_exp), false);
        return $diff > 0 && $diff <= 6;
    });
    $grouped = $kadaluarsa->groupBy('nama_bahan');
@endphp

<div class="row g-3">

    <!-- Box Kadaluarsa -->
    <div class="col-md-6">
        <div class="card border-danger shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title text-danger">
                    {{ $kadaluarsa->count() }} Bahan Kadaluarsa
                </h5>
                <button class="btn btn-outline-danger btn-sm mt-2" onclick="toggleBox('kadaluarsaTable')">
                    Lihat Daftar
                </button>
                <div id="kadaluarsaTable" class="mt-3 d-none">
                    <div class="d-flex justify-content-end mb-2">
                        <a href="{{ route('penyesuaian.exp') }}" class="btn btn-danger btn-sm">
                            Penyesuaian
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-danger text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Bahan</th>
                                    <th>Tanggal Exp</th>
                                    <th>Jumlah Stok Kadaluarsa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($grouped as $nama => $items)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $nama }}</td>
                                        <td>
                                            @foreach ($items as $item)
                                                {{ \Carbon\Carbon::parse($item->tanggal_exp)->format('d M Y') }}@if(!$loop->last), @endif
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            {{ $items->sum('stok') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Tidak ada</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Box Hampir Kadaluarsa -->
    <div class="col-md-6">
        <div class="card border-warning shadow-sm">
            <div class="card-body text-center">
                <h5 class="card-title text-warning">
                    {{ $hampir->count() }} Bahan Hampir Kadaluarsa
                </h5>
                <button class="btn btn-outline-warning btn-sm mt-2" onclick="toggleBox('hampirTable')">
                    Lihat Daftar
                </button>
                <div id="hampirTable" class="mt-3 d-none">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-warning text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Bahan</th>
                                    <th>Tanggal Exp</th>
                                    <th>H-?</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($hampir as $index => $item)
                                    @php
                                        $exp = \Carbon\Carbon::parse($item->tanggal_exp);
                                        $diff = \Carbon\Carbon::today()->diffInDays($exp);
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $item->nama_bahan }}</td>
                                        <td class="text-center">{{ $exp->format('d M Y') }}</td>
                                        <td class="text-center">H-{{ $diff }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted">Tidak ada</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Script toggle -->
<script>
function toggleBox(id) {
    const box = document.getElementById(id);
    box.classList.toggle('d-none');
}
</script>

@endsection