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
    <h4 class="mb-4">Form Input Kas Keluar</h4>

    <form action="{{ route('kaskeluar.store') }}" method="POST">
        @csrf

        <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-md-6">
                <!-- No BKK -->
<div class="mb-3 row">
    <label class="col-sm-4 col-form-label">No Bukti</label>
    <div class="col-sm-8">
        <input type="text" name="no_BKK" class="form-control" value="{{ $no_BKK }}" readonly>
    </div>
</div>

                <!-- Tanggal -->
                <div class="mb-3 row">
                    <label for="tanggal" class="col-sm-4 col-form-label">Tanggal</label>
                    <div class="col-sm-8">
                        <input type="date" id="tanggal" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <!-- No Referensi -->
                <div class="mb-3 row">
                    <label for="no_referensi" class="col-sm-4 col-form-label">No Referensi</label>
                    <div class="col-sm-8">
                        <input type="text" id="no_referensi" name="no_referensi" class="form-control">
                    </div>
                </div>

                <!-- Penerima -->
                <div class="mb-3 row">
                    <label for="penerima" class="col-sm-4 col-form-label">Penerima</label>
                    <div class="col-sm-8">
                        <input type="text" id="penerima" name="penerima" class="form-control" required>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-md-6">
                <!-- Kas Digunakan -->
<div class="mb-3 row">
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
                <div class="mb-3 row">
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
                <div class="mb-3 row">
                    <label for="jumlah" class="col-sm-4 col-form-label">Jumlah</label>
                    <div class="col-sm-8">
                        <input type="number" id="jumlah" name="jumlah" class="form-control" required>
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="mb-3 row">
                    <label for="keterangan" class="col-sm-4 col-form-label">Keterangan</label>
                    <div class="col-sm-8">
                        <textarea id="keterangan" name="keterangan" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Simpan -->
        <div class="row mt-3">
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
</div>
@endsection
