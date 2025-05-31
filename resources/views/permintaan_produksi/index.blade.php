@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Daftar Permintaan Produksi</h2>

    <a href="{{ route('permintaan_produksi.create') }}" class="btn btn-success mb-3">
        + Tambah Permintaan Produksi
    </a>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($permintaanProduksi as $item)
                <tr>
                    <td>{{ $item->kode_permintaan_produksi }}</td>
                    <td>{{ $item->tanggal }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td><span class="badge bg-{{ $item->status === 'Selesai' ? 'success' : 'warning' }}">{{ $item->status }}</span></td>
                    <td>
                        <button class="btn btn-sm btn-primary" 
                                type="button" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#detail-{{ $item->kode_permintaan_produksi }}" 
                                aria-expanded="false" 
                                aria-controls="detail-{{ $item->kode_permintaan_produksi }}">
                            Lihat
                        </button>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" class="p-0">
                        <div class="collapse" id="detail-{{ $item->kode_permintaan_produksi }}">
                            <div class="p-3">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr class="table-secondary">
                                            <th>Kode Produk</th>
                                            <th>Nama Produk</th>
                                            <th>Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($item->details as $detail)
                                            <tr>
                                                <td>{{ $detail->kode_produk }}</td>
                                                <td>{{ $detail->produk->nama_produk ?? '-' }}</td>
                                                <td>{{ $detail->unit }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center">Tidak ada detail permintaan</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection