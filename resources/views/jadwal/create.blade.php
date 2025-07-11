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
        <h5>Pilih Pesanan / Permintaan Produksi</h5>
       <!-- Tombol -->
<button type="button" class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#permintaanModal">
    + Tambah Produksi
</button>

<!-- Modal -->
<div class="modal fade" id="permintaanModal" tabindex="-1" aria-labelledby="permintaanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="permintaanModalLabel">Pilih Permintaan / Pesanan Penjualan</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
            <ul class="nav nav-tabs" id="tabJadwal" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="permintaan-tab" data-bs-toggle="tab" data-bs-target="#permintaan" type="button" role="tab">Permintaan Produksi</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pesanan-tab" data-bs-toggle="tab" data-bs-target="#pesanan" type="button" role="tab">Pesanan Penjualan</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="setor-tab" data-bs-toggle="tab" data-bs-target="#setor" type="button" role="tab">Setor Konsinyasi</button>
                </li>
            </ul>
            <div class="tab-content mt-3">
                <!-- Tab Permintaan -->
                <div class="tab-pane fade show active" id="permintaan" role="tabpanel">
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
                                        onclick='tambahPermintaan(@json($p->load("details.produk")), "permintaan")' data-bs-dismiss="modal">
                                        Pilih
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Tab Pesanan -->
                <div class="tab-pane fade" id="pesanan" role="tabpanel">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Pilih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pesanan as $ps)
                            <tr>
                                <td>{{ $ps->no_pesanan ?? $ps->kode_pesanan }}</td>
                                <td>{{ $ps->tanggal_pesanan ?? $ps->tanggal }}</td>
                                <td>{{ $ps->pelanggan->nama_pelanggan ?? '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary"
                                        onclick='tambahPermintaan(@json($ps->load("details.produk")), "pesanan")' data-bs-dismiss="modal">
                                        Pilih
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Tab Setor Konsinyasi -->
                <div class="tab-pane fade" id="setor" role="tabpanel">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Kode Setor</th>
                                <th>Consignee</th>
                                <th>Produk</th>
                                <th>Jumlah Setor</th>
                                <th>Pilih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($setorKonsinyasi as $setor)
                            <tr>
                                <td>{{ $setor->kode_consignee_setor }}</td>
                                <td>{{ $setor->nama_consignee }}</td>
                                <td>{{ $setor->nama_produk }}</td>
                                <td>{{ $setor->jumlah_setor }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary"
                                        onclick='tambahSetorKonsinyasi(@json($setor))' data-bs-dismiss="modal">
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

function tambahPermintaan(data, type) {
    let kodeSumber = type === "permintaan" ? data.kode_permintaan_produksi : data.no_pesanan || data.kode_pesanan;

    let details = data.details || [];
    details.forEach(detail => {
        let uniqueKey = kodeSumber + '-' + detail.kode_produk;
        if (added[uniqueKey]) return;

        // Pastikan nama produk dan jumlah terbaca
        const namaProduk = detail.produk && detail.produk.nama_produk ? detail.produk.nama_produk : 'undefined';
        const jumlah = detail.jumlah ?? detail.unit ?? 0;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                ${namaProduk}
                <input type="hidden" name="produk[${produkIndex}][kode_produk]" value="${detail.kode_produk}">
            </td>
            <td>
                ${jumlah}
                <input type="hidden" name="produk[${produkIndex}][jumlah]" value="${jumlah}">
            </td>
            <td>
                ${kodeSumber}
                <input type="hidden" name="produk[${produkIndex}][kode_sumber]" value="${kodeSumber}">
                <input type="hidden" name="produk[${produkIndex}][tipe_sumber]" value="${type}">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this, '${uniqueKey}')">Hapus</button>
            </td>
        `;
        document.querySelector('#produk-table tbody').appendChild(tr);
        added[uniqueKey] = true;
        produkIndex++;
    });
}

function tambahSetorKonsinyasi(setor) {
    let uniqueKey = 'setor-' + setor.kode_consignee_setor + '-' + setor.kode_produk;
    if (added[uniqueKey]) return;

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td>
            ${setor.nama_produk}
            <input type="hidden" name="produk[${produkIndex}][kode_produk]" value="${setor.kode_produk}">
        </td>
        <td>
            ${setor.jumlah_setor}
            <input type="hidden" name="produk[${produkIndex}][jumlah]" value="${setor.jumlah_setor}">
        </td>
        <td>
            ${setor.kode_consignee_setor}
            <input type="hidden" name="produk[${produkIndex}][kode_sumber]" value="${setor.kode_consignee_setor}">
            <input type="hidden" name="produk[${produkIndex}][tipe_sumber]" value="setor_konsinyasi">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this, '${uniqueKey}')">Hapus</button>
        </td>
    `;
    document.querySelector('#produk-table tbody').appendChild(tr);
    added[uniqueKey] = true;
    produkIndex++;
}

function hapusBaris(el, uniqueKey) {
    const row = el.closest('tr');
    row.remove();
    if (uniqueKey) {
        delete added[uniqueKey];
    }
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

@if(isset($selectedPermintaan) && $selectedPermintaan)
<script>
document.addEventListener('DOMContentLoaded', function() {
    tambahPermintaan(@json($selectedPermintaan));
});
</script>
@endif
@endsection
