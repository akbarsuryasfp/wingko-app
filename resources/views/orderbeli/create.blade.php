@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
                            <style>
input[readonly] {
    background-color: #e9ecef;
    color: #495057;
}
                    </style>

<div class="card-header bg-transparent border-0 text-center py-3">
    <h4 class="mb-0">Tambah Order Pembelian</h4>
</div>
        
        <div class="card-body">
            <form action="{{ route('orderbeli.store') }}" method="POST">
                @csrf
                
<div class="row g-3 mb-4 align-items-stretch">
    <!-- Kolom Kiri: Data Order -->
    <div class="col-lg-6">
        <div class="card h-100 border-light shadow-sm">
            <div class="card-body d-flex flex-column">
                <h5 class="card-title mb-3">
                    <i class="fas fa-clipboard-list me-0"></i>Informasi Order Pembelian
                </h5>
                
                <div class="flex-grow-1">
                    <div class="mb-2 row align-items-center">
                        <label class="col-sm-4 col-form-label fw-medium">Kode Order</label>
                        <div class="col-sm-8">
                            <input type="text" name="no_order_beli" class="form-control form-control-sm" value="{{ $no_order_beli }}" readonly>
                        </div>
                    </div>
                    
                    <div class="mb-2 row align-items-center">
                        <label class="col-sm-4 col-form-label fw-medium">Tanggal Order</label>
                        <div class="col-sm-8">
                            <input type="date" name="tanggal_order" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    
                    <div class="mb-2 row align-items-center">
                        <label class="col-sm-4 col-form-label fw-medium">Supplier</label>
                        <div class="col-sm-8">
                            <select name="kode_supplier" class="form-select form-select-sm" required>
                                <option value="">Pilih Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->kode_supplier }}">{{ $supplier->nama_supplier }}</option>
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
        
        <!-- Tombol Kebutuhan Produksi -->
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

                <!-- Daftar Order Pembelian -->
                <div class="card border-light mb-">
                    <div class="card-body">
                        <h5 class="card-title text-center mb-3">Daftar Order Pembelian</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="daftar-bahan">
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

                <!-- Footer Buttons -->
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
    <div class="d-flex align-items-center justify-content-md-end gap-3">
        <label class="mb-0">Total Harga:</label>
        <div class="input-group" style="width: 150px;">
            <span class="input-group-text bg-light">Rp</span>
            <input type="text" id="total_order" name="total_order" readonly 
                   class="form-control text-end">
        </div>
        <button type="submit" class="btn btn-success">
            <i class="fas fa-save me-1"></i> Simpan
        </button>
    </div>
</div>
                </div>

                <input type="hidden" name="detail_json" id="detail_json">
            </form>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 8px;
    }
    .card-header {
        font-weight: 600;
        font-size: 1.25rem;
    }
    .card.border-light {
        border: 1px solid #e0e0e0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }
    .form-control, .form-select {
        font-size: 1rem;
    }

    /* Warna tab aktif dan tidak aktif pada modal kebutuhan bahan */
    #prediksiModal .nav-tabs .nav-link {
        background: linear-gradient(90deg, #f3f4f6 0%, #e0e7ff 100%);
        color: #1e293b;
        border: 1px solid #dbeafe;
        margin-right: 2px;
        font-weight: 500;
        transition: background 0.2s, color 0.2s;
    }
    #prediksiModal .nav-tabs .nav-link.active {
        background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
        color: #fff !important;
        border-color: #2563eb #2563eb #fff #2563eb;
        box-shadow: 0 2px 8px rgba(37,99,235,0.08);
    }
    #prediksiModal .nav-tabs .nav-link:focus,
    #prediksiModal .nav-tabs .nav-link:hover {
        background: linear-gradient(90deg, #3b82f6 0%, #93c5fd 100%);
        color: #fff;
    }
</style>

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
                <button class="btn btn-sm btn-primary p-1"
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
    let daftarBahan = [];

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
            totalOrder += item.total;

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_bahan}</td>
                    <td>${item.satuan}</td>
                    <td>${item.jumlah_beli}</td>
                    <td class="text-end">
    <div class="d-flex justify-content-end align-items-center">
        <span class="me-1">Rp</span>
        <span>${new Intl.NumberFormat('id-ID').format(item.harga_beli)}</span>
    </div>
</td>
<td class="text-end">
    <div class="d-flex justify-content-end align-items-center">
        <span class="me-1">Rp</span>
        <span>${new Intl.NumberFormat('id-ID').format(item.total)}</span>
    </div>
</td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(${index})">X</button></td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('total_order').value = totalOrder;
        document.getElementById('detail_json').value = JSON.stringify(daftarBahan);
    }

    // Data bahan kurang dari controller (jika ada)
    let bahanKurangList = [];
    @if(isset($bahanKurang) && count($bahanKurang))
        bahanKurangList = @json($bahanKurang);
    @endif

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

    // Fungsi untuk mengisi input bahan dari modal
    function isiInputBahan(kode, nama, satuan, jumlah) {
        const bahanSelect = document.getElementById('kode_bahan');
        for (let i = 0; i < bahanSelect.options.length; i++) {
            if (bahanSelect.options[i].value == kode) {
                bahanSelect.selectedIndex = i;
                break;
            }
        }
        document.getElementById('jumlah_beli').value = jumlah;
    }
    // Data stok minimal dari controller
let stokMinList = [];
@if(isset($stokMinList) && count($stokMinList))
    stokMinList = @json($stokMinList);
@endif

document.addEventListener('DOMContentLoaded', function() {
    const listStokMin = document.getElementById('listStokMin');
    if (listStokMin && stokMinList.length) {
        listStokMin.innerHTML = '';
        stokMinList.forEach((item, idx) => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.style.cursor = 'pointer';
            li.innerHTML = `
                <span>
                    <strong>${item.nama_bahan}</strong> (${item.satuan})<br>
                    <small>Stok Saat Ini: ${item.stok}</small>
                </span>
                <button class="btn btn-sm btn-primary" onclick="isiInputBahan('${item.kode_bahan}', '${item.nama_bahan}', '${item.satuan}', 1)" data-bs-dismiss="modal">Pilih</button>
            `;
            listStokMin.appendChild(li);
        });
    } else if(listStokMin) {
        listStokMin.innerHTML = '<li class="list-group-item text-center text-muted">Tidak ada bahan di bawah stok minimal</li>';
    }
});


</script>
@endsection