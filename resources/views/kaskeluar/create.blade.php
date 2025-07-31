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
            <h4 class="mb-0">Form Input Kas Keluar</h4>
        </div>
        
        <div class="card-body">
            <form action="{{ route('kaskeluar.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-md-6">
                        <!-- No BKK -->
                        <div class="mb-2 row">
                            <label class="col-sm-4 col-form-label">No Bukti</label>
                            <div class="col-sm-8">
                                <input type="text" name="no_BKK" class="form-control" value="{{ $no_BKK }}" readonly>
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div class="mb-2 row">
                            <label for="tanggal" class="col-sm-4 col-form-label">Tanggal</label>
                            <div class="col-sm-8">
                                <input type="date" id="tanggal" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>

                        <!-- Penerima -->
                        <div class="mb-2 row">
                            <label for="penerima" class="col-sm-4 col-form-label">Penerima</label>
                            <div class="col-sm-8">
                                <input type="text" id="penerima" name="penerima" class="form-control" required>
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
                                    <option value="kas_bank" {{ old('kas_digunakan') == 'kas_bank' ? 'selected' : '' }}>Kas di Bank</option>
                                    <option value="kas_kecil" {{ old('kas_digunakan') == 'kas_kecil' ? 'selected' : '' }}>Kas Kecil</option>
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
                                        <option value="{{ $a->kode_akun }}">
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
                                <input type="number" id="jumlah" name="jumlah" class="form-control" required>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-2 row">
                            <label for="keterangan" class="col-sm-4 col-form-label">Keterangan</label>
                            <div class="col-sm-8">
                                <textarea id="keterangan" name="keterangan" class="form-control" rows="3"></textarea>
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
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection