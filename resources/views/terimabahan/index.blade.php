@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    {{-- Baris 1: Judul & Search --}}
    <div class="row align-items-center mb-3">
        <div class="col-md-6 col-12">
            <h4 class="mb-0 fw-semibold text-md-start text-center">Daftar Penerimaan Bahan</h4>
        </div>
        <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
            <form method="GET" action="{{ route('terimabahan.index') }}" class="d-flex justify-content-md-end justify-content-start gap-2 flex-wrap">
                <input type="text" name="search"
                       class="form-control form-control-sm"
                       placeholder="Cari No. Terima / Nama Supplier..."
                       value="{{ request('search') }}"
                       style="max-width: 250px;">
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-search"></i> Cari
                </button>
            </form>
        </div>
    </div>

    {{-- Baris 2: Filter Periode & Tombol Laporan --}}
    @php
        use Carbon\Carbon;
        $now = Carbon::now();
        $tanggal_mulai = request('tanggal_mulai') ?? $now->copy()->startOfMonth()->format('Y-m-d');
        $tanggal_selesai = request('tanggal_selesai') ?? $now->copy()->endOfMonth()->format('Y-m-d');
    @endphp
    <div class="row align-items-center mb-3">
        <div class="col-md-8 col-12 mb-2 mb-md-0">
            <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                {{-- Hidden untuk mempertahankan search saat submit --}}
                <input type="hidden" name="search" value="{{ request('search') }}">
                <label class="mb-0">Periode:</label>
                <input type="date" name="tanggal_mulai" value="{{ $tanggal_mulai }}" class="form-control form-control-sm w-auto" onchange="this.form.submit()">
                <span class="mb-0">s.d.</span>
                <input type="date" name="tanggal_selesai" value="{{ $tanggal_selesai }}" class="form-control form-control-sm w-auto" onchange="this.form.submit()">
            
                    <select name="status" class="form-control form-control-sm w-auto" onchange="this.form.submit()">
        <option value="">-- Semua Status --</option>
        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
        <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Lanjutkan Pembayaran</option>
    </select>
            </form>
        </div>
        <div class="col-md-4 col-12 text-end">
            <a href="{{ route('terimabahan.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Tambah Penerimaan Bahan
            </a>
            {{-- Tambahkan di bawah form filter tanggal --}}
            <a href="{{ route('terimabahan.laporan', [
                    'tanggal_mulai' => $tanggal_mulai,
                    'tanggal_selesai' => $tanggal_selesai,
                    'search' => request('search')
                ]) }}"
               class="btn btn-success btn-sm ms-2"
               target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Cetak Laporan
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center py-2" style="width:50px;">No</th>
                            <th class="text-center py-2" style="width:110px;">Kode Terima</th>
                            <th class="text-center py-2" style="width:130px;">Kode Referensi</th>
                            <th class="text-center py-2" style="width:110px;">Tanggal</th>
                            <th class="text-center py-2" style="width:180px;">Nama Supplier</th>
                            <th class="text-center py-2" style="width:220px;">Keterangan</th>
                            <th class="text-center py-2" style="width:110px;">Status</th>
                            <th class="text-center py-2" style="width:180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                            @php $no = 1; @endphp
    @forelse($terimabahan as $item)
        @if($item)
        <tr>
            <td class="text-center py-2">{{ $no++ }}</td>
                                <td class="text-center py-2">{{ $item->no_terima_bahan ?? '-' }}</td>
                                <td class="text-center py-2">{{ $item->no_order_beli ?? $item->no_pembelian ?? '-' }}</td>
                                <td class="text-center py-2">{{ $item->tanggal_terima ?? '-' }}</td>
                                <td class="text-start py-2">{{ $item->nama_supplier ?? '-' }}</td>
                                <td class="text-start py-2">
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
                                <td class="text-center py-2">
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
                                <td class="text-center py-2">
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <a href="{{ route('terimabahan.show', $item->no_terima_bahan) }}" class="btn btn-info btn-sm" title="Detail">
                                            <i class="bi bi-info-circle"></i>
                                        </a>
                                        @if(!$sudahPembelian)
                                            <a href="{{ route('terimabahan.edit', $item->no_terima_bahan) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
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
                                <td colspan="7" class="text-center py-2">Data tidak tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection