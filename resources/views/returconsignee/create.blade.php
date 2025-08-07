@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm mt-4">
        <div class="card-body">
            <h3 class="mb-4">INPUT RETUR CONSIGNEE (MITRA)</h3>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('returconsignee.store') }}" method="POST">
                @csrf
                <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                    <!-- Kolom Kiri: Data Retur -->
                    <div style="flex: 1;">
                        <div class="mb-3 d-flex align-items-center">
                            <label class="me-2" style="width: 180px;">No Retur Consignee</label>
                            <input type="text" name="no_returconsignee" class="form-control" value="{{ $no_returconsignee }}" readonly style="pointer-events: none; background: #e9ecef;">
                        </div>
                        <div class="mb-3 d-flex align-items-center">
                            <label class="me-2" style="width: 180px;">No Konsinyasi Keluar</label>
                            <select name="no_konsinyasikeluar" id="no_konsinyasikeluar" class="form-control" required>
                                <option value="">---Pilih No Konsinyasi Keluar---</option>
                                @foreach($konsinyasikeluar as $k)
                                    <option value="{{ $k->no_konsinyasikeluar }}" data-consignee="{{ $k->consignee->kode_consignee ?? '' }}" data-nama="{{ $k->consignee->nama_consignee ?? '' }}">
                                        {{ $k->no_konsinyasikeluar }} | {{ $k->consignee->nama_consignee ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 d-flex align-items-center">
                            <label class="me-2" style="width: 180px;">Tanggal Retur</label>
                            <input type="date" name="tanggal_returconsignee" id="tanggal_returconsignee" class="form-control" value="" placeholder="dd/mm/yyyy" required>
                        </div>
                        <div class="mb-3 d-flex align-items-center">
                            <label class="me-2" style="width: 180px;">Nama Consignee (Mitra)</label>
                            <input type="text" name="nama_consignee" id="nama_consignee" class="form-control" value="" readonly>
                            <input type="hidden" name="kode_consignee" id="kode_consignee" value="">
                        </div>
                        <div class="mb-3 d-flex align-items-center">
                            <label class="me-2" style="width: 180px;">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan') }}">
                        </div>
                    </div>
                    <!-- Kolom Kanan: Data Produk Retur -->
                    <!-- HAPUS seluruh input manual produk di sini -->
                </div>

                <hr>

                <!-- Judul di atas tabel, tengah -->
                <h4 class="text-center mb-3">DAFTAR PRODUK RETUR CONSIGNEE (MITRA)</h4>

                <!-- Tabel Produk Retur -->
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
                        <a href="{{ route('returconsignee.index') }}" class="btn btn-secondary">Back</a>
                        <button type="reset" class="btn btn-warning">Reset</button>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <label class="mb-0">Total Retur</label>
                        <div class="input-group" style="width: 180px;">
                            <span class="input-group-text">Rp</span>
                            <input type="text" id="total_nilai_retur_view" readonly class="form-control" style="background:#e9ecef;pointer-events:none;">
                        </div>
                        <input type="hidden" id="total_nilai_retur" name="total_nilai_retur">
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </div>

                <input type="hidden" name="detail_json" id="detail_json">
                <input type="hidden" name="from_retur_terima" value="0">
            </form>
        </div>
    </div>
</div>

<script>
    // Reset tanggal dan keterangan ke kosong
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('reset', function(e) {
            setTimeout(function() {
                document.getElementById('tanggal_returconsignee').value = '';
                const ket = form.querySelector('input[name="keterangan"]');
                if (ket) ket.value = '';
            }, 10);
        });
    });
    // Prefill from penerimaankonsinyasi if prefill_retur param exists
    document.addEventListener('DOMContentLoaded', async function() {
        const urlParams = new URLSearchParams(window.location.search);
        const prefillRetur = urlParams.get('prefill_retur');
        const noKonsinyasiKeluar = urlParams.get('no_konsinyasikeluar');
        const kodeConsignee = urlParams.get('kode_consignee');
        if (prefillRetur && noKonsinyasiKeluar && kodeConsignee) {
            // Set select value and trigger change
            const select = document.getElementById('no_konsinyasikeluar');
            select.value = noKonsinyasiKeluar;
            // Set consignee fields if possible
            const namaConsigneeInput = document.getElementById('nama_consignee');
            const kodeConsigneeInput = document.getElementById('kode_consignee');
            // Try to set nama_consignee from option data-nama
            const selected = select.querySelector('option[value="' + noKonsinyasiKeluar + '"]');
            if (selected) {
                namaConsigneeInput.value = selected.getAttribute('data-nama') || '';
                kodeConsigneeInput.value = selected.getAttribute('data-consignee') || '';
            } else {
                namaConsigneeInput.value = '';
                kodeConsigneeInput.value = kodeConsignee;
            }
            // Fetch detail produk dari penerimaankonsinyasi
            try {
                const res = await fetch('/api/penerimaankonsinyasi-detail/' + noKonsinyasiKeluar);
                const data = await res.json();
                daftarProdukRetur = [];
                maxJumlahPerProduk = {};
                if (data && data.success && Array.isArray(data.produkList)) {
                    data.produkList.forEach(function(item) {
                        const selisih = Math.max((item.jumlah_setor || 0) - (item.jumlah_terjual || 0), 0);
                        if (selisih > 0) {
                            maxJumlahPerProduk[item.kode_produk] = selisih;
                            daftarProdukRetur.push({
                                kode_produk: item.kode_produk,
                                nama_produk: item.nama_produk,
                                satuan: item.satuan,
                                jumlah_retur: selisih,
                                harga_satuan: item.harga_satuan,
                                alasan: '',
                                subtotal: selisih * item.harga_satuan
                            });
                        }
                    });
                }
                updateTabelRetur();
            } catch (e) {}
        }
    });
    let daftarProdukRetur = [];
    let maxJumlahPerProduk = {};

    function hapusBarisRetur(index) {
        daftarProdukRetur.splice(index, 1);
        updateTabelRetur();
    }


    function formatRupiah(angka) {
        return 'Rp' + Number(angka).toLocaleString('id-ID');
    }

    function updateTabelRetur() {
        const tbody = document.querySelector('#daftar-produk-retur tbody');
        tbody.innerHTML = '';

        let totalRetur = 0;

        if (daftarProdukRetur.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8">Tidak ada produk konsinyasi keluar.</td></tr>';
        } else {
            daftarProdukRetur.forEach((item, index) => {
                const subtotal = Number(item.jumlah_retur) * Number(item.harga_satuan) || 0;
                totalRetur += subtotal;

                const max = maxJumlahPerProduk[item.kode_produk] || 0;
                const row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.nama_produk}</td>
                        <td>${item.satuan || '-'}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm" min="0" max="${max}" value="${item.jumlah_retur}" 
                                onchange="updateJumlahRetur(${index}, this.value)">
                            <small class="text-muted">Max Dapat Dinput: ${max}</small>
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
        }

        document.getElementById('total_nilai_retur_view').value = totalRetur > 0 ? formatRupiah(totalRetur) : '';
        document.getElementById('total_nilai_retur').value = totalRetur;
        document.getElementById('detail_json').value = JSON.stringify(daftarProdukRetur);
    }

    function updateJumlahRetur(index, value) {
        const kode_produk = daftarProdukRetur[index].kode_produk;
        const max = maxJumlahPerProduk[kode_produk] || 0;
        if (Number(value) > max) {
            alert("Jumlah retur melebihi jumlah setor (" + max + ")");
            daftarProdukRetur[index].jumlah_retur = max;
        } else {
            daftarProdukRetur[index].jumlah_retur = Number(value);
        }
        daftarProdukRetur[index].subtotal = daftarProdukRetur[index].jumlah_retur * daftarProdukRetur[index].harga_satuan;
        updateTabelRetur();
    }

    function updateAlasanRetur(index, value) {
        daftarProdukRetur[index].alasan = value;
        updateTabelRetur();
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        if (daftarProdukRetur.length === 0) {
            alert('Minimal 1 produk retur harus ditambahkan!');
            e.preventDefault();
            return false;
        }
    });

    document.getElementById('no_konsinyasikeluar').addEventListener('change', function() {
        var no_konsinyasikeluar = this.value;
        var namaConsigneeInput = document.getElementById('nama_consignee');
        var kodeConsigneeInput = document.getElementById('kode_consignee');
        var selected = this.options[this.selectedIndex];
        if (!no_konsinyasikeluar) {
            namaConsigneeInput.value = '';
            kodeConsigneeInput.value = '';
            daftarProdukRetur = [];
            maxJumlahPerProduk = {};
            updateTabelRetur();
            return;
        }
        namaConsigneeInput.value = selected.getAttribute('data-nama') || '';
        kodeConsigneeInput.value = selected.getAttribute('data-consignee') || '';

        fetch('/returconsignee/produk-keluar?no_konsinyasikeluar=' + no_konsinyasikeluar)
            .then(response => response.json())
            .then(async data => {
                maxJumlahPerProduk = {};
                daftarProdukRetur = [];
                if (data.produk && Array.isArray(data.produk)) {
                    // Untuk setiap produk, cek apakah berasal dari penerimaankonsinyasi
                    for (const item of data.produk) {
                        // AJAX ke endpoint baru untuk cek asal dan jumlah_terjual
                        let jumlahSetor = item.jumlah_setor;
                        let jumlahRetur = 0;
                        let maxRetur = jumlahSetor;
                        try {
                            const res = await fetch(`/returconsignee/cek-asal-produk?no_konsinyasikeluar=${no_konsinyasikeluar}&kode_produk=${item.kode_produk}`);
                            const info = await res.json();
                            if (info && info.berasal_penerimaan) {
                                // Jika berasal dari penerimaankonsinyasi, max = jumlah_setor - jumlah_terjual
                                maxRetur = Math.max(jumlahSetor - (info.jumlah_terjual || 0), 0);
                                jumlahRetur = maxRetur;
                            }
                        } catch (e) {}
                        maxJumlahPerProduk[item.kode_produk] = maxRetur;
                        daftarProdukRetur.push({
                            kode_produk: item.kode_produk,
                            nama_produk: item.nama_produk,
                            satuan: item.satuan,
                            jumlah_retur: jumlahRetur,
                            harga_satuan: item.harga_setor,
                            alasan: '',
                            subtotal: jumlahRetur * item.harga_setor
                        });
                    }
                }
                updateTabelRetur();
            });
    });
</script>
@endsection
