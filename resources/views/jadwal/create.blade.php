@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="mb-4">Buat Jadwal Produksi</h3>

                    @if ($errors->any())
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <strong>Perhatian!</strong> Mohon lengkapi data yang wajib diisi.
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('jadwal.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <label for="tanggal_jadwal" class="col-sm-3 col-form-label">Tanggal Jadwal</label>
                            <div class="col-sm-4">
                                <input type="date" name="tanggal_jadwal" class="form-control" value="{{ $tanggalJadwal ?? date('Y-m-d') }}" required>
                                <small class="text-muted">Pilih tanggal pelaksanaan produksi.</small>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="keterangan" class="col-sm-3 col-form-label">Keterangan</label>
                            <div class="col-sm-9">
                                <input type="text" name="keterangan" class="form-control" value="{{ $keterangan ?? '' }}" readonly>
                                <small class="text-muted">Keterangan diisi otomatis dari permintaan/pesanan.</small>
                            </div>
                        </div>

                        <hr>
                        <h5 class="mb-3">Pilih Permintaan / Pesanan Produksi</h5>
                        <button type="button" class="btn btn-secondary mb-3" data-bs-toggle="modal" data-bs-target="#permintaanModal">
                            + Tambah Produksi
                        </button>

                        <!-- Modal -->
                        <div class="modal fade" id="permintaanModal" tabindex="-1" aria-labelledby="permintaanModalLabel" aria-hidden="true">
                          <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="permintaanModalLabel">Pilih Permintaan / Pesanan Penjualan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body pt-3 pb-1 px-3">
                                    <ul class="nav nav-tabs mb-3" id="tabJadwal" role="tablist" style="border-bottom: 2px solid #dee2e6;">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active fw-bold text-dark bg-white" id="permintaan-tab" data-bs-toggle="tab" data-bs-target="#permintaan" type="button" role="tab" aria-controls="permintaan" aria-selected="true" style="border: 1px solid #dee2e6;">
                                                Permintaan Produksi
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link fw-bold text-dark bg-white" id="pesanan-tab" data-bs-toggle="tab" data-bs-target="#pesanan" type="button" role="tab" aria-controls="pesanan" aria-selected="false" style="border: 1px solid #dee2e6;">
                                                Pesanan Penjualan
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link fw-bold text-dark bg-white" id="setor-tab" data-bs-toggle="tab" data-bs-target="#setor" type="button" role="tab" aria-controls="setor" aria-selected="false" style="border: 1px solid #dee2e6;">
                                                Setor Konsinyasi
                                            </button>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <!-- Tab Permintaan -->
                                        <div class="tab-pane fade show active" id="permintaan" role="tabpanel" aria-labelledby="permintaan-tab">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm align-middle text-center">
                                                    <thead class="table-light">
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
                                                            <td>{{ $p->no_permintaan_produksi }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y') }}</td>
                                                            <td class="text-start">{{ $p->keterangan }}</td>
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
                                        </div>
                                        <!-- Tab Pesanan -->
                                        <div class="tab-pane fade" id="pesanan" role="tabpanel" aria-labelledby="pesanan-tab">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm align-middle text-center">
                                                    <thead class="table-light">
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
                                                            <td>{{ \Carbon\Carbon::parse($ps->tanggal_pesanan ?? $ps->tanggal)->format('d-m-Y') }}</td>
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
                                        </div>
                                        <!-- Tab Setor Konsinyasi -->
                                        <div class="tab-pane fade" id="setor" role="tabpanel" aria-labelledby="setor-tab">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm align-middle text-center">
                                                    <thead class="table-light">
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
                        </div>

                        <h5 class="mt-4 mb-2">Produk yang Akan Diproduksi</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="produk-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Jumlah</th>
                                        <th>Sumber</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Baris produk akan diisi JS -->
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-success btn-lg">Simpan Jadwal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let produkIndex = 0;
let added = {};

function tambahPermintaan(data, type) {
    let kodeSumber = type === "permintaan" ? data.no_permintaan_produksi : data.no_pesanan || data.kode_pesanan;
    let details = data.details || [];
    details.forEach(detail => {
        let uniqueKey = kodeSumber + '-' + detail.kode_produk;
        if (added[uniqueKey]) return;

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
                <input type="hidden" name="produk[${produkIndex}][no_sumber]" value="${kodeSumber}">
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
            <input type="hidden" name="produk[${produkIndex}][no_sumber]" value="${setor.kode_consignee_setor}">
            <input type="hidden" name="produk[${produkIndex}][tipe_sumber]" value="konsinyasi">
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

// Otomatis update keterangan saat tanggal jadwal berubah
document.addEventListener('DOMContentLoaded', function() {
    const tanggalInput = document.querySelector('input[name="tanggal_jadwal"]');
    const ketInput = document.querySelector('input[name="keterangan"]');
    if (tanggalInput && ketInput) {
        tanggalInput.addEventListener('change', function() {
            const tgl = this.value;
            if (tgl) {
                const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                const bulan = [
                    'Januari','Februari','Maret','April','Mei','Juni',
                    'Juli','Agustus','September','Oktober','November','Desember'
                ];
                const tglObj = new Date(tgl + 'T00:00:00');
                const namaHari = hari[tglObj.getDay()];
                const tanggal = tglObj.getDate().toString().padStart(2, '0');
                const namaBulan = bulan[tglObj.getMonth()];
                const tahun = tglObj.getFullYear();
                ketInput.value = `Jadwal Produksi Hari ${namaHari}, ${tanggal} ${namaBulan} ${tahun}`;
            } else {
                ketInput.value = '';
            }
        });
    }
});

@if(isset($selectedPermintaan) && $selectedPermintaan)
document.addEventListener('DOMContentLoaded', function() {
    tambahPermintaan(@json($selectedPermintaan));
});
@endif
</script>
@endsection
