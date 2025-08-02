@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h4 class="mb-0">Form Penyesuaian Barang Kadaluarsa</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('penyesuaian.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="tanggal" class="form-label">Tanggal Penyesuaian</label>
                    <input type="date" name="tanggal" class="form-control" 
                           value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan Umum</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="2">Kadaluarsa</textarea>
                </div>

                <h5 class="mt-4">Daftar Barang Kadaluarsa</h5>

                @if ($tipe === 'bahan')
                <div class="table-responsive">
                    <table class="table table-bordered mt-2 align-middle text-center">
    <thead class="table-light">
        <tr>
            <th>No</th>
            <th>Tanggal Expired</th>
            <th>Nama</th>
            <th>Jumlah</th>
            <th>Harga Satuan</th>
            <th>Sub Total</th>
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
            <td>{{ $item->nama_bahan }}</td>
            <td>
                <input type="hidden" name="items[{{ $rowIndex }}][tipe_item]" value="bahan">
                <input type="hidden" name="items[{{ $rowIndex }}][jenis]" value="Bahan">
                <input type="hidden" name="items[{{ $rowIndex }}][kode_item]" value="{{ $item->kode_bahan }}">
                <input type="hidden" name="items[{{ $rowIndex }}][tanggal_exp]" value="{{ $item->tanggal_exp }}">
                <input type="hidden" name="items[{{ $rowIndex }}][satuan]" value="{{ $item->satuan }}">
                <input type="hidden" name="items[{{ $rowIndex }}][alasan]" value="Kadaluarsa">
                <input type="number" name="items[{{ $rowIndex }}][jumlah]" class="form-control form-control-sm" value="{{ $item->stok }}" required readonly>
            </td>
            <td>
                Rp{{ number_format($item->harga, 0, ',', '.') }}
                <input type="hidden" name="items[{{ $rowIndex }}][harga_satuan]" value="{{ $item->harga }}">
            </td>
            <td>
                Rp{{ number_format($subTotal, 0, ',', '.') }}
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
            <td>{{ $item->nama_produk }}</td>
            <td>
                <input type="hidden" name="items[{{ $rowIndex }}][tipe_item]" value="produk">
                <input type="hidden" name="items[{{ $rowIndex }}][jenis]" value="Produk">
                <input type="hidden" name="items[{{ $rowIndex }}][kode_item]" value="{{ $item->kode_produk }}">
                <input type="hidden" name="items[{{ $rowIndex }}][tanggal_exp]" value="{{ $item->tanggal_exp }}">
                <input type="hidden" name="items[{{ $rowIndex }}][satuan]" value="{{ $item->satuan }}">
                <input type="hidden" name="items[{{ $rowIndex }}][alasan]" value="Kadaluarsa">
                <input type="number" name="items[{{ $rowIndex }}][jumlah]" class="form-control form-control-sm" value="{{ $item->stok }}" required readonly>
            </td>
            <td>
                Rp{{ number_format($item->harga, 0, ',', '.') }}
                <input type="hidden" name="items[{{ $rowIndex }}][harga_satuan]" value="{{ $item->harga }}">
            </td>
            <td>
                Rp{{ number_format($subTotal, 0, ',', '.') }}
            </td>
        </tr>
        @php $rowIndex++; @endphp
        @endforeach

        {{-- Total --}}
        <tr class="fw-bold table-secondary">
            <td colspan="5" class="text-end">Total</td>
            <td colspan="2" class="text-start">Rp{{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>
                </div>
                @endif

                @if ($tipe === 'produk')
                <div class="table-responsive">
                    <table class="table table-bordered mt-2 align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Tanggal Expired</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Sub Total</th>
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
                        <td style="text-align: justify; text-justify: inter-word;">{{ $item->nama_produk }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_exp)->format('d M Y') }}</td>
                        <td>
                            <input type="hidden" name="items[{{ $rowIndex }}][tipe_item]" value="produk">
                            <input type="hidden" name="items[{{ $rowIndex }}][kode_item]" value="{{ $item->kode_produk }}">
                            <input type="hidden" name="items[{{ $rowIndex }}][tanggal_exp]" value="{{ $item->tanggal_exp }}">
                            <input type="hidden" name="items[{{ $rowIndex }}][satuan]" value="{{ $item->satuan }}">
                            <input type="hidden" name="items[{{ $rowIndex }}][lokasi]" value="{{ $item->lokasi ?? '' }}">
                            <input type="hidden" name="items[{{ $rowIndex }}][bentuk]" value="{{ $item->bentuk ?? '' }}">
                            <input type="number" name="items[{{ $rowIndex }}][jumlah]" class="form-control form-control-sm" value="{{ $item->stok }}" required readonly style="text-align: center;">            </td>
                        <td>
                            Rp{{ number_format($item->harga, 0, ',', '.') }}
                            <input type="hidden" name="items[{{ $rowIndex }}][harga_satuan]" value="{{ $item->harga }}">
                        </td>
                        <td>
                            Rp{{ number_format($subTotal, 0, ',', '.') }}
                        </td>
                    </tr>
            @php $rowIndex++; @endphp
            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif

                <div class="d-flex justify-content-end mt-4 gap-2">
                    <a href="{{ route('welcome') }}" class="btn btn-secondary px-4 py-2">← Kembali</a>
                    <button type="submit" class="btn btn-danger px-4 py-2">Simpan Penyesuaian</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelector('textarea[name="keterangan"]').value = 'kadaluarsa';
});
</script>
@endpush