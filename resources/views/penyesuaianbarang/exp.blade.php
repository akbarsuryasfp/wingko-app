@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Form Penyesuaian Barang Kadaluarsa</h4>

    <form action="{{ route('penyesuaian.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal Penyesuaian</label>
            <input type="date" name="tanggal" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan Umum</label>
            <textarea name="keterangan" class="form-control" rows="2"></textarea>
        </div>

        <h5 class="mt-4">Daftar Barang Kadaluarsa</h5>

        <table class="table table-bordered mt-2 align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Jenis</th>
                    <th>Nama</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Alasan</th>
                </tr>
            </thead>
            <tbody>
              @php 
    $no = 1; 
    $rowIndex = 0; 
@endphp

                {{-- Bahan --}}
{{-- Bahan --}}
@foreach ($bahanKadaluarsa as $item)
<tr>
    <td>{{ $no++ }}</td>
    <td>Bahan</td>
    <td>{{ $item->nama_bahan }}</td>
    <td>
        <input type="hidden" name="items[{{ $rowIndex }}][tipe_item]" value="bahan">
        <input type="hidden" name="items[{{ $rowIndex }}][kode_item]" value="{{ $item->kode_bahan }}">
        <input type="hidden" name="items[{{ $rowIndex }}][tanggal_exp]" value="{{ $item->tanggal_exp }}">
        <input type="hidden" name="items[{{ $rowIndex }}][satuan]" value="{{ $item->satuan }}">
        <input type="number" name="items[{{ $rowIndex }}][jumlah]" class="form-control form-control-sm" value="{{ $item->stok }}" required readonly>
    </td>
    <td>
        <input type="number" step="0.01" name="items[{{ $rowIndex }}][harga_satuan]" class="form-control form-control-sm" value="{{ $item->harga }}" required readonly>
    </td>
    <td>
        <input type="text" name="items[{{ $rowIndex }}][alasan]" class="form-control form-control-sm" value="Kadaluarsa">
    </td>
</tr>
@php $rowIndex++; @endphp
@endforeach

               {{-- Produk --}}
@foreach ($produkKadaluarsa as $item)
<tr>
    <td>{{ $no++ }}</td>
    <td>Produk</td>
    <td>{{ $item->nama_produk }}</td>
    <td>
        <input type="hidden" name="items[{{ $rowIndex }}][tipe_item]" value="produk">
        <input type="hidden" name="items[{{ $rowIndex }}][kode_item]" value="{{ $item->kode_produk }}">
        <input type="hidden" name="items[{{ $rowIndex }}][tanggal_exp]" value="{{ $item->tanggal_exp }}">
        <input type="hidden" name="items[{{ $rowIndex }}][satuan]" value="{{ $item->satuan }}">
        <input type="number" name="items[{{ $rowIndex }}][jumlah]" class="form-control form-control-sm" value="{{ $item->stok }}" required readonly>
    </td>
    <td>
        <input type="number" step="0.01" name="items[{{ $rowIndex }}][harga_satuan]" class="form-control form-control-sm" value="{{ $item->harga }}" required readonly>
    </td>
    <td>
        <input type="text" name="items[{{ $rowIndex }}][alasan]" class="form-control form-control-sm" value="Kadaluarsa">
    </td>
</tr>
@php $rowIndex++; @endphp
@endforeach

            </tbody>
        </table>

        <div class="text-end">
            <button type="submit" class="btn btn-danger">Simpan Penyesuaian</button>
        </div>
    </form>
</div>
@endsection
