@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h4 class="mb-0 fw-semibold">Edit Transfer Produk</h4>
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

      
<form action="{{ route('transferproduk.update', $transfer->no_transaksi) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mb-2 align-items-center">
        <label class="col-sm-2 col-form-label">No Transaksi</label>
        <div class="col-sm-4">
            <input type="text" class="form-control bg-light" value="{{ $transfer->no_transaksi }}" readonly>
        </div>
    </div>
    <div class="row mb-2 align-items-center">
        <label class="col-sm-2 col-form-label">Tanggal</label>
        <div class="col-sm-4">
            <input type="date" class="form-control" name="tanggal" value="{{ old('tanggal', date('Y-m-d', strtotime($transfer->tanggal))) }}">
        </div>
    </div>
    <div class="row mb-2 align-items-center">
        <label class="col-sm-2 col-form-label">Lokasi Asal</label>
        <div class="col-sm-4">
            <select name="lokasi_asal" class="form-select" required>
                <option value="">Pilih Lokasi</option>
                @foreach($lokasiList as $lokasi)
                    <option value="{{ $lokasi }}" {{ $lokasi == $transfer->lokasi_asal ? 'selected' : '' }}>{{ $lokasi }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row mb-2 align-items-center">
        <label class="col-sm-2 col-form-label">Lokasi Tujuan</label>
        <div class="col-sm-4">
            <select name="lokasi_tujuan" class="form-select" id="lokasi-tujuan" required>
                @foreach($lokasiList as $lokasi)
                    @if($lokasi != $transfer->lokasi_asal)
                        <option value="{{ $lokasi }}" {{ $lokasi == $transfer->lokasi_tujuan ? 'selected' : '' }}>{{ $lokasi }}</option>
                    @endif
                @endforeach
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
    @foreach($groupedDetails as $index => $detail)
    <tr class="produk-item">
        <td class="text-center">{{ $loop->iteration }}</td>
        <td>
            <select name="produk_id[]" class="form-select produk-select" required>
                <option value="">-- Pilih Produk --</option>
                @foreach ($produk as $item)
                    @php
                        $jumlahTransfer = ($detail->kode_produk == $item->kode_produk) ? $detail->keluar : 0;
                        $stokAwal = $item->stok + $jumlahTransfer;
                    @endphp
                    <option value="{{ $item->kode_produk }}"
                        data-satuan="{{ $item->satuan }}"
                        data-exp="{{ $item->tanggal_exp }}"
                        data-stok="{{ $stokAwal }}"
                        {{ $detail->kode_produk == $item->kode_produk ? 'selected' : '' }}>
                        {{ $item->nama_produk }} ({{ $stokAwal }})
                    </option>
                @endforeach
            </select>
        </td>
        <td class="text-center">
            <input type="number" name="jumlah[]" class="form-control text-center"
                   value="{{ $detail->keluar }}" min="1" required>
        </td>
        <td class="text-center">
            <input type="text" name="satuan[]" class="form-control bg-light text-center"
                   value="{{ $detail->satuan ?? '' }}" readonly>
        </td>
        <td class="text-center">
            <input type="date" name="tanggal_exp[]" class="form-control text-center"
                   value="{{ $detail->tanggal_exp ? date('Y-m-d', strtotime($detail->tanggal_exp)) : '' }}" readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm remove-produk" title="Hapus">
                <i class="bi bi-x-lg"></i>
            </button>
        </td>
    </tr>
    @endforeach
</tbody>
        </table>
    </div>
    <div class="d-flex justify-content-start mb-3">
        <button type="button" class="btn btn-sm btn-success" id="tambah-produk">
            <i class="bi bi-plus"></i> Tambah Produk
        </button>
    </div>
    <div class="d-flex justify-content-between">
        <a href="{{ route('transferproduk.index') }}" class="btn btn-secondary"> ← Kembali</a>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
</form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Saat produk dipilih, update satuan
    document.querySelectorAll('.produk-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const satuan = this.options[this.selectedIndex].getAttribute('data-satuan') || '';
            this.closest('tr').querySelector('input[name="satuan[]"]').value = satuan;
        });
        // Trigger sekali saat load (untuk baris yang sudah ada)
        const satuan = select.options[select.selectedIndex].getAttribute('data-satuan') || '';
        select.closest('tr').querySelector('input[name="satuan[]"]').value = satuan;
    });

    // Jika ada tombol tambah produk, pastikan script juga jalan untuk baris baru
    document.getElementById('tambah-produk')?.addEventListener('click', function() {
    const tbody = document.getElementById('produk-list');
    const rowCount = tbody.rows.length + 1;
    const produkOptions = `@foreach ($produk as $item)
        <option value="{{ $item->kode_produk }}" data-satuan="{{ $item->satuan }}" data-exp="{{ $item->tanggal_exp }}">{{ $item->nama_produk }} ({{ $item->stok }})</option>
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
    tbody.appendChild(newRow);

    // Aktifkan event satuan otomatis
    newRow.querySelector('.produk-select').addEventListener('change', function() {
        const satuan = this.options[this.selectedIndex].getAttribute('data-satuan') || '';
        newRow.querySelector('input[name="satuan[]"]').value = satuan;
    });

    // Aktifkan event hapus baris
    newRow.querySelector('.remove-produk').addEventListener('click', function() {
        newRow.remove();
        // Update nomor urut
        document.querySelectorAll('#produk-list tr').forEach((tr, i) => {
            tr.querySelector('td').innerText = i + 1;
        });
    });
});
});
</script>
@endsection