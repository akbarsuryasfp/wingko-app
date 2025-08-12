@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold">Edit Pembelian Bahan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('pembelian.update', $pembelian->no_pembelian) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Kolom Kiri -->
                    <div class="col-md-6">
                        <div class="form-group row mb-2">
                            <label class="col-sm-4 col-form-label font-weight-bold">Kode Pembelian</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control bg-light" value="{{ $pembelian->no_pembelian }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <label class="col-sm-4 col-form-label font-weight-bold">Tanggal Pembelian</label>
                            <div class="col-sm-8">
                                <input type="date" class="form-control" name="tanggal_pembelian" value="{{ $pembelian->tanggal_pembelian }}" required>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <label class="col-sm-4 col-form-label font-weight-bold">Metode Bayar</label>
                            <div class="col-sm-8">
                                <select class="form-control" name="metode_bayar" id="metode_bayar" required>
                                    <option value="Tunai" {{ $pembelian->metode_bayar == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                                    <option value="Hutang" {{ $pembelian->metode_bayar == 'Hutang' ? 'selected' : '' }}>Hutang</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Kolom Kanan -->
                    <div class="col-md-6">
@if($pembelian->jenis_pembelian != 'pembelian langsung')
<div class="form-group row mb-2">
    <label class="col-sm-4 col-form-label">Kode Terima Bahan</label>
    <div class="col-sm-8">
        <input type="text" class="form-control" value="{{ $pembelian->no_terima_bahan }}" readonly>
    </div>
</div>
@endif
                        <div class="form-group row mb-2">
                            <label class="col-sm-4 col-form-label font-weight-bold">Nama Supplier</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control bg-light" value="{{ $nama_supplier }}" readonly>
                            </div>
                        </div>
                        <div class="form-group row mb-2">
                            <label class="col-sm-4 col-form-label font-weight-bold">No Nota</label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="no_nota" value="{{ $pembelian->no_nota }}">
                            </div>
                        </div>
                                <div class="form-group row mb-2">
            <label class="col-sm-4 col-form-label font-weight-bold">Bukti Nota</label>
<div class="col-sm-8">
    <div class="row align-items-center g-2 mb-1">
        @if($pembelian->bukti_nota)
            <div class="col-auto">
                <a href="{{ asset('storage/' . $pembelian->bukti_nota) }}" 
                   target="_blank" 
                   class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-file-alt me-1"></i> Lihat Nota Lama
                </a>
            </div>
        @endif
        <div class="col">
            <input type="file" class="form-control form-control-sm" name="bukti_nota" accept="image/*,application/pdf" placeholder="File maksimal 2MB">
        </div>
        <small class="text-muted">File harus kurang dari 2MB. Kosongkan jika tidak ingin mengubah bukti nota.</small>
    </div>
</div>
                    </div>
                </div>

        <h5 class="mt-4">Daftar Pembelian Bahan</h5>
        
        @if($pembelian->jenis_pembelian == 'pembelian langsung')
        <div class="mb-3">
            <button type="button" class="btn btn-sm btn-success" id="tambah_bahan">Tambah Bahan</button>
            <button type="button" class="btn btn-warning ms-2" data-bs-toggle="modal" data-bs-target="#modalGabungan">
                Kebutuhan Bahan
            </button>
        </div>
        @endif

        <table class="table table-bordered" id="bahan_table">
            <thead>
                <tr>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Tanggal Expired</th>
                    <th>Subtotal</th>
                    @if($pembelian->jenis_pembelian == 'pembelian langsung')
                    <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($details as $i => $detail)
                <tr data-kode="{{ $detail->kode_bahan }}">
                    <td>
                        @if($pembelian->jenis_pembelian == 'pembelian langsung')
                            <select name="bahan[]" class="form-control">
                                @foreach($bahan as $b)
                                <option value="{{ $b->kode_bahan }}" {{ $b->kode_bahan == $detail->kode_bahan ? 'selected' : '' }}>
                                    {{ $b->nama_bahan }} ({{ $b->satuan }})
                                </option>
                                @endforeach
                            </select>
                        @else
                            {{ $detail->nama_bahan }}
                        @endif
                    </td>
                    <td>{{ $detail->satuan }}</td>
                    <td>
                        @if($pembelian->jenis_pembelian == 'pembelian langsung')
                            <input type="number" class="form-control jumlah" name="jumlah[]" value="{{ $detail->bahan_masuk }}" min="1" required>
                        @else
                            {{ $detail->bahan_masuk }}
                        @endif
                    </td>
                    <td>
                        @if($pembelian->jenis_pembelian == 'pembelian langsung')
                            <input type="number" class="form-control harga" name="harga[]" value="{{ $detail->harga_beli }}" min="0" required>
                        @else
                            {{ $detail->harga_beli }}
                        @endif
                    </td>
                    <td>
                        @if($pembelian->jenis_pembelian == 'pembelian langsung')
                            <input type="date" class="form-control" name="tanggal_exp[]" value="{{ $detail->tanggal_exp }}">
                        @else
                            {{ $detail->tanggal_exp ?? '-' }}
                        @endif
                    </td>
                    <td class="subtotal">
                        {{ $detail->bahan_masuk * $detail->harga_beli }}
                    </td>
                    @if($pembelian->jenis_pembelian == 'pembelian langsung')
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove">X</button>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Total Harga</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="total_harga" name="total_harga" value="{{ $total_harga ?? 0 }}" readonly>
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Diskon</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="diskon" name="diskon" value="{{ $pembelian->diskon }}" oninput="hitungTotal()">
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Ongkos Kirim</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="ongkir" name="ongkir" value="{{ $pembelian->ongkir }}" oninput="hitungTotal()">
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Total Pembelian</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="total_pembelian" name="total_pembelian" value="{{ $total_pembelian ?? 0 }}" readonly>
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Uang Muka</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="uang_muka" name="uang_muka" value="{{ $pembelian->uang_muka }}" readonly>
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Total Bayar</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="total_bayar" name="total_bayar" value="{{ $pembelian->total_bayar }}" oninput="hitungTotal()">
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Kurang Bayar</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" id="hutang" name="hutang" value="{{ $kurang_bayar ?? 0 }}" readonly>
            </div>
        </div>
<div class="row mb-2 align-items-center" id="row_jatuh_tempo">
    <label class="col-sm-4 col-form-label">Jatuh Tempo</label>
    <div class="col-sm-8">
        <input type="date" class="form-control" name="jatuh_tempo"
            value="{{ old('jatuh_tempo', $jatuh_tempo ?? $pembelian->jatuh_tempo ?? '') }}">
    </div>
</div>


                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> ← Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($pembelian->jenis_pembelian == 'pembelian langsung')
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
                <ul class="nav nav-tabs mb-2" id="tabKebutuhan" role="tablist">
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
                                <button class="btn btn-sm btn-primary"
                                    onclick="tambahBahanKeTabel('{{ $item['kode_bahan'] }}', '{{ $item['nama_bahan'] }}', '{{ $item['satuan'] }}', {{ $item['jumlah_beli'] }})"
                                    data-bs-dismiss="modal">
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
                                    <td>{{ $item->nama_bahan }}</td>
                                    <td>{{ $item->jumlah_per_order }}</td>
                                    <td>{{ $item->satuan }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary"
                                            onclick="tambahBahanKeTabel('{{ $item->kode_bahan }}', '{{ $item->nama_bahan }}', '{{ $item->satuan }}', {{ $item->jumlah_per_order }})"
                                            data-bs-dismiss="modal">
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
@endif

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
var $j = jQuery.noConflict();
$j(document).ready(function() {
    // Data master bahan dari backend
    let bahanOptions = @json($bahan);

    // === EVENT: Tambah bahan baru ===
    $j('#tambah_bahan').click(function () {
        let selectHtml = '<select name="bahan[]" class="form-control">';
        bahanOptions.forEach(b => {
            selectHtml += `<option value="${b.kode_bahan}" data-harga="${b.harga_beli ?? 0}" data-satuan="${b.satuan}">${b.nama_bahan} (${b.satuan})</option>`;
        });
        selectHtml += '</select>';

        let hargaDefault = bahanOptions[0].harga_beli ?? 0;
        let satuanDefault = bahanOptions[0].satuan ?? '';

        $j('#bahan_table tbody').append(`
            <tr>
                <td>${selectHtml}</td>
                <td>
                    <input type="text" name="satuan[]" class="form-control satuan" value="${satuanDefault}" readonly>
                </td>
                <td><input type="number" name="jumlah[]" class="form-control jumlah" value="1" min="1" required></td>
                <td><input type="number" name="harga[]" class="form-control harga" value="${hargaDefault}" min="0" required></td>
                <td><input type="date" name="tanggal_exp[]" class="form-control"></td>
                <td class="subtotal">${hargaDefault * 1}</td>
                <td><button type="button" class="btn btn-danger btn-sm remove">X</button></td>
            </tr>
        `);
        updateSubtotal();
    });

    // === EVENT: Hapus baris bahan ===
    $j(document).on('click', '.remove', function () {
        $j(this).closest('tr').remove();
        updateSubtotal();
    });

    // === EVENT: Pilih bahan di baris baru, update harga & satuan ===
    $j(document).on('change', 'select[name="bahan[]"]', function() {
        let kodeBahan = $j(this).val();
        let selectedBahan = bahanOptions.find(b => b.kode_bahan == kodeBahan);
        let harga = selectedBahan ? selectedBahan.harga_beli : 0;
        let satuan = selectedBahan ? selectedBahan.satuan : '';
        $j(this).closest('tr').find('input.harga').val(harga);
        $j(this).closest('tr').find('input.satuan').val(satuan); // update satuan
        updateSubtotal();
    });

    // === EVENT: Ubah jumlah/harga, update subtotal & total ===
    $j(document).on('input', '.jumlah, .harga', function () {
        updateSubtotal();
    });

    // === EVENT: Ubah diskon/ongkir/total bayar, update total pembelian & hutang ===
    $j('#diskon, #ongkir, #total_bayar').on('input', function() {
        hitungTotal();
    });

    // === FUNGSI: Update subtotal per baris & total harga ===
    function updateSubtotal() {
        let total = 0;
        $j('#bahan_table tbody tr').each(function () {
            let jumlah = parseFloat($j(this).find('.jumlah').val()) || 0;
            let harga = parseFloat($j(this).find('.harga').val()) || 0;
            let subtotal = jumlah * harga;
            $j(this).find('.subtotal').text(subtotal);
            total += subtotal;
        });
        $j('#total_harga').val(total);
        hitungTotal();
    }

    // === FUNGSI: Hitung total pembelian & hutang ===
    function hitungTotal() {
        let totalHarga = parseFloat($j('#total_harga').val()) || 0;
        let diskon = parseFloat($j('#diskon').val()) || 0;
        let ongkir = parseFloat($j('#ongkir').val()) || 0;
        let totalPembelian = totalHarga - diskon + ongkir;
        let totalBayar = parseFloat($j('#total_bayar').val()) || 0;
        let hutang = totalPembelian - totalBayar;

        $j('#total_pembelian').val(totalPembelian);
        $j('#hutang').val(hutang > 0 ? hutang : 0);
    }

    // === INISIALISASI: Set subtotal per baris sesuai database (TIDAK update total harga/pembelian) ===
    $j('#bahan_table tbody tr').each(function () {
        let jumlah = parseFloat($j(this).find('.jumlah').val()) || 0;
        let harga = parseFloat($j(this).find('.harga').val()) || 0;
        let subtotal = jumlah * harga;
        $j(this).find('.subtotal').text(subtotal);
    });
    // Jangan panggil updateSubtotal() atau hitungTotal() di sini!
});
</script>

@endsection