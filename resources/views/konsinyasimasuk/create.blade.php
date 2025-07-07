@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">TAMBAH KONSINYASI MASUK</h3>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('konsinyasimasuk.store') }}" method="POST">
        @csrf
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Konsinyasi -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Titip Jual</label>
                    <input type="text" name="no_surattitipjual" class="form-control" value="{{ $no_surattitipjual }}" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Nama Consignor</label>
                    <select name="kode_consignor" class="form-control" required>
                        <option value="">---Pilih Consignor---</option>
                        @foreach($consignor as $c)
                            <option value="{{ $c->kode_consignor }}">{{ $c->nama_consignor }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Tanggal Masuk</label>
                    <input type="date" name="tanggal_titip" class="form-control" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control">
                </div>
            </div>

            <!-- Kolom Kanan: Data Produk Titip -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Produk</label>
                    <select id="kode_produk" class="form-control">
                        <option value="">---Pilih Produk---</option>
                        <!-- Opsi produk akan diisi via JS -->
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Jumlah Stok</label>
                    <input type="number" id="jumlah_stok" class="form-control">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Harga Titip Jual</label>
                    <input type="number" id="harga_titip" class="form-control">
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary w-100" onclick="tambahProdukTitip()">Tambah Produk</button>
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PENERIMAAN PRODUK</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk-titip">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Jumlah Stok</th>
                    <th>Harga Titip Jual</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="d-flex justify-content-between mt-4">
            <div>
                <a href="{{ route('konsinyasimasuk.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning">Reset</button>
            </div>
            <div class="d-flex align-items-center gap-3">
                <label class="mb-0">Total</label>
                <input type="text" id="total_titip_view" readonly class="form-control" style="width: 160px;">
                <input type="hidden" id="total_titip" name="total_titip">
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
        </div>

        <input type="hidden" name="detail_json" id="detail_json">
    </form>
</div>


<script>
const allProdukKonsinyasi = @json($produkKonsinyasi);

document.querySelector('select[name="kode_consignor"]').addEventListener('change', function() {
    const consignor = this.value;
    const produkSelect = document.getElementById('kode_produk');
    produkSelect.innerHTML = '<option value="">---Pilih Produk---</option>'; // reset

    if (consignor) {
        // Filter produk sesuai consignor
        const produkFiltered = allProdukKonsinyasi.filter(p => p.kode_consignor === consignor);
        produkFiltered.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.kode_produk;
            opt.textContent = p.nama_produk;
            produkSelect.appendChild(opt);
        });
    }
});
</script>
@endsection