@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 mt-5">
    <div class="card shadow-sm mx-auto" style="max-width: 700px;">
        <div class="card-body">
            <h4 class="mb-4 fw-semibold text-center">Edit Produk Konsinyasi</h4>
            <form action="{{ route('produk-konsinyasi.update', $produk->kode_produk) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row mb-3">
                    <label for="kode_produk" class="col-sm-4 col-form-label text-start">Kode Produk</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="kode_produk" name="kode_produk" value="{{ $produk->kode_produk }}" readonly tabindex="-1" style="pointer-events: none; background-color: #e9ecef;" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="nama_produk" class="col-sm-4 col-form-label text-start">Nama Produk</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="{{ $produk->nama_produk }}" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="satuan" class="col-sm-4 col-form-label text-start">Satuan</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="satuan" name="satuan" value="{{ $produk->satuan }}" required>
                    </div>
                </div>
                <div class="row mb-3">
                <div class="row mb-3">
                    <label for="kode_consignor" class="col-sm-4 col-form-label text-start">Nama Consignor</label>
                    <div class="col-sm-8">
                        <select class="form-select" id="kode_consignor" name="kode_consignor" required>
                            <option value="">-- Pilih Consignor --</option>
                            @foreach($consignors as $c)
                                <option value="{{ $c->kode_consignor }}" {{ ($produk->kode_consignor == $c->kode_consignor) ? 'selected' : '' }}>{{ $c->nama_consignor }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="keterangan" class="col-sm-4 col-form-label text-start">Keterangan</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="keterangan" name="keterangan" value="{{ $produk->keterangan }}">
                    </div>
                </div>
                <div class="d-flex justify-content-between gap-2">
                    <a href="{{ route('consignor.index') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection