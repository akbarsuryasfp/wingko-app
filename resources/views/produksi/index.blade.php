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
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produksi as $p)
                <tr>
                    <td>{{ $p->no_produksi }}</td>
                    <td>{{ $p->tanggal_produksi }}</td>
                    <td>{{ $p->keterangan }}</td>
                    <td>
                        {{-- 
                        <button class="btn btn-sm btn-primary"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#detail-{{ $p->no_produksi }}">
                            Lihat
                        </button>
                        --}}
                        <a href="{{ route('produksi.show', $p->no_produksi) }}"
                           class="btn btn-sm btn-info mt-1">
                            Detail Lengkap
                        </a>
                    </td>
                    <td>
                        <form action="{{ route('produksi.destroy', $p->no_produksi) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin membatalkan produksi ini?')">Batalkan</button>
                        </form>
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
                                {{-- 
                                @foreach ($p->details as $d)
                                    <tr>
                                        <td>{{ $d->produk->nama_produk ?? $d->kode_produk }}</td>
                                        <td>{{ $d->jumlah_unit }}</td>
                                        <td>{{ $d->tanggal_expired }}</td>
                                    </tr>
                                @endforeach
                                --}}
                            </tbody>
                        </table>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
