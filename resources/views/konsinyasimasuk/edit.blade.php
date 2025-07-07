@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">EDIT KONSINYASI MASUK</h3>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('konsinyasimasuk.update', $konsinyasi->no_surattitipjual) }}" method="POST">
        @csrf
        @method('PUT')
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Konsinyasi -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Kode Titip Jual</label>
                    <input type="text" name="no_surattitipjual" class="form-control" value="{{ $konsinyasi->no_surattitipjual }}" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Nama Consignor</label>
                    <select name="kode_consignor" class="form-control" required>
                        <option value="">---Pilih Consignor---</option>
                        @foreach($consignor as $c)
                            <option value="{{ $c->kode_consignor }}" {{ $konsinyasi->kode_consignor == $c->kode_consignor ? 'selected' : '' }}>{{ $c->nama_consignor }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Tanggal Masuk</label>
                    <input type="date" name="tanggal_titip" class="form-control" value="{{ $konsinyasi->tanggal_titip }}" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" value="{{ $konsinyasi->keterangan }}">
                </div>
            </div>

            <!-- Kolom Kanan: Data Produk Titip -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Produk</label>
                    <select id="kode_produk" class="form-control">
                        <option value="">---Pilih Produk---</option>
                        @foreach($produk as $pr)
                            <option value="{{ $pr->kode_produk }}" data-nama="{{ $pr->nama_produk }}">{{ $pr->nama_produk }}</option>
                        @endforeach
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

        <h4 class="text-center">DAFTAR PENERIMAAN PRODUK KONSINYASI</h4>
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
                <button type="submit" class="btn btn-success">Update</button>
            </div>
        </div>

        <input type="hidden" name="detail_json" id="detail_json">
    </form>
</div>

<script>
    // Inisialisasi dari backend
    let daftarProdukTitip = @json($details);

    function tambahProdukTitip() {
        const produkSelect = document.getElementById('kode_produk');
        const kode_produk = produkSelect.value;
        const nama_produk = produkSelect.options[produkSelect.selectedIndex]?.dataset.nama || '';
        const jumlah_stok = Number(document.getElementById('jumlah_stok').value);
        const harga_titip = Number(document.getElementById('harga_titip').value);

        if (!kode_produk || isNaN(jumlah_stok) || isNaN(harga_titip) || jumlah_stok <= 0 || harga_titip <= 0) {
            alert("Silakan lengkapi data produk titip dengan benar.");
            return;
        }

        const subtotal = jumlah_stok * harga_titip;

        daftarProdukTitip.push({ kode_produk, nama_produk, jumlah_stok, harga_titip, subtotal });
        updateTabelTitip();

        // Reset input produk titip
        produkSelect.selectedIndex = 0;
        document.getElementById('jumlah_stok').value = '';
        document.getElementById('harga_titip').value = '';
    }

    function hapusBarisTitip(index) {
        daftarProdukTitip.splice(index, 1);
        updateTabelTitip();
    }

    function formatRupiah(angka) {
        return angka.toLocaleString('id-ID');
    }

    function updateTabelTitip() {
        const tbody = document.querySelector('#daftar-produk-titip tbody');
        tbody.innerHTML = '';

        let totalTitip = 0;

        daftarProdukTitip.forEach((item, index) => {
            const subtotal = Number(item.subtotal) || 0;
            totalTitip += subtotal;

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_produk}</td>
                    <td>${item.jumlah_stok}</td>
                    <td>${formatRupiah(item.harga_titip)}</td>
                    <td>${formatRupiah(subtotal)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBarisTitip(${index})">X</button></td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('total_titip_view').value = formatRupiah(totalTitip);
        document.getElementById('total_titip').value = totalTitip;
        document.getElementById('detail_json').value = JSON.stringify(daftarProdukTitip);
    }

    // Inisialisasi tabel saat halaman dibuka
    updateTabelTitip();

    // Cegah submit jika belum ada produk titip
    document.querySelector('form').addEventListener('submit', function(e) {
        if (daftarProdukTitip.length === 0) {
            alert('Minimal 1 produk titip harus ditambahkan!');
            e.preventDefault();
            return false;
        }
        document.getElementById('total_titip').value = daftarProdukTitip.reduce((sum, item) => sum + (Number(item.subtotal) || 0), 0);
    });
</script>
@endsection