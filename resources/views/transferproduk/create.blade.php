@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Form Transfer Produk</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('transferproduk.store') }}" method="POST">
        @csrf

        {{-- Header Form --}}
        <div class="row mb-3 g-3">
            <div class="col-md-3">
                <label class="form-label">No Transaksi</label>
                <input type="text" name="no_transaksi" class="form-control" value="{{ $kode_otomatis }}" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal</label>
                <input type="date" name="tanggal" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Lokasi Asal</label>
                <input type="text" name="lokasi_asal" class="form-control" value="Gudang" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label">Lokasi Tujuan</label>
                <select name="lokasi_tujuan" class="form-control" required>
                    <option value="">-- Pilih Lokasi Tujuan --</option>
                    <option value="Toko 1">Toko 1</option>
                    <option value="Toko 2">Toko 2</option>
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
                    <th style="width:10%"></th>
                </tr>
            </thead>
            <tbody id="produk-list">
                <tr class="produk-item">
                    <td>
                        <select name="produk_id[]" class="form-control produk-select" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($produk as $item)
                                <option value="{{ $item->kode_produk }}"
                                    data-satuan="{{ $item->satuan }}"
                                    data-harga="{{ $item->hpp }}"
                                    data-exp="{{ $item->tanggal_exp }}">
                                    {{ $item->nama_produk }} ({{ $item->stok }} {{ $item->satuan }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="jumlah[]" class="form-control" placeholder="Jumlah" required>
                    </td>
                    <td>
                        <input type="text" name="satuan[]" class="form-control" readonly placeholder="Satuan">
                    </td>
                    <td>
                        <input type="number" name="harga[]" class="form-control" readonly placeholder="Harga">
                    </td>
                    <td>
                        <input type="date" name="tanggal_exp[]" class="form-control" placeholder="Tanggal Exp">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger w-100 remove-produk">Hapus</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-secondary mb-3" id="tambah-produk">+ Tambah Produk</button>
        <br>
        <button type="submit" class="btn btn-primary">Simpan Transfer</button>
    </form>
</div>

{{-- Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const produkList = document.getElementById('produk-list');

        document.getElementById('tambah-produk').addEventListener('click', function () {
            const firstItem = produkList.querySelector('.produk-item');
            const clone = firstItem.cloneNode(true);

            clone.querySelectorAll('input').forEach(input => input.value = '');
            clone.querySelectorAll('select').forEach(select => select.selectedIndex = 0);

            produkList.appendChild(clone);
        });

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

        produkList.addEventListener('change', function (e) {
            if (e.target.classList.contains('produk-select')) {
                const selected = e.target.selectedOptions[0];
                const satuan = selected.getAttribute('data-satuan') || '';
                const harga = selected.getAttribute('data-harga') || '';
                const exp = selected.getAttribute('data-exp') || '';
                const produkItem = e.target.closest('.produk-item');
                produkItem.querySelector('input[name="satuan[]"]').value = satuan;
                produkItem.querySelector('input[name="harga[]"]').value = harga;
                produkItem.querySelector('input[name="tanggal_exp[]"]').value = exp;
            }
        });
    });
</script>
@endsection
