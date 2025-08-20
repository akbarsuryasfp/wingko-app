@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Terima Barang Retur</h4>
    <form action="{{ route('returbeli.terimaBarang', $retur->no_retur_beli) }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>No Retur</label>
            <input type="text" class="form-control" value="{{ $retur->no_retur_beli }}" readonly>
        </div>
        <div class="mb-3">
            <label>Supplier</label>
            <input type="text" class="form-control" value="{{ $retur->nama_supplier }}" readonly>
        </div>
        <h5>Detail Barang Retur</h5>
        <table class="table">
            <thead>
                <tr class="text-center align-middle">
                    <th>No</th>
                    <th>Nama Bahan</th>
                    <th>Jumlah Terima</th>
                    <th>Harga</th>
                    <th>Tanggal Exp</th>
                </tr>
            </thead>
            <tbody>
                @foreach($retur->details as $detail)
                <tr>
                    <td class="text-center align-middle">{{ $loop->iteration }}</td>
                    <td>
                        <input type="hidden" name="kode_bahan[]" value="{{ $detail->kode_bahan }}">
                        {{ $detail->nama_bahan }}
                    </td>
                    <td>
                        <input type="number" class="form-control" name="jumlah_retur[]" value="{{ $detail->jumlah_retur }}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control" name="harga_beli[]" value="{{ $detail->harga_beli }}" readonly>
                    </td>
    <td>
<input type="date" class="form-control" name="tanggal_exp[]">
    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit" class="btn btn-success">Terima Barang Retur</button>
    </form>
</div>
@endsection