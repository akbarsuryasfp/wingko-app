@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Edit Transfer Produk</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('transferproduk.update', $transfer->no_transaksi) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Header Form --}}
        <div class="row mb-3 g-3">
            <div class="col-md-3">
                <label class="form-label">No Transaksi</label>
                <input type="text" class="form-control" value="{{ $transfer->no_transaksi }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" 
                       value="{{ date('Y-m-d', strtotime($transfer->tanggal)) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Lokasi Asal</label>
                <input type="text" class="form-control" value="{{ $transfer->lokasi }}" readonly>
                <input type="hidden" name="lokasi_asal" value="{{ $transfer->lokasi }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Lokasi Tujuan</label>
                <select name="lokasi_tujuan" class="form-control" required>
                    @if($transfer->lokasi === 'Gudang')
                        <option value="Toko 1" {{ str_contains($transfer->keterangan, 'Toko 1') ? 'selected' : '' }}>Toko 1</option>
                        <option value="Toko 2" {{ str_contains($transfer->keterangan, 'Toko 2') ? 'selected' : '' }}>Toko 2</option>
                    @else
                        <option value="Gudang" selected>Gudang</option>
                    @endif
                </select>
            </div>
        </div>

        <hr>

        {{-- List Produk --}}
        <table class="table table-bordered align-middle" id="produk-table">
            <thead class="table-light">
                <tr>
                    <th style="width:22%">Produk</th>
                    <th style="width:12%">Jumlah Kirim</th>
                    <th style="width:12%">Satuan</th>
                    <th style="width:12%">HPP</th>
                    <th style="width:18%">Tanggal Exp</th>
                    <th style="width:10%">Aksi</th>
                </tr>
            </thead>
            <tbody id="produk-list">
                @foreach($details as $index => $detail)
                @php
                    // Calculate available stock (current stock + transfer amount)
                    $currentStock = DB::table('t_kartupersproduk')
                        ->where('kode_produk', $detail->kode_produk)
                        ->where('lokasi', $transfer->lokasi)
                        ->select(DB::raw('SUM(masuk) - SUM(keluar) as stok'))
                        ->first()->stok + $detail->keluar;
                @endphp
                <tr class="produk-item">
                    <td>
                        <select name="produk_id[]" class="form-control produk-select" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($produk as $item)
                                <option value="{{ $item->kode_produk }}"
                                    data-satuan="{{ $item->satuan }}"
                                    data-harga="{{ $item->hpp }}"
                                    data-exp="{{ $item->tanggal_exp }}"
                                    data-stok="{{ $item->kode_produk == $detail->kode_produk ? $currentStock : $item->stok }}"
                                    {{ $detail->kode_produk == $item->kode_produk ? 'selected' : '' }}>
                                    {{ $item->nama_produk }} ({{ $item->kode_produk == $detail->kode_produk ? $currentStock : $item->stok }} {{ $item->satuan }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="jumlah[]" class="form-control" 
                               value="{{ $detail->keluar }}" min="1" required>
                    </td>
                    <td>
                        <input type="text" name="satuan[]" class="form-control" 
                               value="{{ $detail->satuan }}" readonly>
                    </td>
                    <td>
                        <input type="number" name="harga[]" class="form-control" 
                               value="{{ $detail->hpp }}" readonly>
                    </td>
                    <td>
                        <input type="date" name="tanggal_exp[]" class="form-control" 
                               value="{{ $detail->tanggal_exp ? date('Y-m-d', strtotime($detail->tanggal_exp)) : '' }}">
                    </td>
<td>
    <button type="button" class="btn btn-danger w-30 remove-produk" title="Hapus">
        <i class="bi bi-trash"></i>
    </button>
</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-secondary mb-3" id="tambah-produk">+ Tambah Produk</button>
        <br>
        <div class="d-flex justify-content-between">
            <a href="{{ route('transferproduk.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</div>

{{-- Script --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const produkList = document.getElementById('produk-list');
    const lokasiAsal = "{{ $transfer->lokasi }}";

    // Tambah produk baru
    document.getElementById('tambah-produk').addEventListener('click', function () {
        const firstItem = produkList.querySelector('.produk-item');
        const clone = firstItem.cloneNode(true);

        // Reset values
        clone.querySelectorAll('input').forEach(input => {
            if (input.type !== 'hidden') input.value = '';
        });
        clone.querySelector('select').selectedIndex = 0;

        produkList.appendChild(clone);
    });

    // Hapus produk
    produkList.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-produk')) {
            const items = produkList.querySelectorAll('.produk-item');
            if (items.length > 1) {
                e.target.closest('.produk-item').remove();
            } else {
                alert('Minimal satu produk harus ada.');
            }
        }
    });

    // Update satuan, harga, dan stok saat produk dipilih
    produkList.addEventListener('change', function (e) {
        if (e.target.classList.contains('produk-select')) {
            const selected = e.target.selectedOptions[0];
            const satuan = selected.getAttribute('data-satuan') || '';
            const harga = selected.getAttribute('data-harga') || '';
            const exp = selected.getAttribute('data-exp') || '';
            const stok = selected.getAttribute('data-stok') || '';
            const produkItem = e.target.closest('.produk-item');
            
            // Update product info
            produkItem.querySelector('input[name="satuan[]"]').value = satuan;
            produkItem.querySelector('input[name="harga[]"]').value = harga;
            
            // Update stock display
            const optionText = `${selected.textContent.split(' (')[0]} (${stok} ${satuan})`;
            selected.textContent = optionText;
            
            // Update expiration date if empty
            const expInput = produkItem.querySelector('input[name="tanggal_exp[]"]');
            if (exp && !expInput.value) {
                expInput.value = exp;
            }
        }
    });
});
</script>
@endsection