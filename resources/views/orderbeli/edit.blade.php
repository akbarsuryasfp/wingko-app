@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <style>
            input[readonly] {
                background-color: #e9ecef;
                color: #495057;
            }
            .table th {
                text-align: center;
                vertical-align: middle !important;
            }
        </style>

        <div class="card-header bg-transparent border-0 text-center py-3">
            <h4 class="mb-0">Edit Order Pembelian</h4>
        </div>
        
        <div class="card-body">
            <form action="{{ route('orderbeli.update', $order->no_order_beli) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-1 mb-2"> 
                    <!-- Kolom Kiri: Data Order -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-light shadow-sm ">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-3">
                                    <i class="fas fa-clipboard-list me-0"></i>Informasi Permintaan Pembelian
                                </h5>
                                
                                <div class="flex-grow-1">
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-medium">Kode Permintaan</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="no_order_beli" class="form-control form-control-sm" value="{{ $order->no_order_beli }}" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-medium">Tanggal Order</label>
                                        <div class="col-sm-8">
                                            <input type="date" name="tanggal_order" class="form-control form-control-sm" value="{{ $order->tanggal_order }}" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-medium">Supplier</label>
                                        <div class="col-sm-8">
                                            <select name="kode_supplier" class="form-select form-select-sm" required>
                                                <option value="">Pilih Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->kode_supplier }}" {{ $order->kode_supplier == $supplier->kode_supplier ? 'selected' : '' }}>
                                                        {{ $supplier->nama_supplier }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <div class="row g-3">
                                        <!-- Tombol Kekurangan Produksi -->
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-primary w-100 py-2" data-bs-toggle="modal" data-bs-target="#modalKekurangan">
                                                <i class="fas fa-boxes me-2"></i> Kekurangan Produksi
                                                <span class="badge bg-white text-primary ms-2">!</span>
                                            </button>
                                        </div>
                                        
                                        <!-- Tombol Stok Minimal -->
                                        <div class="col-md-6">
    <button type="button" class="btn btn-warning w-100 py-2" data-bs-toggle="modal" data-bs-target="#stokMinModal">
        <i class="fas fa-exclamation-triangle me-2"></i> Stok Minimal
        <span class="badge bg-white text-warning ms-2">!</span>
    </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Data Bahan -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-light shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-3">
                                    <i class="fas fa-cube me-0"></i>Tambah Bahan
                                </h5>
                                
                                <div class="flex-grow-1">
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-medium">Nama Bahan</label>
                                        <div class="col-sm-8">
                                            <select id="kode_bahan" class="form-select form-select-sm">
                                                <option value="">Pilih Bahan</option>
                                                @foreach($bahans as $bahan)
                                                    <option value="{{ $bahan->kode_bahan }}" data-satuan="{{ $bahan->satuan }}">
                                                        {{ $bahan->nama_bahan }} ({{ $bahan->satuan }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-medium">Jumlah Beli</label>
                                        <div class="col-sm-8">
                                            <input type="number" id="jumlah_beli" class="form-control form-control-sm" min="1">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-medium">Harga/Satuan</label>
                                        <div class="col-sm-8">
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" id="harga_beli" class="form-control form-control-sm" min="0" placeholder="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="button" 
                                            class="btn btn-primary w-100 py-2" 
                                            onclick="tambahBahan()"
                                            id="tambahBahanBtn">
                                        <i class="fas fa-plus-circle me-2"></i> Tambah Bahan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


<!-- Daftar Permintaan Pembelian -->
<div class="card border-light mb-1">
    <div class="card-body p-2">
        <h5 class="card-title text-center mb-2">Daftar Permintaan Pembelian</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle" id="daftar-bahan">
                <thead class="table-light">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th class="text-center">Nama Bahan</th>
                        <th width="10%" class="text-center">Satuan</th>
                        <th width="15%" class="text-center">Jumlah Order</th>
                        <th width="15%" class="text-center">Harga/Satuan</th>
                        <th width="15%" class="text-center">Total</th>
                        <th width="5%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
                    <div class="col-md-6 mb-2">
                        <a href="{{ route('orderbeli.index') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i> ← Kembali
                        </a>
                        <button type="reset" class="btn btn-warning">
                             Reset
                        </button>
                    </div>
    
    <div class="col-md-6 text-md-end mb-2">
        <div class="d-flex align-items-center justify-content-md-end gap-1">
            <label class="mb-0 small">Total:</label>
<div class="input-group input-group-sm" style="width: 180px;">
    <span class="input-group-text">Rp</span>
    <input type="text" id="total_order" name="total_order" readonly
           class="form-control text-end fw-bold"
           style="font-size:1.25em;"
           value="0">
</div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save me-1"></i> Update
        </button>
            <input type="hidden" name="status" value="Baru">
        </div>
    </div>
</div>

<input type="hidden" name="detail_json" id="detail_json">

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


<!-- Modal Stok Minimal -->
<div class="modal fade" id="stokMinModal" tabindex="-1" aria-labelledby="stokMinModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="stokMinModalLabel">Daftar Bahan di Bawah Stok Minimal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @if(count($stokMinList) > 0)
        <table class="table table-bordered table-sm align-middle">
          <thead>
            <tr class="text-center">
              <th style="width: 5%;">No</th>
              <th class="text-start">Nama Bahan</th>
              <th style="width: 15%;">Stok Minimal</th>
              <th style="width: 15%;">Stok Saat Ini</th>
              <th style="width: 15%;">Selisih</th>
              <th style="width: 10%;">Satuan</th>
              <th style="width: 5%;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($stokMinList as $i => $item)
            @php
                $selisih = $item->stokmin - $item->stok;
            @endphp
            <tr>
              <td class="text-center">{{ $i + 1 }}</td>
              <td class="text-start">{{ $item->nama_bahan }}</td>
              <td class="text-center">{{ $item->stokmin }}</td>
              <td class="text-center">{{ $item->stok }}</td>
              <td class="text-center">{{ $selisih }}</td>
              <td class="text-center">{{ $item->satuan }}</td>
              <td class="text-center">
                @if ($selisih > 0)
<button type="button" class="btn btn-sm btn-primary p-1"
  onclick="isiInputBahan('{{ $item->kode_bahan }}', '{{ $item->nama_bahan }}', '{{ $item->satuan }}', {{ $selisih }})"
  data-bs-dismiss="modal">
  Pilih
</button>
                @else
                <span class="text-muted small">Cukup</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @else
        <div class="alert alert-info">Semua stok bahan mencukupi minimal</div>
        @endif
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

        function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', {
            style: 'decimal',
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(angka);
    }

    function formatCurrency(angka) {
        return `Rp ${formatRupiah(angka)}`;
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
        <td class="text-center align-middle">${index + 1}</td>
        <td class="align-middle">${item.nama_bahan}</td>
        <td class="text-center align-middle">${item.satuan}</td>
        <td class="text-center align-middle">
            <input type="number" class="form-control form-control-sm" 
                   value="${item.jumlah_beli}" min="1" 
                   onchange="ubahJumlahOrder(${index}, this.value)">
        </td>
        <td class="text-center align-middle">
            <div class="input-group input-group-sm">
                <span class="input-group-text">Rp</span>
                <input type="number" class="form-control form-control-sm" 
                       value="${item.harga_beli}" min="0"
                       onchange="ubahHargaOrder(${index}, this.value)">
            </div>
        </td>
        <td class="text-center align-middle">
            <span class="d-inline-block text-nowrap">
                ${formatCurrency(item.total)}
            </span>
        </td>
        <td class="text-center align-middle">
            <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(${index})">X</button>
        </td>
    </tr>
`;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('total_order').value = JSON.stringify(totalOrder);
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
    <button type="button" class="btn btn-sm btn-primary"
        onclick="isiInputBahan('${item.kode_bahan}', '${item.nama_bahan}', '${item.satuan}', ${item.jumlah_beli})"
        data-bs-dismiss="modal">
        Pilih
    </button>
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
    <td class="text-center align-middle">${i + 1}</td>
    <td class="align-middle">${bahan.nama_bahan}</td>
    <td class="text-center align-middle">${bahan.interval ? bahan.interval + 'x' : '-'}</td>
    <td class="text-center align-middle">${bahan.jumlah_per_order ? formatRupiah(bahan.jumlah_per_order) : '-'}</td>
    <td class="text-center align-middle" style="color:${isStokKurang ? 'red' : 'inherit'}; font-weight:${isStokKurang ? 'bold' : 'normal'}">
        ${(bahan.stokmin != null) ? formatCurrency(bahan.stokmin) : '-'}
    </td>
    <td class="text-center align-middle">
        ${(bahan.stok != null) ? formatCurrency(bahan.stok) : '-'}
    </td>
    <td class="text-center align-middle">${bahan.satuan}</td>
    <td class="text-center align-middle">
        <button class="btn btn-sm btn-primary py-0 px-2"
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
<tr class="text-center">
    <th style="vertical-align: middle;">No</th>
    <th class="col-nama-bahan" style="vertical-align: middle;">Nama Bahan</th>
    <th style="vertical-align: middle;">Frekuensi Pembelian</th>
    <th class="col-jumlah-order" style="vertical-align: middle;">Jumlah Dibeli per Periode</th>
    <th style="vertical-align: middle;">Stok Minimum</th>
    <th style="vertical-align: middle;">Stok Tersedia</th>
    <th style="vertical-align: middle;">Satuan</th>
    <th style="vertical-align: middle;">Aksi</th>
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
    const bahanSelect = document.getElementById('kode_bahan');
    for (let i = 0; i < bahanSelect.options.length; i++) {
        if (bahanSelect.options[i].value == kode) {
            bahanSelect.selectedIndex = i;
            break;
        }
    }
    document.getElementById('jumlah_beli').value = jumlah;
    // Jika ingin auto-set satuan, bisa tambahkan:
    // document.getElementById('satuan_bahan').value = satuan;
}
</script>
@endsection