@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3>EDIT PENJUALAN</h3>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('penjualan.update', $penjualan->no_jual) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-md-6">
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">No Jual</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" value="{{ $penjualan->no_jual }}" readonly style="pointer-events: none; background: #e9ecef;">
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Tanggal Jual</label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control" name="tanggal_jual" value="{{ $penjualan->tanggal_jual }}" required>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Nama Pelanggan</label>
                    <div class="col-sm-8">
                        <select class="form-control" name="kode_pelanggan" required disabled tabindex="-1">
                            <option value="">---Pilih Pelanggan---</option>
                            @foreach($pelanggan as $p)
                                <option value="{{ $p->kode_pelanggan }}" {{ $penjualan->kode_pelanggan == $p->kode_pelanggan ? 'selected' : '' }}>{{ $p->nama_pelanggan }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="kode_pelanggan" value="{{ $penjualan->kode_pelanggan }}">
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Metode Pembayaran</label>
                    <div class="col-sm-8">
                        <select class="form-control" name="metode_pembayaran" id="metode_pembayaran" required>
                            <option value="tunai" {{ $penjualan->metode_pembayaran == 'tunai' ? 'selected' : '' }}>Tunai</option>
                            <option value="non tunai" {{ $penjualan->metode_pembayaran == 'non tunai' ? 'selected' : '' }}>Non Tunai</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Keterangan</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="keterangan" value="{{ $penjualan->keterangan }}">
                    </div>
                </div>
            </div>
            <!-- Kolom Kanan -->
            <div class="col-md-6">
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Total Harga</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="total_harga" name="total_harga" value="{{ number_format($penjualan->total_harga,0,',','.') }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Diskon (Rp)</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="diskon" name="diskon" value="{{ number_format($penjualan->diskon,0,',','.') }}" oninput="hitungTotal()">
                        </div>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Total Jual</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="total_jual" name="total_jual" value="{{ number_format($penjualan->total_jual,0,',','.') }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Total Bayar</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="total_bayar" name="total_bayar" value="{{ number_format($penjualan->total_bayar,0,',','.') }}" oninput="hitungTotal()">
                        </div>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Kembalian</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="kembalian" name="kembalian" value="{{ number_format($penjualan->kembalian,0,',','.') }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Piutang</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="piutang" name="piutang" value="{{ number_format($penjualan->piutang,0,',','.') }}" readonly>
                        </div>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Tanggal Jatuh Tempo</label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" value="{{ isset($penjualan->tanggal_jatuh_tempo) ? $penjualan->tanggal_jatuh_tempo : '' }}">
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Status Pembayaran</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="status_pembayaran" id="status_pembayaran" value="{{ $penjualan->status_pembayaran }}" readonly tabindex="-1">
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-4 text-center">DAFTAR PRODUK TERJUAL</h5>
        <div id="detail_produk">
            <table class="table table-bordered text-center align-middle" id="daftar-produk">
                <thead>
                    <tr>
                        <th class="text-center align-middle">No</th>
                        <th class="text-center align-middle">Nama Produk</th>
                        <th class="text-center align-middle">Jumlah</th>
                        <th class="text-center align-middle">Harga/Satuan</th>
                        <th class="text-center align-middle">Diskon/Satuan</th>
                        <th class="text-center align-middle">Subtotal</th>
                        <th class="text-center align-middle">Total Jual</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalJual = 0; @endphp
                    @foreach($details as $i => $detail)
                    @php $totalJual += $detail['subtotal']; @endphp
                    <tr>
                        <td class="text-center align-middle">{{ $i+1 }}</td>
                        <td class="text-center align-middle">{{ $detail['nama_produk'] }}</td>
                        <td class="text-center align-middle">{{ $detail['jumlah'] }}</td>
                        <td class="text-center align-middle">{{ (int) $detail['harga_satuan'] }}</td>
                        <td class="text-center align-middle">{{ isset($detail['diskon_satuan']) ? (int)$detail['diskon_satuan'] : 0 }}</td>
                        <td class="text-center align-middle">{{ number_format($detail['subtotal'],0,',','.') }}</td>
                        <td class="text-center align-middle" rowspan="{{ count($details) }}" style="vertical-align: middle; font-weight: bold; background: #f8f9fa;">
                            @if($i == 0)
                                {{ number_format($totalJual,0,',','.') }}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <input type="hidden" name="detail_json" id="detail_json">

        <div class="d-flex justify-content-between mt-4">
            <div>
                <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">Back</a>
                <!-- Reset button dihapus -->
            </div>
            <button type="submit" class="btn btn-success">Update</button>
        </div>
        </form>
    </div>
    </div>
</div>

<style>
    .row.mb-3 { margin-bottom: 0.5rem !important; }
    .form-label, .form-control { margin-bottom: 2px !important; }
    #detail_produk th, #detail_produk td {
        text-align: center !important;
        vertical-align: middle !important;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
jQuery(function($) {
    // Format angka ke format rupiah (ribuan, tanpa Rp jika ingin, atau tambahkan jika perlu)
    function formatRupiah(angka) {
        if (isNaN(angka) || angka === null) return '0';
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Parse string format ribuan ke angka
    function parseAngka(str) {
        if (!str) return 0;
        return parseInt(str.toString().replace(/\D/g, '')) || 0;
    }

    let daftarProduk = @json($details);
    const defaultForm = {
        tanggal_jual: "{{ $penjualan->tanggal_jual }}",
        kode_pelanggan: "{{ $penjualan->kode_pelanggan }}",
        metode_pembayaran: "{{ $penjualan->metode_pembayaran }}",
        keterangan: "{{ $penjualan->keterangan }}",
        diskon: "{{ $penjualan->diskon }}",
        total_bayar: "{{ $penjualan->total_bayar }}"
    };
    const defaultProduk = JSON.parse(JSON.stringify(daftarProduk));

    function tambahProduk() {
        const kode_produk = $('#kode_produk').val();
        const nama_produk = $('#kode_produk option:selected').data('nama');
        const jumlah = parseInt($('#jumlah').val());
        const harga_satuan = parseInt($('#harga_satuan').val());

        if (!kode_produk || !jumlah || !harga_satuan || jumlah <= 0 || harga_satuan <= 0) {
            alert('Lengkapi data produk dengan benar!');
            return;
        }

        const subtotal = jumlah * harga_satuan;
        daftarProduk.push({ kode_produk, nama_produk, jumlah, harga_satuan, subtotal });
        updateTabel();
        $('#kode_produk').val('');
        $('#jumlah').val('');
        $('#harga_satuan').val('');
    }

    

    function updateTabel() {
        let html = `<table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width:40px; text-align:center;">No</th>
                    <th style="text-align:center;">Nama Produk</th>
                    <th style="text-align:center;">Satuan</th>
                    <th style="text-align:center;">Jumlah</th>
                    <th style="text-align:center;">Harga/Satuan</th>
                    <th style="text-align:center;">Diskon/Satuan</th>
                    <th style="text-align:center;">Subtotal</th>
                </tr>
            </thead>
            <tbody>`;
        let totalHarga = 0;
        daftarProduk.forEach((item, i) => {
            if (typeof item.diskon_satuan === 'undefined') item.diskon_satuan = 0;
            if (typeof item.satuan === 'undefined') item.satuan = '-';
            // Pastikan diskon_satuan tidak lebih besar dari harga_satuan
            let diskonSatuan = parseInt(item.diskon_satuan) || 0;
            let hargaSatuan = parseInt(item.harga_satuan) || 0;
            if (diskonSatuan > hargaSatuan) diskonSatuan = hargaSatuan;
            item.diskon_satuan = diskonSatuan;
            const subtotal = (item.jumlah * hargaSatuan) - (item.jumlah * diskonSatuan);
            item.subtotal = subtotal;
            html += `<tr>
                <td style="text-align:center;">${i+1}</td>
                <td style="text-align:center;">${item.nama_produk}</td>
                <td style="text-align:center;">${item.satuan}</td>
                <td><input type="number" class="form-control jumlah-edit" value="${item.jumlah}" data-index="${i}" min="1"></td>
                <td class="harga-satuan-edit">Rp${hargaSatuan.toLocaleString('id-ID')}</td>
                <td><input type="number" class="form-control diskon-edit" value="${diskonSatuan}" data-index="${i}" min="0" max="${hargaSatuan}"></td>
                <td class="subtotal-edit">Rp${subtotal.toLocaleString('id-ID')}</td>
            </tr>`;
            totalHarga += subtotal;
        });
        html += `</tbody></table>`;
        $('#detail_produk').html(html);
        // Update total harga dan total jual di form
        $('#total_harga').val(formatRupiah(totalHarga));
        // Diskon tambahan dari field diskon di luar produk
        let diskonTambahan = parseAngka($('#diskon').val()) || 0;
        let totalJual = totalHarga - diskonTambahan;
        if (totalJual < 0) totalJual = 0;
        $('#total_jual').val(formatRupiah(totalJual));
        hitungTotal();
        $('#detail_json').val(JSON.stringify(daftarProduk));
    }

    // Event delegation untuk input jumlah/diskon_satuan
    $(document).on('input', '.jumlah-edit, .diskon-edit', function() {
        const idx = $(this).data('index');
        const jumlah = parseInt($(this).closest('tr').find('.jumlah-edit').val()) || 0;
        const harga = parseInt(daftarProduk[idx].harga_satuan) || 0;
        let diskon = parseInt($(this).closest('tr').find('.diskon-edit').val()) || 0;
        if (diskon > harga) diskon = harga;
        daftarProduk[idx].jumlah = jumlah;
        daftarProduk[idx].diskon_satuan = diskon;
        const subtotal = (jumlah * harga) - (jumlah * diskon);
        daftarProduk[idx].subtotal = subtotal;
        // Update subtotal di tabel
        $(this).closest('tr').find('.subtotal-edit').text('Rp' + subtotal.toLocaleString('id-ID'));
        // Update total harga dan total jual di form
        let totalHarga = daftarProduk.reduce((sum, item) => sum + ((item.jumlah * item.harga_satuan) - (item.jumlah * item.diskon_satuan)), 0);
        $('#total_harga').val(totalHarga);
        let diskonTambahan = parseAngka($('#diskon').val()) || 0;
        let totalJual = totalHarga - diskonTambahan;
        if (totalJual < 0) totalJual = 0;
        $('#total_jual').val(totalJual);
        hitungTotal();
        $('#detail_json').val(JSON.stringify(daftarProduk));
    });
    // Event untuk input total bayar agar piutang dan status otomatis update
    $(document).on('input', '#total_bayar', function() {
        hitungTotal();
    });
    // Event untuk diskon tambahan dan metode pembayaran
    $(document).on('input', '#diskon', function() {
        // Update total jual jika diskon tambahan berubah
        let totalHarga = daftarProduk.reduce((sum, item) => sum + ((item.jumlah * item.harga_satuan) - (item.jumlah * item.diskon_satuan)), 0);
        let diskonTambahan = parseFloat($('#diskon').val()) || 0;
        let totalJual = totalHarga - diskonTambahan;
        if (totalJual < 0) totalJual = 0;
        $('#total_jual').val(totalJual);
        hitungTotal();
    });
    $(document).on('change', '#metode_pembayaran', function() {
        hitungTotal();
    });
    function hitungTotal() {
        let totalHarga = daftarProduk.reduce((sum, item) => sum + ((item.jumlah * item.harga_satuan) - (item.jumlah * item.diskon_satuan)), 0);
        $('#total_harga').val(formatRupiah(totalHarga));
        let diskon = parseAngka($('#diskon').val()) || 0;
        let totalJual = totalHarga - diskon;
        if (totalJual < 0) totalJual = 0;
        $('#total_jual').val(formatRupiah(totalJual));
        let totalBayar = parseAngka($('#total_bayar').val()) || 0;
        let kembalian = 0, piutang = 0;
        if (totalBayar > totalJual) {
            kembalian = totalBayar - totalJual;
            piutang = 0;
        } else {
            kembalian = 0;
            piutang = totalJual - totalBayar > 0 ? totalJual - totalBayar : 0;
        }
        $('#kembalian').val(formatRupiah(kembalian));
        $('#piutang').val(formatRupiah(piutang));
        // Status pembayaran otomatis
        let status = 'belum lunas';
        if (totalBayar === totalJual && totalJual > 0) {
            status = 'lunas';
        }
        $('#status_pembayaran').val(status);
    }

    // Inisialisasi produk select dan tabel saat dokumen siap
    let produkOptions = `<option value="">---Pilih Produk---</option>
        @foreach($produk as $p)
            <option value="{{ $p->kode_produk }}" data-nama="{{ $p->nama_produk }}">{{ $p->kode_produk }} - {{ $p->nama_produk }}</option>
        @endforeach`;
    $('#kode_produk').html(produkOptions);
    updateTabel();

    // Sebelum submit, ubah semua input nominal ke value asli (tanpa titik)
    $('form').on('submit', function() {
        // List id input nominal
        const fields = ['#total_harga', '#diskon', '#total_jual', '#total_bayar', '#kembalian', '#piutang'];
        fields.forEach(function(id) {
            const val = $(id).val();
            $(id).val(parseAngka(val));
        });
        $('#detail_json').val(JSON.stringify(daftarProduk));
    });
});
</script>
@endsection 