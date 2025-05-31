@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Daftar Resep Produk</h3>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Kode Resep</th>
                <th>Produk</th>
                <th>Keterangan</th>
                <th>Detail Bahan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reseps as $resep)
                <tr>
                    <td>{{ $resep->kode_resep }}</td>
                    <td>{{ $resep->produk->nama_produk ?? '-' }}</td>
                    <td>{{ $resep->keterangan }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#resep-detail-{{ $resep->kode_resep }}">
                            Lihat Bahan
                        </button>
                    </td>
                </tr>
                <tr class="collapse" id="resep-detail-{{ $resep->kode_resep }}">
                    <td colspan="4">
                        <table class="table table-sm mt-2">
                            <thead>
                                <tr class="table-secondary">
                                    <th>Kode Bahan</th>
                                    <th>Nama Bahan</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resep->details as $detail)
                                    <tr>
                                        <td>{{ $detail->kode_bahan }}</td>
                                        <td>{{ $detail->bahan->nama_bahan ?? '-' }}</td>
                                        <td>{{ $detail->jumlah_kebutuhan }}</td>
                                        <td>{{ $detail->satuan }}</td>
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
