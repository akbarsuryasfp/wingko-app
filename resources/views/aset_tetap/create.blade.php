@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-sm" style="max-width: 100%;">
                <div class="card-body">
                    <style>
input[readonly] {
    background-color: #e9ecef;
    color: #495057;
}
                    </style>

                    <h4 class="mb-4 text-center">Tambah Aset Tetap</h4>

                    @if ($errors->any())
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Perhatian!</strong> Mohon lengkapi semua data yang wajib diisi.
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('aset-tetap.store') }}" method="POST">
    @csrf

    {{-- Kode Aset Tetap --}}
    <div class="row mb-3">
        <label for="kode_aset_tetap" class="col-sm-4 col-form-label">Kode Aset Tetap</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="kode_aset_tetap" name="kode_aset_tetap" value="{{ $kodeBaru ?? '' }}" readonly>
            <small class="text-muted">Kode unik aset tetap, otomatis dibuat oleh sistem.</small>
        </div>
    </div>

    {{-- Nama Aset --}}
    <div class="row mb-3">
        <label for="nama_aset" class="col-sm-4 col-form-label">Nama Aset</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="nama_aset" name="nama_aset" value="{{ old('nama_aset') }}" required>
            <small class="text-muted">Masukkan nama aset tetap, contoh: Mesin Penggiling.</small>
            @error('nama_aset')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Tipe Aset --}}
    <div class="row mb-3">
        <label for="tipe_aset" class="col-sm-4 col-form-label">Tipe Aset</label>
        <div class="col-sm-8">
            <select class="form-select" id="tipe_aset" name="tipe_aset" required>
                <option value="">Pilih Tipe</option>
                <option value="mesin" {{ old('tipe_aset') == 'mesin' ? 'selected' : '' }}>Mesin</option>
                <option value="kendaraan" {{ old('tipe_aset') == 'kendaraan' ? 'selected' : '' }}>Kendaraan</option>
                <option value="peralatan" {{ old('tipe_aset') == 'peralatan' ? 'selected' : '' }}>Peralatan</option>
                <option value="bangunan" {{ old('tipe_aset') == 'bangunan' ? 'selected' : '' }}>Bangunan</option>
            </select>
            <small class="text-muted">Pilih tipe aset sesuai kategori barang.</small>
            @error('tipe_aset')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Tanggal Beli --}}
    <div class="row mb-3">
        <label for="tanggal_beli" class="col-sm-4 col-form-label">Tanggal Beli</label>
        <div class="col-sm-8">
            <input type="date" class="form-control" id="tanggal_beli" name="tanggal_beli" value="{{ old('tanggal_beli') }}" required>
            <small class="text-muted">Pilih tanggal pembelian aset tetap.</small>
            @error('tanggal_beli')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Harga Perolehan --}}
    <div class="row mb-3">
        <label for="harga_perolehan" class="col-sm-4 col-form-label">Harga Perolehan</label>
        <div class="col-sm-8">
            <input type="number" class="form-control" id="harga_perolehan" name="harga_perolehan" value="{{ old('harga_perolehan') }}" required>
            <small class="text-muted">Masukkan harga beli aset tetap (tanpa titik/koma).</small>
            @error('harga_perolehan')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Umur Ekonomis --}}
    <div class="row mb-3">
        <label for="umur_ekonomis" class="col-sm-4 col-form-label">Umur Ekonomis (tahun)</label>
        <div class="col-sm-8">
            <input type="number" class="form-control" id="umur_ekonomis" name="umur_ekonomis" value="{{ old('umur_ekonomis') }}" required>
            <small class="text-muted">Isi umur ekonomis aset dalam tahun, contoh: 5.</small>
            @error('umur_ekonomis')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Nilai Sisa --}}
    <div class="row mb-3">
        <label for="nilai_sisa" class="col-sm-4 col-form-label">Nilai Sisa</label>
        <div class="col-sm-8">
            <input type="number" class="form-control" id="nilai_sisa" name="nilai_sisa" value="{{ old('nilai_sisa') }}" required>
            <small class="text-muted">Nilai sisa otomatis dihitung dari tipe aset dan harga perolehan.</small>
            @error('nilai_sisa')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Keterangan --}}
    <div class="row mb-3">
        <label for="keterangan" class="col-sm-4 col-form-label">Keterangan</label>
        <div class="col-sm-8">
            <input type="text" class="form-control" id="keterangan" name="keterangan" value="{{ old('keterangan') }}">
            <small class="text-muted">Keterangan tambahan, contoh: lokasi atau fungsi aset.</small>
            @error('keterangan')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Tombol Aksi --}}
    <div class="row mt-4">
        <div class="col-sm-6">
            <a href="{{ route('aset-tetap.index') }}" class="btn btn-secondary me-2">← Kembali</a>
            <button type="reset" class="btn btn-warning">Reset</button>
        </div>
        <div class="col-sm-6 text-end">
            <button type="submit" class="btn btn-success">Simpan</button>
        </div>
    </div>

</form>
                </div> {{-- end card-body --}}
            </div> {{-- end card --}}
        </div> {{-- end col --}}
    </div> {{-- end row --}}
</div> {{-- end container --}}
<script>
    // Script untuk update nilai_sisa otomatis
    const tipeAsetSelect = document.getElementById('tipe_aset');
    const hargaPerolehanInput = document.getElementById('harga_perolehan');
    const nilaiSisaInput = document.getElementById('nilai_sisa');

    const persentaseResidu = {
        'mesin': 0.10,
        'kendaraan': 0.20,
        'peralatan': 0.00
    };

    function updateNilaiSisa() {
        const tipe = tipeAsetSelect.value;
        const harga = parseFloat(hargaPerolehanInput.value) || 0;
        const persen = persentaseResidu[tipe] || 0;
        nilaiSisaInput.value = Math.round(harga * persen);
    }

    if (tipeAsetSelect && hargaPerolehanInput && nilaiSisaInput) {
        tipeAsetSelect.addEventListener('change', updateNilaiSisa);
        hargaPerolehanInput.addEventListener('input', updateNilaiSisa);
    }
</script>
@endsection