@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Pembelian Langsung</h3>
    <form action="{{ route('pembelian.storeLangsung') }}" method="POST">
        @csrf
<div class="row">
    <div class="col-md-6">
        <div class="form-group row mb-3">
            <label class="col-sm-4 col-form-label">Kode Pembelian</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" name="kode_pembelian" value="{{ $kode_pembelian }}" readonly>
            </div>
        </div>

        <div class="form-group row mb-3">
            <label class="col-sm-4 col-form-label">Tanggal Pembelian</label>
            <div class="col-sm-8">
                <input type="date" class="form-control" name="tanggal_pembelian" 
                       value="{{ date('Y-m-d') }}" required>
            </div>
        </div>

<div class="form-group row mb-3">
    <label class="col-sm-4 col-form-label">Supplier</label>
    <div class="col-sm-8">
        <select class="form-control" name="kode_supplier" required>
            <option value="">--- Pilih Supplier ---</option>
            @foreach ($suppliers as $sup)
                <option value="{{ $sup->kode_supplier }}">{{ $sup->nama_supplier }}</option>
            @endforeach
        </select>
    </div>
</div>
    </div>

    <div class="col-md-6">
        <div class="form-group row mb-3">
            <label class="col-sm-4 col-form-label">Jenis Pembayaran</label>
            <div class="col-sm-8">
                <select class="form-control" id="metode_bayar" name="metode_bayar" required>
                    <option value="">---Pilih Pembayaran---</option>
                    <option value="Tunai">Tunai</option>
                    <option value="Hutang">Transfer</option>
                </select>
            </div>
        </div>

        <div class="form-group row mb-3">
            <label class="col-sm-4 col-form-label">No Nota</label>
            <div class="col-sm-8">
                <input type="text" class="form-control" name="no_nota">
            </div>
        </div>
    </div>
</div>

        <h5 class="mt-4">Input Bahan</h5>
        <table class="table table-bordered" id="bahan_table">
            <thead>
                <tr>
                    <th>Nama Bahan</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Tanggal Expired</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        <div class="mb-3">
    <button type="button" class="btn btn-sm btn-success" id="tambah_bahan">Tambah Bahan</button>

    <button type="button" class="btn btn-warning ms-2" data-bs-toggle="modal" data-bs-target="#modalGabungan">
  Kebutuhan Bahan
</button>
</div>

        <div class="row mb-2">
            <label class="col-sm-4 col-form-label">Total Harga</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="total_harga" name="total_harga" readonly>
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-sm-4 col-form-label">Diskon</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="diskon" name="diskon" value="0" oninput="hitungTotal()">
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-sm-4 col-form-label">Ongkir</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="ongkir" name="ongkir" value="0" oninput="hitungTotal()">
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-sm-4 col-form-label">Total Pembelian</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="total_pembelian" name="total_pembelian" readonly>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-4 col-form-label">Total Bayar</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="total_bayar" name="total_bayar" value="0" oninput="hitungTotal()">
            </div>
        </div>
        <div class="row mb-4">
            <label class="col-sm-4 col-form-label">Kurang Bayar</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="hutang" name="hutang" readonly>
            </div>
        </div>
        <div class="row mb-2 align-items-center" id="row_jatuh_tempo" style="display: none;">
            <label class="col-sm-4 col-form-label">Jatuh Tempo</label>
            <div class="col-sm-8">
                <input type="date" class="form-control" name="jatuh_tempo" id="jatuh_tempo">
            </div>
        </div>

        <input type="hidden" name="status" id="status" value="Proses">

        <div class="d-flex justify-content-between">
            <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
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
            <td>${selectHtml}</td>
            <td><input type="number" name="jumlah[]" class="form-control jumlah" value="1" min="0" step="0.01"></td>
            <td><input type="number" name="harga[]" class="form-control harga" value="0" min="0"></td>
            <td><input type="date" name="tanggal_exp[]" class="form-control"></td>
            <td class="subtotal">0</td>
            <td><button type="button" class="btn btn-danger btn-sm remove">X</button></td>
        </tr>
    `);
});

// Fungsi untuk menambah bahan ke tabel dari modal
function tambahBahanKeTabel(kode, nama, satuan, jumlah) {
    // Cek apakah bahan sudah ada di tabel
    if ($jq(`#bahan_table tbody tr[data-kode="${kode}"]`).length > 0) {
        alert('Bahan sudah ada dalam daftar pembelian');
        return;
    }

    // Buat select bahan (readonly)
    let selectHtml = `<select name="bahan[]" class="form-control" readonly>
        <option value="${kode}">${nama} (${satuan})</option>
    </select>`;

    // Tambahkan baris baru ke tabel
    $jq('#bahan_table tbody').append(`
        <tr data-kode="${kode}">
            <td>${selectHtml}</td>
            <td><input type="number" name="jumlah[]" class="form-control jumlah" value="${jumlah}" min="0" step="0.01"></td>
            <td><input type="number" name="harga[]" class="form-control harga" value="0" min="0"></td>
            <td><input type="date" name="tanggal_exp[]" class="form-control"></td>
            <td class="subtotal">0</td>
            <td><button type="button" class="btn btn-danger btn-sm remove">X</button></td>
        </tr>
    `);

    updateSubtotal();
}

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
        $jq(this).find('.subtotal').text(subtotal.toFixed(2));
        total += subtotal;
    });
    $jq('#total_harga').val(total.toFixed(2));
    hitungTotal();
}

// Fungsi untuk menghitung total pembelian
function hitungTotal() {
    let totalHarga = parseFloat($jq('#total_harga').val()) || 0;
    let diskon = parseFloat($jq('#diskon').val()) || 0;
    let ongkir = parseFloat($jq('#ongkir').val()) || 0;
    let totalPembelian = totalHarga - diskon + ongkir;
    let totalBayar = parseFloat($jq('#total_bayar').val()) || 0;
    let hutang = totalPembelian - totalBayar;

    $jq('#total_pembelian').val(totalPembelian.toFixed(2));
    $jq('#hutang').val(hutang > 0 ? hutang.toFixed(2) : 0);

    // Toggle tampilan jatuh tempo
    $jq('#row_jatuh_tempo').toggle(hutang > 0);
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