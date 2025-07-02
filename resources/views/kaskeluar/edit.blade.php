@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Edit Kas Keluar</h4>

    <form action="{{ route('kaskeluar.update', $kas->id_jurnal) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-md-6">
                <!-- No BKK -->
                <div class="mb-3 row">
                    <label for="no_BKK" class="col-sm-4 col-form-label">No BKK</label>
                    <div class="col-sm-8">
                        <input type="text" id="no_BKK" name="no_BKK" class="form-control" value="{{ $kas->nomor_bukti }}" readonly>
                    </div>
                </div>

                <!-- Tanggal -->
                <div class="mb-3 row">
                    <label for="tanggal" class="col-sm-4 col-form-label">Tanggal</label>
                    <div class="col-sm-8">
                        <input type="date" id="tanggal" name="tanggal" class="form-control" value="{{ $kas->tanggal }}" required>
                    </div>
                </div>

                <!-- No Referensi -->
                <div class="mb-3 row">
                    <label for="no_referensi" class="col-sm-4 col-form-label">No Referensi</label>
                    <div class="col-sm-8">
                        <input type="text" id="no_referensi" name="no_referensi" class="form-control" value="{{ $kas->no_referensi }}">
                    </div>
                </div>

                <!-- Penerima -->
                <div class="mb-3 row">
                    <label for="penerima" class="col-sm-4 col-form-label">Penerima</label>
                    <div class="col-sm-8">
                        <input type="text" id="penerima" name="penerima" class="form-control" value="{{ $kas->penerima }}" required>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-md-6">
                <!-- Kas Digunakan -->
                <div class="mb-3 row">
                    <label class="col-sm-4 col-form-label">Kas</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" value="Kas (101)" readonly>
                    </div>
                </div>

                <!-- Akun Lawan -->
                <div class="mb-3 row">
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
                <div class="mb-3 row">
                    <label for="jumlah" class="col-sm-4 col-form-label">Jumlah</label>
                    <div class="col-sm-8">
                        <input type="number" id="jumlah" name="jumlah" class="form-control" value="{{ $kas->jumlah }}" required>
                    </div>
                </div>

                <!-- Keterangan -->
                <div class="mb-3 row">
                    <label for="keterangan" class="col-sm-4 col-form-label">Keterangan</label>
                    <div class="col-sm-8">
                        <textarea id="keterangan" name="keterangan" class="form-control" rows="3">{{ $kas->keterangan_teks }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Simpan -->
        <div class="row mt-3">
            <div class="col-md-12 text-end">
                <a href="{{ route('kaskeluar.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
            </div>
        </div>
    </form>
</div>
@endsection
