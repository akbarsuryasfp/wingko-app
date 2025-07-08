@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">EDIT RETUR PENJUALAN</h3>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('returjual.update', $returjual->no_returjual) }}" method="POST">
        @csrf
        @method('PUT')
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Retur -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Retur Jual</label>
                    <input type="text" name="no_returjual" class="form-control" value="{{ $returjual->no_returjual }}" readonly tabindex="-1" style="background:#e9ecef; pointer-events:none;">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Jual</label>
                    <input type="text" class="form-control" value="{{ $returjual->no_jual }}" readonly tabindex="-1" style="background:#e9ecef; pointer-events:none;">
                    <input type="hidden" name="no_jual" value="{{ $returjual->no_jual }}">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Tanggal Retur</label>
                    <input type="date" name="tanggal_returjual" class="form-control" value="{{ $returjual->tanggal_returjual }}" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Pelanggan</label>
                    <select name="kode_pelanggan" id="kode_pelanggan" class="form-control" required>
                        <option value="">---Pilih Pelanggan---</option>
                        @foreach($pelanggan as $p)
                            <option value="{{ $p->kode_pelanggan }}" {{ $returjual->kode_pelanggan == $p->kode_pelanggan ? 'selected' : '' }}>{{ $p->nama_pelanggan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" value="{{ $returjual->keterangan }}">
                </div>
            </div>

            <!-- Kolom Kanan: Data Produk Retur -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Produk</label>
                    <select id="kode_produk" class="form-control" disabled tabindex="-1" style="background:#e9ecef; pointer-events:none;">
                        <option value="">---Pilih Produk---</option>
                        @foreach($produk as $pr)
                            <option value="{{ $pr->kode_produk }}" data-nama="{{ $pr->nama_produk }}">{{ $pr->nama_produk }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Jumlah Retur</label>
                    <input type="number" id="jumlah_retur" class="form-control" readonly tabindex="-1" style="background:#e9ecef; pointer-events:none;">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Harga Satuan</label>
                    <input type="number" id="harga_satuan" class="form-control" readonly tabindex="-1" style="background:#e9ecef; pointer-events:none;">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Alasan</label>
                    <input type="text" id="alasan" class="form-control" readonly tabindex="-1" style="background:#e9ecef; pointer-events:none;">
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary w-100" onclick="tambahProdukRetur()">Tambah Produk Retur</button>
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PRODUK RETUR</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk-retur">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Jumlah Retur</th>
                    <th>Harga Satuan</th>
                    <th>Alasan</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="d-flex justify-content-between mt-4">
            <div>
                <a href="{{ route('returjual.index') }}" class="btn btn-secondary">Back</a>
            </div>
            <div class="d-flex align-items-center gap-3">
                <label class="mb-0">Total Retur</label>
                <input type="text" id="total_nilai_retur_view" readonly class="form-control" style="width: 160px;">
                <input type="hidden" id="total_nilai_retur" name="total_nilai_retur">
                <button type="submit" class="btn btn-success">Update</button>
            </div>
        </div>

        <input type="hidden" name="detail_json" id="detail_json">
    </form>
</div>

<script>
    // Inisialisasi dari backend
    let daftarProdukRetur = @json($details);
    // Data jumlah maksimal per produk dari penjualan
    let maxJumlahPerProduk = {};
    @foreach($penjualanDetail as $kode_produk => $detail)
        maxJumlahPerProduk['{{ $kode_produk }}'] = {{ $detail->jumlah }};
    @endforeach

    // Pastikan data awal tidak melebihi max
    daftarProdukRetur.forEach(function(item, idx) {
        const max = maxJumlahPerProduk[item.kode_produk] || 0;
        if (item.jumlah_retur > max) {
            item.jumlah_retur = max;
            item.subtotal = max * item.harga_satuan;
        }
    });

    // Mapping harga satuan per produk
    let hargaSatuanProduk = {};
    @foreach($produk as $pr)
        hargaSatuanProduk['{{ $pr->kode_produk }}'] = {{ $pr->harga_satuan ?? 0 }};
    @endforeach

    // Event: saat produk dipilih, isi harga satuan otomatis
    function setHargaSatuanOtomatis() {
        const produkSelect = document.getElementById('kode_produk');
        const kode = produkSelect.value;
        const harga = hargaSatuanProduk[kode] || '';
        document.getElementById('harga_satuan').value = harga;
    }
    document.addEventListener('DOMContentLoaded', function() {
        const produkSelect = document.getElementById('kode_produk');
        produkSelect.addEventListener('change', setHargaSatuanOtomatis);
        // Jalankan sekali saat halaman dibuka jika ada produk terpilih
        setHargaSatuanOtomatis();
    });

    function tambahProdukRetur() {
        const produkSelect = document.getElementById('kode_produk');
        const kode_produk = produkSelect.value;
        const nama_produk = produkSelect.options[produkSelect.selectedIndex]?.dataset.nama || '';
        const jumlah_retur = Number(document.getElementById('jumlah_retur').value);
        const harga_satuan = Number(document.getElementById('harga_satuan').value);
        const alasan = document.getElementById('alasan').value;

        if (!kode_produk || isNaN(jumlah_retur) || isNaN(harga_satuan) || jumlah_retur <= 0 || harga_satuan <= 0 || !alasan) {
            alert("Silakan lengkapi data produk retur dengan benar.");
            return;
        }

        // Cek apakah produk sudah ada di daftar
        const sudahAda = daftarProdukRetur.some(item => item.kode_produk === kode_produk);
        if (sudahAda) {
            alert("Produk sudah ada di daftar retur. Tidak boleh input produk yang sama dua kali.");
            return;
        }

        // Cek batas maksimal
        const max = maxJumlahPerProduk[kode_produk] || 0;
        if (jumlah_retur > max) {
            alert("Jumlah retur melebihi jumlah penjualan (" + max + ")");
            return;
        }

        const subtotal = jumlah_retur * harga_satuan;

        daftarProdukRetur.push({ kode_produk, nama_produk, jumlah_retur, harga_satuan, alasan, subtotal });
        updateTabelRetur();

        // Reset input produk retur
        produkSelect.selectedIndex = 0;
        document.getElementById('jumlah_retur').value = '';
        document.getElementById('harga_satuan').value = '';
        document.getElementById('alasan').value = '';
    }

    function hapusBarisRetur(index) {
        daftarProdukRetur.splice(index, 1);
        updateTabelRetur();
    }

    function formatRupiah(angka) {
        return 'Rp' + angka.toLocaleString('id-ID');
    }

    function updateTabelRetur() {
        const tbody = document.querySelector('#daftar-produk-retur tbody');
        tbody.innerHTML = '';

        let totalRetur = 0;

        daftarProdukRetur.forEach((item, index) => {
            const max = maxJumlahPerProduk[item.kode_produk] || 0;
            // Jika jumlah_retur melebihi max, set ke max
            if (item.jumlah_retur > max) {
                item.jumlah_retur = max;
                item.subtotal = max * item.harga_satuan;
            }
            const subtotal = Number(item.jumlah_retur) * Number(item.harga_satuan) || 0;
            totalRetur += subtotal;

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_produk}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm" min="0" max="${max}" value="${item.jumlah_retur}" 
                            onchange="updateJumlahRetur(${index}, this.value)">
                        <small class="text-muted">Maks: ${max}</small>
                    </td>
                    <td>${formatRupiah(item.harga_satuan)}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm" value="${item.alasan || ''}" 
                            onchange="updateAlasanRetur(${index}, this.value)">
                    </td>
                    <td>${formatRupiah(subtotal)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="hapusBarisRetur(${index})" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('total_nilai_retur_view').value = formatRupiah(totalRetur);
        document.getElementById('total_nilai_retur').value = totalRetur;
        document.getElementById('detail_json').value = JSON.stringify(daftarProdukRetur);
    }

    function updateJumlahRetur(index, value) {
        const kode_produk = daftarProdukRetur[index].kode_produk;
        const max = maxJumlahPerProduk[kode_produk] || 0;
        if (Number(value) > max) {
            alert("Jumlah retur melebihi jumlah penjualan (" + max + ")");
            daftarProdukRetur[index].jumlah_retur = max;
        } else {
            daftarProdukRetur[index].jumlah_retur = Number(value);
        }
        daftarProdukRetur[index].subtotal = daftarProdukRetur[index].jumlah_retur * daftarProdukRetur[index].harga_satuan;
        updateTabelRetur();
    }
    function updateHargaSatuan(index, value) {
        daftarProdukRetur[index].harga_satuan = Number(value);
        daftarProdukRetur[index].subtotal = daftarProdukRetur[index].jumlah_retur * daftarProdukRetur[index].harga_satuan;
        updateTabelRetur();
    }
    function updateAlasanRetur(index, value) {
        daftarProdukRetur[index].alasan = value;
        updateTabelRetur();
    }

    // Inisialisasi tabel saat halaman dibuka
    updateTabelRetur();

    // Cegah submit jika belum ada produk retur
    document.querySelector('form').addEventListener('submit', function(e) {
        if (daftarProdukRetur.length === 0) {
            alert('Minimal 1 produk retur harus ditambahkan!');
            e.preventDefault();
            return false;
        }
        // Pastikan value total yang dikirim ke backend adalah angka
        document.getElementById('total_nilai_retur').value = daftarProdukRetur.reduce((sum, item) => sum + (Number(item.subtotal) || 0), 0);
    });
</script>
@endsection