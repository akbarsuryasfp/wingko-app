@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">Daftar Retur Consignor (Pemilik Barang)</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0 d-flex justify-content-md-end justify-content-center gap-2">
                    <a href="{{ route('returconsignor.cetak_laporan') . '?' . http_build_query(request()->all()) }}" target="_blank" class="btn btn-sm btn-success d-flex align-items-center gap-2">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </a>
                    <a href="{{ route('returconsignor.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle"></i> Tambah Retur Consignor
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
                        <a href="{{ route('returconsignor.index', array_merge(request()->except('page'), ['sort' => request('sort', 'asc') === 'asc' ? 'desc' : 'asc'])) }}"
                           class="btn btn-sm btn-outline-secondary ms-2">
                            Urutkan No Retur Consignor {!! request('sort', 'asc') === 'asc' ? '▲' : '▼' !!}
                        </a>
                    </form>
                </div>
                <div class="col-md-4 col-12 text-md-end text-start">
                    <form method="GET" action="{{ route('returconsignor.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari No Retur Consignor/Nama Consignor..." value="{{ request('search') }}" style="max-width: 220px;" autocomplete="off">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle table-sm">
        <thead class="table-light">
            <tr>
                <th class="text-center align-middle" style="width:50px;">No</th>
                <th class="text-center align-middle" style="width:220px;">No Retur Consignor</th>
                <th class="text-center align-middle" style="width:250px;">No Konsinyasi Masuk</th>
                <th class="text-center align-middle" style="width:160px;">Tanggal Retur</th>
                <th class="text-center align-middle" style="width:200px;">Nama Consignor (Pemilik Barang)</th>
                <th class="text-center align-middle" style="min-width:250px;">Jumlah Retur & Nama Produk</th>
                <th class="text-center align-middle" style="min-width:180px;">Alasan Retur</th>
                <th class="text-center align-middle" style="width:140px;">Total Retur</th>
                <th class="text-center align-middle" style="width:120px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($returconsignor as $idx => $rc)
                <tr>
                    <td class="text-center align-middle">{{ $idx + 1 }}</td>
                    <td class="text-center align-middle">{{ $rc->no_returconsignor }}</td>
                    <td class="text-center align-middle">{{ $rc->konsinyasimasuk->no_konsinyasimasuk ?? '-' }}</td>
                    <td class="text-center align-middle">{{ $rc->tanggal_returconsignor ? \Carbon\Carbon::parse($rc->tanggal_returconsignor)->format('d-m-Y') : '-' }}</td>
                    <td class="text-center align-middle">{{ $rc->consignor->nama_consignor ?? '-' }}</td>
                    <td class="text-center align-middle">
                        <ul class="list-unstyled mb-0">
                            @if($rc->details && count($rc->details))
                                @foreach($rc->details as $detail)
                                    <li>
                                        <span class="fw-semibold">{{ $detail->jumlah_retur }}</span> x
                                        {{
                                            $detail->produk && isset($detail->produk->nama_produk) && $detail->produk->nama_produk != ''
                                                ? $detail->produk->nama_produk
                                                : (\DB::table('t_produk_konsinyasi')->where('kode_produk', $detail->kode_produk)->value('nama_produk') ?? '-')
                                        }}
                                    </li>
                                @endforeach
                            @else
                                <li>-</li>
                            @endif
                        </ul>
                    </td>
                    <td class="text-center align-middle">
                        <ul class="list-unstyled mb-0">
                            @if($rc->details && count($rc->details))
                                @foreach($rc->details as $detail)
                                    <li>
                                        {{ $detail->alasan ?? '-' }}
                                    </li>
                                @endforeach
                            @else
                                <li>-</li>
                            @endif
                        </ul>
                    </td>
                    <td class="text-center align-middle">Rp{{ number_format($rc->total_nilai_retur, 0, ',', '.') }}</td>
                    <td>
                        <div class="d-flex flex-column gap-1 align-items-center justify-content-center" style="min-width: 140px;">
                            <div class="d-flex gap-1 mb-1">
                                <a href="{{ route('returconsignor.show', $rc->no_returconsignor) }}" class="btn btn-info btn-sm btn-icon-square" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('returconsignor.edit', $rc->no_returconsignor) }}" class="btn btn-warning btn-sm btn-icon-square" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </div>
                            <div class="d-flex gap-1">
                                <form action="{{ route('returconsignor.destroy', $rc->no_returconsignor) }}" method="POST" style="display:inline-block; margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-icon-square" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <a href="{{ route('returconsignor.cetak', $rc->no_returconsignor) }}" class="btn btn-success btn-sm btn-icon-square" title="Cetak Nota Retur Consignor (Pemilik Barang)" target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection

@push('styles')
<style>
th.text-center.align-middle, td.text-center.align-middle {
    padding-top: 20px !important;
    padding-bottom: 20px !important;
}
.btn-icon-square {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 1.1em;
    padding: 0;
    margin: 0;
    box-shadow: none;
}
.btn-sm.btn-icon-square {
    width: 32px;
    height: 32px;
    font-size: 1em;
    border-radius: 7px;
}
.btn-info.btn-icon-square { background: #0fd3ff; color: #111; border: none; }
.btn-warning.btn-icon-square { background: #ffc107; color: #111; border: none; }
.btn-danger.btn-icon-square { background: #f44336; color: #fff; border: none; }
.btn-success.btn-icon-square { background: #219653; color: #fff; border: none; }
/* pastikan icon printer tetap putih di tombol hijau */
.btn-success.btn-icon-square i { color: #fff; }
.btn-icon-square i { margin: 0; }
.btn-icon-square:focus { box-shadow: 0 0 0 2px #aaa; }
</style>
@endpush
