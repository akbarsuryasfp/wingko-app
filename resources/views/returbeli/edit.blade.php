@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">Edit Retur Pembelian</h4>
        </div>
        
        <div class="card-body">
            <form action="{{ route('returbeli.update', $retur->no_retur_beli) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row mb-2 align-items-center">
                    <label class="col-sm-3 col-form-label">Kode Retur Pembelian</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control bg-light" value="{{ $retur->no_retur_beli }}" readonly required style="max-width: 400px;">
                    </div>
                </div>

                <div class="row mb-2 align-items-center">
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

                <div class="row mb-2 align-items-center">
                    <label class="col-sm-3 col-form-label">Tanggal Retur</label>
                    <div class="col-sm-9">
                        <input type="date" name="tanggal_retur_beli" class="form-control" value="{{ $retur->tanggal_retur_beli }}" required style="max-width: 400px;">
                    </div>
                </div>

                <div class="row mb-2 align-items-center">
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

                <h5 class="mt-4 mb-3">Detail Bahan</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
<thead>
    <tr class="text-center">
        <th style="width: 50px">No</th>
        <th style="width: 150px">Nama Bahan</th> 
        <th style="width: 100px">Harga Beli</th>
        <th style="width: 80px">Jumlah Terima</th>
        <th style="width: 80px">Jumlah Retur</th>
        <th style="width: 100px">Subtotal</th>
        <th style="width: 100px">Alasan</th>
        <th style="width: 50px">Aksi</th>
    </tr>
</thead>
<tbody>
    @foreach($details as $i => $d)
    <tr>
        <td class="text-center">{{ $loop->iteration }}</td>
        <td>
            <input type="hidden" name="kode_bahan[]" value="{{ $d->kode_bahan }}">
            {{ $d->nama_bahan }}
        </td>
<td class="text-end">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="harga_beli[]" class="form-control bg-light text-end harga-input" 
                                               value="{{ (int)$d->harga_beli }}" readonly>
                                    </div>
                                </td>
        <td class="text-end">
            <input type="number" class="form-control bg-light text-end" value="{{ $d->jumlah_terima ?? '' }}" readonly>
        </td>
        <td>
            <input type="number" name="jumlah_retur[]" class="form-control text-end" value="{{ $d->jumlah_retur }}" min="1" required>
        </td>
<td class="text-end">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" name="subtotal[]" class="form-control bg-light text-end subtotal-input" 
                                               value="{{ (int)$d->subtotal }}" readonly>
                                    </div>
                                </td>
        <td>
            <input type="text" name="alasan[]" class="form-control" value="{{ $d->alasan }}">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm btn-hapus-baris">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
    @endforeach
</tbody>
                
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">Total Retur</th>
<th>
    <div class="input-group">
        <span class="input-group-text">Rp</span>
        <input type="number" class="form-control bg-light text-end" id="total_retur" name="total_retur" value="{{ array_sum(array_column($details->toArray(), 'subtotal')) }}" readonly>
    </div>
</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex justify-content-between mt-3">
                                        <div>
                        <a href="{{ route('returbeli.index') }}" class="btn btn-secondary"> ← Kembali</a>
                        <button type="reset" class="btn btn-warning">Reset</button>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Retur</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format number with thousand separators and remove ALL decimals
    function formatNumber(num) {
        num = parseFloat(num) || 0;
        // Always remove decimals
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Get all relevant input elements
    const jumlahInputs = document.querySelectorAll('input[name="jumlah_retur[]"]');
    const hargaInputs = document.querySelectorAll('input[name="harga_beli[]"]');
    const subtotalInputs = document.querySelectorAll('input[name="subtotal[]"]');
    const totalReturInput = document.getElementById('total_retur');

    // Calculate and update totals
    function hitungTotal() {
        let total = 0;
        subtotalInputs.forEach(function(sub) {
            // Remove thousand separators before calculation
            const numericValue = parseFloat(sub.value.replace(/\./g, '')) || 0;
            total += numericValue;
            
            // Update display with formatted value (no decimals)
            sub.value = formatNumber(numericValue);
        });
        
        // Update total return with formatted value (no decimals)
        totalReturInput.value = formatNumber(total);
    }

    // Initialize calculation for each row
    jumlahInputs.forEach(function(input, idx) {
        // Format initial values (remove decimals)
        if (hargaInputs[idx].value) {
            hargaInputs[idx].value = formatNumber(hargaInputs[idx].value);
        }
        if (subtotalInputs[idx].value) {
            subtotalInputs[idx].value = formatNumber(subtotalInputs[idx].value);
        }

        input.addEventListener('input', function() {
            // Get numeric values (remove thousand separators)
            const harga = parseFloat(hargaInputs[idx].value.replace(/\./g, '')) || 0;
            const jumlah = parseFloat(input.value) || 0;
            
            // Calculate subtotal
            const subtotal = Math.round(harga * jumlah); // Round to remove decimals
            subtotalInputs[idx].value = formatNumber(subtotal);
            
            // Update total
            hitungTotal();
        });
    });

    // Calculate total when page first loads
    hitungTotal();
    
    // Row deletion handler
    document.querySelectorAll('.btn-hapus-baris').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (confirm('Apakah Anda yakin ingin menghapus baris ini?')) {
                this.closest('tr').remove();
                // Update total after row deletion
                hitungTotal();
            }
        });
    });

    // Validation for jumlah_retur
    jumlahInputs.forEach(input => {
        input.addEventListener('change', function() {
            const jumlahTerimaInput = this.closest('tr').querySelector('td:nth-child(4) input');
            const jumlahTerima = parseFloat(jumlahTerimaInput.value.replace(/\./g, '')) || 0;
            const jumlahRetur = parseFloat(this.value) || 0;
            
            if (jumlahRetur > jumlahTerima) {
                alert('Jumlah retur tidak boleh melebihi jumlah terima!');
                this.value = jumlahTerima;
                this.dispatchEvent(new Event('input'));
            }
        });
    });
});
</script>
@endsection