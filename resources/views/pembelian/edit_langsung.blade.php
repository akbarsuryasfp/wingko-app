@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Edit Pembelian Langsung</h3>
    <form action="{{ route('pembelian.update', $pembelian->no_pembelian) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6">
                <label>Kode Pembelian</label>
                <input type="text" class="form-control mb-2" value="{{ $pembelian->no_pembelian }}" readonly>

                <label>Tanggal Pembelian</label>
                <input type="date" class="form-control mb-2" name="tanggal_pembelian" value="{{ $pembelian->tanggal_pembelian }}" required>

                <label>Supplier</label>
                <select class="form-control mb-2" name="kode_supplier" required>
                    <option value="">--- Pilih Supplier ---</option>
                    @foreach ($supplier as $sup)
                        <option value="{{ $sup->kode_supplier }}" {{ $pembelian->kode_supplier == $sup->kode_supplier ? 'selected' : '' }}>{{ $sup->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label>Metode Bayar</label>
                <select class="form-control mb-2" name="metode_bayar" required>
                    <option value="Tunai" {{ $pembelian->metode_bayar == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                    <option value="Hutang" {{ $pembelian->metode_bayar == 'Hutang' ? 'selected' : '' }}>Hutang</option>
                </select>

                <label>No Nota</label>
                <input type="text" class="form-control mb-2" name="no_nota" value="{{ $pembelian->no_nota }}">
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
                @foreach($details as $d)
                <tr>
                    <td>
                        <select name="bahan[]" class="form-control">
                            @foreach($bahan as $b)
                                <option value="{{ $b->kode_bahan }}" data-satuan="{{ $b->satuan }}"
                                    {{ $d->kode_bahan == $b->kode_bahan ? 'selected' : '' }}>
                                    {{ $b->nama_bahan }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="jumlah[]" class="form-control jumlah" value="{{ $d->bahan_masuk }}"></td>
                    <td><input type="number" name="harga[]" class="form-control harga" value="{{ $d->harga_beli }}"></td>
                    <td><input type="date" name="tanggal_exp[]" class="form-control" value="{{ $d->tanggal_exp }}"></td>
                    <td class="subtotal">{{ $d->bahan_masuk * $d->harga_beli }}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove">X</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mb-3">
            <button type="button" class="btn btn-sm btn-success" id="tambah_bahan">Tambah Bahan</button>
        </div>

        <div class="row mb-2">
            <label class="col-sm-4 col-form-label">Total Harga</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="total_harga" name="total_harga" value="{{ $pembelian->total_harga }}" readonly>
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-sm-4 col-form-label">Diskon</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="diskon" name="diskon" value="{{ $pembelian->diskon }}">
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-sm-4 col-form-label">Ongkir</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="ongkir" name="ongkir" value="{{ $pembelian->ongkir }}">
            </div>
        </div>
        <div class="row mb-2">
            <label class="col-sm-4 col-form-label">Total Pembelian</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="total_pembelian" name="total_pembelian" value="{{ $pembelian->total_pembelian }}" readonly>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-4 col-form-label">Total Bayar</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="total_bayar" name="total_bayar" value="{{ $pembelian->total_bayar }}">
            </div>
        </div>
        <div class="row mb-4">
            <label class="col-sm-4 col-form-label">Kurang Bayar</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="hutang" name="hutang" value="{{ $pembelian->hutang }}" readonly>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let bahanOptions = @json($bahan);

var $jq = jQuery.noConflict();

$jq('#tambah_bahan').click(function () {
    let selectHtml = '<select name="bahan[]" class="form-control">';
    bahanOptions.forEach(b => {
        selectHtml += `<option value="${b.kode_bahan}">${b.nama_bahan}</option>`;
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
    updateSubtotal();
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
}

// Inisialisasi subtotal awal
$jq(document).ready(function() {
    updateSubtotal();
    $jq('#diskon, #ongkir, #total_bayar').on('input', hitungTotal);
});
</script>
@endsection