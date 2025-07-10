@extends('layouts.app')

@section('content')
<div class="container">
<h2>Edit Retur Pembelian</h2>
<form action="{{ route('returbeli.update', $retur->no_retur_beli) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row mb-3 align-items-center">
        <label class="col-sm-3 col-form-label">Kode Retur Pembelian</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" value="{{ $retur->no_retur_beli }}" readonly required style="max-width: 400px;">
        </div>
    </div>

    <div class="row mb-3 align-items-center">
        <label class="col-sm-3 col-form-label">No Pembelian</label>
        <div class="col-sm-9">
            <select name="kode_pembelian" class="form-control" required style="max-width: 400px;">
                @foreach($pembelian as $item)
                    <option value="{{ $item->no_pembelian }}" {{ $retur->no_pembelian == $item->no_pembelian ? 'selected' : '' }}>
                        {{ $item->no_pembelian }} | {{ $item->tanggal_pembelian }} | {{ $item->nama_supplier }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row mb-3 align-items-center">
        <label class="col-sm-3 col-form-label">Tanggal Retur</label>
        <div class="col-sm-9">
            <input type="date" name="tanggal_retur_beli" class="form-control" value="{{ $retur->tanggal_retur_beli }}" required style="max-width: 400px;">
        </div>
    </div>

    <div class="row mb-3 align-items-center">
        <label class="col-sm-3 col-form-label">Jenis Pengembalian</label>
        <div class="col-sm-9">
            <select name="jenis_pengembalian" class="form-control" required style="max-width: 400px;">
                <option value="">-- Pilih Jenis Pengembalian --</option>
                <option value="uang" {{ $retur->jenis_pengembalian == 'uang' ? 'selected' : '' }}>Uang</option>
                <option value="barang" {{ $retur->jenis_pengembalian == 'barang' ? 'selected' : '' }}>Barang</option>
            </select>
        </div>
    </div>

    <div class="row mb-3 align-items-start">
        <label class="col-sm-3 col-form-label">Keterangan (Opsional)</label>
        <div class="col-sm-9">
            <textarea class="form-control" name="keterangan" style="max-width: 400px;">{{ $retur->keterangan }}</textarea>
        </div>
    </div>

    <div class="row">
        <div class="offset-sm-3 col-sm-9">
            <button type="submit" class="btn btn-primary">Update Retur</button>
        </div>
    </div>
</form>


        <h5>Detail Bahan</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Bahan</th> 
                    <th>Harga Beli</th>
                    <th>Jumlah Terima</th>
                    <th>Jumlah Retur</th>
                    <th>Subtotal</th>
                    <th>Alasan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $i => $d)
                <tr>
                    <td>
                        <input type="hidden" name="kode_bahan[]" value="{{ $d->kode_bahan }}">
                        {{ $d->nama_bahan }}
                    </td>
                        <td>
                        <input type="number" name="harga_beli[]" class="form-control" value="{{ $d->harga_beli }}" readonly>
                    </td>
                     <td>
                        <input type="number" class="form-control" value="{{ $d->jumlah_terima ?? '' }}" readonly>                    </td>
</td>
                    <td>
                        <input type="number" name="jumlah_retur[]" class="form-control" value="{{ $d->jumlah_retur }}" min="1" required>
                    </td>
                    <td>
                        <input type="number" name="subtotal[]" class="form-control" value="{{ $d->subtotal }}" readonly>
                    </td>
                    <td>
                        <input type="text" name="alasan[]" class="form-control" value="{{ $d->alasan }}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm btn-hapus-baris">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total Retur</th>
                    <th>
                        <input type="number" class="form-control" id="total_retur" name="total_retur" value="0" readonly>
                    </th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('returbeli.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const jumlahInputs = document.querySelectorAll('input[name="jumlah_retur[]"]');
    const hargaInputs = document.querySelectorAll('input[name="harga_beli[]"]');
    const subtotalInputs = document.querySelectorAll('input[name="subtotal[]"]');
    const totalReturInput = document.getElementById('total_retur');

    function hitungTotal() {
        let total = 0;
        subtotalInputs.forEach(function(sub) {
            total += parseFloat(sub.value) || 0;
        });
        totalReturInput.value = total;
    }

    jumlahInputs.forEach(function(input, idx) {
        input.addEventListener('input', function() {
            let harga = parseFloat(hargaInputs[idx].value) || 0;
            let jumlah = parseFloat(input.value) || 0;
            let subtotal = harga * jumlah;
            subtotalInputs[idx].value = subtotal;
            hitungTotal();
        });
    });

    // Hitung total saat halaman pertama kali dibuka
    hitungTotal();
    // Script hapus baris
    document.querySelectorAll('.btn-hapus-baris').forEach(function(btn) {
        btn.addEventListener('click', function() {
            this.closest('tr').remove();
            // Update total setelah hapus baris
            const subtotalInputs = document.querySelectorAll('input[name="subtotal[]"]');
            let total = 0;
            subtotalInputs.forEach(function(sub) {
                total += parseFloat(sub.value) || 0;
            });
            document.getElementById('total_retur').value = total;
        });
    });
});

</script>