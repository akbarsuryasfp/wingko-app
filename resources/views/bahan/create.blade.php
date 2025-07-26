@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">

            <div class="bg-white p-4 rounded shadow">
                <h4 class="mb-4">📋 Formulir Data Bahan</h4>

                <form action="{{ route('bahan.store') }}" method="POST">
                    @csrf

                    {{-- Grouped form using row and col --}}
                    <div class="row mb-3">
                        <label for="kode_bahan" class="col-sm-3 col-form-label">ID Bahan (Otomatis)</label>
                        <div class="col-sm-6">
                            <input type="text" readonly class="form-control" id="kode_bahan" name="kode_bahan" value="{{ $kode_bahan }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="kode_kategori" class="col-sm-3 col-form-label">Kategori Bahan</label>
                        <div class="col-sm-6">
                            <select class="form-select" id="kode_kategori" name="kode_kategori" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategori as $kat)
                                    @if(Str::startsWith($kat->kode_kategori, 'B'))
                                        <option value="{{ $kat->kode_kategori }}" {{ old('kode_kategori') == $kat->kode_kategori ? 'selected' : '' }}>
                                            {{ $kat->jenis_kategori }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('kode_kategori')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="nama_bahan" class="col-sm-3 col-form-label">Nama Bahan</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="nama_bahan" name="nama_bahan" placeholder="Contoh: Tepung Terigu" value="{{ old('nama_bahan') }}" required>
                            @error('nama_bahan')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="satuan" class="col-sm-3 col-form-label">Satuan (misal: kg, liter)</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="satuan" name="satuan" value="{{ old('satuan') }}" required>
                            @error('satuan')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="stokmin" class="col-sm-3 col-form-label">Stok Minimum</label>
                        <div class="col-sm-6">
                            <input type="number" step="0.01" class="form-control" id="stokmin" name="stokmin" value="{{ old('stokmin') }}" placeholder="Minimum agar tidak kehabisan">
                            @error('stok_minimal')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row mb-3 align-items-center">
    <label class="col-sm-3 col-form-label">Frekuensi Order</label>
    
    <div class="col-sm-1">
        <input 
            type="number" 
            class="form-control" 
            name="interval" 
            placeholder="1" 
            min="1" 
            value="{{ old('interval') }}">
    </div>

    <label class="col-form-label col-sm-auto text-center">x     per</label>

    <div class="col-sm-4">
        <select class="form-control" name="frekuensi_order">
            <option value="">-- Pilih Frekuensi --</option>
            <option value="Harian" {{ old('frekuensi_order') == 'Harian' ? 'selected' : '' }}>Harian</option>
            <option value="Mingguan" {{ old('frekuensi_order') == 'Mingguan' ? 'selected' : '' }}>Mingguan</option>
            <option value="Dua Mingguan" {{ old('frekuensi_order') == 'Dua Mingguan' ? 'selected' : '' }}>Dua Mingguan</option>
            <option value="Bulanan" {{ old('frekuensi_order') == 'Bulanan' ? 'selected' : '' }}>Bulanan</option>
            <option value="Tiga Bulanan" {{ old('frekuensi_order') == 'Tiga Bulanan' ? 'selected' : '' }}>Tiga Bulanan</option>
        </select>
    </div>
</div>

                    <div class="row mb-3">
                        <label for="jumlah_per_order" class="col-sm-3 col-form-label">Jumlah Sekali Order</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" id="jumlah_per_order" name="jumlah_per_order" value="{{ old('jumlah_per_order') }}" placeholder="Misal: 20">
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="row mt-4">
    <div class="offset-sm-3 col-sm-6 d-flex justify-content-between">
        <a href="{{ route('bahan.index') }}" class="btn btn-secondary">← Kembali</a>
        <button type="reset" class="btn btn-warning">Reset</button>
        <button type="submit" class="btn btn-success">Simpan</button>
    </div>
</div>

                </form>
            </div>

        </div>
    </div>
</div>
@endsection
