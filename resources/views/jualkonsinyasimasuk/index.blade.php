@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:1100px;">
    <h4 class="mb-4">DAFTAR PENJUALAN KONSINYASI</h4>
    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap">
        <form method="GET" class="d-flex align-items-center gap-2 mb-0 flex-wrap">
            @foreach(request()->except(['tanggal_awal','tanggal_akhir','page','sort']) as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
            <span class="fw-semibold">Periode:</span>
            <input type="date" name="tanggal_awal" class="form-control form-control-sm w-auto" value="{{ request('tanggal_awal') }}">
            <span class="mx-1">s/d</span>
            <input type="date" name="tanggal_akhir" class="form-control form-control-sm w-auto" value="{{ request('tanggal_akhir') }}">
            <button type="submit" class="btn btn-secondary btn-sm">Terapkan</button>
            @php
                $sort = request('sort', 'asc');
                $nextSort = $sort === 'asc' ? 'desc' : 'asc';
                $icon = $sort === 'asc' ? '▲' : '▼';
            @endphp
            <a href="{{ route('jualkonsinyasimasuk.index', array_merge(request()->except('page','sort'), ['sort' => $nextSort])) }}"
               class="btn btn-outline-secondary btn-sm ms-2">
                Urutkan No Jual {!! $icon !!}
            </a>
        </form>
        <div>
            <a href="{{ route('jualkonsinyasimasuk.create') }}" class="btn btn-primary btn-sm" title="Tambah Data">
                Tambah Data
            </a>
        </div>
    </div>
    <table class="table table-bordered table-striped align-middle text-center">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>No Jual</th>
                <th>Tanggal Jual</th>
                <th>Pelanggan</th>
                <th>Total Jual</th>
                <th>Produk Konsinyasi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($penjualanKonsinyasi as $i => $p)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $p->no_jual }}</td>
                <td>{{ $p->tanggal_jual }}</td>
                <td>{{ $p->pelanggan->nama_pelanggan ?? '-' }}</td>
                <td>Rp{{ number_format($p->total_jual,0,',','.') }}</td>
                <td>
                    <ul class="mb-0" style="list-style: none; padding-left:0;">
                        @foreach($p->details->where('kode_produk', 'like', 'PKM%') as $d)
                            <li>{{ $d->nama_produk ?? ($d->produk->nama_produk ?? '-') }} ({{ $d->jumlah }})</li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    @if($p->status_pembayaran == 'lunas')
                        <span class="badge bg-success">Lunas</span>
                    @else
                        <span class="badge bg-warning text-dark">Belum Lunas</span>
                    @endif
                </td>
                <td class="d-flex justify-content-center gap-1">
                    <a href="{{ route('jualkonsinyasimasuk.show', $p->no_jual) }}" class="btn btn-info btn-sm" title="Detail">
                        <i class="bi bi-eye"></i>
                    </a>
                    @if($p->status_pembayaran != 'lunas')
                    <a href="{{ route('jualkonsinyasimasuk.edit', $p->no_jual) }}" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @endif
                    <a href="{{ route('jualkonsinyasimasuk.cetak', $p->no_jual) }}" class="btn btn-success btn-sm" title="Cetak" target="_blank">
                        <i class="bi bi-printer"></i>
                    </a>
                    <form action="{{ route('jualkonsinyasimasuk.destroy', $p->no_jual) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">Data penjualan konsinyasi belum ada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
