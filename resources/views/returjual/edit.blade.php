@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
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
                            <label class="me-2" style="width: 180px;">Nama Pelanggan</label>
                            <select name="kode_pelanggan" id="kode_pelanggan" class="form-control" required style="pointer-events: none; background: #e9ecef;" tabindex="-1" readonly>
                                <option value="">---Pilih Pelanggan---</option>
                                @foreach($pelanggan as $p)
                                    <option value="{{ $p->kode_pelanggan }}" {{ $returjual->kode_pelanggan == $p->kode_pelanggan ? 'selected' : '' }}>{{ $p->nama_pelanggan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 d-flex align-items-center">
                            <label class="me-2" style="width: 180px;">Jenis Retur</label>
                            <select name="jenis_retur" class="form-control" required>
                                <option value="">---Pilih Jenis Retur---</option>
                                <option value="Barang" {{ $returjual->jenis_retur == 'Barang' ? 'selected' : '' }}>Barang</option>
                                <option value="Uang" {{ $returjual->jenis_retur == 'Uang' ? 'selected' : '' }}>Uang</option>
                            </select>
                        </div>
                        <div class="mb-3 d-flex align-items-center">
                            <label class="me-2" style="width: 180px;">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" value="{{ $returjual->keterangan }}">
                        </div>
                    </div>

                    <!-- Kolom Kanan: Data Produk Retur dihapus agar tampilan edit sama seperti create -->
                </div>

                <hr>

                <h4 class="text-center">DAFTAR PRODUK RETUR PENJUALAN</h4>
                <table class="table table-bordered text-center align-middle" id="daftar-produk-retur">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Satuan</th>
                            <th>Jumlah Retur</th>
                            <th>Harga/Satuan</th>
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
                        <div class="input-group" style="width: 180px;">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="total_nilai_retur_view" readonly class="form-control" style="background:#e9ecef;pointer-events:none;">
                        </div>
                        <input type="hidden" id="total_nilai_retur" name="total_nilai_retur">
                        <button type="submit" class="btn btn-success">Update</button>
                    </div>
                </div>

                <input type="hidden" name="detail_json" id="detail_json">
            </form>
        </div>
    </div>
</div>

@php
    if (!isset($jenisList)) {
        $jenisList = ['Penjualan', 'Pengembalian'];
    }
@endphp

<script>
    // Gabungkan semua produk penjualan (t_penjualan_detail) dengan detail retur
    let daftarProdukRetur = [];
    let maxJumlahPerProduk = {};
    let hargaSatuanProduk = {};
    // Mapping harga satuan per produk
    @foreach($produk as $pr)
        hargaSatuanProduk['{{ $pr->kode_produk }}'] = {{ $pr->harga_satuan ?? 0 }};
    @endforeach

    // Data jumlah maksimal per produk dari penjualan
    @foreach($penjualanDetail as $kode_produk => $detail)
        maxJumlahPerProduk['{{ $kode_produk }}'] = {{ $detail->jumlah }};
    @endforeach

    // Gabungkan produk penjualan dan detail retur
    @php
        // Pastikan $produk asosiatif: [kode_produk => produkObj]
        if (is_array($produk)) {
            $produkAssoc = $produk;
        } elseif (method_exists($produk, 'keyBy')) {
            $produkAssoc = $produk->keyBy('kode_produk')->all();
        } else {
            $produkAssoc = [];
        }
    @endphp
    @foreach($penjualanDetail as $kode_produk => $detail)
        @php
            $found = null;
            foreach ($details as $d) {
                if ($d['kode_produk'] == $kode_produk) { $found = $d; break; }
            }
            // Selalu prioritaskan nama produk dari t_produk (master produk)
            $nama_produk = '';
            if (isset($produkAssoc[$kode_produk]) && isset($produkAssoc[$kode_produk]->nama_produk)) {
                $nama_produk = $produkAssoc[$kode_produk]->nama_produk;
            } else if (isset($detail->produk) && isset($detail->produk->nama_produk)) {
                $nama_produk = $detail->produk->nama_produk;
            } else if (isset($detail->nama_produk)) {
                $nama_produk = $detail->nama_produk;
            } else if ($found && isset($found['nama_produk'])) {
                $nama_produk = $found['nama_produk'];
            }
            if (empty($nama_produk)) {
                $nama_produk = 'Tidak ditemukan';
            }
            $harga_satuan = isset($detail->harga_satuan) ? $detail->harga_satuan : (isset($detail->produk) && isset($detail->produk->harga_satuan) ? $detail->produk->harga_satuan : 0);
        @endphp
        // DEBUG: tampilkan nama_produk dan kode_produk di console
        console.log('kode_produk: {{ $kode_produk }} | nama_produk: {{ $nama_produk }}');
        @if ($loop->first)
        console.log('DAFTAR KODE PRODUK DI $produk:', {!! json_encode(array_keys($produkAssoc)) !!});
        @endif
        daftarProdukRetur.push({
            kode_produk: '{{ $kode_produk }}',
            nama_produk: {!! json_encode($nama_produk) !!},
            satuan: @php
                $satuan = '-';
                if (isset($produkAssoc[$kode_produk]) && isset($produkAssoc[$kode_produk]->satuan)) {
                    $satuan = $produkAssoc[$kode_produk]->satuan;
                } else if (isset($detail->produk) && isset($detail->produk->satuan)) {
                    $satuan = $detail->produk->satuan;
                } else if (isset($detail->satuan)) {
                    $satuan = $detail->satuan;
                } else if ($found && isset($found['satuan'])) {
                    $satuan = $found['satuan'];
                }
            @endphp {!! json_encode($satuan) !!},
            jumlah_retur: {{ $found ? $found['jumlah_retur'] : 0 }},
            harga_satuan: {{ $found ? $found['harga_satuan'] : $harga_satuan }},
            alasan: {!! json_encode($found ? $found['alasan'] : '') !!},
            subtotal: {{ $found ? ($found['jumlah_retur'] * $found['harga_satuan']) : 0 }}
        });
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
        if (!angka && angka !== 0) return '';
        return parseInt(angka).toLocaleString('id-ID');
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
                    <td>${item.satuan || '-'}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm" min="0" max="${max}" value="${item.jumlah_retur}" 
                            onchange="updateJumlahRetur(${index}, this.value)">
                        <small class="text-muted">Max Dapat Diinput: ${max}</small>
                    </td>
                    <td>Rp ${formatRupiah(item.harga_satuan)}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm" value="${item.alasan || ''}" 
                            onchange="updateAlasanRetur(${index}, this.value)">
                    </td>
                    <td>Rp ${formatRupiah(subtotal)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="hapusBarisRetur(${index})" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('total_nilai_retur_view').value = totalRetur > 0 ? formatRupiah(totalRetur) : '';
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