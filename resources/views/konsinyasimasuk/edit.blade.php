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
    <form action="{{ route('konsinyasimasuk.update', $konsinyasi->no_surattitipjual) }}" method="POST" id="form-edit-konsinyasi">
        @csrf
        @method('PUT')
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Konsinyasi -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Konsinyasi Masuk</label>
                    <input type="text" name="no_konsinyasimasuk" class="form-control" value="{{ $konsinyasi->no_konsinyasimasuk }}" readonly tabindex="-1" style="background:#e9ecef; pointer-events: none;">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Surat Titip Jual</label>
                    <input type="text" name="no_surattitipjual" class="form-control" value="{{ $konsinyasi->no_surattitipjual }}" required>
                    <input type="hidden" name="no_surattitipjual_lama" value="{{ $konsinyasi->no_surattitipjual }}">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Nama Consignor (Pemilik Barang)</label>
                    <select name="kode_consignor" class="form-control" required>
                        <option value="">---Pilih Consignor---</option>
                        @foreach($consignor as $c)
                            <option value="{{ $c->kode_consignor }}" {{ $konsinyasi->kode_consignor == $c->kode_consignor ? 'selected' : '' }}>{{ $c->nama_consignor }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" id="tanggal_masuk" class="form-control" value="{{ $konsinyasi->tanggal_masuk ?? $konsinyasi->tanggal_titip }}" required>
                    <input type="hidden" name="tanggal_titip" id="tanggal_titip" value="{{ $konsinyasi->tanggal_masuk ?? $konsinyasi->tanggal_titip }}">
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
            </div>
            <div class="d-flex align-items-center gap-3">
                <label class="mb-0">Total Titip</label>
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
    const allProdukKonsinyasi = @json($produkKonsinyasi);
    // Pastikan setiap detail punya nama_produk
    const produkKonsinyasiMap = {};
    allProdukKonsinyasi.forEach(function(pr) {
        produkKonsinyasiMap[pr.kode_produk] = pr.nama_produk;
    });
    daftarProdukTitip = daftarProdukTitip.map(function(item) {
        if (!item.nama_produk && produkKonsinyasiMap[item.kode_produk]) {
            item.nama_produk = produkKonsinyasiMap[item.kode_produk];
        }
        return item;
    });

    // Filter produk berdasarkan consignor
    document.querySelector('select[name="kode_consignor"]').addEventListener('change', function() {
        const consignor = this.value;
        const produkSelect = document.getElementById('kode_produk');
        produkSelect.innerHTML = '<option value="">---Pilih Produk---</option>';
        if (consignor) {
            const produkFiltered = allProdukKonsinyasi.filter(p => p.kode_consignor === consignor);
            produkFiltered.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.kode_produk;
                opt.textContent = p.nama_produk;
                opt.setAttribute('data-nama', p.nama_produk);
                produkSelect.appendChild(opt);
            });
        }
    });

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

        // Cek jika produk sudah ada, jangan tambahkan lagi
        if (daftarProdukTitip.some(p => p.kode_produk === kode_produk)) {
            alert('Produk sudah ada di daftar!');
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
        if (!angka && angka !== 0) return '';
        return 'Rp ' + parseFloat(angka).toLocaleString('id-ID');
    }

    function updateTabelTitip() {
        const tbody = document.querySelector('#daftar-produk-titip tbody');
        tbody.innerHTML = '';
        let totalTitip = 0;
        daftarProdukTitip.forEach((item, index) => {
            const subtotal = Number(item.jumlah_stok) * Number(item.harga_titip);
            item.subtotal = subtotal;
            totalTitip += subtotal;
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_produk}</td>
                    <td><input type="number" class="form-control form-control-sm" value="${item.jumlah_stok}" min="1" onchange="ubahJumlahStok(${index}, this.value)"></td>
                    <td><input type="number" class="form-control form-control-sm" value="${item.harga_titip}" min="1" onchange="ubahHargaTitip(${index}, this.value)"></td>
                    <td>${formatRupiah(subtotal)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBarisTitip(${index})" title="Hapus"><span style='font-size:1.2em;'>&#128465;</span></button></td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
        document.getElementById('total_titip_view').value = formatRupiah(totalTitip);
        document.getElementById('total_titip').value = totalTitip;
        document.getElementById('detail_json').value = JSON.stringify(daftarProdukTitip);
    }

    window.ubahJumlahStok = function(idx, val) {
        const jumlah = Number(val);
        if (jumlah > 0) {
            daftarProdukTitip[idx].jumlah_stok = jumlah;
            daftarProdukTitip[idx].subtotal = jumlah * daftarProdukTitip[idx].harga_titip;
            updateTabelTitip();
        }
    }

    window.ubahHargaTitip = function(idx, val) {
        const harga = Number(val);
        if (harga > 0) {
            daftarProdukTitip[idx].harga_titip = harga;
            daftarProdukTitip[idx].subtotal = harga * daftarProdukTitip[idx].jumlah_stok;
            updateTabelTitip();
        }
    }

    // Inisialisasi produk sesuai consignor terpilih saat load
    document.addEventListener('DOMContentLoaded', function() {
        const consignorSelect = document.querySelector('select[name="kode_consignor"]');
        if (consignorSelect.value) {
            const event = new Event('change');
            consignorSelect.dispatchEvent(event);
        }
        updateTabelTitip();
    });

    // Sinkronkan tanggal_titip dengan tanggal_masuk
    document.getElementById('tanggal_masuk').addEventListener('input', function() {
        document.getElementById('tanggal_titip').value = this.value;
    });

    // Cegah submit jika belum ada produk titip
    document.querySelector('form').addEventListener('submit', function(e) {
        if (daftarProdukTitip.length === 0) {
            alert('Minimal 1 produk titip harus ditambahkan!');
            e.preventDefault();
            return false;
        }
        document.getElementById('total_titip').value = daftarProdukTitip.reduce((sum, item) => sum + (Number(item.subtotal) || 0), 0);
    });

    // Pastikan form action update pakai value lama saat submit
    document.getElementById('form-edit-konsinyasi').addEventListener('submit', function(e) {
        const noSuratLama = document.querySelector('input[name="no_surattitipjual_lama"]').value;
        this.action = this.action.replace(/update\/(.+)$/, 'update/' + encodeURIComponent(noSuratLama));
    });
</script>
@endsection