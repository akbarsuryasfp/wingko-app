@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">INPUT PENERIMAAN KONSINYASI</h3>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('penerimaankonsinyasi.store') }}" method="POST">
        @csrf
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Penerimaan -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Penerimaan Konsinyasi</label>
                    <input type="text" name="no_penerimaankonsinyasi" id="no_penerimaankonsinyasi" class="form-control" value="{{ $no_penerimaankonsinyasi ?? old('no_penerimaankonsinyasi') }}" readonly required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Konsinyasi Keluar</label>
                    <select name="no_konsinyasikeluar" id="no_konsinyasikeluar" class="form-control" required>
                        <option value="">---Pilih No Konsinyasi Keluar---</option>
                        @php
                            // Ambil daftar no_konsinyasikeluar yang sudah dipakai di penerimaan konsinyasi
                            $sudahDipakai = isset($sudahDipakaiKonsinyasiKeluar) ? $sudahDipakaiKonsinyasiKeluar : [];
                        @endphp
                        @foreach($konsinyasiKeluarList as $kk)
                            @if(!in_array($kk->no_konsinyasikeluar, $sudahDipakai))
                                <option value="{{ $kk->no_konsinyasikeluar }}" data-kode_consignee="{{ $kk->kode_consignee }}">{{ $kk->no_konsinyasikeluar }} - {{ $kk->consignee->nama_consignee ?? '' }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Tanggal Terima</label>
                    <input type="date" name="tanggal_terima" id="tanggal_terima" class="form-control" required value="{{ old('tanggal_terima') }}">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Nama Consignee (Mitra)</label>
                    <input type="text" id="nama_consignee" class="form-control" readonly>
                    <input type="hidden" name="kode_consignee" id="kode_consignee" value="{{ old('kode_consignee') }}">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                        <option value="">---Pilih Metode---</option>
                        <option value="Tunai" {{ old('metode_pembayaran') == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                        <option value="Non Tunai" {{ old('metode_pembayaran') == 'Non Tunai' ? 'selected' : '' }}>Non Tunai</option>
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Keterangan</label>
                    <input type="text" name="keterangan" id="keterangan" class="form-control" value="{{ old('keterangan') }}">
                </div>
                <input type="hidden" name="kode_consignor" id="kode_consignor">
            </div>
            <!-- Kolom Kanan: Data Produk Terima -->
            <!-- Hapus seluruh kolom input produk manual, hanya tampilkan info saja jika perlu -->
        </div>
        <hr>
        <h4 class="text-center">DAFTAR PRODUK PENERIMAAN KONSINYASI</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk-terima">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Jumlah Setor</th>
                    <th>Jumlah Terjual</th>
                    <th>Satuan</th>
                    <th>Harga/Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data produk akan diisi via JS -->
            </tbody>
        </table>
        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap">
            <div class="d-flex gap-2">
                <a href="{{ route('penerimaankonsinyasi.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning">Reset</button>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0">Total Terima</label>
                <input type="text" class="form-control" id="total_terima" value="Rp0" readonly style="width:180px;">
                <input type="hidden" name="total_terima" id="input_total_terima" value="0">
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
        </div>
        <input type="hidden" name="detail_json" id="detail_json">
    </form>
</div>
<script>
let produkKonsinyasiKeluar = [];
let produkTerimaList = [];

// Saat no konsinyasi keluar dipilih, fetch detail dan isi produk, consignee, dsb
const noKonsinyasiKeluarSelect = document.getElementById('no_konsinyasikeluar');
noKonsinyasiKeluarSelect.addEventListener('change', function() {
    const no_konsinyasikeluar = this.value;
    // Ambil kode_consignor dari option terpilih
    const selectedOption = this.options[this.selectedIndex];
    const kodeConsignor = selectedOption ? selectedOption.getAttribute('data-kode_consignee') : '';
    document.getElementById('kode_consignor').value = kodeConsignor;
    if (no_konsinyasikeluar) {
        fetch('/api/konsinyasikeluar/detail/' + no_konsinyasikeluar)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    produkKonsinyasiKeluar = Array.isArray(data.produkList) ? data.produkList : [];
                    produkTerimaList = produkKonsinyasiKeluar.map(p => ({
                        kode_produk: p.kode_produk,
                        nama_produk: p.nama_produk,
                        jumlah_setor: p.jumlah_setor,
                        jumlah_terjual: 0,
                        satuan: p.satuan,
                        harga_satuan: p.harga_satuan,
                        subtotal: 0 // Subtotal awal 0, dihitung setelah jumlah terjual diinput
                    }));
                    renderTabelProdukTerima();
                    document.getElementById('kode_consignee').value = data.kode_consignee;
                    document.getElementById('nama_consignee').value = data.nama_consignee;
                } else {
                    produkKonsinyasiKeluar = [];
                    produkTerimaList = [];
                    renderTabelProdukTerima();
                    document.getElementById('kode_consignee').value = '';
                    document.getElementById('nama_consignee').value = '';
                }
            })
            .catch(() => {
                produkKonsinyasiKeluar = [];
                produkTerimaList = [];
                renderTabelProdukTerima();
                document.getElementById('kode_consignee').value = '';
                document.getElementById('nama_consignee').value = '';
            });
    } else {
        produkKonsinyasiKeluar = [];
        produkTerimaList = [];
        renderTabelProdukTerima();
        document.getElementById('kode_consignee').value = '';
        document.getElementById('nama_consignee').value = '';
    }
});

function renderTabelProdukTerima() {
    const tbody = document.querySelector('#daftar-produk-terima tbody');
    tbody.innerHTML = '';
    let total = 0;
    produkTerimaList.forEach((item, idx) => {
        // Subtotal dihitung dari jumlah terjual * harga satuan
        const subtotal = (item.jumlah_terjual || 0) * (item.harga_satuan || 0);
        produkTerimaList[idx].subtotal = subtotal;
        total += subtotal;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${idx + 1}</td>
            <td>${item.nama_produk}</td>
            <td>${item.jumlah_setor}</td>
            <td><input type="number" min="0" max="${item.jumlah_setor}" value="${item.jumlah_terjual}" class="form-control form-control-sm" onchange="ubahJumlahTerjual(${idx}, this.value)"></td>
            <td>${item.satuan}</td>
            <td>Rp${item.harga_satuan.toLocaleString('id-ID')}</td>
            <td>Rp${subtotal.toLocaleString('id-ID')}</td>
        `;
        tbody.appendChild(tr);
    });
    // Update total terima dan field hidden
    document.getElementById('total_terima').value = 'Rp' + total.toLocaleString('id-ID');
    document.getElementById('input_total_terima').value = total;
    document.getElementById('detail_json').value = JSON.stringify(produkTerimaList);
}
function ubahJumlahTerjual(idx, val) {
    val = parseInt(val) || 0;
    if (val < 0) val = 0;
    if (val > produkTerimaList[idx].jumlah_setor) val = produkTerimaList[idx].jumlah_setor;
    produkTerimaList[idx].jumlah_terjual = val;
    renderTabelProdukTerima();
}
// Render tabel saat load
renderTabelProdukTerima();
// Validasi sebelum submit form
const form = document.querySelector('form[action="{{ route('penerimaankonsinyasi.store') }}"]');
form.addEventListener('submit', function(e) {
    if (produkTerimaList.length === 0) {
        alert('Minimal 1 produk harus ada!');
        e.preventDefault();
        return false;
    }
    document.getElementById('detail_json').value = JSON.stringify(produkTerimaList);
});
</script>
@endsection
