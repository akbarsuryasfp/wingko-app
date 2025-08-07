@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-4">INPUT KONSINYASI KELUAR</h3>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('konsinyasikeluar.store') }}" method="POST">
        @csrf
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Konsinyasi -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Konsinyasi Keluar</label>
                    <input type="text" name="kode_setor" class="form-control" required value="{{ $kodeOtomatis ?? old('kode_setor') }}" readonly style="pointer-events: none; background: #e9ecef;">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Surat Konsinyasi Keluar</label>
                    <input type="text" name="no_suratpengiriman" id="no_suratpengiriman" class="form-control" style="width:100%;" required value="{{ $noSuratOtomatis ?? old('no_suratpengiriman') }}" readonly>
                </div>
                <div class="mb-1 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;"></label>
                    <div class="form-control" style="background: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; min-height: 38px;">Nomor surat akan ditampilkan secara lengkap setelah Anda memilih Nama Consignee (Mitra) dan Tanggal Setor.</div>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Nama Consignee (Mitra)</label>
                    <select name="kode_consignee" class="form-control" required>
                        <option value="">---Pilih Consignee---</option>
                        @foreach($consignees as $c)
                            <option value="{{ $c->kode_consignee }}" {{ old('kode_consignee')==$c->kode_consignee?'selected':'' }}>{{ $c->nama_consignee }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Tanggal Setor</label>
                    <input type="date" name="tanggal_setor" class="form-control" required value="{{ old('tanggal_setor') }}">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control">
                </div>
            </div>

            <!-- Kolom Kanan: Data Produk Setor -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Nama Produk</label>
                    <select id="kode_produk" class="form-control">
                        <option value="">---Pilih Produk---</option>
                        <!-- Opsi produk akan diisi via JS -->
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Jumlah Setor</label>
                    <input type="number" id="jumlah_setor" class="form-control">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Satuan</label>
                    <input type="text" id="satuan" class="form-control" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Harga Setor/Satuan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="harga_setor" class="form-control" autocomplete="off">
                    </div>
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary w-100" onclick="tambahProdukSetor()">Tambah Produk</button>
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PRODUK KONSINYASI KELUAR</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk-setor">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Satuan</th>
                    <th>Jumlah Setor</th>
                    <th>Harga Setor/Satuan</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="d-flex justify-content-between mt-4">
            <div>
                <a href="{{ route('konsinyasikeluar.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning">Reset</button>
            </div>
            <div class="d-flex align-items-center gap-3">
                <label class="mb-0">Total Setor</label>
                <div class="input-group" style="width: 180px;">
                    <span class="input-group-text">Rp</span>
                    <input type="text" id="total_setor_view" readonly class="form-control" style="background:#e9ecef;pointer-events:none;">
                </div>
                <input type="hidden" id="total_setor" name="total_setor">
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
        </div>

        <input type="hidden" name="detail_json" id="detail_json">
            </form>
        </div>
    </div>
</div>

<script>
const allProdukKonsinyasi = @json($produkList);
const produkConsigneeMap = @json($produkConsigneeMap ?? []);

// --- Nomor Surat Otomatis ---
function getMonthRomawi(month) {
    const romawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
    return romawi[month-1] || '';
}
const tanggalSetorInput = document.querySelector('input[name="tanggal_setor"]');
const noSuratpengirimanInput = document.getElementById('no_suratpengiriman');

function updateNoSuratpengiriman() {
    let base = noSuratpengirimanInput.value;
    if (!base) return;
    // base: 002/KONS-KELUAR/WBP-SMG/
    const date = new Date(tanggalSetorInput.value);
    if (!tanggalSetorInput.value) return;
    const bulan = date.getMonth() + 1;
    const tahun = date.getFullYear();
    const bulanRomawi = getMonthRomawi(bulan);
    // Ganti bagian setelah /WBP-SMG/ menjadi /VII/2025
    base = base.replace(/\/WBP-SMG\/(.*)?$/, `/WBP-SMG/${bulanRomawi}/${tahun}`);
    noSuratpengirimanInput.value = base;
}

tanggalSetorInput.addEventListener('change', updateNoSuratpengiriman);
if (tanggalSetorInput.value) {
    updateNoSuratpengiriman();
}

// Populate produk select
function updateProdukSelect() {
    const produkSelect = document.getElementById('kode_produk');
    produkSelect.innerHTML = '<option value="">---Pilih Produk---</option>';
    allProdukKonsinyasi.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.kode_produk;
        opt.textContent = p.nama_produk;
        opt.setAttribute('data-satuan', p.satuan);
        // Set harga_setor, harga_jual, harga_beli ke data attribute jika ada
        if (p.harga_setor) opt.setAttribute('data-harga_setor', p.harga_setor);
        if (p.harga_jual) opt.setAttribute('data-harga_jual', p.harga_jual);
        if (p.harga_beli) opt.setAttribute('data-harga_beli', p.harga_beli);
        produkSelect.appendChild(opt);
    });
}
updateProdukSelect();

document.getElementById('kode_produk').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const satuan = selected?.getAttribute('data-satuan') || '';
    document.getElementById('satuan').value = satuan;
    // Harga default: Moaci = 25000, Wingko Babat = 20000, selain itu cek data produk
    let harga = '';
    const nama = selected?.textContent?.toLowerCase() || '';
    if (nama.includes('moaci')) {
        harga = 25000;
    } else if (nama.includes('wingko babat')) {
        harga = 20000;
    } else {
        harga = selected?.getAttribute('data-harga_setor')
            || selected?.getAttribute('data-harga_jual')
            || selected?.getAttribute('data-harga_beli')
            || '';
    }
    document.getElementById('harga_setor').value = harga ? formatNumberInput(harga) : '';
});

// Data array untuk detail produk setor
let produkSetorList = [];

function formatNumberInput(val) {
    val = String(val).replace(/[^\d]/g, '');
    if (!val) return '';
    return val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
function parseNumberInput(val) {
    return parseInt(String(val).replace(/\D/g, '')) || 0;
}
function tambahProdukSetor() {
    const kode_produk = document.getElementById('kode_produk').value;
    const jumlah_setor = parseInt(document.getElementById('jumlah_setor').value);
    const harga_setor = parseNumberInput(document.getElementById('harga_setor').value);
    const satuan = document.getElementById('satuan').value;
    const produkSelect = document.getElementById('kode_produk');
    const nama_produk = produkSelect.options[produkSelect.selectedIndex]?.text || '';

    if (!kode_produk || !jumlah_setor || !harga_setor) {
        alert('Lengkapi data produk!');
        return;
    }
    // Cek duplikat produk
    if (produkSetorList.some(p => p.kode_produk === kode_produk)) {
        alert('Produk sudah ditambahkan!');
        return;
    }
    const subtotal = jumlah_setor * harga_setor;
    produkSetorList.push({ kode_produk, nama_produk, jumlah_setor, satuan, harga_setor, subtotal });
    renderTabelProdukSetor();
    resetInputProduk();
}

function formatRupiah(angka) {
    if (!angka && angka !== 0) return '';
    return parseInt(angka).toLocaleString('id-ID');
}
function renderTabelProdukSetor() {
    const tbody = document.querySelector('#daftar-produk-setor tbody');
    tbody.innerHTML = '';
    let total = 0;
    produkSetorList.forEach((item, idx) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${idx + 1}</td>
            <td>${item.nama_produk}</td>
            <td>${item.satuan}</td>
            <td><input type="number" class="form-control form-control-sm jumlah-edit" data-idx="${idx}" value="${item.jumlah_setor || ''}" min="1" style="width:100px;"></td>
            <td><div class="input-group"><span class="input-group-text">Rp</span><input type="text" class="form-control form-control-sm harga-edit" data-idx="${idx}" value="${item.harga_setor ? formatRupiah(item.harga_setor) : ''}" style="width:100px;" inputmode="numeric"></div></td>
            <td class="subtotal">${formatRupiah(item.subtotal)}</td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusProdukSetor(${idx})" title="Hapus"><i class='bi bi-trash'></i></button></td>
        `;
        tbody.appendChild(tr);
    });
    // Update total setor dan field hidden
    const totalSetor = produkSetorList.reduce((a,b)=>a+(parseInt(b.subtotal)||0),0);
    document.getElementById('total_setor_view').value = totalSetor > 0 ? formatRupiah(totalSetor) : '';
    document.getElementById('total_setor').value = totalSetor;
    document.getElementById('detail_json').value = JSON.stringify(produkSetorList.map(p => ({
        kode_produk: p.kode_produk,
        jumlah_setor: p.jumlah_setor,
        satuan: p.satuan,
        harga_setor: p.harga_setor,
        subtotal: p.subtotal
    })));
    // Event listener untuk input jumlah/harga
    document.querySelectorAll('.jumlah-edit').forEach(input => {
        input.addEventListener('input', function() {
            const idx = this.dataset.idx;
            produkSetorList[idx].jumlah_setor = parseInt(this.value) || '';
            produkSetorList[idx].subtotal = (parseInt(produkSetorList[idx].jumlah_setor)||0) * (parseInt(produkSetorList[idx].harga_setor)||0);
            // Update subtotal kolom
            this.closest('tr').querySelector('.subtotal').textContent = formatRupiah(produkSetorList[idx].subtotal);
            // Update total setor dan field hidden
            const totalSetor = produkSetorList.reduce((a,b)=>a+(parseInt(b.subtotal)||0),0);
            document.getElementById('total_setor_view').value = formatRupiah(totalSetor);
            document.getElementById('total_setor').value = totalSetor;
            document.getElementById('detail_json').value = JSON.stringify(produkSetorList.map(p => ({
                kode_produk: p.kode_produk,
                jumlah_setor: p.jumlah_setor,
                satuan: p.satuan,
                harga_setor: p.harga_setor,
                subtotal: p.subtotal
            })));
        });
    });
    document.querySelectorAll('.harga-edit').forEach(input => {
        input.addEventListener('input', function(e) {
            // Live format ribuan saat mengetik
            let cursor = this.selectionStart;
            let oldLength = this.value.length;
            let val = this.value.replace(/[^\d]/g, '');
            // Update data
            const idx = this.dataset.idx;
            produkSetorList[idx].harga_setor = parseInt(val) || '';
            produkSetorList[idx].subtotal = (parseInt(produkSetorList[idx].jumlah_setor)||0) * (parseInt(produkSetorList[idx].harga_setor)||0);
            // Format value dengan titik ribuan
            this.value = formatNumberInput(val);
            let newLength = this.value.length;
            this.setSelectionRange(cursor + (newLength - oldLength), cursor + (newLength - oldLength));
            // Update subtotal kolom
            this.closest('tr').querySelector('.subtotal').textContent = formatRupiah(produkSetorList[idx].subtotal);
            // Update total setor dan field hidden
            const totalSetor = produkSetorList.reduce((a,b)=>a+(parseInt(b.subtotal)||0),0);
            document.getElementById('total_setor_view').value = formatRupiah(totalSetor);
            document.getElementById('total_setor').value = totalSetor;
            document.getElementById('detail_json').value = JSON.stringify(produkSetorList.map(p => ({
                kode_produk: p.kode_produk,
                jumlah_setor: p.jumlah_setor,
                satuan: p.satuan,
                harga_setor: p.harga_setor,
                subtotal: p.subtotal
            })));
        });
        input.addEventListener('blur', function() {
            if (this.value) {
                this.value = formatNumberInput(this.value);
            }
        });
        input.addEventListener('focus', function() {
            let val = (produkSetorList[this.dataset.idx]?.harga_setor || 0).toString();
            this.value = val;
            this.setSelectionRange(this.value.length, this.value.length);
        });
    });
}

// Saat consignee dipilih, auto-populate daftar produk konsinyasi ke tabel
const consigneeSelect = document.querySelector('select[name="kode_consignee"]');
if (consigneeSelect) {
    consigneeSelect.addEventListener('change', function() {
        updateProdukSelect();
        const kodeConsignee = this.value;
        if (!kodeConsignee || !produkConsigneeMap[kodeConsignee]) return;
        // Reset list produk setor
        produkSetorList = [];
        produkConsigneeMap[kodeConsignee].forEach(p => {
            let harga_setor = '';
            const nama = (p.nama_produk || '').toLowerCase();
            if (nama.includes('moaci')) {
                harga_setor = 25000;
            } else if (nama.includes('wingko babat')) {
                harga_setor = 20000;
            }
            produkSetorList.push({
                kode_produk: p.kode_produk,
                nama_produk: p.nama_produk,
                jumlah_setor: p.jumlah_setor || '',
                satuan: p.satuan,
                harga_setor: harga_setor,
                subtotal: (parseInt(p.jumlah_setor)||0) * (parseInt(harga_setor)||0)
            });
        });
        renderTabelProdukSetor();
    });
}

// Validasi sebelum submit form
const form = document.querySelector('form[action="{{ route('konsinyasikeluar.store') }}"]');
form.addEventListener('submit', function(e) {
    if (produkSetorList.length === 0) {
        alert('Minimal 1 produk harus ditambahkan!');
        e.preventDefault();
        return false;
    }
    // Pastikan detail_json terisi data terbaru
    document.getElementById('detail_json').value = JSON.stringify(produkSetorList.map(p => ({
        kode_produk: p.kode_produk,
        jumlah_setor: p.jumlah_setor,
        satuan: p.satuan,
        harga_setor: p.harga_setor,
        subtotal: p.subtotal
    })));
});

document.addEventListener('DOMContentLoaded', function() {
    const hargaSetorInput = document.getElementById('harga_setor');
    if (hargaSetorInput) {
        hargaSetorInput.addEventListener('input', function(e) {
            const cursor = this.selectionStart;
            const oldLength = this.value.length;
            let val = this.value;
            this.value = formatNumberInput(val);
            const newLength = this.value.length;
            this.setSelectionRange(cursor + (newLength - oldLength), cursor + (newLength - oldLength));
        });
    }
});
</script>
@endsection
