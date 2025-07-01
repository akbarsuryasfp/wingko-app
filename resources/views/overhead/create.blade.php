@extends('layouts.app')
@section('content')
<div class="container py-3">
    <h4 class="mb-3">Input Overhead Aktual Bulanan</h4>
    <form action="{{ route('overhead.store') }}" method="POST">
        @csrf
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Bulan</label>
                <div class="d-flex gap-2">
                    <select id="bulan" class="form-control" style="width:auto;">
                        <option value="">Bulan</option>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ sprintf('%02d', $m) }}">{{ DateTime::createFromFormat('!m', $m)->format('F') }}</option>
                        @endfor
                    </select>
                    <select id="tahun" class="form-control" style="width:auto;">
                        <option value="">Tahun</option>
                        @for ($y = date('Y')-5; $y <= date('Y')+5; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <input type="hidden" name="periode" id="periode" required>
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
                @foreach($bop as $i => $o)
                <tr>
                    <td>
                        {{ $o->nama_bop }}
                        <input type="hidden" name="bop[{{ $i }}][kode_bop]" value="{{ $o->kode_bop }}">
                    </td>
                    <td>
                        <input type="number" class="form-control" name="bop[{{ $i }}][jumlah]" min="0" step="0.01">
                    </td>
                    <td>
                        <input type="text" class="form-control" name="bop[{{ $i }}][keterangan]">
                    </td>
                </tr>
                @endforeach
                <tr>
                    <td>Penyusutan Aset Tetap</td>
                    <td>
                        <input type="number" class="form-control" name="penyusutan_aset_tetap" value="{{ round($totalPenyusutan, 2) }}" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control" value="Otomatis dihitung sistem" readonly>
                    </td>
                </tr>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Simpan Overhead Aktual</button>
    </form>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bulan = document.getElementById('bulan');
    const tahun = document.getElementById('tahun');
    const periode = document.getElementById('periode');

    // Ambil bulan dan tahun sekarang
    const now = new Date();
    const sysBulan = String(now.getMonth() + 1).padStart(2, '0');
    const sysTahun = String(now.getFullYear());

    // Set value awal jika belum dipilih
    if (!bulan.value) bulan.value = sysBulan;
    if (!tahun.value) tahun.value = sysTahun;

    function updatePeriode() {
        if (bulan.value && tahun.value) {
            periode.value = tahun.value + '-' + bulan.value;
        } else {
            periode.value = '';
        }
    }

    // Set periode awal
    updatePeriode();

    bulan.addEventListener('change', updatePeriode);
    tahun.addEventListener('change', updatePeriode);
});
</script>
@endpush
@endsection