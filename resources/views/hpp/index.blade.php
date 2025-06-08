@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <h3 class="mb-0">Daftar Produksi untuk Perhitungan HPP</h3>
        <form method="GET" class="d-flex gap-2 align-items-center" action="{{ route('hpp.index') }}">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari No Produksi / Produk" value="{{ $search }}">
            <button class="btn btn-sm btn-primary" type="submit">Cari</button>
        </form>
    </div>
   <div class="mb-3" style="text-align: right;">
    <select name="per_page" class="form-select form-select-sm d-inline-block" onchange="this.form.submit()" style="width: auto;">
         @foreach([10, 20, 30, 40, 50] as $opt)
                    <option value="{{ $opt }}" @selected($perPage == $opt)>{{ $opt }} / page </option>
                @endforeach
            </select>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle table-bordered rounded shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>No Produksi Detail</th>
                    <th>Produk</th>
                    <th>Jumlah Diproduksi</th>
                    <th>Tanggal Produksi</th>
                    <th>Status HPP</th>
                    <th style="width:120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($produksiDetails as $detail)
                    <tr>
                        <td>{{ $detail->no_detail_produksi }}</td>
                        <td>{{ $detail->produk->nama_produk ?? $detail->kode_produk }}</td>
                        <td>{{ $detail->jumlah_unit }}</td>
                        <td>{{ $detail->tanggal_produksi ?? ($detail->produksi->tanggal_produksi ?? '-') }}</td>
                        <td>
                            @if (in_array($detail->no_detail_produksi, $hppSudahInput))
                                <span class="badge bg-success">Sudah Diinput</span>
                            @else
                                <span class="badge bg-warning text-dark">Belum Diinput</span>
                            @endif
                        </td>
                        <td>
                            @if (in_array($detail->no_detail_produksi, $hppSudahInput))
                                <a href="{{ route('hpp.edit', ['no_detail' => $detail->no_detail_produksi]) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                            @else
                                <a href="{{ route('hpp.input', ['no_detail' => $detail->no_detail_produksi]) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-circle"></i> Input
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada data ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <div>
            <small>
                Menampilkan {{ $produksiDetails->firstItem() ?? 0 }} - {{ $produksiDetails->lastItem() ?? 0 }} dari {{ $produksiDetails->total() }} data
            </small>
        </div>
        <div>
            {{ $produksiDetails->links() }}
        </div>
    </div>
</div>
@endsection
