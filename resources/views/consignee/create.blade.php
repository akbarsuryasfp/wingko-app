@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Tambah Consignee</h3>
    <form action="{{ route('consignee.store') }}" method="POST">
        @csrf
        <!-- Data Consignee -->
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
        <!-- Data Setor Banyak Produk -->
        <hr>
        <h5>Setor Produk</h5>
        <table class="table table-bordered" id="produk-setor-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Jumlah Setor</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="produk_setor[0][kode_produk]" class="form-control" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach(\DB::table('t_produk')->get() as $produk)
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
            </tbody>
        </table>
        <button type="button" class="btn btn-success btn-sm" onclick="addRow()">Tambah Produk</button>
        <hr>
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('consignee.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script>
let rowIdx = 1;
function addRow() {
    let table = document.getElementById('produk-setor-table').getElementsByTagName('tbody')[0];
    let newRow = table.rows[0].cloneNode(true);
    // Reset value input
    Array.from(newRow.querySelectorAll('input,select')).forEach(el => el.value = '');
    // Ganti name index
    newRow.querySelectorAll('select, input').forEach(function(el) {
        if (el.name) {
            el.name = el.name.replace(/\[\d+\]/, '['+rowIdx+']');
        }
    });
    table.appendChild(newRow);
    rowIdx++;
}
function removeRow(btn) {
    let table = document.getElementById('produk-setor-table').getElementsByTagName('tbody')[0];
    if (table.rows.length > 1) {
        btn.closest('tr').remove();
    }
}
</script>
@endsection