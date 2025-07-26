@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3>Tambah Consignee</h3>
            <form action="{{ route('consignee.store') }}" method="POST">
        @csrf
        <!-- Data Consignee -->
        <div class="mb-3">
            <label for="kode_consignee" class="form-label">Kode Consignee</label>
            <input type="text" name="kode_consignee" class="form-control" value="{{ $kode_consignee }}" readonly required tabindex="-1" style="background:#e9ecef;pointer-events:none;">
        </div>
        <div class="mb-3">
            <label for="nama_consignee" class="form-label">Nama Consignee</label>
            <input type="text" name="nama_consignee" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <input type="text" name="alamat" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="no_telp" class="form-label">No. Telepon</label>
            <input type="text" name="no_telp" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="kode_consignee_setor" class="form-label">Kode Consignee Setor</label>
            <input type="text" name="kode_consignee_setor" class="form-control" value="{{ 'CS' . date('ymd') . sprintf('%02d', rand(0,99)) }}" readonly required tabindex="-1" style="background:#e9ecef;pointer-events:none;" maxlength="10">
        </div>
        <!-- Data Setor Banyak Produk -->
        <hr>
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
                <tr>
                    <td class="align-middle">
                        <select name="produk_setor[0][kode_produk]" class="form-control produk-select" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach(\DB::table('t_produk')->get() as $produk)
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
            </tbody>
        </table>
        <button type="button" class="btn btn-primary w-100 mb-2" onclick="addRow()">Tambah Produk</button>
        <hr>
        <div class="d-flex justify-content-between mt-3">
            <div class="d-flex gap-2">
                <a href="{{ route('consignee.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning">Reset</button>
            </div>
            <button type="submit" class="btn btn-success">Submit</button>
        </div>
            </form>
        </div>
    </div>
</div>

<script>
let rowIdx = 1;
function addRow() {
    let table = document.getElementById('produk-setor-table').getElementsByTagName('tbody')[0];
    let newRow = table.rows[0].cloneNode(true);
    // Reset value input
    Array.from(newRow.querySelectorAll('input,select')).forEach(el => el.value = '');
    // Ganti name index agar setiap row unik
    newRow.querySelectorAll('select, input').forEach(function(el) {
        if (el.name) {
            el.name = el.name.replace(/\[\d+\]/, '['+rowIdx+']');
        }
    });
    table.appendChild(newRow);
    rowIdx++;
    // Tambahkan hidden input agar semua baris dikirim
    let hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'produk_setor['+(rowIdx-1)+'][_row]';
    hidden.value = '1';
    newRow.appendChild(hidden);
    updateProdukOptions();
}
function removeRow(btn) {
    let table = document.getElementById('produk-setor-table').getElementsByTagName('tbody')[0];
    if (table.rows.length > 1) {
        btn.closest('tr').remove();
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

// Reset seluruh form (input dan tabel setor produk) ke kondisi awal
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('reset', function(e) {
        setTimeout(function() {
            // Reset field input
            form.querySelector('input[name="nama_consignee"]').value = '';
            form.querySelector('input[name="alamat"]').value = '';
            form.querySelector('input[name="no_telp"]').value = '';
            // Reset tabel setor produk ke satu baris kosong
            let tbody = document.getElementById('produk-setor-table').getElementsByTagName('tbody')[0];
            tbody.innerHTML = '';
            let row = document.createElement('tr');
            row.innerHTML = `
                <td class="align-middle">
                    <select name="produk_setor[0][kode_produk]" class="form-control produk-select" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach(\DB::table('t_produk')->get() as $produk)
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
                </td>`;
            tbody.appendChild(row);
            rowIdx = 1;
            updateProdukOptions();
        }, 10);
    });
    updateProdukOptions();
});
</script>
@endsection