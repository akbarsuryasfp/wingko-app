@extends('layouts.app')
@section('content')
<div class="container">
    <h4>Tambah Aset Tetap</h4>
    <form action="{{ route('aset-tetap.store') }}" method="POST">
        @csrf
        <div class="mb-2">
            <label>Kode Aset Tetap</label>
            <input type="text" name="kode_aset_tetap" class="form-control" value="{{ $kodeBaru ?? '' }}" readonly>
        </div>
        <div class="mb-2">
            <label>Nama Aset</label>
            <input type="text" name="nama_aset" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Tipe Aset</label>
            <select name="tipe_aset" class="form-control" id="tipe_aset" required>
                <option value="">Pilih Tipe</option>
                <option value="mesin">Mesin</option>
                <option value="kendaraan">Kendaraan</option>
                <option value="peralatan">Peralatan</option>
                <option value="bangunan">Bangunan</option>
            </select>
        </div>
        <div class="mb-2">
            <label>Tanggal Beli</label>
            <input type="date" name="tanggal_beli" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Harga Perolehan</label>
            <input type="number" name="harga_perolehan" class="form-control" id="harga_perolehan" required>
        </div>
        <div class="mb-2">
            <label>Umur Ekonomis (tahun)</label>
            <input type="number" name="umur_ekonomis" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Nilai Sisa</label>
            <input type="number" name="nilai_sisa" class="form-control" id="nilai_sisa" required>
        </div>
        <div class="mb-2">
            <label>Keterangan</label>
            <input type="text" name="keterangan" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
        <a href="{{ route('aset-tetap.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
<script>
    // Contoh script di form create aset tetap
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

    tipeAsetSelect.addEventListener('change', updateNilaiSisa);
    hargaPerolehanInput.addEventListener('input', updateNilaiSisa);
</script>
@endsection