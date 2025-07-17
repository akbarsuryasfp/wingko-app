@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">INPUT KONSINYASI MASUK</h3>
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
                    <label class="me-2" style="width: 180px;">No Konsinyasi Masuk</label>
                    <input type="text" name="no_konsinyasimasuk" class="form-control" value="{{ $no_konsinyasimasuk }}" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Surat Titip Jual</label>
                    <input type="text" name="no_surat_titip_jual" class="form-control" value="{{ old('no_surat_titip_jual') }}">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Nama Consignor (Pemilik Barang)</label>
                    <select name="kode_consignor" class="form-control" required>
                        <option value="">---Pilih Consignor---</option>
                        @foreach($consignor as $c)
                            <option value="{{ $c->kode_consignor }}">{{ $c->nama_consignor }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" class="form-control" required>
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
                    <label class="me-2" style="width: 120px;">Harga Titip/Produk</label>
                    <input type="number" id="harga_titip" class="form-control">
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary w-100" onclick="tambahProdukTitip()">Tambah Produk</button>
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PRODUK KONSINYASI MASUK</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk-titip">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Jumlah Stok</th>
                    <th>Harga Titip/Produk</th>
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
                <label class="mb-0">Total Titip</label>
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

// Data array untuk detail produk titip
let produkTitipList = [];

function tambahProdukTitip() {
    const kode_produk = document.getElementById('kode_produk').value;
    const jumlah_stok = parseInt(document.getElementById('jumlah_stok').value);
    const harga_titip = parseFloat(document.getElementById('harga_titip').value);
    const produkSelect = document.getElementById('kode_produk');
    const nama_produk = produkSelect.options[produkSelect.selectedIndex]?.text || '';


    // Cek duplikat produk
    if (produkTitipList.some(p => p.kode_produk === kode_produk)) {
        alert('Produk sudah ditambahkan!');
        return;
    }

    const subtotal = jumlah_stok * harga_titip;
    produkTitipList.push({ kode_produk, nama_produk, jumlah_stok, harga_titip, subtotal });
    renderTabelProdukTitip();
    resetInputProduk();
}

function renderTabelProdukTitip() {
    const tbody = document.querySelector('#daftar-produk-titip tbody');
    tbody.innerHTML = '';
    let total = 0;
    function formatRupiah(angka) {
        if (!angka && angka !== 0) return '';
        return 'Rp ' + parseFloat(angka).toLocaleString('id-ID');
    }
    produkTitipList.forEach((item, idx) => {
        total += item.subtotal;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${idx + 1}</td>
            <td>${item.nama_produk}</td>
            <td>${item.jumlah_stok}</td>
            <td>${formatRupiah(item.harga_titip)}</td>
            <td>${formatRupiah(item.subtotal)}</td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusProdukTitip(${idx})" title="Hapus"><span style='font-size:1.2em;'>&#128465;</span></button></td>
        `;
        tbody.appendChild(tr);
    });
    document.getElementById('total_titip_view').value = formatRupiah(total);
    document.getElementById('total_titip').value = total;
    document.getElementById('detail_json').value = JSON.stringify(produkTitipList.map(p => ({
        kode_produk: p.kode_produk,
        jumlah_stok: p.jumlah_stok,
        harga_titip: p.harga_titip,
        subtotal: p.subtotal
    })));
}

function hapusProdukTitip(idx) {
    produkTitipList.splice(idx, 1);
    renderTabelProdukTitip();
}

function resetInputProduk() {
    document.getElementById('kode_produk').value = '';
    document.getElementById('jumlah_stok').value = '';
    document.getElementById('harga_titip').value = '';
}

// Validasi sebelum submit form
const form = document.querySelector('form[action="{{ route('konsinyasimasuk.store') }}"]');
form.addEventListener('submit', function(e) {
    if (produkTitipList.length === 0) {
        alert('Minimal 1 produk harus ditambahkan!');
        e.preventDefault();
        return false;
    }
    // Pastikan detail_json terisi data terbaru
    document.getElementById('detail_json').value = JSON.stringify(produkTitipList.map(p => ({
        kode_produk: p.kode_produk,
        jumlah_stok: p.jumlah_stok,
        harga_titip: p.harga_titip,
        subtotal: p.subtotal
    })));
});
</script>
@endsection