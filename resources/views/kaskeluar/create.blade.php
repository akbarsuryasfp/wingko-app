@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Input Kas Keluar</h4>
    <form action="{{ route('kaskeluar.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>No BKK</label>
            <input type="text" name="no_BKK" class="form-control" value="{{ $no_BKK }}" readonly>
        </div>
        <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="mb-3">
            <label>No Referensi (Opsional)</label>
            <input type="text" name="no_referensi" class="form-control">
        </div>
        <div class="mb-3">
            <label>Akun Lawan</label>
            <select name="id_akun" class="form-control" required>
                <option value="">-- Pilih Akun --</option>
                @foreach($akun as $a)
                    <option value="{{ $a->id_akun }}">
                        [{{ $a->kode_akun }}] {{ $a->nama_akun }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Jumlah</label>
            <input type="number" name="jumlah" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Penerima</label>
            <input type="text" name="penerima" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control"></textarea>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Kas yang Digunakan</label>
            <div class="col-md-8">
                <select name="jenis_kas" class="form-control" required>
                    <option value="">-- Pilih Kas --</option>
                    <option value="kas">Kas</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
@endsection
