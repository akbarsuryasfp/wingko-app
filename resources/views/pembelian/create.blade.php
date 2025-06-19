@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Input Pembelian Bahan</h3>
    <form action="{{ route('pembelian.store') }}" method="POST">
    @csrf

        <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-md-6">
                <div class="row mb-3 align-items-center">
                    <label for="kode_pembelian" class="col-sm-4 col-form-label">Kode Pembelian</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="kode_pembelian" value="{{ $kode_pembelian }}" readonly required>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label for="tanggal_pembelian" class="col-sm-4 col-form-label">Tanggal Pembelian</label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control" id="tanggal_pembelian" name="tanggal_pembelian" required>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label for="metode_bayar" class="col-sm-4 col-form-label">Jenis Pembayaran</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="metode_bayar" name="metode_bayar" required>
                            <option value="">---Pilih Pembayaran---</option>
                            <option value="Tunai">-</option>
                            <option value="Tunai">Tunai</option>
                            <option value="Hutang">Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label for="no_nota" class="col-sm-4 col-form-label">No Nota</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="no_nota">
                    </div>
                </div>
            </div>
            <!-- Kolom Kanan -->
            <div class="col-md-6">
                <div class="row mb-3 align-items-center">
                    <label for="no_terima_bahan" class="col-sm-4 col-form-label">Kode Terima Bahan</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="no_terima_bahan" name="no_terima_bahan" required>
                            <option value="">---Pilih Kode Terima Bahan---</option>
                            @foreach($terimabahan as $terima)
                                <option value="{{ $terima->no_terima_bahan }}"
                                    {{ request('terima') == $terima->no_terima_bahan ? 'selected' : '' }}>
                                    {{ $terima->no_terima_bahan }} - {{ $terima->nama_supplier }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label for="tanggal_terima" class="col-sm-4 col-form-label">Tanggal Terima</label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control" id="tanggal_terima" name="tanggal_terima" readonly>
                    </div>
                </div>

                <input type="hidden" name="kode_supplier" id="kode_supplier">

                <div class="row mb-3 align-items-center">
                    <label for="nama_supplier" class="col-sm-4 col-form-label">Nama Supplier</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="nama_supplier" name="nama_supplier" readonly>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-4">Daftar Pembelian Bahan</h5>
        <div id="detail_bahan"></div>

        <!-- Total Harga -->
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Total Harga</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="total_harga" name="total_harga" readonly>
            </div>
        </div>

        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Diskon</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="diskon" name="diskon" value="0" oninput="hitungTotal()">
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Ongkos Kirim</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="ongkir" name="ongkir" value="0" oninput="hitungTotal()">
            </div>
        </div>

        <div class="row mb-3 align-items-center">
            <label for="total_pembelian" class="col-sm-4 col-form-label">Total Pembelian</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="total_pembelian" name="total_pembelian" readonly>
            </div>
        </div>

         <div class="row mb-3 align-items-center">
            <label for="uang_muka" class="col-sm-4 col-form-label">Uang Muka</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="uang_muka" name="uang_muka" value="0" readonly>
            </div>
        </div>

        <div class="row mb-3 align-items-center">
            <label for="total_bayar" class="col-sm-4 col-form-label">Total Bayar</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="total_bayar" name="total_bayar" value="0" oninput="hitungTotal()">
            </div>
        </div>
        <div class="row mb-4 align-items-center">
            <label class="col-sm-4 col-form-label">Kurang Bayar</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="hutang" name="hutang" readonly>
            </div>
        </div>

        <input type="hidden" name="status" id="status">

        <div class="d-flex justify-content-between">
            <div>
                <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning">Reset</button>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>

<style>
    .row.mb-3, .row.mb-2, .row.mb-4 {
        margin-bottom: 0.5rem !important;
    }
    .form-label, .form-control {
        margin-bottom: 2px !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var $jq = jQuery.noConflict();
    $jq('#no_terima_bahan').change(function () {
        var no_terima = $jq(this).val();
        if (no_terima) {
            $jq.ajax({
                url: '/pembelian/detail-terima-bahan/' + no_terima,
                method: 'GET',
                success: function (response) {
                    $jq('#kode_supplier').val(response.terimaBahan.kode_supplier);
                    $jq('#nama_supplier').val(response.terimaBahan.nama_supplier);
                    $jq('#tanggal_terima').val(response.terimaBahan.tanggal_terima);

                    // Ambil uang muka dari response (jika ada)
// Di dalam success: function(response) { ... }
let totalHarga = 0; // deklarasi sekali di sini
response.details.forEach(function (item) {
    totalHarga += item.bahan_masuk * item.harga_beli;
});
let uangMuka = response.terimaBahan.sisa_uang_muka || 0;
let totalPembelian = totalHarga - (parseFloat($jq('#diskon').val()) || 0) + (parseFloat($jq('#ongkir').val()) || 0);
if (uangMuka > totalPembelian) uangMuka = totalPembelian;
$jq('#uang_muka').val(uangMuka);

                    let html = '<table class="table table-bordered"><thead><tr><th>Nama Bahan</th><th>Satuan</th><th>Jumlah</th><th>Harga</th><th>Subtotal</th></tr></thead><tbody>';
                    totalHarga = 0;

                    response.details.forEach(function (item) {
                        let subtotal = item.bahan_masuk * item.harga_beli;
                        totalHarga += subtotal;

                        html += '<tr>' +
                            '<td>' + item.nama_bahan +
                                '<input type="hidden" name="kode_bahan[]" value="' + item.kode_bahan + '">' +
                            '</td>' +
                            '<td>' + item.satuan + '</td>' +
                            '<td>' + item.bahan_masuk +
                                '<input type="hidden" name="jumlah[]" value="' + item.bahan_masuk + '">' +
                            '</td>' +
                            '<td>' + item.harga_beli +
                                '<input type="hidden" name="harga_beli[]" value="' + item.harga_beli + '">' +
                            '</td>' +
                            '<td>' + subtotal +
                                '<input type="hidden" name="subtotal[]" value="' + subtotal + '">' +
                            '</td>' +
                            '</tr>';
                    });

                    html += '</tbody></table>';
                    $jq('#detail_bahan').html(html);

                    // Isi total harga
                    $jq('#total_harga').val(totalHarga);

                    // Hitung total pembelian
                    hitungTotal();
                },
                error: function(xhr) {
                    alert('AJAX error: ' + xhr.status + ' ' + xhr.statusText);
                    console.log(xhr.responseText);
                }
            });
        } else {
            $jq('#kode_supplier').val('');
            $jq('#nama_supplier').val('');
            $jq('#tanggal_terima').val('');
            $jq('#detail_bahan').html('');
            $jq('#total_harga').val(0);
            hitungTotal();
        }
    });

    // Fungsi untuk menghitung total pembelian dan kurang bayar
    function hitungTotal() {
    let totalHarga = parseFloat($jq('#total_harga').val()) || 0;
    let diskon = parseFloat($jq('#diskon').val()) || 0;
    let ongkir = parseFloat($jq('#ongkir').val()) || 0;
    let totalPembelian = totalHarga - diskon + ongkir;
    if (totalPembelian < 0) totalPembelian = 0;

    let uangMuka = parseFloat($jq('#uang_muka').val()) || 0;
    if (uangMuka > totalPembelian) uangMuka = totalPembelian;
    $jq('#uang_muka').val(uangMuka);

    let totalBayar = parseFloat($jq('#total_bayar').val()) || 0;
    let kurangBayar = totalPembelian - uangMuka - totalBayar;
    if (kurangBayar < 0) kurangBayar = 0;

    $jq('#total_pembelian').val(totalPembelian);
    $jq('#hutang').val(kurangBayar);
}

    // Trigger AJAX jika ada value awal pada select (misal dari parameter ?terima=...)
    $jq(document).ready(function () {
        var awal = $jq('#no_terima_bahan').val();
        if (awal) {
            $jq('#no_terima_bahan').trigger('change');
        }
    });
</script>
@endsection

