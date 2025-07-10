@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Pembayaran Utang</h4>
    <form action="{{ route('hutang.bayar.store', $hutang->no_utang) }}" method="POST" class="card p-4 shadow-sm">
        @csrf
        <div class="row">
            {{-- Kolom Kiri --}}
            <div class="col-md-6">
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Nomor BKK</label>
                    <div class="col-sm-8">
                        <input type="text" name="no_BKK" class="form-control" value="{{ $no_BKK ?? old('no_BKK') }}" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-4 col-form-label">Tanggal</label>
                    <div class="col-sm-8">
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
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
                        <textarea name="keterangan" class="form-control" rows="2">Pembayaran utang</textarea>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan --}}
            <div class="col-md-6">
                <div class="row mb-3">
                    <label class="col-sm-5 col-form-label">Kas yang Digunakan</label>
                    <div class="col-sm-7">
                        <select name="jenis_kas" class="form-control" required>
                            <option value="">-- Pilih Kas --</option>
                            <option value="kas">Kas</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-5 col-form-label">Total Hutang</label>
                    <div class="col-sm-7">
                        <input type="text" name="total_tagihan" class="form-control nominal" value="{{ $hutang->total_tagihan }}" readonly>
                    </div>
                </div>
<div class="row mb-3">
    <label class="col-sm-5 col-form-label">Cicilan Sebelumnya</label>
    <div class="col-sm-7">
        <input type="text" name="total_bayar" class="form-control" 
               value="Rp {{ number_format($hutang->total_bayar, 0, ',', '.') }}" readonly>
    </div>
</div>

<div class="row mb-3">
    <label class="col-sm-5 col-form-label">Sisa Utang</label>
    <div class="col-sm-7">
        <input type="text" name="sisa_utang" class="form-control" 
               value="Rp {{ number_format($hutang->sisa_utang, 0, ',', '.') }}" readonly>
    </div>
</div>
                <div class="row mb-3">
                    <label class="col-sm-5 col-form-label">Nominal Bayar</label>
                    <div class="col-sm-7">
                        <input type="number" name="jumlah" class="form-control" min="1" max="{{ $hutang->sisa_utang }}" required>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden Input dan Tombol --}}
        <input type="hidden" name="kode_akun" value="201"> {{-- akun utang --}}
        <div class="text-end mt-3">
            <button type="submit" class="btn btn-primary">Simpan Pembayaran</button>
            <a href="{{ route('hutang.detail', $hutang->no_utang) }}" class="btn btn-secondary ms-2">Kembali</a>
        </div>
    </form>
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