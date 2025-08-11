@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow mb-4">
        <div class="card-header text-center border-0">
            <h5 class="m-0 font-weight-bold">Input Pembelian Langsung</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('pembelian.storeLangsung') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 mb-4 align-items-stretch">
                    <!-- Sisi Kiri -->
                    <div class="col-lg-6">
                        <div class="card h-100 border-light shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-3">
                                    <i class="fas fa-clipboard-list me-0"></i>Informasi Pembelian
                                </h5>
                                <div class="flex-grow-1">
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-medium">Kode Pembelian</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control bg-light" name="kode_pembelian" value="{{ $kode_pembelian }}" readonly>
                                        </div>
                                    </div>
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-medium">Tanggal Pembelian</label>
                                        <div class="col-sm-8">
                                            <input type="date" class="form-control" name="tanggal_pembelian" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-medium">Supplier</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" name="kode_supplier" required>
                                                <option value="">--- Pilih Supplier ---</option>
                                                @foreach ($suppliers as $sup)
                                                    <option value="{{ $sup->kode_supplier }}">{{ $sup->nama_supplier }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-medium">Jenis Pembayaran</label>
                                        <div class="col-sm-8">
                                            <select class="form-control" id="metode_bayar" name="metode_bayar" required>
                                                <option value="">---Pilih Pembayaran---</option>
                                                <option value="Tunai">Tunai</option>
                                                <option value="Transfer">Transfer</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-medium">No Nota</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control" name="no_nota">
                                        </div>
                                    </div>
                                    <div class="mb-2 row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-medium">Upload Bukti Nota</label>
                                        <div class="col-sm-8">
                                            <input type="file" class="form-control" name="bukti_nota" accept="image/*,application/pdf">
                                            <small class="text-muted">File harus kurang dari 2MB (jpg, png, pdf).</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Sisi Kanan -->
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
                                                @foreach($bahan as $b)
                                                    <option value="{{ $b->kode_bahan }}" data-satuan="{{ $b->satuan }}">
                                                        {{ $b->nama_bahan }} ({{ $b->satuan }})
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
        <i class="fas fa-plus-circle me-1"></i> Tambah Bahan
    </button>
    <button type="button" class="btn btn-warning w-100 py-2 mt-4" data-bs-toggle="modal" data-bs-target="#modalGabungan">
        Kebutuhan Bahan
    </button>
</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daftar Pembelian Bahan -->
                <div class="card border-light mt-4">
                    <div class="card-body">
                        <h5 class="card-title text-center mb-3">Daftar Pembelian Bahan</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="bahan_table">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th class="text-center">Nama Bahan</th>
                                        <th width="10%" class="text-center">Satuan</th>
                                        <th width="15%" class="text-center">Jumlah Beli</th>
                                        <th width="15%" class="text-center">Harga/Satuan</th>
                                        <th width="15%" class="text-center">Total</th>
                                        <th width="5%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                <div class="mb-3">
                    
                </div>

              
                    <div class="card-body">
                        <div class="row mb-2">
                            <label class="col-sm-4 col-form-label">Total Harga</label>
                            <div class="col-sm-8">
                                <div class="input-group">
    <span class="input-group-text">Rp</span>
    <input type="number" class="form-control bg-light" id="total_harga" name="total_harga" readonly>
</div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 col-form-label">Diskon</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="diskon" name="diskon" value="0" oninput="hitungTotal()">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 col-form-label">Ongkir</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="ongkir" name="ongkir" value="0" oninput="hitungTotal()">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <label class="col-sm-4 col-form-label">Total Pembelian</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control bg-light" id="total_pembelian" name="total_pembelian" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-4 col-form-label">Total Bayar</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="total_bayar" name="total_bayar" value="0" oninput="hitungTotal()">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <label class="col-sm-4 col-form-label">Kurang Bayar</label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control bg-light" id="hutang" name="hutang" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-2 align-items-center" id="row_jatuh_tempo" style="display: none;">
                            <label class="col-sm-4 col-form-label">Jatuh Tempo</label>
                            <div class="col-sm-8">
                                <input type="date" class="form-control" name="jatuh_tempo" id="jatuh_tempo">
                            </div>
                        </div>
                    </div>
             

                <input type="hidden" name="status" id="status" value="Proses">

                <div class="d-flex justify-content-between">
                  <div>
                    <a href="{{ route('pembelian.index') }}" class="btn btn-secondary"> ← Kembali</a>
                    <button type="reset" class="btn btn-warning">Reset</button>
</div>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Gabungan -->
<div class="modal fade" id="modalGabungan" tabindex="-1" aria-labelledby="modalGabunganLabel" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalGabunganLabel">Kebutuhan Bahan Produksi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-3" id="tabKebutuhan" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="kekurangan-tab" data-bs-toggle="tab" data-bs-target="#kekurangan" type="button" role="tab">
              Kekurangan Produksi
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="prediksi-tab" data-bs-toggle="tab" data-bs-target="#prediksi" type="button" role="tab">
              Prediksi Kebutuhan (Harian)
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="stok-tab" data-bs-toggle="tab" data-bs-target="#stok" type="button" role="tab">
              Stok Minimal
            </button>
          </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="tabKebutuhanContent">

          <!-- Tab 1: Kekurangan Bahan -->
          <div class="tab-pane fade show active" id="kekurangan" role="tabpanel" aria-labelledby="kekurangan-tab">
            @if(count($bahanKurangProduksi) > 0)
            <ul class="list-group" id="listKekuranganLangsung">
              @foreach($bahanKurangProduksi as $item)
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                  <strong>{{ $item['nama_bahan'] }}</strong> ({{ $item['satuan'] }})<br>
                  <small>Kurang: {{ $item['jumlah_beli'] }}</small>
                </span>
                <button class="btn btn-sm btn-primary" onclick="tambahBahanKeTabel('{{ $item['kode_bahan'] }}', '{{ $item['nama_bahan'] }}', '{{ $item['satuan'] }}', {{ $item['jumlah_beli'] }})" data-bs-dismiss="modal">
                  Pilih
                </button>
              </li>
              @endforeach
            </ul>
            @else
            <div class="alert alert-info">Tidak ada bahan yang kurang untuk produksi saat ini</div>
            @endif
          </div>

          <!-- Tab 2: Prediksi Kebutuhan Harian -->
          <div class="tab-pane fade" id="prediksi" role="tabpanel" aria-labelledby="prediksi-tab">
            @if(count($bahansPrediksiHarian) > 0)
            <table class="table table-bordered table-striped">
              <thead class="table-light text-center align-middle">
                <tr>
                <th style="width: 5%;">No</th>
                  <th style="width: 35%;">Nama Bahan</th>
                  <th style="width: 25%;">Jumlah Pembelian</th>
                  <th style="width: 15%;">Satuan</th>
                  <th style="width: 5%;">Aksi</th>
                </tr>
              </thead>
              <tbody id="listPrediksiLangsung">
                @foreach($bahansPrediksiHarian as $index => $item)
                <tr>
                  <td>{{ $index + 1 }}</td>
                  <td>{{ $item['nama_bahan'] }}</td>
                  <td>{{ $item['jumlah_per_order'] }}</td>
                  <td>{{ $item['satuan'] }}</td>
                  <td>
                    <button class="btn btn-sm btn-primary" onclick="tambahBahanKeTabel('{{ $item['kode_bahan'] }}', '{{ $item['nama_bahan'] }}', '{{ $item['satuan'] }}', {{ $item['jumlah_per_order'] }})" data-bs-dismiss="modal">
                      Pilih
                    </button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
            @else
            <div class="alert alert-info">Tidak ada prediksi kebutuhan harian</div>
            @endif
          </div>

          <!-- Tab 3: Stok Minimal -->
          <div class="tab-pane fade" id="stok" role="tabpanel" aria-labelledby="stok-tab">
            @if(count($stokMinList) > 0)
            <table class="table table-bordered table-sm align-middle">
              <thead class="text-center">
                <tr>
                  <th style="width: 5%;">No</th>
                  <th>Nama Bahan</th>
                  <th style="width: 15%;">Stok Minimal</th>
                  <th style="width: 15%;">Stok Saat Ini</th>
                  <th style="width: 15%;">Selisih</th>
                  <th style="width: 10%;">Satuan</th>
                  <th style="width: 5%;">Aksi</th>
                </tr>
              </thead>
              <tbody id="listStokMin" class="text-center">
                @foreach ($stokMinList as $i => $item)
                @php
                  $selisih = $item->stokmin - $item->stok;
                @endphp
                <tr>
                  <td>{{ $i + 1 }}</td>
                  <td class="text-start">{{ $item->nama_bahan }}</td>
                  <td>{{ $item->stokmin }}</td>
                  <td>{{ $item->stok }}</td>
                  <td>{{ $selisih }}</td>
                  <td>{{ $item->satuan }}</td>
                  <td>
                    @if ($selisih > 0)
                    <button class="btn btn-sm btn-primary p-1"
                      onclick="tambahBahanKeTabel('{{ $item->kode_bahan }}', '{{ $item->nama_bahan }}', '{{ $item->satuan }}', {{ $selisih }})"
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
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let bahanOptions = @json($bahan);
let bahanKurangProduksi = @json($bahanKurangProduksi ?? []);
let bahansPrediksiHarian = @json($bahansPrediksiHarian ?? []);
let stokMinList = @json($stokMinList ?? []);

var $jq = jQuery.noConflict();

// Tambah bahan baru ke tabel
$jq('#tambah_bahan').click(function () {
    let selectHtml = '<select name="bahan[]" class="form-control">';
    bahanOptions.forEach(b => {
        selectHtml += `<option value="${b.kode_bahan}">${b.nama_bahan} (${b.satuan})</option>`;
    });
    selectHtml += '</select>';

    $jq('#bahan_table tbody').append(`
        <tr>
        <td class="text-center align-middle"></td>
            <td>${selectHtml}</td>
            <td><input type="number" name="jumlah[]" class="form-control jumlah" value="1" min="0" step="0.01"></td>
            <td><input type="number" name="harga[]" class="form-control harga" value="0" min="0"></td>
            <td><input type="date" name="tanggal_exp[]" class="form-control"></td>
            <td class="subtotal">0</td>
            <td><button type="button" class="btn btn-danger btn-sm remove">X</button></td>
        </tr>
    `);
    updateNoUrut(); // <-- Tambahkan ini
});

// Fungsi untuk menambah bahan ke tabel dari modal
function tambahBahan() {
    var bahanSelect = document.getElementById('kode_bahan');
    var kode = bahanSelect.value;
    var nama = bahanSelect.options[bahanSelect.selectedIndex].text;
    var satuan = bahanSelect.options[bahanSelect.selectedIndex].getAttribute('data-satuan') || '';
    var jumlah = parseFloat(document.getElementById('jumlah_beli').value);
    var harga = parseFloat(document.getElementById('harga_beli').value);

    if (!kode || !jumlah || !harga) {
        alert("Silakan lengkapi data bahan, jumlah, dan harga.");
        return;
    }

    // Cek duplikasi
    if ($jq(`#bahan_table tbody tr[data-kode="${kode}"]`).length > 0) {
        alert('Bahan sudah ada dalam daftar pembelian');
        return;
    }

    var tbody = document.querySelector('#bahan_table tbody');
    var rowCount = tbody.rows.length + 1;
    var subtotal = jumlah * harga;

    var row = document.createElement('tr');
    row.setAttribute('data-kode', kode);
    row.innerHTML = `
        <td class="text-center">${rowCount}</td>
        <td>
            <input type="hidden" name="bahan[]" value="${kode}">
            ${nama}
        </td>
        <td>${satuan}</td>
        <td><input type="number" name="jumlah[]" class="form-control jumlah" value="${jumlah}" min="0" step="0.01"></td>
        <td><input type="number" name="harga[]" class="form-control harga" value="${harga}" min="0"></td>
        <td><input type="date" name="tanggal_exp[]" class="form-control"></td>
        <td class="subtotal">${Math.round(subtotal)}</td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBahan(this)">X</button></td>
    `;
    tbody.appendChild(row);

    // Reset input
    bahanSelect.selectedIndex = 0;
    document.getElementById('jumlah_beli').value = '';
    document.getElementById('harga_beli').value = '';

    updateNoUrut();
    updateTotalHarga();
}
function updateNoUrut() {
    $jq('#bahan_table tbody tr').each(function(i) {
        $jq(this).find('td:first').text(i + 1);
    });
}

function updateSubtotal(inputElement) {
    const row = $jq(inputElement).closest('tr');
    const jumlah = parseFloat(row.find('.jumlah').val()) || 0;
    const harga = parseFloat(row.find('.harga').val()) || 0;
    const subtotal = Math.round(jumlah * harga); // Round to integer
    
    row.find('.subtotal').text(subtotal); // No .toFixed(2)
    updateTotalHarga();
}

function updateTotalHarga() {
    let total = 0;
    $jq('#bahan_table tbody tr').each(function() {
        const subtotalText = $jq(this).find('.subtotal').text();
        total += parseInt(subtotalText) || 0; // Use parseInt instead of parseFloat
    });
    
    $jq('#total_harga').val(total); // No decimal places
    hitungTotal();
}

function hitungTotal() {
    const totalHarga = parseInt($jq('#total_harga').val()) || 0;
    const diskon = parseInt($jq('#diskon').val()) || 0;
    const ongkir = parseInt($jq('#ongkir').val()) || 0;
    const totalBayar = parseInt($jq('#total_bayar').val()) || 0;
    
    const totalPembelian = totalHarga - diskon + ongkir;
    const hutang = totalPembelian - totalBayar;
    
    $jq('#total_pembelian').val(totalPembelian);
    $jq('#hutang').val(hutang > 0 ? hutang : 0);
    $jq('#row_jatuh_tempo').toggle(hutang > 0);
    $jq('#status').val(hutang > 0 ? 'Hutang' : 'Lunas');
}

function hapusBahan(button) {
    $jq(button).closest('tr').remove();
    updateTotalHarga();
        updateNoUrut();
}



// Initialize event listeners when document is ready
$jq(document).ready(function() {
    // Event delegation for dynamically added elements
    $jq('#bahan_table').on('input', '.jumlah, .harga', function() {
        updateSubtotal(this);
    });
    
    $jq('#diskon, #ongkir, #total_bayar').on('input', hitungTotal);
});

// Update subtotal ketika jumlah atau harga diubah
$jq(document).on('input', '.jumlah, .harga', function () {
    updateSubtotal();
});

// Hapus baris bahan
$jq(document).on('click', '.remove', function () {
    $jq(this).closest('tr').remove();
    updateSubtotal();
});

// Fungsi untuk menghitung subtotal dan total
function updateSubtotal() {
    let total = 0;
    $jq('#bahan_table tbody tr').each(function () {
        let jumlah = parseFloat($jq(this).find('.jumlah').val()) || 0;
        let harga = parseFloat($jq(this).find('.harga').val()) || 0;
        let subtotal = jumlah * harga;
        $jq(this).find('.subtotal').text(Math.round(subtotal)); // tanpa .00
        total += subtotal;
    });
    $jq('#total_harga').val(Math.round(total)); // tanpa .00
    hitungTotal();
}

// Fungsi untuk menghitung total pembelian
function hitungTotal() {
    let totalHarga = Math.round(parseFloat($jq('#total_harga').val())) || 0;
    let diskon = Math.round(parseFloat($jq('#diskon').val())) || 0;
    let ongkir = Math.round(parseFloat($jq('#ongkir').val())) || 0;
    let totalBayar = Math.round(parseFloat($jq('#total_bayar').val())) || 0;
    
    let totalPembelian = totalHarga - diskon + ongkir;
    let hutang = totalPembelian - totalBayar;

    $jq('#total_pembelian').val(totalPembelian);
    $jq('#hutang').val(hutang > 0 ? hutang : 0);
    $jq('#row_jatuh_tempo').toggle(hutang > 0);
    $jq('#status').val(hutang > 0 ? 'Hutang' : 'Lunas');
}

// Inisialisasi saat dokumen siap
$jq(document).ready(function() {
    // Tambahkan event listener untuk input yang mempengaruhi total
    $jq('#total_bayar, #diskon, #ongkir').on('input', hitungTotal);
    
    // Inisialisasi pertama kali
    updateSubtotal();
});
</script>
@endsection
