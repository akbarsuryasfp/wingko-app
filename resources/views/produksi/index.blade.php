@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Daftar Produksi</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>No Produksi</th>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Detail Produk</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produksi as $p)
                <tr data-bs-toggle="collapse" data-bs-target="#detail-{{ $p->no_produksi }}" class="accordion-toggle">
                    <td>{{ $p->no_produksi }}</td>
                    <td>{{ $p->tanggal_produksi }}</td>
                    <td>{{ $p->keterangan }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary">Lihat</button>
                    </td>
                </tr>
                <tr class="collapse" id="detail-{{ $p->no_produksi }}">
                    <td colspan="4">
                        <table class="table table-sm mt-2">
                            <thead>
                                <tr class="table-secondary">
                                    <th>Produk</th>
                                    <th>Jumlah Diproduksi</th>
                                    <th>Tanggal Expired</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($p->details as $d)
                                    <tr>
                                        <td>{{ $d->produk->nama_produk ?? $d->kode_produk }}</td>
                                        <td>{{ $d->jumlah_unit }}</td>
                                        <td>{{ $d->tanggal_expired }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
