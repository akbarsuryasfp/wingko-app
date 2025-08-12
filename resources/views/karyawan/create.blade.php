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

                    <h4 class="mb-4 text-center">Tambah Data Karyawan</h4>

                    <form action="{{ route('karyawan.store') }}" method="POST">
                        @csrf

                        {{-- Kode Karyawan --}}
                        <div class="row mb-3">
                            <label for="kode_karyawan" class="col-sm-4 col-form-label">Kode Karyawan</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="kode_karyawan" name="kode_karyawan" value="{{ $kodeBaru }}" readonly>
                            </div>
                        </div>

                        {{-- Nama --}}
                        <div class="row mb-3">
                            <label for="nama" class="col-sm-4 col-form-label">Nama</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="nama" name="nama" value="{{ old('nama') }}" required>
                                @error('nama')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Jabatan --}}
                        <div class="row mb-3">
                            <label for="jabatan" class="col-sm-4 col-form-label">Jabatan</label>
                            <div class="col-sm-8">
                                <select class="form-select" id="jabatan" name="jabatan" required>
                                    <option value="">-- Pilih Jabatan --</option>
                                    @foreach($jabatan as $j)
                                        <option value="{{ $j }}" {{ old('jabatan') == $j ? 'selected' : '' }}>{{ $j }}</option>
                                    @endforeach
                                </select>
                                @error('jabatan')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Departemen --}}
                        <div class="row mb-3">
                            <label for="departemen" class="col-sm-4 col-form-label">Departemen</label>
                            <div class="col-sm-8">
                                <select class="form-select" id="departemen" name="departemen" required>
                                    <option value="">-- Pilih Departemen --</option>
                                    @foreach($departemen as $d)
                                        <option value="{{ $d }}" {{ old('departemen') == $d ? 'selected' : '' }}>{{ $d }}</option>
                                    @endforeach
                                </select>
                                @error('departemen')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Gaji --}}
                        <div class="row mb-3">
                            <label for="gaji" class="col-sm-4 col-form-label">Gaji</label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control" id="gaji" name="gaji" value="{{ old('gaji') }}">
                                @error('gaji')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Tanggal Masuk --}}
                        <div class="row mb-3">
                            <label for="tanggal_masuk" class="col-sm-4 col-form-label">Tanggal Masuk</label>
                            <div class="col-sm-8">
                                <input type="date" class="form-control" id="tanggal_masuk" name="tanggal_masuk" value="{{ old('tanggal_masuk') }}">
                                @error('tanggal_masuk')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Alamat --}}
                        <div class="row mb-3">
                            <label for="alamat" class="col-sm-4 col-form-label">Alamat</label>
                            <div class="col-sm-8">
                                <textarea class="form-control" id="alamat" name="alamat">{{ old('alamat') }}</textarea>
                                @error('alamat')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    
                        {{-- No Telepon --}}
                        <div class="row mb-3">
                            <label for="no_telepon" class="col-sm-4 col-form-label">No Telepon</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}">
                                @error('no_telepon')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="row mt-4">
                            <div class="col-sm-6">
                                <a href="{{ route('karyawan.index') }}" class="btn btn-secondary me-2">← Kembali</a>
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
@endsection