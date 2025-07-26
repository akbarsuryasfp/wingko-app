@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">EDIT PERMINTAAN PEMBELIAN</h3>
    <style>
    .table th {
        text-align: center;
        vertical-align: middle !important;
    }
    .col-nama-bahan {
        width: 250px;
    }
    .col-jumlah-order {
        width: 180px;
    }
    </style>
    <form action="{{ route('orderbeli.update', $order->no_order_beli) }}" method="POST">
        @csrf
        @method('PUT')
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Order -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Kode Order Pembelian</label>
                    <input type="text" name="no_order_beli" class="form-control" value="{{ $order->no_order_beli }}" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Tanggal Order</label>
                    <input type="date" name="tanggal_order" class="form-control" value="{{ $order->tanggal_order }}" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Nama Supplier</label>
                    <select name="kode_supplier" class="form-control" required>
                        <option value="">---Pilih Supplier---</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->kode_supplier }}" {{ $order->kode_supplier == $supplier->kode_supplier ? 'selected' : '' }}>{{ $supplier->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalKekurangan">
                    Kekurangan Bahan
                </button>
                <button type="button" class="btn btn-warning ms-2" data-bs-toggle="modal" data-bs-target="#prediksiModal">
                    Kebutuhan Produksi
                </button>
            </div>

            <!-- Kolom Kanan: Data Bahan -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Nama Bahan</label>
                    <select id="kode_bahan" class="form-control">
                        <option value="">---Pilih Bahan---</option>
                        @foreach($bahans as $bahan)
                            <option value="{{ $bahan->kode_bahan }}" data-satuan="{{ $bahan->satuan }}">
                                {{ $bahan->nama_bahan }} ({{ $bahan->satuan }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Jumlah Beli</label>
                    <input type="number" id="jumlah_beli" class="form-control">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Harga/Satuan</label>
                    <input type="number" id="harga_beli" class="form-control">
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary w-100" onclick="tambahBahan()">Tambah Bahan</button>
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PERMINTAAN PEMBELIAN</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-bahan">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Jumlah Order</th>
                    <th>Harga/Satuan</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="d-flex justify-content-between mt-4">
            <!-- Tombol kiri -->
            <div>
                <a href="{{ route('orderbeli.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning">Reset</button>
            </div>

            <!-- Total dan Submit kanan -->
            <div class="d-flex align-items-center gap-3">
                <label class="mb-0">Total Harga</label>
                <input type="text" id="total_order" name="total_order" readonly class="form-control" style="width: 160px;">
                <button type="submit" class="btn btn-success">Update</button>
                <input type="hidden" name="status" value="Baru">
            </div>
        </div>

        <input type="hidden" name="detail_json" id="detail_json">
    </form>
</div>

<!-- Modal Kekurangan Bahan -->
<div class="modal fade" id="modalKekurangan" tabindex="-1" aria-labelledby="modalKekuranganLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalKekuranganLabel">Daftar Bahan Kurang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="list-group" id="listKekurangan">
          <!-- Akan diisi via JS -->
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Modal Prediksi Kebutuhan -->
<div class="modal fade" id="prediksiModal" tabindex="-1" aria-labelledby="prediksiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="prediksiModalLabel">Kebutuhan Produksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs" id="prediksiTab" role="tablist"></ul>
        <div class="tab-content" id="prediksiTabContent"></div>
      </div>
    </div>
  </div>
</div>

<script>
    let daftarBahan = @json($details);
    let bahanKurangList = @json($bahanKurang ?? []);
    let bahansPrediksi = @json($bahansPrediksi ?? []);

    function tambahBahan() {
        const bahanSelect = document.getElementById('kode_bahan');
        const kode_bahan = bahanSelect.value;
        const nama_bahan = bahanSelect.options[bahanSelect.selectedIndex].text;
        const satuan = bahanSelect.options[bahanSelect.selectedIndex].dataset.satuan;
        const jumlah_beli = parseFloat(document.getElementById('jumlah_beli').value);
        const harga_beli = parseFloat(document.getElementById('harga_beli').value);

        if (!kode_bahan || !jumlah_beli || !harga_beli) {
            alert("Silakan lengkapi data bahan.");
            return;
        }

        const total = jumlah_beli * harga_beli;

        daftarBahan.push({ kode_bahan, nama_bahan, satuan, jumlah_beli, harga_beli, total });
        updateTabel();

        // Reset input bahan
        bahanSelect.selectedIndex = 0;
        document.getElementById('jumlah_beli').value = '';
        document.getElementById('harga_beli').value = '';
    }

    function hapusBaris(index) {
        daftarBahan.splice(index, 1);
        updateTabel();
    }

    function updateTabel() {
        const tbody = document.querySelector('#daftar-bahan tbody');
        tbody.innerHTML = '';

        let totalOrder = 0;

        daftarBahan.forEach((item, index) => {
            item.jumlah_beli = parseFloat(item.jumlah_beli) || 0;
            item.harga_beli = parseFloat(item.harga_beli) || 0;
            item.total = item.jumlah_beli * item.harga_beli;

            totalOrder += item.total;

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_bahan}</td>
                    <td>${item.satuan}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm" value="${item.jumlah_beli}" min="1" onchange="ubahJumlahOrder(${index}, this.value)">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm" value="${item.harga_beli}" min="0" onchange="ubahHargaOrder(${index}, this.value)">
                    </td>
                    <td>${item.total.toLocaleString()}</td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(${index})">X</button></td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('total_order').value = totalOrder.toLocaleString();
        document.getElementById('detail_json').value = JSON.stringify(daftarBahan);
    }

    function ubahJumlahOrder(index, value) {
        value = parseFloat(value) || 0;
        daftarBahan[index].jumlah_beli = value;
        daftarBahan[index].total = value * (parseFloat(daftarBahan[index].harga_beli) || 0);
        updateTabel();
    }

    function ubahHargaOrder(index, value) {
        value = parseFloat(value) || 0;
        daftarBahan[index].harga_beli = value;
        daftarBahan[index].total = (parseFloat(daftarBahan[index].jumlah_beli) || 0) * value;
        updateTabel();
    }

    // Inisialisasi tabel saat halaman dibuka
    updateTabel();

    // Tampilkan daftar bahan kurang di modal
    document.addEventListener('DOMContentLoaded', function() {
        const listKekurangan = document.getElementById('listKekurangan');
        if (listKekurangan && bahanKurangList.length) {
            listKekurangan.innerHTML = '';
            bahanKurangList.forEach((item, idx) => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.style.cursor = 'pointer';
                li.innerHTML = `
                    <span>
                        <strong>${item.nama_bahan}</strong> (${item.satuan})<br>
                        <small>Kurang: ${item.jumlah_beli}</small>
                    </span>
                    <button class="btn btn-sm btn-primary" onclick="isiInputBahan('${item.kode_bahan}', '${item.nama_bahan}', '${item.satuan}', ${item.jumlah_beli})" data-bs-dismiss="modal">Pilih</button>
                `;
                listKekurangan.appendChild(li);
            });
        } else if(listKekurangan) {
            listKekurangan.innerHTML = '<li class="list-group-item text-center text-muted">Tidak ada bahan yang kurang</li>';
        }
    });

    // Kelompokkan bahan berdasarkan frekuensi pembelian
    function groupByFrekuensi(bahans) {
        const group = {};
        bahans.forEach(b => {
            const freq = b.frekuensi_pembelian || 'Stok Minimal';
            if (!group[freq]) group[freq] = [];
            group[freq].push(b);
        });
        return group;
    }

    // Tampilkan modal prediksi kebutuhan
    document.addEventListener('DOMContentLoaded', function() {
        const prediksiTab = document.getElementById('prediksiTab');
        const prediksiTabContent = document.getElementById('prediksiTabContent');
        if (prediksiTab && prediksiTabContent && bahansPrediksi.length) {
            const grouped = groupByFrekuensi(bahansPrediksi);
            prediksiTab.innerHTML = '';
            prediksiTabContent.innerHTML = '';
            
            // Urutan tab yang diinginkan
            const tabOrder = ['Mingguan', 'Dua Mingguan', 'Bulanan', 'Tiga Bulanan', 'Stok Minimal'];
            
            tabOrder.forEach((freq, idx) => {
                if (!grouped[freq]) return;
                
                // Tab header
                prediksiTab.innerHTML += `
                    <li class="nav-item" role="presentation">
                      <button class="nav-link ${idx === 0 ? 'active' : ''}" id="tab-${idx}" data-bs-toggle="tab" data-bs-target="#tab-content-${idx}" type="button" role="tab">${freq}</button>
                    </li>
                `;
                
                // Tab content
                let rows = '';
                grouped[freq].forEach((bahan, i) => {
                    const isStokKurang = parseFloat(bahan.stok ?? 0) < parseFloat(bahan.stokmin ?? 0);
                    rows += `
                        <tr style="background-color: ${isStokKurang ? '#fff3cd' : 'inherit'}">
                            <td class="text-center">${i + 1}</td>
                            <td>${bahan.nama_bahan}</td>
                            <td class="text-center">${bahan.interval ? bahan.interval + 'x' : '-'}</td>
                            <td class="text-center">${bahan.jumlah_per_order ?? '-'}</td>
                            <td class="text-center" style="color:${isStokKurang ? 'red' : 'inherit'}; font-weight:${isStokKurang ? 'bold' : 'normal'}">
                                ${(bahan.stokmin != null) ? parseFloat(bahan.stokmin).toFixed(2) : '-'}
                            </td>
                            <td class="text-center">
                                ${(bahan.stok != null) ? parseFloat(bahan.stok).toFixed(2) : '-'}
                            </td>
                            <td class="text-center">${bahan.satuan}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-primary"
                                        onclick="isiInputBahan('${bahan.kode_bahan}', '${bahan.nama_bahan}', '${bahan.satuan}', ${bahan.jumlah_per_order ?? 1})"
                                        data-bs-dismiss="modal">
                                    Pilih
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                prediksiTabContent.innerHTML += `
                    <div class="tab-pane fade ${idx === 0 ? 'show active' : ''}" id="tab-content-${idx}" role="tabpanel">
                        <table class="table table-bordered mt-3">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th>No</th>
                                    <th class="col-nama-bahan">Nama Bahan</th>
                                    <th>Frekuensi Pembelian</th>
                                    <th class="col-jumlah-order">Jumlah Dibeli per Periode</th>
                                    <th>Stok Minimum</th>
                                    <th>Stok Tersedia</th>
                                    <th>Satuan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows}
                            </tbody>
                        </table>
                    </div>
                `;
            });
        }
    });

    function isiInputBahan(kode, nama, satuan, jumlah) {
        let idx = daftarBahan.findIndex(b => b.kode_bahan === kode);
        if (idx !== -1) {
            daftarBahan[idx].jumlah_beli += jumlah;
            daftarBahan[idx].total = daftarBahan[idx].jumlah_beli * (parseFloat(daftarBahan[idx].harga_beli) || 0);
        } else {
            daftarBahan.push({
                kode_bahan: kode,
                nama_bahan: nama,
                satuan: satuan,
                jumlah_beli: jumlah,
                harga_beli: 0,
                total: 0
            });
        }
        updateTabel();
        
        // Auto-fill the form
        const bahanSelect = document.getElementById('kode_bahan');
        for (let i = 0; i < bahanSelect.options.length; i++) {
            if (bahanSelect.options[i].value == kode) {
                bahanSelect.selectedIndex = i;
                break;
            }
        }
        document.getElementById('jumlah_beli').value = jumlah;
    }
</script>
@endsection