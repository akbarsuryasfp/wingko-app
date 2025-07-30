
@extends('layouts.app')

@section('content')
<div class="container py-2">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h4 class="mb-0 fw-semibold">Form Transfer Produk</h4>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('transferproduk.store') }}" method="POST">
                @csrf

                {{-- Header Form --}}
                <div class="row mb-2 align-items-center">
                    <label class="col-sm-2 col-form-label">No Transaksi</label>
                    <div class="col-sm-4">
                        <input type="text" name="no_transaksi" class="form-control bg-light" value="{{ $kode_otomatis }}" readonly>
                    </div>
                </div>
                <div class="row mb-2 align-items-center">
                    <label class="col-sm-2 col-form-label">Tanggal</label>
                    <div class="col-sm-4">
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="row mb-2 align-items-center">
                    <label class="col-sm-2 col-form-label">Lokasi Asal</label>
                    <div class="col-sm-4">
                        <select name="lokasi_asal" class="form-select" id="lokasi-asal" required>
                            <option value="Gudang" {{ old('lokasi_asal', $lokasiAsal) == 'Gudang' ? 'selected' : '' }}>Gudang</option>
                            <option value="Toko 1" {{ old('lokasi_asal', $lokasiAsal) == 'Toko 1' ? 'selected' : '' }}>Toko 1</option>
                            <option value="Toko 2" {{ old('lokasi_asal', $lokasiAsal) == 'Toko 2' ? 'selected' : '' }}>Toko 2</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-2 align-items-center">
                    <label class="col-sm-2 col-form-label">Lokasi Tujuan</label>
                    <div class="col-sm-4">
                        <select name="lokasi_tujuan" class="form-select" id="lokasi-tujuan" required>
                            <option value="">-- Pilih Lokasi Tujuan --</option>
                            @if(old('lokasi_asal', $lokasiAsal) == 'Gudang')
                                <option value="Toko 1">Toko 1</option>
                                <option value="Toko 2">Toko 2</option>
                            @else
                                <option value="Gudang">Gudang</option>
                            @endif
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <div>
                    <h5 class="fw-semibold">Daftar Produk</h5>
                </div>
                <div class="table-responsive mb-2">
                    <table class="table table-bordered table-sm align-middle" id="produk-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:5%;">No</th>
                                <th class="text-center" style="width:30%;">Produk</th>
                                <th class="text-center" style="width:12%;">Jumlah Kirim</th>
                                <th class="text-center" style="width:12%;">Satuan</th>
                                <th class="text-center" style="width:18%;">Tanggal Exp</th>
                                <th class="text-center" style="width:5%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="produk-list">
                            <tr class="produk-item">
                                <td class="text-center">1</td>
                                <td>
                                    <select name="produk_id[]" class="form-select produk-select" required>
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach ($produk as $item)
                                            <option value="{{ $item->kode_produk }}"
                                                data-satuan="{{ $item->satuan }}"
                                                data-exp="{{ $item->tanggal_exp }}">
                                                {{ $item->nama_produk }} ({{ $item->stok }} {{ $item->satuan }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input type="number" name="jumlah[]" class="form-control text-center" placeholder="Jumlah" min="1" required>
                                </td>
                                <td class="text-center">
                                    <input type="text" name="satuan[]" class="form-control bg-light text-center" readonly placeholder="Satuan">
                                </td>
                                <td class="text-center">
                                    <input type="date" name="tanggal_exp[]" class="form-control text-center" readonly placeholder="Tanggal Exp">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm remove-produk" title="Hapus">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-start mb-2">
                    <button type="button" class="btn btn-sm btn-success" id="tambah-produk">
                        <i class="bi bi-plus"></i> Tambah Produk
                    </button>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('transferproduk.index') }}" class="btn btn-secondary">← Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const produkList = document.getElementById('produk-list');
    const lokasiAsalSelect = document.getElementById('lokasi-asal');
    const lokasiTujuanSelect = document.getElementById('lokasi-tujuan');

    // Handle origin location change
    lokasiAsalSelect.addEventListener('change', function() {
        // Update destination options based on origin
        const origin = this.value;
        let destinationOptions = '<option value="">-- Pilih Lokasi Tujuan --</option>';
        if (origin === 'Gudang') {
            destinationOptions += '<option value="Toko 1">Toko 1</option>';
            destinationOptions += '<option value="Toko 2">Toko 2</option>';
        } else {
            destinationOptions += '<option value="Gudang">Gudang</option>';
        }
        lokasiTujuanSelect.innerHTML = destinationOptions;
    });

    // Tambah produk
    document.getElementById('tambah-produk').addEventListener('click', function () {
        const rowCount = produkList.querySelectorAll('tr').length + 1;
        const produkOptions = `@foreach ($produk as $item)
            <option value="{{ $item->kode_produk }}" data-satuan="{{ $item->satuan }}" data-exp="{{ $item->tanggal_exp }}">
                {{ $item->nama_produk }} ({{ $item->stok }} {{ $item->satuan }})
            </option>
        @endforeach`;

        const newRow = document.createElement('tr');
        newRow.classList.add('produk-item');
        newRow.innerHTML = `
            <td class="text-center">${rowCount}</td>
            <td>
                <select name="produk_id[]" class="form-select produk-select" required>
                    <option value="">-- Pilih Produk --</option>
                    ${produkOptions}
                </select>
            </td>
            <td class="text-center">
                <input type="number" name="jumlah[]" class="form-control text-center" min="1" required>
            </td>
            <td class="text-center">
                <input type="text" name="satuan[]" class="form-control bg-light text-center" readonly>
            </td>
            <td class="text-center">
                <input type="date" name="tanggal_exp[]" class="form-control text-center" readonly>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm remove-produk" title="Hapus">
                    <i class="bi bi-x-lg"></i>
                </button>
            </td>
        `;
        produkList.appendChild(newRow);

        // Event satuan otomatis
        newRow.querySelector('.produk-select').addEventListener('change', function() {
            const satuan = this.options[this.selectedIndex].getAttribute('data-satuan') || '';
            const exp = this.options[this.selectedIndex].getAttribute('data-exp') || '';
            newRow.querySelector('input[name="satuan[]"]').value = satuan;
            newRow.querySelector('input[name="tanggal_exp[]"]').value = exp;
        });

        // Event hapus baris
        newRow.querySelector('.remove-produk').addEventListener('click', function() {
            newRow.remove();
            // Update nomor urut
            produkList.querySelectorAll('tr').forEach((tr, i) => {
                tr.querySelector('td').innerText = i + 1;
            });
        });
    });

    // Event satuan otomatis untuk baris pertama
    produkList.querySelectorAll('.produk-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const satuan = this.options[this.selectedIndex].getAttribute('data-satuan') || '';
            const exp = this.options[this.selectedIndex].getAttribute('data-exp') || '';
            this.closest('tr').querySelector('input[name="satuan[]"]').value = satuan;
            this.closest('tr').querySelector('input[name="tanggal_exp[]"]').value = exp;
        });
    });

    // Event hapus baris untuk baris pertama
    produkList.querySelectorAll('.remove-produk').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const items = produkList.querySelectorAll('.produk-item');
            if (items.length > 1) {
                btn.closest('tr').remove();
                produkList.querySelectorAll('tr').forEach((tr, i) => {
                    tr.querySelector('td').innerText = i + 1;
                });
            } else {
                alert('Minimal satu produk harus ada.');
            }
        });
    });
});
</script>
@endsection