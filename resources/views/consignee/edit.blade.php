@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Edit Consignee</h3>
    <form action="{{ route('consignee.update', $consignee->kode_consignee) }}" method="POST">
        @csrf
        @method('PUT')
        <!-- Data Consignee -->
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
        <div class="mb-3 d-flex align-items-center">
            <label for="no_telp" class="form-label mb-0" style="width:150px;">No. Telepon</label>
            <input type="text" class="form-control" id="no_telp" name="no_telp" value="{{ old('no_telp', $consignee->no_telp) }}" required style="width:300px;">
            @error('no_telp')
                <div class="text-danger ms-2">{{ $message }}</div>
            @enderror
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('consignee.index') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-success">Update</button>
        </div>
        <!-- Data Setor Banyak Produk -->
        <hr>
        <h5>Setor Produk</h5>
        <table class="table table-bordered" id="produk-setor-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Jumlah Setor</th>
                    <th>Keterangan</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php $idx = 0; @endphp
                @foreach($setorList as $setor)
                <tr>
                    <td>
                        <select name="produk_setor[{{ $idx }}][kode_produk]" class="form-control produk-select" required onchange="updateProdukOptions()">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produkList as $produk)
                                <option value="{{ $produk->kode_produk }}" {{ $setor->kode_produk == $produk->kode_produk ? 'selected' : '' }}>
                                    {{ $produk->nama_produk }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="produk_setor[{{ $idx }}][jumlah_setor]" class="form-control" min="1" value="{{ $setor->jumlah_setor }}" required>
                    </td>

                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Hapus</button>
                    </td>
                </tr>
                @php $idx++; @endphp
                @endforeach
                @if($setorList->count() == 0)
                <tr>
                    <td>
                        <select name="produk_setor[0][kode_produk]" class="form-control produk-select" required onchange="updateProdukOptions()">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($produkList as $produk)
                                <option value="{{ $produk->kode_produk }}">{{ $produk->nama_produk }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="produk_setor[0][jumlah_setor]" class="form-control" min="1" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Hapus</button>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
        <button type="button" class="btn btn-success btn-sm" onclick="addRow()">Tambah Produk</button>
        <hr>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('consignee.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
let rowIdx = {{ $setorList->count() }};
function addRow() {
    let table = document.getElementById('produk-setor-table').getElementsByTagName('tbody')[0];
    let newRow = table.rows[0].cloneNode(true);
    Array.from(newRow.querySelectorAll('input,select')).forEach(el => el.value = '');
    newRow.querySelectorAll('select, input').forEach(function(el) {
        if (el.name) {
            el.name = el.name.replace(/\[\d+\]/, '['+rowIdx+']');
        }
    });
    table.appendChild(newRow);
    rowIdx++;
    updateProdukOptions();
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