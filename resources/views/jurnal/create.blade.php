@extends('layouts.app')

@section('content')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Form Input Jurnal Manual</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('jurnal.store') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-bold">Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" required placeholder="Contoh: Modal Awal">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label fw-bold">Nomor Bukti</label>
                        <input type="text" name="nomor_bukti" class="form-control" placeholder="Opsional">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35%">Akun</th>
                                <th style="width:20%">Debit</th>
                                <th style="width:20%">Kredit</th>
                                <th style="width:10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="jurnal-details">
                            <tr>
                                <td>
                                    <select name="details[0][kode_akun]" class="form-select akun-select" required>
                                        <option value="">-- Pilih Akun --</option>
                                        @foreach($akuns as $a)
                                            <option value="{{ $a->kode_akun }}">
                                                [{{ $a->kode_akun }}] {{ $a->nama_akun }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="details[0][debit]" class="form-control text-end" step="0.01" min="0"></td>
                                <td><input type="number" name="details[0][kredit]" class="form-control text-end" step="0.01" min="0"></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-success mb-3" onclick="addRow()">
                    <i class="fas fa-plus"></i> Tambah Baris
                </button>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Jurnal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let rowIdx = 1;
function addRow() {
    let akunOptions = `{!! collect($akuns)->map(fn($a) => "<option value='{$a->kode_akun}'>[{$a->kode_akun}] {$a->nama_akun}</option>")->implode('') !!}`;
    let html = `<tr>
        <td>
            <select name="details[${rowIdx}][kode_akun]" class="form-select akun-select" required>
                <option value="">-- Pilih Akun --</option>
                ${akunOptions}
            </select>
        </td>
        <td><input type="number" name="details[${rowIdx}][debit]" class="form-control text-end" step="0.01" min="0"></td>
        <td><input type="number" name="details[${rowIdx}][kredit]" class="form-control text-end" step="0.01" min="0"></td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>`;
    document.getElementById('jurnal-details').insertAdjacentHTML('beforeend', html);
    rowIdx++;
}

function removeRow(btn) {
    btn.closest('tr').remove();
}

// Prevent akun yang sama di debit/kredit
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('akun-select')) {
        let selects = document.querySelectorAll('.akun-select');
        let values = [];
        selects.forEach(s => {
            if (s.value) values.push(s.value);
        });
        selects.forEach(s => {
            let options = s.querySelectorAll('option');
            options.forEach(o => {
                o.disabled = values.includes(o.value) && o.value !== s.value && o.value !== '';
            });
        });
    }
});
</script>
@endsection