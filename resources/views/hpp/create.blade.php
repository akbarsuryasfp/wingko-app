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
                                        <th>Jumlah</th>
                                        <th>Harga per Unit</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($resep as $i => $r)
                                        @php
                                            $jumlah = $r->jumlah_kebutuhan * $jumlahProduksi;
                                        @endphp
                                        <tr>
                                            <td>
                                                {{ $r->bahan->nama_bahan }}
                                                <input type="hidden" name="bahan[{{ $i }}][kode_bahan]" value="{{ $r->kode_bahan }}">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="bahan[{{ $i }}][jumlah]" value="{{ $jumlah }}" step="0.01" min="0">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="bahan[{{ $i }}][harga]" step="0.01" min="0" value="{{ $r->bahan->harga_satuan ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="bahan[{{ $i }}][total]" readonly>
                                            </td>
                                        </tr>
                                    @endforeach
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
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- TAB OVERHEAD --}}
                    <div class="tab-pane fade" id="overhead" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Jenis Overhead</th>
                                        <th>Biaya</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bop as $i => $o)
                                        <tr>
                                            <td>
                                                {{ $o->nama_bop }}
                                                <input type="hidden" name="overhead[{{ $i }}][kode_bop]" value="{{ $o->kode_bop }}">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control" name="overhead[{{ $i }}][biaya]" step="0.01" min="0">
                                            </td>
                                        </tr>
                                    @endforeach
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
    });
    // Event delegation untuk tab tenaga kerja
    document.querySelector('#tenaga').addEventListener('input', function(e) {
        if (e.target.matches('input[name*="[jam]"], input[name*="[tarif]"]')) {
            hitungTotalTK();
        }
    });

    // Hitung awal saat halaman load
    hitungTotalBahan();
    hitungTotalTK();
});
</script>
@endpush
