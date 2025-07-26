@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">Daftar Pembayaran Consignor (Pemilik Barang)</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0 d-flex justify-content-md-end justify-content-center gap-2">
                    <a href="{{ route('bayarconsignor.cetak_laporan') . '?' . http_build_query(request()->all()) }}" target="_blank" class="btn btn-sm btn-success d-flex align-items-center gap-2">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </a>
                    <a href="{{ route('bayarconsignor.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle"></i> Tambah Konsinyasi Masuk
                    </a>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-md-8 col-12 text-md-start text-start mb-2 mb-md-0">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap w-100 mt-1 justify-content-start">
                        <span class="fw-semibold">Periode:</span>
                        <input type="date" name="tanggal_awal" class="form-control form-control-sm w-auto" value="{{ request('tanggal_awal') }}">
                        <span class="mx-1">s/d</span>
                        <input type="date" name="tanggal_akhir" class="form-control form-control-sm w-auto" value="{{ request('tanggal_akhir') }}">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-funnel"></i> Terapkan
                        </button>
                        @php
                            $sort = request('sort', 'asc');
                            $nextSort = $sort === 'asc' ? 'desc' : 'asc';
                            $icon = $sort === 'asc' ? '▲' : '▼';
                        @endphp
                        <a href="{{ route('bayarconsignor.index', array_merge(request()->except('page'), ['sort' => $nextSort])) }}"
                           class="btn btn-sm btn-outline-secondary ms-2">
                            Urutkan No Bayar Consignor {!! $icon !!}
                        </a>
                    </form>
                </div>
                <div class="col-md-4 col-12 text-md-end text-start">
                    <form method="GET" action="{{ route('bayarconsignor.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari No Konsinyasi/Nama Consig" value="{{ request('search') }}" style="max-width: 220px;" autocomplete="off">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle table-sm">
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
                            <td class="text-center align-middle">{{ \Carbon\Carbon::parse($row->tanggal_bayar)->format('d-m-Y') }}</td>
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
