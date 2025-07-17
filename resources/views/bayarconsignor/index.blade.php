@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">DAFTAR PEMBAYARAN CONSIGNOR (PEMILIK BARANG)</h4>
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
            <a href="{{ route('bayarconsignor.index', array_merge(request()->except('page'), ['sort' => $nextSort])) }}"
               class="btn btn-outline-secondary btn-sm ms-2">
                Urutkan No Bayar Consignor {!! $icon !!}
            </a>
        </form>
        <div>
            <a href="{{ route('bayarconsignor.create') }}" class="btn btn-primary btn-sm" title="Tambah Data">
                Tambah Pembayaran
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center align-middle" style="width: 5%;">No</th>
                        <th class="text-center align-middle">No Bayar Consignor</th>
                        <th class="text-center align-middle">Tanggal Bayar</th>
                        <th class="text-center align-middle">Nama Consignor (Pemilik Barang)</th>
                        <th class="text-center align-middle">Jumlah Terjual & Nama Produk</th>
                        <th class="text-center align-middle">Total Bayar</th>
                        <th class="text-center align-middle">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @forelse($list as $row)
                        @php
                            $consignor = null;
                            if (isset($row->kode_consignor)) {
                                $consignor = \DB::table('t_consignor')->where('kode_consignor', $row->kode_consignor)->first();
                            }
                        @endphp
                        <tr>
                            <td class="text-center align-middle">{{ $no++ }}</td>
                            <td class="text-center align-middle">{{ $row->no_bayarconsignor }}</td>
                            <td class="text-center align-middle">{{ $row->tanggal_bayar }}</td>
                            <td class="text-center align-middle">{{ $consignor->nama_consignor ?? '-' }}</td>
                            <td class="text-center align-middle">
                                @foreach($row->details as $detail)
                                    <div><b>{{ $detail->jumlah_terjual }}</b> x {{ $detail->produk->nama_produk ?? '-' }}</div>
                                @endforeach
                            </td>
                            <td class="text-center align-middle">Rp{{ number_format($row->total_bayar,0,',','.') }}</td>
                            <td class="text-center align-middle">
                                <a href="{{ route('bayarconsignor.show', $row->no_bayarconsignor) }}" class="btn btn-info btn-sm me-1" title="Detail" style="padding: 0.25rem 0.5rem; font-size: 1rem;">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if (\Route::has('bayarconsignor.cetak'))
                                <a href="{{ route('bayarconsignor.cetak', $row->no_bayarconsignor) }}" class="btn btn-success btn-sm me-1" title="Cetak" target="_blank" style="padding: 0.25rem 0.5rem; font-size: 1rem;">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @endif
                                <form action="{{ route('bayarconsignor.destroy', $row->no_bayarconsignor) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus" style="padding: 0.25rem 0.5rem; font-size: 1rem;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Tidak ada data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
