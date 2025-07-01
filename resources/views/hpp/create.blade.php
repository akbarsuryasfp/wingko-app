@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="card shadow mb-4">
        <div class="card-body">
            <h4 class="mb-3 text-primary">
                <i class="bi bi-calculator"></i> Input HPP: 
            </h4>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row mb-4">
                <div class="col-12 col-md-6">
                    <div class="mb-2"><strong>No Detail Produksi:</strong> {{ $detail->no_detail_produksi }}</div>
                    <div class="mb-2"><strong>Produk:</strong> {{ $detail->produk->nama_produk ?? $detail->kode_produk }}</div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-2"><strong>Jumlah Produksi:</strong> {{ $detail->jumlah_unit }}</div>
                    <div class="mb-2"><strong>Tanggal Produksi:</strong> {{ $detail->produksi->tanggal_produksi }}</div>
                </div>
            </div>

            <form action="{{ route('hpp.store') }}" method="POST" id="hppForm">
                @csrf
                <input type="hidden" name="no_detail_produksi" value="{{ $detail->no_detail_produksi }}">

                <ul class="nav nav-tabs mb-3" id="hppTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="bahan-tab" data-bs-toggle="tab" data-bs-target="#bahan" type="button" role="tab">Bahan Baku</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tenaga-tab" data-bs-toggle="tab" data-bs-target="#tenaga" type="button" role="tab">Tenaga Kerja</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="overhead-tab" data-bs-toggle="tab" data-bs-target="#overhead" type="button" role="tab">Overhead</button>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- TAB BAHAN --}}
                    <div class="tab-pane fade show active" id="bahan" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bahan</th>
                                        <th>Total Biaya (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($bahanProduksi) && count($bahanProduksi))
                                        @php $grandTotal = 0; @endphp
                                        @foreach ($bahanProduksi as $i => $b)
                                            @php $grandTotal += $b->total_biaya; @endphp
                                            <tr>
                                                <td>
                                                    {{ $b->nama_bahan }}
                                                    <input type="hidden" name="bahan[{{ $i }}][kode_bahan]" value="{{ $b->kode_bahan }}">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control" name="bahan[{{ $i }}][total]" value="{{ round($b->total_biaya) }}" min="0" step="0.01">
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-info fw-bold">
                                            <td class="text-end">Total</td>
                                            <td>
                                                <input type="number" class="form-control-plaintext fw-bold text-end" id="grandTotalBahan" value="{{ round($grandTotal) }}" readonly tabindex="-1">
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="2" class="text-center">Tidak ada data bahan dari produksi.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB TENAGA KERJA --}}
                    <div class="tab-pane fade" id="tenaga" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Karyawan</th>
                                        <th>Jam Kerja</th>
                                        <th>Tarif/Jam</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $grandTotalTK = 0; @endphp
                                    @foreach ($karyawan as $i => $k)
                                        <tr>
                                            <td>
                                                {{ $k->nama }}
                                                <input type="hidden" name="tk[{{ $i }}][kode_karyawan]" value="{{ $k->kode_karyawan }}">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="tk[{{ $i }}][jam]" step="0.1" min="0">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="tk[{{ $i }}][tarif]" step="0.01" min="0" value="{{ $k->tarif_per_jam ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="tk[{{ $i }}][total]" readonly>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-info fw-bold">
                                        <td colspan="3" class="text-end">Total</td>
                                        <td>
                                            <input type="number" class="form-control-plaintext fw-bold text-end" id="grandTotalTK" value="0" readonly tabindex="-1">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB OVERHEAD --}}
                    <div class="tab-pane fade" id="overhead" role="tabpanel">
                        <div class="alert alert-info mb-2">
                            Overhead diisi otomatis dari estimasi bulan sebelumnya (Rp{{ number_format($tarif_bop_per_unit,0) }} x {{ $detail->jumlah_unit }} unit).
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Jenis Overhead</th>
                                        <th>Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Overhead Pabrik (Estimasi)</td>
                                        <td>
                                            <input type="number" class="form-control" name="overhead[0][biaya]"
                                                value="{{ old('overhead.0.biaya', $tarif_bop_per_unit * $detail->jumlah_unit) }}" min="0" step="0.01" id="overheadBiaya">
                                            <input type="hidden" name="overhead[0][kode_bop]" value="ESTIMASI">
                                        </td>
                                    </tr>
                                    <tr class="table-info fw-bold">
                                        <td class="text-end">Total</td>
                                        <td>
                                            <input type="number" class="form-control-plaintext fw-bold text-end" id="grandTotalOverhead" value="{{ old('overhead.0.biaya', $tarif_bop_per_unit * $detail->jumlah_unit) }}" readonly tabindex="-1">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-success btn-lg shadow-sm">
                        <i class="bi bi-save"></i> Simpan HPP
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function hitungGrandTotalBahan() {
    let total = 0;
    document.querySelectorAll('#bahan tbody input[name^="bahan"][name$="[total]"]').forEach(function(input) {
        total += parseFloat(input.value || 0);
    });
    let grand = document.getElementById('grandTotalBahan');
    if (grand) grand.value = Math.round(total);
}
function hitungGrandTotalTK() {
    let total = 0;
    document.querySelectorAll('#tenaga tbody input[name^="tk"][name$="[total]"]').forEach(function(input) {
        total += parseFloat(input.value || 0);
    });
    let grand = document.getElementById('grandTotalTK');
    if (grand) grand.value = Math.round(total);
}
function hitungGrandTotalOverhead() {
    let total = 0;
    document.querySelectorAll('#overhead tbody input[name^="overhead"][name$="[biaya]"]').forEach(function(input) {
        total += parseFloat(input.value || 0);
    });
    let grand = document.getElementById('grandTotalOverhead');
    if (grand) grand.value = Math.round(total);
}
document.addEventListener('DOMContentLoaded', function() {
    function hitungTotalBahan() {
        document.querySelectorAll('#bahan tbody tr').forEach(function(row) {
            let jumlah = row.querySelector('input[name*="[jumlah]"]');
            let harga = row.querySelector('input[name*="[harga]"]');
            let total = row.querySelector('input[name*="[total]"]');
            if (jumlah && harga && total) {
                let hasil = parseFloat(jumlah.value || 0) * parseFloat(harga.value || 0);
                total.value = Math.round(hasil);
            }
        });
    }
    function hitungTotalTK() {
        document.querySelectorAll('#tenaga tbody tr').forEach(function(row) {
            let jam = row.querySelector('input[name*="[jam]"]');
            let tarif = row.querySelector('input[name*="[tarif]"]');
            let total = row.querySelector('input[name*="[total]"]');
            if (jam && tarif && total) {
                let hasil = parseFloat(jam.value || 0) * parseFloat(tarif.value || 0);
                total.value = Math.round(hasil);
            }
        });
    }

    // Event delegation untuk tab bahan
    document.querySelector('#bahan').addEventListener('input', function(e) {
        if (e.target.matches('input[name*="[jumlah]"], input[name*="[harga]"]')) {
            hitungTotalBahan();
        }
        hitungGrandTotalBahan();
    });
    // Event delegation untuk tab tenaga kerja
    document.querySelector('#tenaga').addEventListener('input', function(e) {
        if (e.target.matches('input[name*="[jam]"], input[name*="[tarif]"]')) {
            hitungTotalTK();
        }
        hitungGrandTotalTK();
    });
    document.querySelector('#overhead').addEventListener('input', function(e) {
        if (e.target.matches('input[name*="[biaya]"]')) {
            hitungGrandTotalOverhead();
        }
    });

    // Hitung awal saat halaman load
    hitungTotalBahan();
    hitungTotalTK();
    hitungGrandTotalBahan();
    hitungGrandTotalTK();
    hitungGrandTotalOverhead();
});
</script>
@endpush
