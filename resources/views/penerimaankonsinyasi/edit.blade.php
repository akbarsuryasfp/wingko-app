@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">EDIT PENERIMAAN KONSINYASI</h4>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('penerimaankonsinyasi.update', $header->no_penerimaankonsinyasi) }}" method="POST">
        @csrf
        @method('PUT')
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Penerimaan -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Penerimaan Konsinyasi</label>
                    <input type="text" class="form-control" value="{{ $header->no_penerimaankonsinyasi }}" readonly>
                    <input type="hidden" name="no_penerimaankonsinyasi" value="{{ $header->no_penerimaankonsinyasi }}">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Konsinyasi Keluar</label>
                    <input type="text" class="form-control" value="{{ $header->no_konsinyasikeluar }} - {{ $header->consignee->nama_consignee ?? '' }}" readonly>
                    <input type="hidden" name="no_konsinyasikeluar" value="{{ $header->no_konsinyasikeluar }}">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Tanggal Terima</label>
                    <input type="date" name="tanggal_terima" class="form-control" value="{{ $header->tanggal_terima }}" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Nama Consignee (Mitra)</label>
                    <input type="text" class="form-control" value="{{ $header->consignee->nama_consignee ?? '-' }}" readonly>
                    <input type="hidden" name="kode_consignee" value="{{ $header->kode_consignee }}">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Metode Pembayaran</label>
                    <select name="metode_pembayaran" class="form-control" required>
                        <option value="">---Pilih Metode---</option>
                        <option value="Tunai" {{ $header->metode_pembayaran == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                        <option value="Non Tunai" {{ $header->metode_pembayaran == 'Non Tunai' ? 'selected' : '' }}>Non Tunai</option>
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" value="{{ $header->keterangan }}">
                </div>
            </div>
            <!-- Kolom Kanan: Tidak ada input produk manual, hanya info produk di tabel -->
        </div>
        <hr>
        <h4 class="text-center">DAFTAR PRODUK PENERIMAAN KONSINYASI</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk-terima">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Jumlah Setor</th>
                    <th>Jumlah Terjual</th>
                    <th>Satuan</th>
                    <th>Harga/Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detailList as $i => $d)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $d->produk->nama_produk ?? $d->nama_produk ?? '-' }}</td>
                    <td>{{ $d->jumlah_setor }}</td>
                    <td>
                        <input type="number" name="detail[{{ $i }}][jumlah_terjual]" min="0" max="{{ $d->jumlah_setor }}" value="{{ $d->jumlah_terjual }}" class="form-control form-control-sm" style="width:90px;display:inline-block;">
                        <input type="hidden" name="detail[{{ $i }}][no_detailpenerimaankonsinyasi]" value="{{ $d->no_detailpenerimaankonsinyasi }}">
                    </td>
                    <td>{{ $d->satuan }}</td>
                    <td class="harga-satuan" data-value="{{ $d->harga_satuan }}">Rp{{ number_format($d->harga_satuan,0,',','.') }}</td>
                    <td class="subtotal">Rp{{ number_format($d->subtotal,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap">
            <div>
                <a href="{{ route('penerimaankonsinyasi.index') }}" class="btn btn-secondary">Back</a>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0">Total Terima</label>
                <input type="text" class="form-control" id="total_terima" value="Rp{{ number_format($header->total_terima,0,',','.') }}" readonly style="width:180px;">
                <input type="hidden" name="total_terima" id="total_terima_hidden" value="{{ $header->total_terima }}">
                <button type="submit" class="btn btn-success">Update</button>
            </div>
        </div>
    </form>
</div>
<script>
// Script untuk update subtotal dan total terima otomatis saat jumlah terjual diubah
function formatRupiah(angka) {
    return 'Rp' + (angka ? parseInt(angka).toLocaleString('id-ID') : '0');
}
function unformatRupiah(str) {
    return parseInt((str||'').replace(/[^\d]/g, '')) || 0;
}
function updateSubtotalAndTotal() {
    let total = 0;
    document.querySelectorAll('tr[data-idx]').forEach(function(row) {
        const idx = row.getAttribute('data-idx');
        const jumlahTerjualInput = row.querySelector('input[name="detail['+idx+'][jumlah_terjual]"]');
        const hargaSatuan = parseInt(row.querySelector('.harga-satuan').dataset.value);
        const jumlahTerjual = parseInt(jumlahTerjualInput.value) || 0;
        const subtotal = jumlahTerjual * hargaSatuan;
        row.querySelector('.subtotal').textContent = formatRupiah(subtotal);
        total += subtotal;
    });
    document.getElementById('total_terima').value = formatRupiah(total);
    document.getElementById('total_terima_hidden').value = total;
}
document.querySelectorAll('input[name^="detail"][name$="[jumlah_terjual]"]').forEach(function(input) {
    input.addEventListener('input', updateSubtotalAndTotal);
});
// Tambahkan atribut data-idx, .harga-satuan, dan .subtotal pada setiap baris produk
window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#daftar-produk-terima tbody tr').forEach(function(row, i) {
        row.setAttribute('data-idx', i);
        const hargaCell = row.querySelectorAll('td')[5];
        hargaCell.classList.add('harga-satuan');
        hargaCell.setAttribute('data-value', hargaCell.textContent.replace(/[^\d]/g, ''));
        row.querySelectorAll('td')[6].classList.add('subtotal');
    });
    updateSubtotalAndTotal(); // Hitung ulang saat load
});
</script>
@endsection
