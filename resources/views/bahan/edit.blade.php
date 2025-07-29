@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <style>
                        input[readonly] {
                            background-color: #e9ecef;
                            color: #495057;
                        }
                    </style>

                    <h4 class="mb-4 text-center">Edit Data Bahan</h4>

                    <form action="{{ route('bahan.update', $bahan->kode_bahan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Kode Bahan --}}
                        <div class="row mb-3">
                            <label for="kode_bahan" class="col-sm-4 col-form-label">Kode Bahan</label>
                            <div class="col-sm-8">
                                <input type="text" readonly class="form-control" id="kode_bahan" name="kode_bahan" value="{{ old('kode_bahan', $bahan->kode_bahan) }}">
                            </div>
                        </div>

                        {{-- Kategori Bahan --}}
                        <div class="row mb-3">
                            <label for="kode_kategori" class="col-sm-4 col-form-label">Kategori Bahan</label>
                            <div class="col-sm-8">
                                <select class="form-select" id="kode_kategori" name="kode_kategori" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($kategori as $kat)
                                        @if(Str::startsWith($kat->kode_kategori, 'B'))
                                            <option value="{{ $kat->kode_kategori }}" {{ old('kode_kategori', $bahan->kode_kategori) == $kat->kode_kategori ? 'selected' : '' }}>
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

                        {{-- Nama Bahan --}}
                        <div class="row mb-3">
                            <label for="nama_bahan" class="col-sm-4 col-form-label">Nama Bahan</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="nama_bahan" name="nama_bahan" value="{{ old('nama_bahan', $bahan->nama_bahan) }}" required>
                                @error('nama_bahan')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Satuan --}}
                        <div class="row mb-3">
                            <label for="satuan" class="col-sm-4 col-form-label">Satuan</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" id="satuan" name="satuan" value="{{ old('satuan', $bahan->satuan) }}" required>
                                @error('satuan')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Stok Minimum --}}
                        <div class="row mb-3">
                            <label for="stokmin" class="col-sm-4 col-form-label">Stok Minimum</label>
                            <div class="col-sm-8">
                                <input type="number" step="0.01" class="form-control" id="stokmin" name="stokmin" value="{{ old('stokmin', $bahan->stokmin) }}" placeholder="Minimum agar tidak kehabisan">
                                @error('stokmin')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Frekuensi Order --}}
                        <div class="form-group row mb-3 align-items-center">
                            <label class="col-sm-4 col-form-label" data-bs-toggle="tooltip" title="Contoh: 2 x per Mingguan berarti order dilakukan 2 kali setiap minggu.">Frekuensi Order</label>

                            <!-- Input interval + x -->
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <input type="number" class="form-control" name="interval" min="1" value="{{ old('interval', $bahan->interval) }}">
                                    <span class="input-group-text">x</span>
                                </div>
                            </div>

                            <!-- Tulisan 'per' -->
                            <div class="col-sm-1 d-flex align-items-center justify-content-center">
                                <span>per</span>
                            </div>

                            <!-- Dropdown frekuensi -->
                            <div class="col-sm-4">
                                <select class="form-select" name="frekuensi_pembelian">
                                    <option value="">-- Pilih Frekuensi --</option>
                                    <option value="Harian" {{ old('frekuensi_pembelian', $bahan->frekuensi_pembelian) == 'Harian' ? 'selected' : '' }}>Harian</option>
                                    <option value="Mingguan" {{ old('frekuensi_pembelian', $bahan->frekuensi_pembelian) == 'Mingguan' ? 'selected' : '' }}>Mingguan</option>
                                    <option value="Dua Mingguan" {{ old('frekuensi_pembelian', $bahan->frekuensi_pembelian) == 'Dua Mingguan' ? 'selected' : '' }}>Dua Mingguan</option>
                                    <option value="Bulanan" {{ old('frekuensi_pembelian', $bahan->frekuensi_pembelian) == 'Bulanan' ? 'selected' : '' }}>Bulanan</option>
                                    <option value="Tiga Bulanan" {{ old('frekuensi_pembelian', $bahan->frekuensi_pembelian) == 'Tiga Bulanan' ? 'selected' : '' }}>Tiga Bulanan</option>
                                </select>
                            </div>
                        </div>

                        {{-- Jumlah Order --}}
                        <div class="row mb-3">
                            <label for="jumlah_per_order" class="col-sm-4 col-form-label">Jumlah Order/Satuan</label>
                            <div class="col-sm-8">
                                <input type="number" class="form-control" id="jumlah_per_order" name="jumlah_per_order" value="{{ old('jumlah_per_order', $bahan->jumlah_per_order) }}">
                            </div>
                        </div>

                        {{-- Tombol Aksi --}}
<div class="row mt-4">
    <div class="col-sm-6">
        <a href="{{ route('bahan.index') }}" class="btn btn-secondary me-2">← Kembali</a>
        <button type="reset" class="btn btn-warning">Reset</button>
    </div>
    <div class="col-sm-6 text-end">
        <button type="submit" class="btn btn-success">Update</button>
    </div>
</div>
                    </form>
                </div> {{-- end card-body --}}
            </div> {{-- end card --}}
        </div>
    </div>
</div>

{{-- Tooltip Init Script --}}
<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
</script>
@endsection