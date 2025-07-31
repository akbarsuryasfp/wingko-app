@extends('layouts.app')

@section('content')
<style>
    .table-fixed-height thead th,
    .table-fixed-height tbody td {
        height: 35px;
        vertical-align: middle;
        padding-top: 0;
        padding-bottom: 0;
    }
    .card {
        border-radius: 8px;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .action-buttons {
        display: flex;
        gap: 0.25rem;
        justify-content: center;
    }
</style>

<div class="container-fluid px-3">
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

<div class="card shadow-sm">
    <div class="card-body">
        @php
            $now = Carbon\Carbon::now();
            $tanggal_mulai = request('tanggal_mulai') ?? $now->copy()->startOfMonth()->format('Y-m-d');
            $tanggal_selesai = request('tanggal_selesai') ?? $now->copy()->endOfMonth()->format('Y-m-d');
        @endphp

        {{-- Header --}}
        <div class="row align-items-center mb-2">
            <div class="col-md-6 col-12 text-md-start text-center">
                <h4 class="mb-0 fw-semibold">Daftar Penerimaan Bahan</h4>
            </div>
            <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
                <div class="d-flex justify-content-md-end justify-content-center gap-2 flex-wrap">
<a href="{{ route('terimabahan.laporan', request()->all()) }}"
                    class="btn btn-sm btn-success"
                    target="_blank">
                        <i class="bi bi-file-earmark-pdf"></i> Cetak Laporan
                    </a>
                    <a href="{{ route('terimabahan.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Penerimaan Bahan
                    </a>
                </div>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="row align-items-end mb-3">
            <div class="col-md-8 col-12 mb-2 mb-md-0">
                <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="small">Periode:</span>
                    <input type="date" name="tanggal_mulai"
                           value="{{ $tanggal_mulai }}"
                           class="form-control form-control-sm"
                           style="width: 140px;"
                           onchange="this.form.submit()">
                    <span class="small">s.d.</span>
                    <input type="date" name="tanggal_selesai"
                           value="{{ $tanggal_selesai }}"
                           class="form-control form-control-sm"
                           style="width: 140px;"
                           onchange="this.form.submit()">
                    <select name="status" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Lanjutkan Pembayaran</option>
                    </select>
                </form>
            </div>
            <div class="col-md-4 col-12 text-md-end text-center">
                <form method="GET" action="{{ route('terimabahan.index') }}" class="d-inline-flex gap-2 flex-wrap justify-content-end w-100">
                    <input type="text" name="search"
                           class="form-control form-control-sm"
                           placeholder="Cari No. Terima / Supplier"
                           value="{{ request('search') }}"
                           style="max-width: 250px;">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-search"></i> Cari
                        </button>
                                                            @if(request('search'))
        <a href="{{ route('terimabahan.index', array_merge(request()->except('search'))) }}"
           class="btn btn-sm btn-outline-danger" title="Reset">
            <i class="bi bi-x"></i>
        </a>
        @endif
                </form>
            </div>
        </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30px;" class="text-center align-middle">No</th>
                            <th style="width: 120px;" class="text-center align-middle">Kode Terima</th>
                            <th style="width: 130px;" class="text-center align-middle">Kode Referensi</th>
                            <th style="width: 90px;" class="text-center align-middle">Tanggal</th>
                            <th style="width: 180px;" class="text-center align-middle">Nama Supplier</th>
                            <th style="width: 220px;" class="text-start align-middle">Keterangan</th>
                            <th style="width: 110px;" class="text-center align-middle">Status</th>
                            <th style="width: 180px;" class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @forelse($terimabahan as $item)
                            @if($item)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $item->no_terima_bahan ?? '-' }}</td>
                                <td>{{ $item->no_order_beli ?? $item->no_pembelian ?? '-' }}</td>
                                <td>{{ $item->tanggal_terima ?? '-' }}</td>
                                <td class="text-start">{{ $item->nama_supplier ?? '-' }}</td>
                                <td class="text-start">
                                    @if($item->details && count($item->details))
                                        @php
                                            $keterangan = [];
                                            foreach($item->details as $detail) {
                                                if ($detail->bahan_masuk > 0) {
                                                    $keterangan[] = ($detail->nama_bahan ?? $detail->kode_bahan) . ' diterima ' . $detail->bahan_masuk;
                                                }
                                            }
                                            echo count($keterangan) ? implode(', ', $keterangan) : '<em>Tidak ada detail</em>';
                                        @endphp
                                    @else
                                        <em>Tidak ada detail</em>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $sudahPembelian = \DB::table('t_pembelian')
                                            ->where('no_terima_bahan', $item->no_terima_bahan)
                                            ->exists();
                                    @endphp
                                    @if($sudahPembelian)
                                        <span class="badge bg-success">Selesai</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Lanjutkan Pembayaran</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('terimabahan.show', $item->no_terima_bahan) }}" class="btn btn-info btn-sm" title="Detail">
                                            <i class="bi bi-info-circle"></i>
                                        </a>
                                        @if(!$sudahPembelian)
                                            <a href="{{ route('terimabahan.edit', $item->no_terima_bahan) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('terimabahan.destroy', $item->no_terima_bahan) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                            <a href="{{ route('pembelian.create', ['terima' => $item->no_terima_bahan]) }}" class="btn btn-success btn-sm" title="Pembayaran">
                                                <i class="bi bi-cash-coin"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Data tidak tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection