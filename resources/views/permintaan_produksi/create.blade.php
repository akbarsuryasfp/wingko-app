@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="mb-4">
                <h2 class="fw-bold text-primary">Form Permintaan Produksi</h2>
                <hr>
            </div>

            <form action="{{ route('permintaan.store') }}" method="POST">
                @csrf

                <!-- Informasi Permintaan -->
                <div class="card shadow-sm rounded mb-4">
                    <div class="card-header bg-primary text-white rounded-top">
                        <i class="bi bi-info-circle"></i> Informasi Permintaan
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="tanggal" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Opsional">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Produk -->
                <div class="card shadow-sm rounded">
                    <div class="card-header bg-success text-white rounded-top">
                        <i class="bi bi-box-seam"></i> Detail Produk
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="produk-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:40%">Produk</th>
                                        <th style="width:30%">Unit</th>
                                        <th style="width:15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($produk as $i => $p)
                                    <tr>
                                        <td>
                                            <select name="produk[{{ $i }}][kode_produk]" class="form-select" required>
                                                <option value="{{ $p->kode_produk }}">{{ $p->nama_produk }}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="produk[{{ $i }}][unit]" class="form-control" min="1" value="{{ $p->selisih }}" required>
                                            <small class="text-muted">Stok gudang: {{ $p->stok_gudang }}, Min: {{ $p->stokmin }}</small>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="hapusBaris(this)">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="tambahBaris()">
                            <i class="bi bi-plus-circle"></i> Tambah Produk
                        </button>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save"></i> Simpan Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

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
                <input type="number" name="produk[${index}][unit]" class="form-control" min="1" placeholder="Jumlah unit" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="hapusBaris(this)">
                    <i class="bi bi-trash"></i> Hapus
                </button>
            </td>
        </tr>`;
        document.querySelector('#produk-table tbody').insertAdjacentHTML('beforeend', row);
        index++;
    }

    function hapusBaris(el) {
        if (confirm('Yakin ingin menghapus baris ini?')) {
            el.closest('tr').remove();
        }
    }

    // Keterangan otomatis
    document.getElementById('tanggal').addEventListener('change', function() {
        const tanggal = this.value;
        if (tanggal) {
            const tglFormat = new Date(tanggal).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'long', year: 'numeric'
            });
            document.getElementById('keterangan').value = `Permintaan produksi harian tanggal ${tglFormat}`;
        } else {
            document.getElementById('keterangan').value = '';
        }
    });

    // Inisialisasi keterangan saat halaman dibuka
    window.addEventListener('DOMContentLoaded', function() {
        const tanggal = document.getElementById('tanggal').value;
        if (tanggal) {
            const tglFormat = new Date(tanggal).toLocaleDateString('id-ID', {
                day: 'numeric', month: 'long', year: 'numeric'
            });
            document.getElementById('keterangan').value = `Permintaan produksi harian tanggal ${tglFormat}`;
        }
    });
</script>
@endsection
