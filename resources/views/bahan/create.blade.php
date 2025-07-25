@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            {{-- Kotak putih dengan padding dan bayangan --}}
            <div class="bg-white p-4 rounded shadow">
                <h4 class="mb-4">Input Data Bahan</h4>
                <form action="{{ route('bahan.store') }}" method="POST">
                    @csrf
                    <div class="mb-3 d-flex align-items-center">
                        <label for="kode_bahan" class="form-label mb-0" style="width:150px;">Kode Bahan</label>
                        <input type="text" class="form-control" id="kode_bahan" name="kode_bahan" value="{{ $kode_bahan }}" readonly style="width:300px;">
                    </div>

                    <div class="mb-3 d-flex align-items-center">
                        <label for="kode_kategori" class="form-label mb-0" style="width:150px;">Kategori</label>
                        <select class="form-control" id="kode_kategori" name="kode_kategori" required style="width:300px;">
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
                            <div class="text-danger ms-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 d-flex align-items-center">
                        <label for="nama_bahan" class="form-label mb-0" style="width:150px;">Nama Bahan</label>
                        <input type="text" class="form-control" id="nama_bahan" name="nama_bahan" value="{{ old('nama_bahan') }}" required style="width:300px;">
                        @error('nama_bahan')
                            <div class="text-danger ms-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 d-flex align-items-center">
                        <label for="satuan" class="form-label mb-0" style="width:150px;">Satuan</label>
                        <input type="text" class="form-control" id="satuan" name="satuan" value="{{ old('satuan') }}" required style="width:300px;">
                        @error('satuan')
                            <div class="text-danger ms-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 d-flex align-items-center">
                        <label for="stokmin" class="form-label mb-0" style="width:150px;">Stok Minimal</label>
                        <input type="number" step="0.01" class="form-control" id="stokmin" name="stokmin" value="{{ old('stokmin') }}" style="width:300px;">
                        @error('stok_minimal')
                            <div class="text-danger ms-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 d-flex align-items-center">
    <label for="frekuensi_pembelian" class="form-label mb-0" style="width:150px;">Frekuensi Order</label>
    <select class="form-control" id="frekuensi_pembelian" name="frekuensi_pembelian" style="width:300px;">
        <option value="">-- Pilih Frekuensi --</option>
        <option value="Harian" {{ old('frekuensi_pembelian') == 'Harian' ? 'selected' : '' }}>Harian</option>
        <option value="Mingguan" {{ old('frekuensi_pembelian') == 'Mingguan' ? 'selected' : '' }}>Mingguan</option>
        <option value="Dua Mingguan" {{ old('frekuensi_pembelian') == 'Dua Mingguan' ? 'selected' : '' }}>Dua Mingguan</option>
        <option value="Bulanan" {{ old('frekuensi_pembelian') == 'Bulanan' ? 'selected' : '' }}>Bulanan</option>
        <option value="Tiga Bulanan" {{ old('frekuensi_pembelian') == 'Tiga Bulanan' ? 'selected' : '' }}>Tiga Bulanan</option>
    </select>
</div>
<div class="mb-3 d-flex align-items-center">
    <label for="interval" class="form-label mb-0" style="width:150px;">Interval</label>
    <input type="number" class="form-control" id="interval" name="interval" value="{{ old('interval') }}" style="width:300px;">
</div>
<div class="mb-3 d-flex align-items-center">
    <label for="jumlah_per_order" class="form-label mb-0" style="width:150px;">Jumlah per Order</label>
    <input type="number" class="form-control" id="jumlah_per_order" name="jumlah_per_order" value="{{ old('jumlah_per_order') }}" style="width:300px;">
</div>

<div class="d-flex gap-2 justify-content-start mt-3" style="margin-left:150px;">
    <a href="{{ route('bahan.index') }}" class="btn btn-secondary">Back</a>
    <button type="reset" class="btn btn-warning">Reset</button>
    <button type="submit" class="btn btn-success">Submit</button>
</div>

                </form>
            </div>

        </div>
    </div>
</div>
@endsection
