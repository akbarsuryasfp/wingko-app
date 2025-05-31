@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Detail Penjualan</h4>
    <table class="table">
        <tr>
            <th>No Penjualan</th>
            <td>{{ $penjualan->no_jual }}</td>
        </tr>
        <tr>
            <th>Tanggal Jual</th>
            <td>{{ $penjualan->tanggal_jual }}</td>
        </tr>
        <tr>
            <th>Pelanggan</th>
            <td>{{ $penjualan->nama_pelanggan ?? '-' }}</td>
        </tr>
        <tr>
            <th>Total Penjualan</th>
            <td>{{ number_format($penjualan->total,0,',','.') }}</td>
        </tr>
        <tr>
            <th>Metode Pembayaran</th>
            <td>{{ ucfirst($penjualan->metode_pembayaran) }}</td>
        </tr>
        <tr>
            <th>Status Pembayaran</th>
            <td>
                @if($penjualan->status_pembayaran == 'lunas')
                    <span class="badge bg-success">Lunas</span>
                @else
                    <span class="badge bg-warning text-dark">Belum Lunas</span>
                @endif
            </td>
        </tr>
        <tr>
            <th>Keterangan</th>
            <td>{{ $penjualan->keterangan ?? '-' }}</td>
        </tr>
    </table>

    <h5>Detail Produk Terjual</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Jumlah</th>
                <th>Harga Satuan</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($details as $i => $d)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $d->nama_produk ?? '-' }}</td>
                <td>{{ $d->jumlah }}</td>
                <td>{{ number_format($d->harga_satuan,0,',','.') }}</td>
                <td>{{ number_format($d->subtotal,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    if (daftarProduk.length === 0) {
        alert('Minimal 1 produk harus ditambahkan!');
        e.preventDefault();
        return false;
    }
});
</script>