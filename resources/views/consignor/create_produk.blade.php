{{-- filepath: resources/views/consignor/create_produk.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:600px;">
    <h4 class="mb-4">TAMBAH PRODUK KONSINYASI</h4>
    <form action="{{ route('produk-konsinyasi.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="kode_produk" class="form-label">Kode Produk</label>
            <input type="text" class="form-control" id="kode_produk" name="kode_produk" value="{{ $kode_produk }}" readonly required>
            @error('kode_produk')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="nama_produk" class="form-label">Nama Produk</label>
            <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="{{ old('nama_produk') }}" required>
            @error('nama_produk')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="satuan" class="form-label">Satuan</label>
            <input type="text" class="form-control" id="satuan" name="satuan" value="{{ old('satuan') }}" required>
            @error('satuan')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="harga_konsinyasi" class="form-label">Harga Konsinyasi</label>
            <input type="number" class="form-control" id="harga_konsinyasi" name="harga_konsinyasi" value="{{ old('harga_konsinyasi') }}" min="0" required>
            @error('harga_konsinyasi')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="kode_consignor" class="form-label">Consignor</label>
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
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control" id="keterangan" name="keterangan">{{ old('keterangan') }}</textarea>
            @error('keterangan')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="d-flex justify-content-between">
            <a href="{{ route('consignor.index') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-success">Submit</button>
        </div>
    </form>
</div>
@endsection