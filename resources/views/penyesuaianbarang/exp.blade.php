@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Form Penyesuaian Barang Kadaluarsa</h4>

    <form action="{{ route('penyesuaian.store') }}" method="POST">
        @csrf
<div class="mb-3">
    <label for="tanggal" class="form-label">Tanggal Penyesuaian</label>
    <input type="date" name="tanggal" class="form-control" 
           value="{{ date('Y-m-d') }}" required>
</div>

        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan Umum</label>
            <textarea name="keterangan" class="form-control" rows="2"></textarea>
        </div>

        <h5 class="mt-4">Daftar Barang Kadaluarsa</h5>

        @if ($tipe === 'bahan')
        <table class="table table-bordered mt-2 align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal Expired</th>
                    <th>Jenis</th>
                    <th>Nama</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Sub Total</th>
                    <th>Alasan</th>
                </tr>
            </thead>
            <tbody>
              @php 
    $no = 1; 
    $rowIndex = 0; 
    $grandTotal = 0;
@endphp

                {{-- Bahan --}}
@foreach ($bahanKadaluarsa as $item)
@php
    $item->harga = $item->harga;
    $subTotal = $item->stok * $item->harga;
    $grandTotal += $subTotal;
@endphp
<tr>
    <td>{{ $no++ }}</td>
    <td>{{ \Carbon\Carbon::parse($item->tanggal_exp)->format('d M Y') }}</td>
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
        Rp{{ number_format($item->harga, 0, ',', '.') }}
        <input type="hidden" name="items[{{ $rowIndex }}][harga_satuan]" value="{{ $item->harga }}">
    </td>
    <td>
        Rp{{ number_format($subTotal, 0, ',', '.') }}
    </td>
    <td>
        <input type="text" name="items[{{ $rowIndex }}][alasan]" class="form-control form-control-sm" value="Kadaluarsa">
    </td>
</tr>
@php $rowIndex++; @endphp
@endforeach

               {{-- Produk --}}
@foreach ($produkKadaluarsa as $item)
@php
    $item->harga = $item->hpp;
    $subTotal = $item->stok * $item->harga;
    $grandTotal += $subTotal;
@endphp
<tr>
    <td>{{ $no++ }}</td>
    <td>{{ \Carbon\Carbon::parse($item->tanggal_exp)->format('d M Y') }}</td>
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
        Rp{{ number_format($item->harga, 0, ',', '.') }}
        <input type="hidden" name="items[{{ $rowIndex }}][harga_satuan]" value="{{ $item->harga }}">
    </td>
    <td>
        Rp{{ number_format($subTotal, 0, ',', '.') }}
    </td>
    <td>
        <input type="text" name="items[{{ $rowIndex }}][alasan]" class="form-control form-control-sm" value="Kadaluarsa">
    </td>
</tr>
@php $rowIndex++; @endphp
@endforeach

{{-- Total --}}
<tr class="fw-bold table-secondary">
    <td colspan="6" class="text-end">Total</td>
    <td colspan="2" class="text-start">Rp{{ number_format($grandTotal, 0, ',', '.') }}</td>
</tr>
            </tbody>
        </table>
        @endif

        @if ($tipe === 'produk')
        <table class="table table-bordered mt-2 align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal Expired</th>
                    <th>Jenis</th>
                    <th>Nama</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Sub Total</th>
                    <th>Alasan</th>
                </tr>
            </thead>
            <tbody>
              @php 
    $no = 1; 
    $rowIndex = 0; 
    $grandTotal = 0;
@endphp

                {{-- Produk --}}
@foreach ($produkKadaluarsa as $item)
@php
    $item->harga = $item->hpp;
    $subTotal = $item->stok * $item->harga;
    $grandTotal += $subTotal;
@endphp
<tr>
    <td>{{ $no++ }}</td>
    <td>{{ \Carbon\Carbon::parse($item->tanggal_exp)->format('d M Y') }}</td>
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
        Rp{{ number_format($item->harga, 0, ',', '.') }}
        <input type="hidden" name="items[{{ $rowIndex }}][harga_satuan]" value="{{ $item->harga }}">
    </td>
    <td>
        Rp{{ number_format($subTotal, 0, ',', '.') }}
    </td>
    <td>
        <input type="text" name="items[{{ $rowIndex }}][alasan]" class="form-control form-control-sm" value="Kadaluarsa">
    </td>
</tr>
@php $rowIndex++; @endphp
@endforeach

{{-- Total --}}
<tr class="fw-bold table-secondary">
    <td colspan="6" class="text-end">Total</td>
    <td colspan="2" class="text-start">Rp{{ number_format($grandTotal, 0, ',', '.') }}</td>
</tr>
            </tbody>
        </table>
        @endif

        @if ($tipe === null)
        <table class="table table-bordered mt-2 align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal Expired</th>
                    <th>Jenis</th>
                    <th>Nama</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Sub Total</th>
                    <th>Alasan</th>
                </tr>
            </thead>
            <tbody>
              @php 
    $no = 1; 
    $rowIndex = 0; 
    $grandTotal = 0;
@endphp

                {{-- Bahan --}}
@foreach ($bahanKadaluarsa as $item)
@php
    $item->harga = $item->harga;
    $subTotal = $item->stok * $item->harga;
    $grandTotal += $subTotal;
@endphp
<tr>
    <td>{{ $no++ }}</td>
    <td>{{ \Carbon\Carbon::parse($item->tanggal_exp)->format('d M Y') }}</td>
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
        Rp{{ number_format($item->harga, 0, ',', '.') }}
        <input type="hidden" name="items[{{ $rowIndex }}][harga_satuan]" value="{{ $item->harga }}">
    </td>
    <td>
        Rp{{ number_format($subTotal, 0, ',', '.') }}
    </td>
    <td>
        <input type="text" name="items[{{ $rowIndex }}][alasan]" class="form-control form-control-sm" value="Kadaluarsa">
    </td>
</tr>
@php $rowIndex++; @endphp
@endforeach

               {{-- Produk --}}
@foreach ($produkKadaluarsa as $item)
@php
    $item->harga = $item->hpp;
    $subTotal = $item->stok * $item->harga;
    $grandTotal += $subTotal;
@endphp
<tr>
    <td>{{ $no++ }}</td>
    <td>{{ \Carbon\Carbon::parse($item->tanggal_exp)->format('d M Y') }}</td>
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
        Rp{{ number_format($item->harga, 0, ',', '.') }}
        <input type="hidden" name="items[{{ $rowIndex }}][harga_satuan]" value="{{ $item->harga }}">
    </td>
    <td>
        Rp{{ number_format($subTotal, 0, ',', '.') }}
    </td>
    <td>
        <input type="text" name="items[{{ $rowIndex }}][alasan]" class="form-control form-control-sm" value="Kadaluarsa">
    </td>
</tr>
@php $rowIndex++; @endphp
@endforeach

{{-- Total --}}
<tr class="fw-bold table-secondary">
    <td colspan="6" class="text-end">Total</td>
    <td colspan="2" class="text-start">Rp{{ number_format($grandTotal, 0, ',', '.') }}</td>
</tr>
            </tbody>
        </table>
        @endif

        <div class="text-end">
            <a href="{{ route('welcome') }}" class="btn btn-secondary me-2">Back</a>
            <button type="submit" class="btn btn-danger">Simpan Penyesuaian</button>
        </div>
    </form>
</div>
@endsection
