@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Edit Retur Pembelian</h4>
    <form action="{{ route('returbeli.update', $retur->no_retur_beli) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>No Retur</label>
            <input type="text" class="form-control" value="{{ $retur->no_retur_beli }}" readonly>
        </div>
        <div class="mb-3">
            <label>No Pembelian</label>
            <select name="kode_pembelian" class="form-control" required>
                @foreach($pembelian as $item)
                    <option value="{{ $item->no_pembelian }}" {{ $retur->no_pembelian == $item->no_pembelian ? 'selected' : '' }}>
                        {{ $item->no_pembelian }} | {{ $item->tanggal_pembelian }} | {{ $item->nama_supplier }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Tanggal Retur</label>
            <input type="date" name="tanggal_retur_beli" class="form-control" value="{{ $retur->tanggal_retur_beli }}" required>
        </div>
        <div class="mb-3">
            <label>Keterangan (Opsional)</label>
            <textarea class="form-control" name="keterangan">{{ $retur->keterangan }}</textarea>
        </div>
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