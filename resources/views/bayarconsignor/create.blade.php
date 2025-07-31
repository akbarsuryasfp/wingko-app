@extends('layouts.app')

@section('content')

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="mb-4">INPUT PEMBAYARAN CONSIGNOR (PEMILIK BARANG)</h2>
            <form method="POST" action="{{ route('bayarconsignor.store') }}">
        @csrf
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">No Bayar Consignor</label>
                    <input type="text" name="no_bayarconsignor" id="no_bayarconsignor" class="form-control" value="{{ $no_bayarconsignor ?? '' }}" readonly style="pointer-events: none; background: #e9ecef;">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Tanggal Bayar</label>
                    <input type="date" name="tanggal_bayar" id="tanggal_bayar" class="form-control" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Nama Consignor (Pemilk Barang)</label>
                    <select name="kode_consignor" id="consignor" class="form-control" required>
                        <option value="">-- Pilih Consignor --</option>
                        @foreach($consignors as $consignor)
                            <option value="{{ $consignor->kode_consignor }}">{{ $consignor->nama_consignor }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                        <option value="">-- Pilih Metode Pembayaran --</option>
                        <option value="Tunai">Tunai</option>
                        <option value="Non Tunai">Non Tunai</option>
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 180px;">Keterangan</label>
                    <input type="text" name="keterangan" id="keterangan" class="form-control">
                </div>
            </div>
        </div>
        <hr>
        <h4 class="text-center mb-3">DAFTAR PRODUK PEMBAYARAN CONSIGNOR (PEMILIK BARANG)</h4>
        <div id="produk-table-wrapper">
            <table class="table table-bordered text-center align-middle mt-3">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Produk</th>
                        <th>Nama Produk</th>
                        <th>Satuan</th>
                        <th>Jumlah Terjual</th>
                        <th>Total Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($produk) && count($produk) === 0)
                        <tr>
                            <td colspan="6" class="text-center">Data produk tidak ditemukan.</td>
                        </tr>
                    @elseif(isset($produk))
                        @foreach($produk as $i => $p)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $p['kode_produk'] }}</td>
                            <td>{{ $p['nama_produk'] }}</td>
                            <td>{{ $p['satuan'] ?? ($p['produk']['satuan'] ?? '-') }}</td>
                            <td>{{ $p['terjual'] }}</td>
                            <td>Rp {{ number_format($p['total_penjualan'],0,',','.') }}</td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <!-- Total Bayar mirip Total Pesanan -->
            <div class="d-flex justify-content-end align-items-center mt-2" style="max-width: 400px; margin-left: auto;">
                <label class="me-2 fw-bold mb-0">Total Bayar</label>
                <div class="input-group" style="width: 220px;">
                    <span class="input-group-text">Rp</span>
                    <input type="text" id="total_bayar_display" class="form-control fw-bold" value="{{ isset($produk) ? number_format(collect($produk)->sum('total_penjualan'),0,',','.') : '' }}" readonly tabindex="-1" style="background:#e9ecef;pointer-events:none;">
                </div>
                <input type="hidden" id="total_bayar" name="total_bayar" value="{{ isset($produk) ? collect($produk)->sum('total_penjualan') : '' }}">
            </div>
        </div>
        <div class="d-flex justify-content-between mt-4 gap-2 flex-wrap">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('bayarconsignor.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning ms-2">Reset</button>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Simpan Pembayaran</button>
            </div>
        </div>
            </form>
        </div>
    </div>
</div>


<script>
    document.getElementById('consignor').addEventListener('change', function() {
        let kode = this.value;
        if (!kode) {
            document.getElementById('produk-table-wrapper').innerHTML = '';
            return;
        }
        fetch(`/bayarconsignor/produk/${kode}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('produk-table-wrapper').innerHTML = html;
            });
    });

    // Reset form fields (metode pembayaran dan keterangan) saat tombol reset diklik
    document.querySelector('form').addEventListener('reset', function() {
        setTimeout(function() {
            document.getElementById('metode_pembayaran').value = '';
            document.getElementById('keterangan').value = '';
            document.getElementById('produk-table-wrapper').innerHTML = '';
        }, 10);
    });
</script>
@endsection
