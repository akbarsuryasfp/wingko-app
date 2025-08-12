@extends('layouts.app')

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mt-4">
    <div class="card">
        <style>
            input[readonly] {
                background-color: #e9ecef;
                color: #495057;
            }
        </style>

        <div class="card-header">
            <h4 class="mb-0">Edit Kas Keluar</h4>
        </div>
        
        <div class="card-body">
            <form action="{{ route('kaskeluar.update', $kas->no_jurnal) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-md-6">
                        <!-- No Bukti -->
                        <div class="mb-2 row">
                            <label class="col-sm-4 col-form-label">No Bukti</label>
                            <div class="col-sm-8">
                                <input type="text" name="no_BKK" class="form-control" value="{{ $kas->nomor_bukti }}" readonly>
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div class="mb-2 row">
                            <label for="tanggal" class="col-sm-4 col-form-label">Tanggal</label>
                            <div class="col-sm-8">
                                <input type="date" id="tanggal" name="tanggal" class="form-control" value="{{ $kas->tanggal }}" required>
                            </div>
                        </div>

                        <!-- Penerima -->
                        <div class="mb-2 row">
                            <label for="penerima" class="col-sm-4 col-form-label">Penerima</label>
                            <div class="col-sm-8">
                                <input type="text" id="penerima" name="penerima" class="form-control" value="{{ $kas->penerima }}" required>
                            </div>
                        </div>

                        <!-- Bukti Nota -->
                        <div class="mb-2 row">
                            <label for="bukti_nota" class="col-sm-4 col-form-label">Upload Bukti Nota</label>
                            <div class="col-sm-8">
                                @php
                                    $bukti = DB::table('t_buktikaskeluar')->where('no_jurnal', $kas->no_jurnal)->first();
                                @endphp
                                @if($bukti && $bukti->bukti_nota)
                                    <a href="{{ asset('storage/' . $bukti->bukti_nota) }}" target="_blank" class="btn btn-outline-primary btn-sm mb-1">
                                        Lihat Bukti Nota Lama
                                    </a>
                                @endif
                                <input type="file" name="bukti_nota" id="bukti_nota" class="form-control" accept="image/*,application/pdf">
                                <small class="text-muted">File harus kurang dari 2MB (jpg, png, pdf). Kosongkan jika tidak ingin mengubah bukti nota.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan -->
                    <div class="col-md-6">
                        <!-- Kas Digunakan -->
                        <div class="mb-2 row">
                            <label class="col-sm-4 col-form-label">Kas Digunakan</label>
                            <div class="col-sm-8">
                                <select name="kas_digunakan" class="form-control" required>
                                    <option value="">-- Pilih Kas --</option>
                                    <option value="kas_bank" {{ old('kas_digunakan', $kas->kas_digunakan) == 'kas_bank' ? 'selected' : '' }}>Kas di Bank</option>
                                    <option value="kas_kecil" {{ old('kas_digunakan', $kas->kas_digunakan) == 'kas_kecil' ? 'selected' : '' }}>Kas Kecil</option>
                                </select>
                            </div>
                        </div>

                        <!-- Akun Lawan -->
                        <div class="mb-2 row">
                            <label for="kode_akun" class="col-sm-4 col-form-label">Akun Lawan</label>
                            <div class="col-sm-8">
                                <select id="kode_akun" name="kode_akun" class="form-select" required>
                                    <option value="">-- Pilih Akun --</option>
                                    @foreach($akun as $a)
                                        <option value="{{ $a->kode_akun }}" {{ $kas->kode_akun == $a->kode_akun ? 'selected' : '' }}>
                                            [{{ $a->kode_akun }}] {{ $a->nama_akun }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

<!-- Jumlah -->
<div class="mb-2 row">
    <label for="jumlah" class="col-sm-4 col-form-label">Jumlah</label>
    <div class="col-sm-8">
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="text" id="jumlah" name="jumlah" class="form-control" 
                   value="{{ $kas->jumlah }}" required>
        </div>
    </div>
</div>

                        <!-- Keterangan -->
                        <div class="mb-2 row">
                            <label for="keterangan" class="col-sm-4 col-form-label">Keterangan</label>
                            <div class="col-sm-8">
                                <textarea id="keterangan" name="keterangan" class="form-control" rows="3">{{ $kas->keterangan_teks }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="row mt-4">
                    <div class="col-md-12 d-flex justify-content-between">
                        <div>
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="reset" class="btn btn-warning ms-2">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    var jumlahInput = document.getElementById('jumlah');
    if (jumlahInput) {
        // Hapus semua karakter selain angka
        jumlahInput.value = jumlahInput.value.replace(/[^0-9]/g, '');
    }
});
</script>
@endsection
