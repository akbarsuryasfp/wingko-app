@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Edit Kas Keluar</h4>
    <form action="{{ route('kaskeluar.update', $item->no_BKK) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>No BKK</label>
            <input type="text" class="form-control" value="{{ $item->no_BKK }}" readonly>
        </div>
        <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="{{ $item->tanggal }}" required>
        </div>
        <div class="mb-3">
            <label>No Referensi (Opsional)</label>
            <input type="text" name="no_referensi" class="form-control" value="{{ $item->no_referensi }}">
        </div>
        <div class="mb-3">
            <label>Akun Lawan</label>
            <select name="id_akun" class="form-control" required>
                <option value="">-- Pilih Akun --</option>
                @php
                    $akun = \DB::table('t_akun')->get();
                @endphp
                @foreach($akun as $a)
                    <option value="{{ $a->id_akun }}" {{ $item->kode_akun == $a->kode_akun ? 'selected' : '' }}>
                        [{{ $a->kode_akun }}] {{ $a->nama_akun }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Jumlah</label>
            <input type="number" name="jumlah" class="form-control" value="{{ $item->jumlah }}" required>
        </div>
        <div class="mb-3">
            <label>Penerima</label>
            <input type="text" name="penerima" class="form-control" value="{{ $item->penerima }}" required>
        </div>
        <div class="mb-3">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control">{{ $item->keterangan }}</textarea>
        </div>
        <div class="mb-3 row">
            <label class="col-md-4 col-form-label">Kas yang Digunakan</label>
            <div class="col-md-8">
                <select name="jenis_kas" class="form-control" required>
                    <option value="">-- Pilih Kas --</option>
                    <option value="kas" {{ $item->jenis_kas == 'kas' ? 'selected' : '' }}>Kas</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('kaskeluar.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection