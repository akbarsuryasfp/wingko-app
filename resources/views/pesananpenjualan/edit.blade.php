@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-4">EDIT PESANAN</h3>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('pesananpenjualan.update', $pesanan->no_pesanan) }}" method="POST">
                @csrf
                @method('PUT')
                <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Pesanan -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">No Pesanan</label>
                    <input type="text" name="no_pesanan" class="form-control" value="{{ $pesanan->no_pesanan }}" readonly tabindex="-1" style="background:#e9ecef;pointer-events:none;">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Tanggal Pesanan</label>
                    <input type="date" name="tanggal_pesanan" class="form-control" value="{{ $pesanan->tanggal_pesanan }}" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Tanggal Pengiriman</label>
                    <input type="date" name="tanggal_pengiriman" class="form-control" value="{{ $pesanan->tanggal_pengiriman }}">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Nama Pelanggan</label>
                    <select name="kode_pelanggan" class="form-control" required style="pointer-events: none; background: #e9ecef;" tabindex="-1" readonly>
                        <option value="">---Pilih Pelanggan---</option>
                        @foreach($pelanggan as $p)
                            <option value="{{ $p->kode_pelanggan }}" {{ $pesanan->kode_pelanggan == $p->kode_pelanggan ? 'selected' : '' }}>{{ $p->nama_pelanggan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" maxlength="255" value="{{ $pesanan->keterangan }}">
                </div>
            </div>

            <!-- Kolom Kanan: Data Produk -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Nama Produk</label>
                    <select id="kode_produk" class="form-control">
                        <option value="">---Pilih Produk---</option>
                        @foreach($produk as $pr)
                            <option value="{{ $pr->kode_produk }}" data-nama="{{ $pr->nama_produk }}" data-satuan="{{ $pr->satuan ?? '' }}"
                                @if($pr->nama_produk == 'Moaci') data-harga="25000"
                                @elseif($pr->nama_produk == 'Wingko Babat') data-harga="20000"
                                @else data-harga="0" @endif>
                                {{ $pr->nama_produk }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Jumlah</label>
                    <input type="number" id="jumlah" class="form-control">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Satuan</label>
                    <input type="text" id="satuan" class="form-control" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Harga/Satuan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="harga_satuan" class="form-control" autocomplete="off">
                    </div>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Diskon/Satuan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="diskon_satuan" class="form-control" min="0" autocomplete="off">
                    </div>
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary w-100" onclick="tambahProduk()">Tambah Produk</button>
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PRODUK PESANAN</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk">
            <thead>
    <tr>
        <th>No</th>
        <th>Nama Produk</th>
        <th>Satuan</th>
        <th>Jumlah</th>
        <th>Harga/Satuan</th>
        <th>Diskon/Satuan</th>
        <th>Subtotal</th>
        <th>Aksi</th>
    </tr>
</thead>
            <tbody></tbody>
        </table>

        <div class="row mt-4 justify-content-start">
            <div class="col-md-6">
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Pesanan</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="total_pesanan_display" readonly class="form-control" tabindex="-1" style="background:#e9ecef;pointer-events:none;">
                        </div>
                        <input type="hidden" id="total_pesanan" name="total_pesanan">
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Uang Muka (DP)</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                        <input type="text" id="uang_muka" name="uang_muka" class="form-control" autocomplete="off" value="{{ old('uang_muka', $pesanan->uang_muka ?? '') }}">
                        </div>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Sisa Tagihan</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                        <input type="text" id="sisa_tagihan" name="sisa_tagihan" class="form-control" readonly tabindex="-1" style="background:#e9ecef;pointer-events:none;" value="{{ old('sisa_tagihan', $pesanan->sisa_tagihan ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-end mt-3" style="min-height:40px;">
            <a href="{{ route('pesananpenjualan.index') }}" class="btn btn-secondary" title="Kembali">Back</a>
            <button type="submit" class="btn btn-success">Update</button>
        </div>

        <input type="hidden" name="detail_json" id="detail_json">
    </form>
        </div>
    </div>
</div>

<script>
    // Inisialisasi dari backend
    @php
        $daftarProduk = array_map(function($d) {
            return [
                'kode_produk' => isset($d['kode_produk']) ? $d['kode_produk'] : (isset($d->kode_produk) ? $d->kode_produk : null),
                'nama_produk' => isset($d['nama_produk']) ? $d['nama_produk'] : (isset($d->nama_produk) ? $d->nama_produk : null),
                'satuan' => isset($d['satuan']) ? $d['satuan'] : (isset($d->satuan) ? $d->satuan : ''),
                'jumlah' => isset($d['jumlah']) ? $d['jumlah'] : (isset($d->jumlah) ? $d->jumlah : 0),
                'harga_satuan' => isset($d['harga_satuan']) ? $d['harga_satuan'] : (isset($d->harga_satuan) ? $d->harga_satuan : 0),
                'diskon_satuan' => isset($d['diskon_produk']) ? $d['diskon_produk'] : (isset($d->diskon_produk) ? $d->diskon_produk : 0),
                'subtotal' => isset($d['subtotal']) ? $d['subtotal'] : (isset($d->subtotal) ? $d->subtotal : 0),
            ];
        }, (array) $details);
    @endphp
    let daftarProduk = @json($daftarProduk);

    // Helper format ribuan
    function formatNumberInput(val) {
        // Hilangkan format ribuan, hanya kembalikan angka murni
        val = String(val).replace(/\D/g, '');
        return val;
    }
    function parseNumberInput(val) {
        // Ambil semua digit, lalu parse ke integer
        val = String(val).replace(/\D/g, '');
        return val ? parseInt(val, 10) : 0;
    }

    // Format ribuan hanya saat blur agar input tetap lancar
    function addBlurRibuanFormat(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('blur', function(e) {
            let val = parseNumberInput(this.value);
            this.value = val ? val.toLocaleString('id-ID') : '';
        });
        input.addEventListener('focus', function(e) {
            // Hilangkan format ribuan saat fokus agar mudah edit
            this.value = parseNumberInput(this.value) || '';
        });
    }
    addBlurRibuanFormat('harga_satuan');
    addBlurRibuanFormat('diskon_satuan');
    addBlurRibuanFormat('uang_muka');
    // Format ulang uang muka dan sisa tagihan saat halaman dibuka (untuk value awal)
    window.addEventListener('DOMContentLoaded', function() {
        var uangMukaInput = document.getElementById('uang_muka');
        if (uangMukaInput) {
            // Ubah ke integer/bulat jika ada value
            if (uangMukaInput.value) {
                uangMukaInput.value = parseInt(uangMukaInput.value, 10) || 0;
            }
        }
        var sisaTagihanInput = document.getElementById('sisa_tagihan');
        if (sisaTagihanInput) {
            if (sisaTagihanInput.value) {
                sisaTagihanInput.value = parseInt(sisaTagihanInput.value, 10) || 0;
            }
        }
        updateTabel();
    });
    // Pastikan value uang_muka dan sisa_tagihan yang dikirim ke backend adalah numerik (tanpa titik)
    document.querySelector('form').addEventListener('submit', function(e) {
        // Pastikan value uang_muka dan sisa_tagihan yang dikirim ke backend adalah numerik (tanpa titik)
        var uangMukaInput = document.getElementById('uang_muka');
        if (uangMukaInput) {
            uangMukaInput.value = parseNumberInput(uangMukaInput.value);
        }
        var sisaTagihanInput = document.getElementById('sisa_tagihan');
        if (sisaTagihanInput) {
            sisaTagihanInput.value = parseNumberInput(sisaTagihanInput.value);
        }
        if (daftarProduk.length === 0) {
            alert('Minimal 1 produk harus ditambahkan!');
            e.preventDefault();
            return false;
        }
    });

    function tambahProduk() {
        const produkSelect = document.getElementById('kode_produk');
        const kode_produk = produkSelect.value;
        const nama_produk = produkSelect.options[produkSelect.selectedIndex].dataset.nama;
        const jumlah = parseFloat(document.getElementById('jumlah').value);
        // Gunakan parseNumberInput agar titik ribuan tidak error
        const harga_satuan = parseNumberInput(document.getElementById('harga_satuan').value);
        const diskon_satuan = parseNumberInput(document.getElementById('diskon_satuan').value) || 0;
        const satuan = document.getElementById('satuan').value;

        if (!kode_produk || !jumlah || !harga_satuan || jumlah <= 0 || harga_satuan <= 0) {
            alert("Silakan lengkapi data produk.");
            return;
        }

        // Cek apakah produk sudah ada di daftar
        if (daftarProduk.some(item => item.kode_produk === kode_produk)) {
            alert("Produk sudah ada di daftar!");
            return;
        }

        const subtotal = jumlah * (harga_satuan - diskon_satuan);
        daftarProduk.push({ kode_produk, nama_produk, satuan, jumlah, harga_satuan, diskon_satuan, subtotal });
        updateTabel();

        // Reset input produk
        produkSelect.selectedIndex = 0;
        document.getElementById('jumlah').value = '';
        document.getElementById('harga_satuan').value = '';
        document.getElementById('diskon_satuan').value = 0;
    }

    function hapusBaris(index) {
        daftarProduk.splice(index, 1);
        updateTabel();
    }

    function formatRupiah(angka) {
        if (!angka && angka !== 0) return '';
        return 'Rp' + parseFloat(angka).toLocaleString('id-ID');
    }

    function updateJumlah(index, input) {
        let val = parseFloat(input.value);
        if (isNaN(val) || val <= 0) val = 1;
        daftarProduk[index].jumlah = val;
        let harga = parseFloat(daftarProduk[index].harga_satuan) || 0;
        let diskon = parseFloat(daftarProduk[index].diskon_satuan) || 0;
        daftarProduk[index].subtotal = val * (harga - diskon);
        updateTabel();
    }

    function updateDiskon(index, input) {
        let val = parseFloat(input.value);
        if (isNaN(val) || val < 0) val = 0;
        daftarProduk[index].diskon_satuan = val;
        let harga = parseFloat(daftarProduk[index].harga_satuan) || 0;
        let jumlah = parseFloat(daftarProduk[index].jumlah) || 0;
        daftarProduk[index].subtotal = jumlah * (harga - val);
        if (daftarProduk[index].subtotal < 0) daftarProduk[index].subtotal = 0;
        updateTabel();
    }

    function updateTabel() {
        const tbody = document.querySelector('#daftar-produk tbody');
        tbody.innerHTML = '';

        let totalPesanan = 0;

        daftarProduk.forEach((item, index) => {
            // Hitung ulang subtotal agar selalu akurat
            item.jumlah = parseFloat(item.jumlah) || 0;
            item.harga_satuan = parseFloat(item.harga_satuan) || 0;
            item.diskon_satuan = parseFloat(item.diskon_satuan) || 0;
            item.subtotal = item.jumlah * (item.harga_satuan - item.diskon_satuan);
            if (item.subtotal < 0) item.subtotal = 0;

            totalPesanan += item.subtotal;

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_produk}</td>
                    <td>${item.satuan || ''}</td>
                    <td><input type="number" min="1" class="form-control form-control-sm text-center jumlah-inline" value="${item.jumlah}" data-index="${index}"></td>
                    <td>${formatRupiah(item.harga_satuan)}</td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input type="text" min="0" class="form-control text-center diskon-inline" value="${item.diskon_satuan}" data-index="${index}">
                        </div>
                    </td>
                    <td class="subtotal-col">${formatRupiah(item.subtotal)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(${index})" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        // Format total pesanan for display, but set hidden input for backend
        document.getElementById('total_pesanan_display').value = totalPesanan > 0 ? formatNumberInput(totalPesanan) : '';
        document.getElementById('total_pesanan').value = totalPesanan;
        document.getElementById('detail_json').value = JSON.stringify(daftarProduk);

        // Hitung sisa tagihan (total pesanan - uang muka)
        const uangMukaInput = document.getElementById('uang_muka');
        const sisaTagihanInput = document.getElementById('sisa_tagihan');
        let uangMuka = 0;
        if (uangMukaInput) {
            // Jangan parse ulang value input, biarkan angka asli
            uangMuka = parseNumberInput(uangMukaInput.value);
        }
        const sisaTagihan = Math.max(totalPesanan - uangMuka, 0);
        if (sisaTagihanInput) {
            sisaTagihanInput.value = sisaTagihan;
        }
    }

    // Tambahkan event listener untuk input jumlah dan diskon/satuan inline hanya sekali
    if (!window._inlineListener) {
        // Batasi input hanya angka dan kontrol pada jumlah-inline dan diskon-inline, tapi izinkan input multi digit
        document.addEventListener('keydown', function(e) {
            if ((e.target && (e.target.classList.contains('jumlah-inline') || e.target.classList.contains('diskon-inline')))) {
                // Izinkan: angka, numpad, backspace, delete, tab, escape, enter, panah, ctrl/cmd+A/C/V/X/Z
                if (
                    (e.key >= '0' && e.key <= '9') ||
                    (e.key >= 'Numpad0' && e.key <= 'Numpad9') ||
                    ["Backspace","Delete","Tab","Escape","Enter","ArrowLeft","ArrowRight","Home","End"].includes(e.key) ||
                    (e.ctrlKey || e.metaKey)
                ) {
                    return;
                }
                // Cegah karakter lain (misal huruf, simbol)
                e.preventDefault();
            }
        });
        document.addEventListener('blur', function(e) {
            // Jumlah
            if (e.target && e.target.classList.contains('jumlah-inline')) {
                const idx = parseInt(e.target.getAttribute('data-index'));
                let num = parseInt(e.target.value, 10);
                if (isNaN(num) || num <= 0) num = 1;
                daftarProduk[idx].jumlah = num;
                let harga = parseFloat(daftarProduk[idx].harga_satuan) || 0;
                let diskon = parseFloat(daftarProduk[idx].diskon_satuan) || 0;
                daftarProduk[idx].subtotal = num * (harga - diskon);
                if (daftarProduk[idx].subtotal < 0) daftarProduk[idx].subtotal = 0;
                // Update subtotal cell only
                const row = e.target.closest('tr');
                if (row) {
                    const subtotalCell = row.querySelector('.subtotal-col');
                    if (subtotalCell) subtotalCell.textContent = formatRupiah(daftarProduk[idx].subtotal);
                }
                // Update total pesanan
                let total = daftarProduk.reduce((sum, item) => sum + item.subtotal, 0);
                document.getElementById('total_pesanan_display').value = total > 0 ? formatNumberInput(total) : '';
                document.getElementById('total_pesanan').value = total;
                document.getElementById('detail_json').value = JSON.stringify(daftarProduk);
                updateTabel();
            }
            // Diskon
            if (e.target && e.target.classList.contains('diskon-inline')) {
                const idx = parseInt(e.target.getAttribute('data-index'));
                let num = parseInt(e.target.value, 10);
                if (isNaN(num) || num < 0) num = 0;
                daftarProduk[idx].diskon_satuan = num;
                let harga = parseFloat(daftarProduk[idx].harga_satuan) || 0;
                let jumlah = parseFloat(daftarProduk[idx].jumlah) || 0;
                daftarProduk[idx].subtotal = jumlah * (harga - num);
                if (daftarProduk[idx].subtotal < 0) daftarProduk[idx].subtotal = 0;
                // Update subtotal cell only
                const row = e.target.closest('tr');
                if (row) {
                    const subtotalCell = row.querySelector('.subtotal-col');
                    if (subtotalCell) subtotalCell.textContent = formatRupiah(daftarProduk[idx].subtotal);
                }
                // Update total pesanan
                let total = daftarProduk.reduce((sum, item) => sum + item.subtotal, 0);
                document.getElementById('total_pesanan_display').value = total > 0 ? formatNumberInput(total) : '';
                document.getElementById('total_pesanan').value = total;
                document.getElementById('detail_json').value = JSON.stringify(daftarProduk);
                updateTabel();
            }
        }, true);
        // Format ribuan pada blur agar input tetap lancar
        document.addEventListener('blur', function(e) {
            if (e.target && e.target.classList.contains('diskon-inline')) {
                let val = e.target.value.replace(/\D/g, '');
                e.target.value = val ? parseInt(val, 10).toLocaleString('id-ID') : '';
            }
            if (e.target && e.target.classList.contains('jumlah-inline')) {
                let val = e.target.value.replace(/\D/g, '');
                e.target.value = val ? parseInt(val, 10) : '';
            }
        }, true);
        window._inlineListener = true;
    }

    // Inisialisasi tabel saat halaman dibuka
    updateTabel();

    // Harga satuan otomatis saat pilih produk
    document.getElementById('kode_produk').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const harga = selected.getAttribute('data-harga');
        const satuan = selected.getAttribute('data-satuan') || '';
        document.getElementById('harga_satuan').value = harga ? formatNumberInput(harga) : '';
        document.getElementById('satuan').value = satuan;
    });

    // Update sisa tagihan saat uang muka diubah
    document.getElementById('uang_muka').addEventListener('input', function() {
        // Ambil total pesanan dan uang muka
        const total = parseNumberInput(document.getElementById('total_pesanan_display').value);
        const uangMuka = parseNumberInput(this.value);
        const sisaTagihan = Math.max(total - uangMuka, 0);
        document.getElementById('sisa_tagihan').value = sisaTagihan > 0 ? sisaTagihan : '';
        // Tidak perlu format ribuan
        this.value = parseNumberInput(this.value);
    });
    document.getElementById('uang_muka').addEventListener('blur', function() {
        // Tidak perlu format ribuan
        this.value = parseNumberInput(this.value);
    });

    // Cegah submit jika belum ada produk
    document.querySelector('form').addEventListener('submit', function(e) {
        if (daftarProduk.length === 0) {
            alert('Minimal 1 produk harus ditambahkan!');
            e.preventDefault();
            return false;
        }
    });
</script>
@endsection