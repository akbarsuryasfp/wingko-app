@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Form Pembayaran Hutang</h3>
    <form action="{{ route('hutang.store') }}" method="POST">
        @csrf

        <div class="row mb-3">
            <div class="col">
                <label>No. BKK</label>
                <input type="text" name="no_bkk" class="form-control" required>
            </div>
            <div class="col">
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label>No Utang</label>
            <select name="hutang_id" class="form-control" required>
                <option value="">-- Pilih No Utang --</option>
                @foreach($hutangs as $hutang)
                    <option value="{{ $hutang->id }}">
                        {{ $hutang->no_utang }} - {{ $hutang->supplier }} (Sisa: Rp{{ number_format($hutang->sisa,0,',','.') }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label>Kas yang digunakan</label>
                <input type="text" name="kas_digunakan" class="form-control" required>
            </div>
            <div class="col">
                <label>Bayar Sekarang</label>
                <input type="number" name="bayar" class="form-control" required>
            </div>
        </div>

        <button type="submit" class="btn btn-success">✅ Submit</button>
        <a href="{{ route('hutang.index') }}" class="btn btn-secondary">🔙 Back</a>
    </form>
</div>
@endsection
