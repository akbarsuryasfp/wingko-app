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
                    @foreach ($supplier as $sup)
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
        
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalKekuranganLangsung">
        Kekurangan Bahan
    </button>
    <button type="button" class="btn btn-warning ms-2" data-bs-toggle="modal" data-bs-target="#modalPrediksiLangsung">
        Kebutuhan Produksi
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
        <div class="row mb-2 align-items-center" id="row_jatuh_tempo" style="display: {{ ($pembelian->hutang ?? 0) > 0 ? 'flex' : 'none' }};">
    <label class="col-sm-4 col-form-label">Jatuh Tempo</label>
    <div class="col-sm-8">
        <input type="date" class="form-control" name="jatuh_tempo" id="jatuh_tempo"
            value="{{ old('jatuh_tempo', $pembelian->jatuh_tempo ?? '') }}">
    </div>
</div>

        <input type="hidden" name="status" id="status" value="Proses">

        <div class="d-flex justify-content-between">
            <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>

<!-- Modal Kekurangan Bahan -->
<div class="modal fade" id="modalKekuranganLangsung" tabindex="-1" aria-labelledby="modalKekuranganLangsungLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalKekuranganLangsungLabel">Daftar Bahan Kurang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="list-group" id="listKekuranganLangsung">
          <!-- Akan diisi via JS -->
          @foreach($bahanKurangLangsung as $item)
          <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>
                  <strong>{{ $item->nama_bahan }}</strong> ({{ $item->satuan }})<br>
                  <small>Kurang: {{ $item->jumlah_beli }}</small>
              </span>
              <button class="btn btn-sm btn-primary" onclick="isiInputBahan('{{ $item->kode_bahan }}', '{{ $item->nama_bahan }}', '{{ $item->satuan }}', {{ $item->jumlah_beli }})" data-bs-dismiss="modal">Pilih</button>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Modal Prediksi Kebutuhan Harian -->
<div class="modal fade" id="modalPrediksiLangsung" tabindex="-1" aria-labelledby="modalPrediksiLangsungLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPrediksiLangsungLabel">Prediksi Kebutuhan Bahan (Harian)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="list-group" id="listPrediksiLangsung">
          <!-- Akan diisi via JS -->
        </ul>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let bahanOptions = @json($bahan);
let bahanKurangLangsung = [];
let bahansPrediksiHarian = [];
@if(isset($bahanKurangLangsung))
    bahanKurangLangsung = @json($bahanKurangLangsung);
@endif
@if(isset($bahansPrediksiHarian))
    bahansPrediksiHarian = @json($bahansPrediksiHarian);
@endif

var $jq = jQuery.noConflict();

$jq('#tambah_bahan').click(function () {
    let selectHtml = '<select name="bahan[]" class="form-control">';
    bahanOptions.forEach(b => {
        selectHtml += `<option value="${b.kode_bahan}">${b.nama_bahan} (${b.satuan})</option>`;
    });
    selectHtml += '</select>';

    $jq('#bahan_table tbody').append(`
        <tr>
            <td>${selectHtml}</td>
            <td><input type="number" name="jumlah[]" class="form-control jumlah" value="1"></td>
            <td><input type="number" name="harga[]" class="form-control harga" value="0"></td>
            <td><input type="date" name="tanggal_exp[]" class="form-control"></td>
            <td class="subtotal">0</td>
            <td><button type="button" class="btn btn-danger btn-sm remove">X</button></td>
        </tr>
    `);
});

$jq(document).on('input', '.jumlah, .harga', function () {
    updateSubtotal();
});

$jq(document).on('click', '.remove', function () {
    $jq(this).closest('tr').remove();
    updateSubtotal();
});

function updateSubtotal() {
    let total = 0;
    $jq('#bahan_table tbody tr').each(function () {
        let jumlah = parseFloat($jq(this).find('.jumlah').val()) || 0;
        let harga = parseFloat($jq(this).find('.harga').val()) || 0;
        let subtotal = jumlah * harga;
        $jq(this).find('.subtotal').text(subtotal);
        total += subtotal;
    });
    $jq('#total_harga').val(total);
    hitungTotal();
}

function hitungTotal() {
    let totalHarga = parseFloat($jq('#total_harga').val()) || 0;
    let diskon = parseFloat($jq('#diskon').val()) || 0;
    let ongkir = parseFloat($jq('#ongkir').val()) || 0;
    let totalPembelian = totalHarga - diskon + ongkir;
    let totalBayar = parseFloat($jq('#total_bayar').val()) || 0;
    let hutang = totalPembelian - totalBayar;

    $jq('#total_pembelian').val(totalPembelian);
    $jq('#hutang').val(hutang);

    toggleJatuhTempo();
}

function toggleJatuhTempo() {
    var kurangBayar = parseFloat(document.getElementById('hutang').value) || 0;
    var row = document.getElementById('row_jatuh_tempo');
    row.style.display = kurangBayar > 0 ? 'flex' : 'none';
}
document.getElementById('total_bayar').addEventListener('input', hitungTotal);
document.getElementById('diskon').addEventListener('input', hitungTotal);
document.getElementById('ongkir').addEventListener('input', hitungTotal);

document.addEventListener('DOMContentLoaded', function() {
    updateSubtotal();
    toggleJatuhTempo();

    // Kekurangan Bahan
    const listKekuranganLangsung = document.getElementById('listKekuranganLangsung');
    if (listKekuranganLangsung && bahanKurangLangsung.length) {
        listKekuranganLangsung.innerHTML = '';
        bahanKurangLangsung.forEach((item, idx) => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `
                <span>
                    <strong>${item.nama_bahan}</strong> (${item.satuan})<br>
                    <small>Kurang: ${item.jumlah_beli}</small>
                </span>
                <button class="btn btn-sm btn-primary" onclick="isiInputBahan('${item.kode_bahan}', '${item.nama_bahan}', '${item.satuan}', ${item.jumlah_beli})" data-bs-dismiss="modal">Pilih</button>
            `;
            listKekuranganLangsung.appendChild(li);
        });
    }

    // Prediksi Kebutuhan Harian
    const listPrediksiLangsung = document.getElementById('listPrediksiLangsung');
    if (listPrediksiLangsung && bahansPrediksiHarian.length) {
        listPrediksiLangsung.innerHTML = '';
        bahansPrediksiHarian.forEach((item, idx) => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `
                <span>
                    <strong>${item.nama_bahan}</strong> (${item.satuan})<br>
                    <small>Jumlah/Order: ${item.jumlah_per_order ?? '-'}</small>
                </span>
                <button class="btn btn-sm btn-primary" onclick="isiInputBahan('${item.kode_bahan}', '${item.nama_bahan}', '${item.satuan}', ${item.jumlah_per_order ?? 1})" data-bs-dismiss="modal">Pilih</button>
            `;
            listPrediksiLangsung.appendChild(li);
        });
    }
});

function isiInputBahan(kode, nama, satuan, jumlah) {
    // Cek apakah bahan sudah ada di tabel
    if (document.getElementById('row-' + kode)) return;

    // Cari tbody dari tabel utama
    const tbody = document.querySelector('#bahan_table tbody');
    // Buat select bahan (readonly)
    let selectHtml = `<select name="bahan[]" class="form-control" readonly>
        <option value="${kode}">${nama} (${satuan})</option>
    </select>`;

    // Buat baris baru
    const row = document.createElement('tr');
    row.id = 'row-' + kode;
    row.innerHTML = `
        <td>${selectHtml}</td>
        <td><input type="number" name="jumlah[]" class="form-control jumlah" value="${jumlah}" min="0" step="0.01"></td>
        <td><input type="number" name="harga[]" class="form-control harga" value="0"></td>
        <td><input type="date" name="tanggal_exp[]" class="form-control"></td>
        <td class="subtotal">0</td>
        <td><button type="button" class="btn btn-danger btn-sm remove" onclick="hapusBahan('${kode}')">X</button></td>
    `;
    tbody.appendChild(row);

    updateSubtotal();
}

function hapusBahan(kode) {
    const row = document.getElementById('row-' + kode);
    if (row) row.remove();
    updateSubtotal();
}
</script>
@endsection
