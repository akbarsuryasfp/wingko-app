{{-- filepath: resources/views/consignor/create_produk.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h4 class="mb-4 fw-semibold text-center">Tambah Produk Konsinyasi</h4>
            <form action="{{ route('produk-konsinyasi.store') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <label for="kode_produk" class="col-sm-4 col-form-label text-start">Kode Produk</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="kode_produk" name="kode_produk" value="{{ $kode_produk }}" readonly tabindex="-1" style="pointer-events: none; background-color: #e9ecef;" required>
                        @error('kode_produk')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="nama_produk" class="col-sm-4 col-form-label text-start">Nama Produk</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="{{ old('nama_produk') }}" required>
                        @error('nama_produk')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="satuan" class="col-sm-4 col-form-label text-start">Satuan</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="satuan" name="satuan" value="{{ old('satuan') }}" required>
                        @error('satuan')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                <div class="row mb-3">
                    <label for="kode_consignor" class="col-sm-4 col-form-label text-start">Nama Consignor</label>
                    <div class="col-sm-8">
                        <select class="form-select" id="kode_consignor" name="kode_consignor" required {{ $selectedConsignor ? 'readonly disabled' : '' }}>
                            <option value="">-- Pilih Consignor --</option>
                            @foreach($consignors as $c)
                                <option value="{{ $c->kode_consignor }}"
                                    {{ (old('kode_consignor', $selectedConsignor) == $c->kode_consignor) ? 'selected' : '' }}>
                                    {{ $c->nama_consignor }}
                                </option>
                            @endforeach
                        </select>
                        @if($selectedConsignor)
                            <input type="hidden" name="kode_consignor" value="{{ $selectedConsignor }}">
                        @endif
                        @error('kode_consignor')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="keterangan" class="col-sm-4 col-form-label text-start">Keterangan</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="keterangan" name="keterangan" value="{{ old('keterangan') }}">
                        @error('keterangan')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-2">
                    <div>
                        <a href="{{ route('consignor.index') }}" class="btn btn-secondary me-2">Back</a>
                        <button type="reset" class="btn btn-warning">Reset</button>
                    </div>
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection