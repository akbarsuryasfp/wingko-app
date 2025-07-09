@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Edit Pembayaran Utang</h4>
    <form action="{{ route('hutang.bayar.update', [$hutang->no_utang, $no_jurnal]) }}" method="POST" class="card p-4 shadow-sm">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <div class="row align-items-center">
                    <label class="col-md-4 col-form-label">Kas yang Digunakan</label>
                    <div class="col-md-8">
                        <select name="kode_akun" class="form-control" required>
                            <option value="">-- Pilih Kas --</option>
                            <option value="1010" {{ old('kode_akun', $form['kode_akun'] ?? '') == '1010' ? 'selected' : '' }}>Kas</option>
                            <option value="1000" {{ old('kode_akun', $form['kode_akun'] ?? '') == '1000' ? 'selected' : '' }}>Bank</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="row align-items-center">
                    <label class="col-md-4 col-form-label">Nomor BKK</label>
                    <div class="col-md-8">
                        <input type="text" name="no_BKK" class="form-control" value="{{ old('no_BKK', $form['no_BKK'] ?? '') }}" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <div class="row align-items-center">
                    <label class="col-md-4 col-form-label">Tanggal</label>
                    <div class="col-md-8">
                        <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $form['tanggal'] ?? date('Y-m-d')) }}" required>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="row align-items-center">
                    <label class="col-md-4 col-form-label">Nama Supplier</label>
                    <div class="col-md-8">
                        <input type="text" name="nama_supplier" class="form-control" value="{{ $nama_supplier ?? $hutang->kode_supplier ?? '' }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <div class="row align-items-center">
                    <label class="col-md-4 col-form-label">No Utang</label>
                    <div class="col-md-8">
                        <input type="text" name="no_referensi" class="form-control" value="{{ $hutang->no_utang }}" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="row align-items-center">
                    <label class="col-md-4 col-form-label">Hutang</label>
                    <div class="col-md-8">
                        <input type="text" name="total_tagihan" class="form-control" value="{{ $hutang->total_tagihan }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <div class="row align-items-center">
                    <label class="col-md-4 col-form-label">Cicilan Sebelumnya</label>
                    <div class="col-md-8">
                        <input type="text" name="total_bayar" class="form-control" value="{{ $hutang->total_bayar }}" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="row align-items-center">
                    <label class="col-md-4 col-form-label">Sisa Utang</label>
                    <div class="col-md-8">
                        <input type="text" name="sisa_utang" class="form-control" value="{{ $hutang->sisa_utang }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 mb-3">
                <div class="row align-items-center">
                    <label class="col-md-4 col-form-label">Nominal Pembayaran</label>
                    <div class="col-md-8">
                        <input type="number" name="jumlah" class="form-control" min="1" max="{{ $hutang->sisa_utang + ($form['jumlah'] ?? 0) }}" value="{{ old('jumlah', $form['jumlah'] ?? '') }}" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12 mb-3">
                <div class="row">
                    <label class="col-md-2 col-form-label">Keterangan</label>
                    <div class="col-md-10">
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $form['keterangan'] ?? 'Pembayaran utang') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">Update Pembayaran</button>
                <a href="{{ route('hutang.detail', $hutang->no_utang) }}" class="btn btn-secondary ms-2">Kembali</a>
            </div>
        </div>
    </form>
</div>
@endsection