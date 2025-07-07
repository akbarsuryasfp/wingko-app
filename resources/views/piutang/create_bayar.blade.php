@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:700px;">
    <h5 class="fw-bold mb-4">
        FORM PEMBAYARAN PIUTANG
    </h5>
    <form action="{{ route('piutang.bayar.store', $piutang->no_piutang) }}" method="POST" class="border rounded p-4 bg-white">
        @csrf
        <table class="table table-borderless mb-0">
            <tbody>
                <tr>
                    <th style="width:180px;">Kas yang Digunakan</th>
                    <td>
                        <select name="kas" class="form-control" required>
                            <option value="">-- Pilih Kas --</option>
                            @foreach($kasList as $kas)
                                <option value="{{ $kas->id_akun }}">{{ $kas->nama_akun }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Nomor BKM</th>
                    <td>
                        <input type="text" name="no_bkm" class="form-control" value="{{ $no_bkm }}" readonly>
                    </td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>
                        <input type="date" name="tanggal_bayar" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </td>
                </tr>
                <tr>
                    <th>Nama Pelanggan</th>
                    <td>
                        <input type="text" class="form-control" value="{{ $pelanggan ? $pelanggan->nama_pelanggan : '-' }}" readonly>
                    </td>
                </tr>
                <tr>
                    <th>No Piutang</th>
                    <td>
                        <input type="text" class="form-control" value="{{ $piutang->no_piutang }}" readonly>
                    </td>
                </tr>
                <tr>
                    <th>Jumlah Piutang</th>
                    <td>
                        <input type="text" class="form-control" value="Rp{{ number_format($piutang->total_tagihan,0,',','.') }}" readonly>
                    </td>
                </tr>
                <tr>
                    <th>Cicilan Sebelumnya</th>
                    <td>
                        <input type="text" class="form-control" value="Rp{{ number_format($piutang->total_bayar,0,',','.') }}" readonly>
                    </td>
                </tr>
                <tr>
                    <th>Sisa Piutang</th>
                    <td>
                        <input type="text" class="form-control" value="Rp{{ number_format($piutang->sisa_piutang,0,',','.') }}" readonly>
                    </td>
                </tr>
                <tr>
                    <th>Nominal Pembayaran</th>
                    <td>
                        <input type="number" name="jumlah_bayar" class="form-control" min="1" max="{{ $piutang->sisa_piutang }}" required>
                    </td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td>
                        <input type="text" name="keterangan" class="form-control" value="Pembayaran piutang">
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-success me-2">Simpan Pembayaran</button>
            <a href="{{ route('piutang.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>
@endsection