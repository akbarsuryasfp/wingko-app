@extends('layouts.app')

@section('content')
<div class="container mt-3">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Edit Pembayaran Utang</h5>
        </div>
        <div class="card-body p-3">
            <form action="{{ route('hutang.bayar.update', [$hutang->no_utang, $no_jurnal]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    {{-- Kolom Kiri --}}
                    <div class="col-md-6">
                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-4 col-form-label">Nomor BKK</label>
                            <div class="col-sm-8">
                                <input type="text" name="no_BKK" class="form-control py-2" value="{{ old('no_BKK', $form['no_BKK'] ?? '') }}" required>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-4 col-form-label">Tanggal</label>
                            <div class="col-sm-8">
                                <input type="date" name="tanggal" class="form-control py-2" value="{{ old('tanggal', $form['tanggal'] ?? date('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-4 col-form-label">Nama Supplier</label>
                            <div class="col-sm-8">
                                <input type="text" name="nama_supplier" class="form-control py-2 bg-light" value="{{ $nama_supplier ?? $hutang->kode_supplier ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-4 col-form-label">No Utang</label>
                            <div class="col-sm-8">
                                <input type="text" name="no_referensi" class="form-control py-2 bg-light" value="{{ $hutang->no_utang }}" readonly>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-4 col-form-label">Keterangan</label>
                            <div class="col-sm-8">
                                <textarea name="keterangan" class="form-control py-2" rows="2">{{ old('keterangan', $form['keterangan'] ?? 'Pembayaran utang') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan --}}
                    <div class="col-md-6">
                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-5 col-form-label">Kas yang Digunakan</label>
                            <div class="col-sm-7">
                                <select name="kode_akun" class="form-select py-2" required>
                                    <option value="">-- Pilih Kas --</option>
                                    <option value="1010" {{ old('kode_akun', $form['kode_akun'] ?? '') == '1010' ? 'selected' : '' }}>Kas</option>
                                    <option value="1000" {{ old('kode_akun', $form['kode_akun'] ?? '') == '1000' ? 'selected' : '' }}>Bank</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-5 col-form-label">Total Hutang</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control py-2 bg-light nominal" value="{{ $hutang->total_tagihan }}" readonly>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-5 col-form-label">Cicilan Sebelumnya</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control py-2 bg-light" value="Rp {{ number_format($hutang->total_bayar, 0, ',', '.') }}" readonly>
                            </div>
                        </div>

                        @php
                            $sisaYangHarusDibayar = $hutang->sisa_utang + ($form['jumlah'] ?? 0);
                        @endphp

                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-5 col-form-label">Sisa Utang</label>
                            <div class="col-sm-7">
                                <input type="text" class="form-control py-2 bg-light" value="Rp {{ number_format($sisaYangHarusDibayar, 0, ',', '.') }}" readonly>
                            </div>
                        </div>
                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-5 col-form-label">Nominal Bayar</label>
                            <div class="col-sm-7">
                                <input type="number" name="jumlah" class="form-control py-2" min="1" max="{{ $hutang->sisa_utang + ($form['jumlah'] ?? 0) }}" value="{{ old('jumlah', $form['jumlah'] ?? '') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('hutang.detail', $hutang->no_utang) }}" class="btn btn-outline-secondary px-4 py-2">← Kembali</a>
                    <button type="submit" class="btn btn-primary px-4 py-2">Update Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const formatRupiah = (angka) => {
            return 'Rp ' + parseInt(angka).toLocaleString('id-ID');
        };

        document.querySelectorAll('.nominal').forEach(input => {
            let value = input.value;
            if (!isNaN(value)) {
                input.value = formatRupiah(value);
            }
        });
    });
</script>
@endpush