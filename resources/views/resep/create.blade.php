@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Tambah Resep Baru</h3>

    <form action="{{ route('resep.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Kode Resep</label>
            <input type="text" name="kode_resep" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Produk</label>
            <select name="kode_produk" class="form-select" required>
                <option value="">-- Pilih Produk --</option>
                @foreach ($produk as $p)
                    <option value="{{ $p->kode_produk }}">{{ $p->nama_produk }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Keterangan</label>
            <input type="text" name="keterangan" class="form-control">
        </div>

        <hr>
        <h5>Detail Bahan</h5>

        <table class="table table-bordered" id="tabel-bahan">
            <thead>
                <tr>
                    <th>Bahan</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <select name="bahan[0][kode_bahan]" class="form-select" required>
                            <option value="">-- Pilih Bahan --</option>
                            @foreach ($bahan as $b)
                                <option value="{{ $b->kode_bahan }}">{{ $b->nama_bahan }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="bahan[0][jumlah_kebutuhan]" class="form-control" step="0.01" required></td>
                    <td><input type="text" name="bahan[0][satuan]" class="form-control" required></td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">Hapus</button></td>
                </tr>
            </tbody>
        </table>

        <button type="button" class="btn btn-success btn-sm" onclick="tambahBaris()">+ Tambah Bahan</button>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Simpan Resep</button>
        </div>
    </form>
</div>

<script>
    let index = 1;

    function tambahBaris() {
        const baris = `
        <tr>
            <td>
                <select name="bahan[${index}][kode_bahan]" class="form-select" required>
                    <option value="">-- Pilih Bahan --</option>
                    @foreach ($bahan as $b)
                        <option value="{{ $b->kode_bahan }}">{{ $b->nama_bahan }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="bahan[${index}][jumlah_kebutuhan]" class="form-control" step="0.01" required></td>
            <td><input type="text" name="bahan[${index}][satuan]" class="form-control" required></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">Hapus</button></td>
        </tr>
        `;
        document.querySelector('#tabel-bahan tbody').insertAdjacentHTML('beforeend', baris);
        index++;
    }

    function hapusBaris(el) {
        el.closest('tr').remove();
    }
</script>
@endsection
