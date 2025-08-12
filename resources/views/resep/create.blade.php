@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="mb-4 text-center">Tambah Resep Baru</h3>
                    <form action="{{ route('resep.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="kode_resep" class="form-control" id="kodeResep" 
                                        value="{{ $kode_resep }}" placeholder="Kode Resep" readonly style="background-color: #e9ecef;">
                                    <label for="kodeResep">Kode Resep</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select name="kode_produk" class="form-select" id="produkSelect" required>
                                        <option value="" disabled selected>-- Pilih Produk --</option>
                                        @foreach ($produk as $p)
                                            <option value="{{ $p->kode_produk }}">{{ $p->nama_produk }}</option>
                                        @endforeach
                                    </select>
                                    <label for="produkSelect">Produk</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="keterangan" class="form-control" id="keteranganInput" placeholder="Keterangan">
                            <label for="keteranganInput">Keterangan</label>
                        </div>

                        <hr>
                        <h5 class="mb-3">Detail Bahan</h5>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="tabel-bahan">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th style="width: 35%">Bahan</th>
                                        <th style="width: 20%">Jumlah</th>
                                        <th style="width: 20%">Satuan</th>
                                        <th style="width: 10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select name="bahan[0][kode_bahan]" class="form-select bahan-select" required>
                                                <option value="" disabled selected>-- Pilih Bahan --</option>
                                                @foreach ($bahan as $b)
                                                    <option value="{{ $b->kode_bahan }}">{{ $b->nama_bahan }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="bahan[0][jumlah_kebutuhan]" class="form-control" step="0.01" min="0" placeholder="Jumlah" required>
                                        </td>
                                        <td>
                                            <input type="text" name="bahan[0][satuan]" class="form-control" placeholder="Satuan" required>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="hapusBaris(this)" title="Hapus Baris">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-3 text-end">
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="tambahBaris()">
                                <i class="bi bi-plus-circle"></i> Tambah Bahan
                            </button>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100">Simpan Resep</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Optional: Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<script>
    let index = 1;

    function tambahBaris() {
        const baris = `
        <tr>
            <td>
                <select name="bahan[${index}][kode_bahan]" class="form-select bahan-select" required>
                    <option value="" disabled selected>-- Pilih Bahan --</option>
                    @foreach ($bahan as $b)
                        <option value="{{ $b->kode_bahan }}">{{ $b->nama_bahan }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="bahan[${index}][jumlah_kebutuhan]" class="form-control" step="any" min="0" placeholder="Jumlah" required>
            </td>
            <td>
                <input type="text" name="bahan[${index}][satuan]" class="form-control" placeholder="Satuan" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="hapusBaris(this)" title="Hapus Baris">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
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
