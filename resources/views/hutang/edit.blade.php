@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Edit Pembayaran Utang</h4>
    <form action="{{ route('hutang.bayar.update', [$hutang->no_utang, $no_jurnal]) }}" method="POST" class="card p-4 shadow-sm">
        @csrf
        @method('PUT')
        <div class="row">
            {{-- Kolom Kiri --}}
            <div class="col-md-6">
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Nomor BKK</label>
                    <div class="col-sm-8">
                        <input type="text" name="no_BKK" class="form-control" value="{{ old('no_BKK', $form['no_BKK'] ?? '') }}" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Tanggal</label>
                    <div class="col-sm-8">
                        <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', $form['tanggal'] ?? date('Y-m-d')) }}" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Nama Supplier</label>
                    <div class="col-sm-8">
                        <input type="text" name="nama_supplier" class="form-control" value="{{ $nama_supplier ?? $hutang->kode_supplier ?? '' }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">No Utang</label>
                    <div class="col-sm-8">
                        <input type="text" name="no_referensi" class="form-control" value="{{ $hutang->no_utang }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Keterangan</label>
                    <div class="col-sm-8">
                        <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $form['keterangan'] ?? 'Pembayaran utang') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan --}}
            <div class="col-md-6">
                <div class="row mb-3">
                    <label class="col-sm-5 col-form-label">Kas yang Digunakan</label>
                    <div class="col-sm-7">
                        <select name="kode_akun" class="form-control" required>
                            <option value="">-- Pilih Kas --</option>
                            <option value="1010" {{ old('kode_akun', $form['kode_akun'] ?? '') == '1010' ? 'selected' : '' }}>Kas</option>
                            <option value="1000" {{ old('kode_akun', $form['kode_akun'] ?? '') == '1000' ? 'selected' : '' }}>Bank</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <label class="col-sm-5 col-form-label">Total Hutang</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control nominal" value="{{ $hutang->total_tagihan }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-5 col-form-label">Cicilan Sebelumnya</label>
                    <div class="col-sm-7">
                        <input type="text" class="form-control" value="Rp {{ number_format($hutang->total_bayar, 0, ',', '.') }}" readonly>
                    </div>
                </div>

@php
    $sisaYangHarusDibayar = $hutang->sisa_utang + ($form['jumlah'] ?? 0);
@endphp

<div class="row mb-3">
    <label class="col-sm-5 col-form-label">Sisa Utang</label>
    <div class="col-sm-7">
        <input type="text" class="form-control" value="Rp {{ number_format($sisaYangHarusDibayar, 0, ',', '.') }}" readonly>
    </div>
</div>
                <div class="row mb-3">
                    <label class="col-sm-5 col-form-label">Nominal Bayar</label>
                    <div class="col-sm-7">
                        <input type="number" name="jumlah" class="form-control" min="1" max="{{ $hutang->sisa_utang + ($form['jumlah'] ?? 0) }}" value="{{ old('jumlah', $form['jumlah'] ?? '') }}" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="text-end mt-3">
            <button type="submit" class="btn btn-primary">Update Pembayaran</button>
            <a href="{{ route('hutang.detail', $hutang->no_utang) }}" class="btn btn-secondary ms-2">Kembali</a>
        </div>
    </form>
</div>
@endsection

