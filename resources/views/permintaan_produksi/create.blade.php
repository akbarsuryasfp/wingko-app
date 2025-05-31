@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Form Permintaan Produksi</h3>

    <form action="{{ route('permintaan.store') }}" method="POST">
        @csrf

        <!-- Bagian Atas -->
        <div class="card mb-4">
            <div class="card-header">Informasi Permintaan</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="tanggal" class="form-label">Tanggal</label>
                    <input type="date" class="form-control" name="tanggal" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <input type="text" class="form-control" name="keterangan" placeholder="Opsional">
                </div>
            </div>
        </div>

        <!-- Bagian Bawah -->
        <div class="card">
            <div class="card-header">Detail Produk</div>
            <div class="card-body">
                <table class="table table-bordered" id="produk-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Unit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="produk[0][kode_produk]" class="form-select" required>
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach ($produk as $p)
                                        <option value="{{ $p->kode_produk }}">{{ $p->nama_produk }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="produk[0][unit]" class="form-control" min="1" required>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <button type="button" class="btn btn-success btn-sm" onclick="tambahBaris()">+ Tambah Produk</button>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Simpan Permintaan</button>
        </div>
    </form>
</div>

<script>
    let index = 1;

    function tambahBaris() {
        const row = `
        <tr>
            <td>
                <select name="produk[${index}][kode_produk]" class="form-select" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach ($produk as $p)
                        <option value="{{ $p->kode_produk }}">{{ $p->nama_produk }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="produk[${index}][unit]" class="form-control" min="1" required>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">Hapus</button>
            </td>
        </tr>`;
        document.querySelector('#produk-table tbody').insertAdjacentHTML('beforeend', row);
        index++;
    }

    function hapusBaris(el) {
        el.closest('tr').remove();
    }
</script>
@endsection
