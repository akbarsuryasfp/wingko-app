@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">Daftar Penjualan</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0 d-flex flex-column align-items-md-end align-items-center gap-2">
                    <div class="d-flex flex-row gap-2 justify-content-md-end justify-content-center w-100">
                        <a href="{{ route('penjualan.cetak_laporan') . '?' . http_build_query(request()->all()) }}" target="_blank" class="btn btn-sm btn-success btn-icon-square d-inline-flex align-items-center gap-2" style="width: 140px; justify-content: center;">
                            <i class="bi bi-printer"></i> Cetak Laporan
                        </a>
                        <a href="{{ route('penjualan.create', ['jenis_penjualan' => 'langsung']) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Penjualan Langsung
                        </a>
                        <a href="{{ route('penjualan.createPesanan', ['jenis_penjualan' => 'pesanan']) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i> Pesanan Penjualan
                        </a>
                    </div>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-12">
                    <form method="GET" class="row gx-2 gy-2 align-items-center flex-wrap">
                        @foreach(request()->except(['tanggal_awal','tanggal_akhir','page','sort','search','jenis_penjualan','status_pembayaran']) as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endforeach
                        <div class="col-auto d-flex align-items-center">
                            <span class="fw-semibold me-2">Periode:</span>
                            <input type="date" name="tanggal_awal" class="form-control form-control-sm w-auto" value="{{ request('tanggal_awal') }}">
                            <span class="mx-1">s/d</span>
                            <input type="date" name="tanggal_akhir" class="form-control form-control-sm w-auto" value="{{ request('tanggal_akhir') }}">
                            <button type="submit" class="btn btn-sm btn-outline-secondary ms-2">
                                <i class="bi bi-funnel"></i> Terapkan
                            </button>
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <span class="fw-semibold me-2">Filter:</span>
                            <select name="jenis_penjualan" class="form-select form-select-sm w-auto me-2" onchange="this.form.submit()">
                                <option value="">Semua Jenis</option>
                                <option value="langsung" {{ request('jenis_penjualan') == 'langsung' ? 'selected' : '' }}>Langsung</option>
                                <option value="pesanan" {{ request('jenis_penjualan') == 'pesanan' ? 'selected' : '' }}>Pesanan</option>
                            </select>
                            <select name="status_pembayaran" class="form-select form-select-sm w-auto me-2" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="lunas" {{ request('status_pembayaran') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                <option value="belum lunas" {{ request('status_pembayaran') == 'belum lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            </select>
                            @php
                                $sort = request('sort', 'asc');
                                $nextSort = $sort === 'asc' ? 'desc' : 'asc';
                                $icon = $sort === 'asc' ? '▲' : '▼';
                            @endphp
                            <a href="{{ route('penjualan.index', array_merge(request()->except('page','sort'), ['sort' => $nextSort])) }}"
                               class="btn btn-sm btn-outline-secondary">
                                Urutkan No Jual {!! $icon !!}
                            </a>
                        </div>
                        <div class="col ms-auto">
                            <div class="d-flex justify-content-end">
                                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari No Jual/Nama Pelanggan..." value="{{ request('search') }}" style="max-width: 220px;" autocomplete="off">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle table-sm">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center align-middle py-2" style="width:40px;">No</th>
                            <th class="text-center align-middle py-2" style="width:120px;">No Jual</th>
                            <th class="text-center align-middle py-2" style="width:120px;">Tanggal Jual</th>
                            <th class="text-center align-middle py-2" style="width:120px;">Pelanggan</th>
                            <th class="text-center align-middle py-2" style="width:120px;">Total Harga</th>
                            <th class="text-center align-middle py-2" style="width:80px;">Diskon</th>
                            <th class="text-center align-middle py-2" style="width:120px;">Total Jual</th>
                            <th class="text-center align-middle py-2" style="width:120px;">Piutang</th>
                            <th class="text-center align-middle py-2" style="width:100px;">Metode</th>
                            <th class="text-center align-middle py-2" style="width:90px;">Status</th>
                            <th class="text-center align-middle py-2" style="width:180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penjualan as $no => $p)
                        <tr>
                            <td class="text-center py-1">{{ $no+1 }}</td>
                            <td class="text-center py-1">{{ $p->no_jual }}</td>
                            <td class="text-center py-1">{{ $p->tanggal_jual ? \Carbon\Carbon::parse($p->tanggal_jual)->format('d-m-Y') : '-' }}</td>
                            <td class="text-center py-1">{{ $p->pelanggan->nama_pelanggan ?? '-' }}</td>
                            <td class="text-center py-1">Rp{{ number_format($p->total_harga,0,',','.') }}</td>
                            <td class="text-center py-1">
                                @if(isset($p->tipe_diskon) && $p->tipe_diskon == 'persen')
                                    {{ $p->diskon }}%
                                @else
                                    Rp{{ number_format($p->diskon,0,',','.') }}
                                @endif
                            </td>
                            <td class="text-center py-1">Rp{{ number_format($p->total_jual,0,',','.') }}</td>
                            <td class="text-center py-1">
                                @if($p->status_pembayaran == 'belum lunas')
                                    <span style="color:#d90429; font-weight:bold;">
                                        Rp{{ number_format($p->piutang,0,',','.') }}
                                    </span>
                                @else
                                    Rp{{ number_format($p->piutang,0,',','.') }}
                                @endif
                            </td>
                            <td class="text-center py-1">{{ ucfirst($p->metode_pembayaran) }}</td>
                            <td class="text-center py-1">
                                @if($p->status_pembayaran == 'lunas')
                                    <span class="badge bg-success">Lunas</span>
                                @else
                                    <span class="badge bg-warning text-dark">Belum Lunas</span>
                                @endif
                            </td>
                            <td class="d-flex flex-wrap gap-1 justify-content-center align-items-center py-1">
                                <a href="{{ route('penjualan.show', $p->no_jual) }}" class="btn btn-info btn-sm" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($p->status_pembayaran != 'lunas')
                                    <a href="{{ route('penjualan.edit', $p->no_jual) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                @if($p->status_pembayaran == 'belum lunas')
                                    <a href="{{ route('penjualan.cetak_tagihan', $p->no_jual) }}" target="_blank" class="btn btn-dark btn-sm" title="Cetak Nota Tagihan">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                    @php
                                        $piutang = \App\Models\Piutang::where('no_jual', $p->no_jual)->first();
                                    @endphp
                                    @if($piutang)
                                        <a href="{{ route('piutang.bayar', $piutang->no_piutang) }}" class="btn btn-primary btn-sm" title="Pembayaran">
                                            <i class="bi bi-cash-coin"></i>
                                        </a>
                                    @endif
                                @endif
                                @if($p->status_pembayaran == 'lunas')
                                    <a href="{{ route('penjualan.cetak', $p->no_jual) }}" target="_blank" class="btn btn-success btn-sm" title="Cetak Nota Penjualan">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                @endif
                                <form action="{{ route('penjualan.destroy', $p->no_jual) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
                            <td colspan="11" class="text-center py-1">Data tidak tersedia.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection