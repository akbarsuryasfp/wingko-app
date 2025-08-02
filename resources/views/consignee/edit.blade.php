@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3>Edit Consignee</h3>
            <form action="{{ route('consignee.update', $consignee->kode_consignee) }}" method="POST">
                @csrf
                @method('PUT')
                <!-- Data Consignee -->
                <div class="mb-3">
                    <label for="kode_consignee" class="form-label">Kode Consignee</label>
                    <input type="text" name="kode_consignee" class="form-control" value="{{ $consignee->kode_consignee }}" readonly required tabindex="-1" style="background:#e9ecef;pointer-events:none;">
                </div>
                <div class="mb-3">
                    <label for="nama_consignee" class="form-label">Nama Consignee</label>
                    <input type="text" name="nama_consignee" class="form-control" value="{{ old('nama_consignee', $consignee->nama_consignee) }}" required>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <input type="text" name="alamat" class="form-control" value="{{ old('alamat', $consignee->alamat) }}" required>
                </div>
                <div class="mb-3">
                    <label for="no_telp" class="form-label">No. Telepon</label>
                    <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp', $consignee->no_telp) }}" required>
                </div>
                <!-- Data Setor Banyak Produk -->
                <hr>
                <div class="mb-3">
                    <label for="kode_consignee_setor" class="form-label">Kode Consignee Setor</label>
                    <input type="text" name="kode_consignee_setor" class="form-control" value="{{ $setorList[0]->kode_consignee_setor ?? ('CS' . date('ymd') . sprintf('%02d', rand(0,99))) }}" readonly required tabindex="-1" style="background:#e9ecef;pointer-events:none;" maxlength="10">
                </div>
                <label class="form-label mb-3">Setor Produk</label>
                <table class="table table-bordered" id="produk-setor-table">
                    <thead>
                        <tr>
                            <th class="text-center align-middle">Produk</th>
                            <th class="text-center align-middle">Jumlah Setor</th>
                            <th class="text-center align-middle" style="width:70px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $idx = 0; @endphp
                        @foreach($setorList as $setor)
                        <tr>
                            <td class="align-middle">
                                <select name="produk_setor[{{ $idx }}][kode_produk]" class="form-control produk-select" required onchange="updateProdukOptions()">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($produkList as $produk)
                                        <option value="{{ $produk->kode_produk }}" {{ $setor->kode_produk == $produk->kode_produk ? 'selected' : '' }}>
                                            {{ $produk->nama_produk }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="align-middle">
                                <input type="number" name="produk_setor[{{ $idx }}][jumlah_setor]" class="form-control" min="1" value="{{ $setor->jumlah_setor }}" required>
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @php $idx++; @endphp
                        @endforeach
                        @if($setorList->count() == 0)
                        <tr>
                            <td class="align-middle">
                                <select name="produk_setor[0][kode_produk]" class="form-control produk-select" required onchange="updateProdukOptions()">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($produkList as $produk)
                                        <option value="{{ $produk->kode_produk }}">{{ $produk->nama_produk }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="align-middle">
                                <input type="number" name="produk_setor[0][jumlah_setor]" class="form-control" min="1" required>
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
                <button type="button" class="btn btn-primary w-100 mb-2" onclick="addRow()">Tambah Produk</button>
                <hr>
                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('consignee.index') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-success">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let rowIdx = {{ $setorList->count() }};
function addRow() {
    let table = document.getElementById('produk-setor-table').getElementsByTagName('tbody')[0];
    let newRow = table.rows[0].cloneNode(true);
    Array.from(newRow.querySelectorAll('input,select')).forEach(el => el.value = '');
    // Ganti name index agar setiap row unik
    newRow.querySelectorAll('select, input').forEach(function(el) {
        if (el.name) {
            el.name = el.name.replace(/\[\d+\]/, '['+rowIdx+']');
        }
    });
    table.appendChild(newRow);
    rowIdx++;
    updateProdukOptions();

    // Tambahkan hidden input agar semua baris dikirim
    let hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'produk_setor['+(rowIdx-1)+'][_row]';
    hidden.value = '1';
    newRow.appendChild(hidden);
}
function removeRow(btn) {
    let table = document.getElementById('produk-setor-table').getElementsByTagName('tbody')[0];
    if (table.rows.length > 1) {
        btn.closest('tr').remove();
        updateProdukOptions();
    }
}

// Disable produk yang sudah dipilih di select lain
function updateProdukOptions() {
    let selects = document.querySelectorAll('.produk-select');
    let selectedValues = Array.from(selects).map(s => s.value).filter(v => v);

    selects.forEach(function(select) {
        let currentValue = select.value;
        Array.from(select.options).forEach(function(opt) {
            if (opt.value === "" || opt.value === currentValue) {
                opt.disabled = false;
            } else if (selectedValues.includes(opt.value)) {
                opt.disabled = true;
            } else {
                opt.disabled = false;
            }
        });
    });
}

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    updateProdukOptions();
});
</script>
@endsection