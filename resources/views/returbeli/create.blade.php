@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h4 class="mb-0">Input Retur Pembelian</h4>
        </div>
        
        <div class="card-body">
            <form action="{{ route('returbeli.store') }}" method="POST">
                @csrf

                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 col-form-label">Kode Retur Pembelian</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control bg-light" name="kode_retur" value="{{ $kode_retur }}" readonly required>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 col-form-label">No Pembelian</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="kode_pembelian" id="kode_pembelian" required>
                            <option value="">-- Pilih No Pembelian --</option>
                            @foreach($pembelian as $item)
                                <option value="{{ $item->no_pembelian }}"
                                    data-tanggal="{{ $item->tanggal_pembelian }}"
                                    data-supplier="{{ $item->nama_supplier }}">
                                    {{ $item->no_pembelian }} | {{ $item->tanggal_pembelian }} | {{ $item->nama_supplier }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 col-form-label">Nama Supplier</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control bg-light" id="nama_supplier" readonly>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 col-form-label">Tanggal Retur</label>
                    <div class="col-sm-9">
                        <input type="date" class="form-control" name="tanggal_retur_beli" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="row mb-3 align-items-center">
                    <label class="col-sm-3 col-form-label">Jenis Pengembalian</label>
                    <div class="col-sm-9">
                        <select class="form-control" name="jenis_pengembalian" required>
                            <option value="">-- Pilih Jenis Pengembalian --</option>
                            <option value="uang">Uang</option>
                            <option value="barang">Barang</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3 align-items-start">
                    <label class="col-sm-3 col-form-label">Keterangan (Opsional)</label>
                    <div class="col-sm-9">
                        <textarea class="form-control" name="keterangan">{{ old('keterangan') }}</textarea>
                    </div>
                </div>

                <h5 class="mt-4 mb-3">Detail Bahan</h5>
                <div class="table-responsive">
                    <table class="table table-sm" id="detail_table">
                        <thead>
<tr class="text-center align-middle">
    <th style="width: 50px; vertical-align: middle;">No</th>
    <th style="vertical-align: middle;">Nama Bahan</th>
    <th style="width: 100px; vertical-align: middle;">Satuan</th>
    <th style="width: 100px; vertical-align: middle;">Jumlah Terima</th>
    <th style="width: 100px; vertical-align: middle;">Jumlah Retur</th>
    <th style="width: 120px; vertical-align: middle;">Harga/Satuan</th>
    <th style="width: 120px; vertical-align: middle;">Subtotal</th>
    <th style="vertical-align: middle;">Alasan</th>
    <th style="width: 50px; vertical-align: middle;">Aksi</th>
</tr>

                        </thead>
                        <tbody>
                            <!-- Data bahan akan diisi otomatis oleh JS -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">Total Retur</th>
                                <th class="text-end">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control bg-light text-end" id="total_retur" name="total_retur" readonly>
                                    </div>
                                </th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <div>
                        <a href="{{ route('returbeli.index') }}" class="btn btn-secondary">← Kembali</a>
                        <button type="reset" class="btn btn-warning">Reset</button>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Retur</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery dulu -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Baru script Anda -->
<script>
jQuery(document).ready(function() {
    jQuery('#kode_pembelian').change(function() {
        var no_pembelian = jQuery(this).val();
        if (no_pembelian) {
            jQuery.get('/returbeli/detail-pembelian/' + no_pembelian, function(response) {
                jQuery('#detail_table tbody').empty();
                let idx = 1;
                response.details.forEach(function(item) {
                    jQuery('#detail_table tbody').append(`
                        <tr>
                            <td>${idx++}</td>
                            <td>
                                <input type="hidden" name="kode_bahan[]" value="${item.kode_bahan}">
                                <input type="text" name="nama_bahan[]" class="form-control" value="${item.nama_bahan}" readonly>
                            </td>
                            <td><input type="text" name="satuan[]" class="form-control" value="${item.satuan || ''}" readonly></td>
                            <td><input type="number" class="form-control" value="${item.jumlah}" readonly></td>
                            <td>
                                <input type="number" name="jumlah_retur[]" class="form-control jumlah" max="${item.jumlah}" min="0" value="0" required oninput="cekRetur(this, ${item.jumlah})">
                            </td>
                            <td><input type="number" name="harga_beli[]" class="form-control harga" value="${item.harga_beli}" readonly></td>
                            <td>
    <input type="number" name="total[]" class="form-control" value="0" readonly style="min-width:140px;">
</td>                            <td><input type="text" name="alasan[]" class="form-control"></td>
                            <td>
    <button type="button" class="btn btn-danger btn-sm btn-hapus-baris" title="Hapus">
        <i class="bi bi-x"></i>
    </button>
</td>                        </tr>
                    `);
                });
                updateSubtotal();
            });
        } else {
            jQuery('#detail_table tbody').empty();
            updateSubtotal();
        }
    });

    jQuery('#no_terima_bahan').change(function() {
        var no_terima_bahan = jQuery(this).val();
        if (no_terima_bahan) {
            jQuery.get('/returbeli/detail-pembelian/' + no_terima_bahan, function(response) {
                jQuery('#detail_table tbody').empty();
                let idx = 1;
                response.details.forEach(function(item) {
                    jQuery('#detail_table tbody').append(`
                        <tr>
                            <td>${idx++}</td>
                            <td>
                                <input type="hidden" name="kode_bahan[]" value="${item.kode_bahan}">
                                <input type="text" name="nama_bahan[]" class="form-control" value="${item.nama_bahan}" readonly>
                            </td>
                            <td><input type="text" name="satuan[]" class="form-control" value="${item.satuan || ''}" readonly></td>
                            <td><input type="number" class="form-control" value="${item.jumlah}" readonly></td>
                            <td>
                                <input type="number" name="jumlah_retur[]" class="form-control jumlah" max="${item.jumlah}" min="0" value="0" required oninput="cekRetur(this, ${item.jumlah})">
                            </td>
                            <td><input type="number" name="harga_beli[]" class="form-control harga" value="${item.harga_beli}" readonly></td>
                            <td>
    <input type="number" name="total[]" class="form-control" value="0" readonly style="min-width:140px;">
</td>                            <td><input type="text" name="alasan[]" class="form-control"></td>
                            <td>
<button type="button" class="btn btn-danger btn-sm btn-hapus-baris" title="Hapus">
    <i class="bi bi-x"></i>
</button>
            </td>                        </tr>
                    `);
                });
                updateSubtotal();
            });
        } else {
            jQuery('#detail_table tbody').empty();
            updateSubtotal();
        }
    });

    // Update nama supplier otomatis
    jQuery('#kode_pembelian').change(function() {
        var selected = this.options[this.selectedIndex];
        jQuery('#nama_supplier').val(selected.getAttribute('data-supplier') || '');
    });

    jQuery('#no_pembelian').on('change', function() {
        var no_pembelian = jQuery(this).val();
        if(no_pembelian) {
            jQuery.get('/returbeli/detail-pembelian/' + no_pembelian, function(res) {
                var html = '';
                jQuery.each(res.details, function(i, d) {
                    html += '<tr>';
                    html += '<td><input type="hidden" name="kode_bahan[]" value="'+d.kode_bahan+'">'+d.nama_bahan+'</td>';
                    html += '<td><input type="number" class="form-control" value="'+d.jumlah_terima+'" readonly></td>';
                    html += '<td><input type="number" name="harga_beli[]" class="form-control" value="'+d.harga_beli+'" readonly></td>';
                    html += '<td><input type="number" name="jumlah_retur[]" class="form-control" min="0" max="'+d.jumlah_terima+'" value="0"></td>';
                    html += '<td><input type="number" name="subtotal[]" class="form-control" value="0" readonly></td>';
                    html += '<td><input type="text" name="alasan[]" class="form-control"></td>';
                    html += '</tr>';
                });
                jQuery('#detail_bahan').html('<table class="table"><thead><tr><th>Nama Bahan</th><th>Jumlah Terima</th><th>Harga Beli</th><th>Jumlah Retur</th><th>Subtotal</th><th>Alasan</th></tr></thead><tbody>'+html+'</tbody></table>');
            });
        } else {
            jQuery('#detail_bahan').html('');
        }
    });

    // Script hitung subtotal otomatis saat jumlah_retur berubah
    jQuery('input[name="jumlah_retur[]"]').on('input', function() {
        var idx = jQuery(this).closest('tr').index();
        var harga = jQuery('input[name="harga_beli[]"]').eq(idx).val();
        var jumlah = jQuery(this).val();
        var subtotal = harga * jumlah;
        jQuery('input[name="subtotal[]"]').eq(idx).val(subtotal);
        // Update total retur
        var total = 0;
        jQuery('input[name="subtotal[]"]').each(function() {
            total += parseFloat(jQuery(this).val()) || 0;
        });
        jQuery('#total_retur').val(total);
    });
});

// Fungsi cekRetur dan updateSubtotal tetap seperti sebelumnya
function cekRetur(input, max) {
    let val = parseInt(input.value) || 0;
    if (val > max) {
        input.value = max;
        val = max;
    }
    // Hitung total per baris
    let row = input.closest('tr');
    let harga = parseFloat(row.querySelector('input[name="harga_beli[]"]').value) || 0;
    row.querySelector('input[name="total[]"]').value = val * harga;

    // Hitung total retur
    let total = 0;
    document.querySelectorAll('input[name="total[]"]').forEach(function(el) {
        total += parseFloat(el.value) || 0;
    });
    document.getElementById('total_retur').value = total;
}

function updateSubtotal() {
    let total = 0;
    $('#detail_table tbody tr').each(function () {
        let jumlah = parseFloat($(this).find('.jumlah').val()) || 0;
        let harga = parseFloat($(this).find('.harga').val()) || 0;
        let subtotal = jumlah * harga;
        $(this).find('input[name="total[]"]').val(subtotal);
        total += subtotal;
    });
    $('#total_retur').val(total);
}

jQuery(document).on('click', '.btn-hapus-baris', function() {
    jQuery(this).closest('tr').remove();
    updateSubtotal();
});

</script>
@endsection
