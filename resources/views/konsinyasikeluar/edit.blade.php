@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">EDIT KONSINYASI KELUAR</h4>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('konsinyasikeluar.update', $header->no_konsinyasikeluar) }}" method="POST">
        @csrf
        @method('PUT')
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Konsinyasi -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Konsinyasi Keluar</label>
                    <input type="text" name="no_konsinyasikeluar" class="form-control" required value="{{ old('no_konsinyasikeluar', $header->no_konsinyasikeluar) }}" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Surat Konsinyasi Keluar</label>
                    <input type="text" name="no_suratpengiriman" class="form-control" required value="{{ old('no_suratpengiriman', $header->no_suratpengiriman) }}" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Nama Consignee (Mitra)</label>
                    <select name="kode_consignee" class="form-control" required disabled tabindex="-1">
                        <option value="">---Pilih Consignee---</option>
                        @foreach($consignees as $c)
                            <option value="{{ $c->kode_consignee }}" {{ old('kode_consignee', $header->kode_consignee)==$c->kode_consignee?'selected':'' }}>{{ $c->nama_consignee }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="kode_consignee" value="{{ $header->kode_consignee }}">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Tanggal Setor</label>
                    <input type="date" name="tanggal_setor" class="form-control" required value="{{ old('tanggal_setor', $header->tanggal_setor) }}" readonly tabindex="-1">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan', $header->keterangan) }}">
                </div>
            </div>

            <!-- Kolom Kanan: Data Produk Setor (Input) -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Produk</label>
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
                    <label class="me-2" style="width: 120px;">Harga Setor/Produk</label>
                    <input type="number" id="harga_setor" class="form-control">
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
                    <th>Jumlah Setor</th>
                    <th>Satuan</th>
                    <th>Harga Setor/Produk</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        <div class="d-flex justify-content-between mt-4">
            <div>
                <a href="{{ route('konsinyasikeluar.index') }}" class="btn btn-secondary">Back</a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <label class="mb-0">Total Setor</label>
                <input type="text" id="total_setor_view" readonly class="form-control" style="width: 160px;">
                <input type="hidden" id="total_setor" name="total_setor">
                <button type="submit" class="btn btn-success">Update</button>
            </div>
        </div>
        <input type="hidden" name="detail_json" id="detail_json">
    </form>
</div>
@php
    $produkSetorList = [];
    foreach (($header->details ?? []) as $d) {
        $produkObj = collect($produkList)->firstWhere('kode_produk', $d->kode_produk);
        $nama = $produkObj ? (is_array($produkObj) ? $produkObj['nama_produk'] : $produkObj->nama_produk) : '';
        $produkSetorList[] = [
            'kode_produk' => $d->kode_produk,
            'nama_produk' => $nama,
            'jumlah_setor' => $d->jumlah_setor,
            'satuan' => $d->satuan,
            'harga_setor' => $d->harga_setor,
            'subtotal' => $d->jumlah_setor * $d->harga_setor
        ];
    }
@endphp
<script>
const allProdukKonsinyasi = @json($produkList);
// Populate produk select
function updateProdukSelect() {
    const produkSelect = document.getElementById('kode_produk');
    produkSelect.innerHTML = '<option value="">---Pilih Produk---</option>';
    allProdukKonsinyasi.forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.kode_produk;
        opt.textContent = p.nama_produk;
        opt.setAttribute('data-satuan', p.satuan);
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
    let harga = '';
    const nama = selected?.textContent?.toLowerCase() || '';
    if (nama.includes('moaci')) {
        harga = 20000;
    } else if (nama.includes('wingko babat')) {
        harga = 25000;
    } else {
        harga = selected?.getAttribute('data-harga_setor')
            || selected?.getAttribute('data-harga_jual')
            || selected?.getAttribute('data-harga_beli')
            || '';
    }
    document.getElementById('harga_setor').value = harga;
});
// Data array untuk detail produk setor
let produkSetorList = @json($produkSetorList);
function tambahProdukSetor() {
    const kode_produk = document.getElementById('kode_produk').value;
    const jumlah_setor = parseInt(document.getElementById('jumlah_setor').value);
    const harga_setor = parseFloat(document.getElementById('harga_setor').value);
    const satuan = document.getElementById('satuan').value;
    const produkSelect = document.getElementById('kode_produk');
    const nama_produk = produkSelect.options[produkSelect.selectedIndex]?.text || '';
    if (!kode_produk || !jumlah_setor || !harga_setor) {
        alert('Lengkapi data produk!');
        return;
    }
    if (produkSetorList.some(p => p.kode_produk === kode_produk)) {
        alert('Produk sudah ditambahkan!');
        return;
    }
    const subtotal = jumlah_setor * harga_setor;
    produkSetorList.push({ kode_produk, nama_produk, jumlah_setor, satuan, harga_setor, subtotal });
    renderTabelProdukSetor();
    resetInputProduk();
}
function renderTabelProdukSetor() {
    const tbody = document.querySelector('#daftar-produk-setor tbody');
    tbody.innerHTML = '';
    let total = 0;
    function formatRupiah(angka) {
        if (!angka && angka !== 0) return '';
        return 'Rp ' + parseFloat(angka).toLocaleString('id-ID');
    }
    produkSetorList.forEach((item, idx) => {
        total += item.subtotal;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${idx + 1}</td>
            <td>${item.nama_produk}</td>
            <td><input type="number" class="form-control form-control-sm jumlah-edit" data-idx="${idx}" value="${item.jumlah_setor}" min="1" style="width:90px;"></td>
            <td>${item.satuan}</td>
            <td><input type="number" class="form-control form-control-sm harga-edit" data-idx="${idx}" value="${item.harga_setor}" min="0" style="width:120px;"></td>
            <td class="subtotal">${formatRupiah(item.subtotal)}</td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusProdukSetor(${idx})" title="Hapus"><span style='font-size:1.2em;'>&#128465;</span></button></td>
        `;
        tbody.appendChild(tr);
    });
    // Update total setor dan field hidden
    const totalSetor = produkSetorList.reduce((a,b)=>a+(b.subtotal||0),0);
    document.getElementById('total_setor_view').value = formatRupiah(totalSetor);
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
            produkSetorList[idx].jumlah_setor = parseInt(this.value) || 0;
            produkSetorList[idx].subtotal = produkSetorList[idx].jumlah_setor * produkSetorList[idx].harga_setor;
            // Update subtotal kolom
            this.closest('tr').querySelector('.subtotal').textContent = formatRupiah(produkSetorList[idx].subtotal);
            // Update total setor dan field hidden
            const totalSetor = produkSetorList.reduce((a,b)=>a+(b.subtotal||0),0);
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
        input.addEventListener('input', function() {
            const idx = this.dataset.idx;
            produkSetorList[idx].harga_setor = parseFloat(this.value) || 0;
            produkSetorList[idx].subtotal = produkSetorList[idx].jumlah_setor * produkSetorList[idx].harga_setor;
            // Update subtotal kolom
            this.closest('tr').querySelector('.subtotal').textContent = formatRupiah(produkSetorList[idx].subtotal);
            // Update total setor dan field hidden
            const totalSetor = produkSetorList.reduce((a,b)=>a+(b.subtotal||0),0);
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
}
function hapusProdukSetor(idx) {
    produkSetorList.splice(idx, 1);
    renderTabelProdukSetor();
}
function resetInputProduk() {
    document.getElementById('kode_produk').value = '';
    document.getElementById('jumlah_setor').value = '';
    document.getElementById('harga_setor').value = '';
    document.getElementById('satuan').value = '';
}
// Render tabel saat load
renderTabelProdukSetor();
// Validasi sebelum submit form
const form = document.querySelector('form[action="{{ route('konsinyasikeluar.update', $header->no_konsinyasikeluar) }}"]');
form.addEventListener('submit', function(e) {
    if (produkSetorList.length === 0) {
        alert('Minimal 1 produk harus ditambahkan!');
        e.preventDefault();
        return false;
    }
    document.getElementById('detail_json').value = JSON.stringify(produkSetorList.map(p => ({
        kode_produk: p.kode_produk,
        jumlah_setor: p.jumlah_setor,
        satuan: p.satuan,
        harga_setor: p.harga_setor,
        subtotal: p.subtotal
    })));
});
</script>
@endsection
