@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2>Edit Bahan</h2>
    <form action="{{ route('bahan.update', $bahan->kode_bahan) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3 d-flex align-items-center">
            <label for="kode_bahan" class="form-label mb-0" style="width:150px;">Kode Bahan</label>
            <input type="text" class="form-control" id="kode_bahan" name="kode_bahan" value="{{ old('kode_bahan', $bahan->kode_bahan) }}" required readonly style="width:300px;">
            @error('kode_bahan')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="kode_kategori" class="form-label mb-0" style="width:150px;">Kategori</label>
            <select class="form-control" id="kode_kategori" name="kode_kategori" required style="width:300px;">
                <option value="">-- Pilih Kategori --</option>
                @foreach($kategori as $kat)
                    @if(Str::startsWith($kat->kode_kategori, 'B'))
                        <option value="{{ $kat->kode_kategori }}" 
                            {{ old('kode_kategori', $bahan->kode_kategori) == $kat->kode_kategori ? 'selected' : '' }}>
                            {{ $kat->jenis_kategori }}
                        </option>
                    @endif
                @endforeach
            </select>
            @error('kode_kategori')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="nama_bahan" class="form-label mb-0" style="width:150px;">Nama Bahan</label>
            <input type="text" class="form-control" id="nama_bahan" name="nama_bahan" value="{{ old('nama_bahan', $bahan->nama_bahan) }}" required style="width:300px;">
            @error('nama_bahan')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="satuan" class="form-label mb-0" style="width:150px;">Satuan</label>
            <input type="text" class="form-control" id="satuan" name="satuan" value="{{ old('satuan', $bahan->satuan) }}" required style="width:300px;">
            @error('satuan')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="stokmin" class="form-label mb-0" style="width:150px;">Stok Minimal</label>
            <input type="number" step="0.01" class="form-control" id="stokmin" name="stokmin" value="{{ old('stokmin', $bahan->stokmin) }}" required style="width:300px;">
            @error('stokmin')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3 d-flex align-items-center">
    <label for="frekuensi_pembelian" class="form-label mb-0" style="width:150px;">Frekuensi Order</label>
    <select class="form-control" id="frekuensi_pembelian" name="frekuensi_pembelian" style="width:300px;">
        <option value="">-- Pilih Frekuensi --</option>
        <option value="Harian" {{ old('frekuensi_pembelian', $bahan->frekuensi_pembelian) == 'Harian' ? 'selected' : '' }}>Harian</option>
        <option value="Mingguan" {{ old('frekuensi_pembelian', $bahan->frekuensi_pembelian) == 'Mingguan' ? 'selected' : '' }}>Mingguan</option>
        <option value="Dua Mingguan" {{ old('frekuensi_pembelian', $bahan->frekuensi_pembelian) == 'Dua Mingguan' ? 'selected' : '' }}>Dua Mingguan</option>
        <option value="Bulanan" {{ old('frekuensi_pembelian', $bahan->frekuensi_pembelian) == 'Bulanan' ? 'selected' : '' }}>Bulanan</option>
        <option value="Tiga Bulanan" {{ old('frekuensi_pembelian', $bahan->frekuensi_pembelian) == 'Tiga Bulanan' ? 'selected' : '' }}>Tiga Bulanan</option>
    </select>
</div>
<div class="mb-3 d-flex align-items-center">
    <label for="interval" class="form-label mb-0" style="width:150px;">Interval</label>
    <input type="number" class="form-control" id="interval" name="interval" value="{{ old('interval', $bahan->interval) }}" style="width:300px;">
</div>
<div class="mb-3 d-flex align-items-center">
    <label for="jumlah_per_order" class="form-label mb-0" style="width:150px;">Jumlah per Order</label>
    <input type="number" class="form-control" id="jumlah_per_order" name="jumlah_per_order" value="{{ old('jumlah_per_order', $bahan->jumlah_per_order) }}" style="width:300px;">
</div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('bahan.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection