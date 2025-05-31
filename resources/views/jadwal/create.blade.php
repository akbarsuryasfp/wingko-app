@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Form Jadwal Produksi</h3>

    <form action="{{ route('jadwal.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Tanggal Jadwal</label>
            <input type="date" name="tanggal_jadwal" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="mb-3">
            <label>Keterangan</label>
            <input type="text" name="keterangan" class="form-control">
        </div>

        <hr>
        <h5>Pilih Permintaan Produksi</h5>
       <!-- Tombol -->
<button type="button" class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#permintaanModal">
    + Tambah Permintaan Produksi
</button>

<!-- Modal -->
<div class="modal fade" id="permintaanModal" tabindex="-1" aria-labelledby="permintaanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="permintaanModalLabel">Pilih Permintaan Produksi</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Pilih</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permintaan as $p)
                    <tr>
                        <td>{{ $p->kode_permintaan_produksi }}</td>
                        <td>{{ $p->tanggal }}</td>
                        <td>{{ $p->keterangan }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary"
                                onclick='tambahPermintaan({!! json_encode($p) !!})' data-bs-dismiss="modal">
                                Pilih
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
  </div>
</div>

        <h5>Produk yang Akan Diproduksi</h5>
        <table class="table table-bordered" id="produk-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Jumlah</th>
                    <th>Sumber</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <script>
let produkIndex = 0;
let added = {};

function tambahPermintaan(data) {
    if (added[data.kode_permintaan_produksi]) return;

    data.details.forEach(detail => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                ${detail.produk.nama_produk}
                <input type="hidden" name="produk[${produkIndex}][kode_produk]" value="${detail.kode_produk}">
            </td>
            <td>
                ${detail.unit}
                <input type="hidden" name="produk[${produkIndex}][jumlah]" value="${detail.unit}">
            </td>
            <td>
                ${data.kode_permintaan_produksi}
                <input type="hidden" name="produk[${produkIndex}][kode_sumber]" value="${data.kode_permintaan_produksi}">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">Hapus</button>
            </td>
        `;
        document.querySelector('#produk-table tbody').appendChild(tr);
        produkIndex++;
    });

    added[data.kode_permintaan_produksi] = true;
}

function hapusBaris(el) {
    const row = el.closest('tr');
    const kodeSumber = row.querySelector('input[name$="[kode_sumber]"]').value;
    row.remove();
    delete added[kodeSumber];
}
</script>

            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Simpan Jadwal</button>
    </form>
</div>

<script>
    let produkIndex = 0;
    let added = {};

    function tambahPermintaan() {
        const select = document.getElementById('permintaan-select');
        const selectedOption = select.options[select.selectedIndex];
        const kode = selectedOption.value;

        if (!kode || added[kode]) return;

        const data = JSON.parse(selectedOption.dataset.json);

        data.details.forEach(detail => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    ${detail.produk.nama_produk}
                    <input type="hidden" name="produk[${produkIndex}][kode_produk]" value="${detail.kode_produk}">
                </td>
                <td>
                    ${detail.unit}
                    <input type="hidden" name="produk[${produkIndex}][jumlah]" value="${detail.unit}">
                </td>
                <td>
                    ${data.kode_permintaan_produksi}
                    <input type="hidden" name="produk[${produkIndex}][kode_sumber]" value="${data.kode_permintaan_produksi}">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">Hapus</button>
                </td>
            `;
            document.querySelector('#produk-table tbody').appendChild(tr);
            produkIndex++;
        });

        added[kode] = true;
    }

    function hapusBaris(el) {
        const row = el.closest('tr');
        const kodeSumber = row.querySelector('input[name$="[kode_sumber]"]').value;
        row.remove();
        delete added[kodeSumber];
    }
</script>
@endsection
