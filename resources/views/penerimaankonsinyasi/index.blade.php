@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">Daftar Penerimaan Konsinyasi</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0 d-flex justify-content-md-end justify-content-center gap-2">
                    <a href="{{ route('penerimaankonsinyasi.cetak_laporan_pdf') . '?' . http_build_query(request()->all()) }}" target="_blank" class="btn btn-sm btn-success d-flex align-items-center gap-2">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </a>
                    <a href="{{ route('penerimaankonsinyasi.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle"></i> Tambah Penerimaan
                    </a>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-md-8 col-12 text-md-start text-start mb-2 mb-md-0">
                    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap w-100 mt-1 justify-content-start">
                        @foreach(request()->except(['tanggal_awal','tanggal_akhir','page','sort','search']) as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endforeach
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
                        <a href="{{ route('penerimaankonsinyasi.index', array_merge(request()->except('page','sort'), ['sort' => $nextSort])) }}"
                           class="btn btn-sm btn-outline-secondary ms-2">
                            Urutkan No Penerimaan Konsinyasi {!! $icon !!}
                        </a>
                    </form>
                </div>
                <div class="col-md-4 col-12 text-md-end text-start">
                    <form method="GET" action="{{ route('penerimaankonsinyasi.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        <input type="text" name="search" id="searchPenerimaanKonsinyasi" class="form-control form-control-sm" placeholder="Cari No Terima/Nama Consignee..." value="{{ request('search') }}" style="max-width: 220px;" autocomplete="off">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center align-middle text-nowrap">No</th>
                        <th class="text-center align-middle text-nowrap" style="width:1%;white-space:nowrap;">
                            <div class="w-100 text-center">No Penerimaan<br>Konsinyasi</div>
                        </th>
                        <th class="text-center align-middle text-nowrap">No Konsinyasi Keluar</th>
                        <th class="text-center align-middle text-nowrap">Tanggal Terima</th>
                        <th class="text-center align-middle text-nowrap"><div class="w-100 text-center">Nama Consignee<br>(Mitra)</div></th>
                        <th class="text-center align-middle text-nowrap">Jumlah Terjual & Nama Produk</th>
                        <th class="text-center align-middle text-nowrap">Total Terima</th>
                        <th class="text-center align-middle text-nowrap" style="width:1%;white-space:nowrap;">Bukti Pembayaran</th>
                        <th class="text-center align-middle text-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody>
@forelse($penerimaanKonsinyasiList as $i => $item)
    @php
        $kodeConsignee = $item->kode_consignee;
        $noKonsinyasiKeluar = $item->no_konsinyasikeluar;
        $canRetur = false;


        $alreadyRetur = false;
        $adaReturTidakTerjual = false;
        if ($noKonsinyasiKeluar) {
            $returList = \App\Models\ReturConsignee::where('no_konsinyasikeluar', $noKonsinyasiKeluar)->get();
            foreach ($returList as $retur) {
                $details = \App\Models\ReturConsigneeDetail::where('no_returconsignee', $retur->no_returconsignee)->get();
                foreach ($details as $d) {
                    if (strtolower(trim($d->alasan ?? '')) === 'tidak terjual') {
                        $adaReturTidakTerjual = true;
                        break 2;
                    }
                }
            }
            $alreadyRetur = $returList->count() > 0;
        }

        if ($item->details && count($item->details)) {
            foreach ($item->details as $d) {
                $jumlahTerjual = $d->jumlah_terjual ?? 0;
                $jumlahSetor = $d->jumlah_setor ?? 0;
                // Tidak perlu cek retur, karena retur langsung ditukar
                $sisa = $jumlahSetor - $jumlahTerjual;
                if ($sisa > 0) {
                    $canRetur = true;
                    break;
                }
            }
        }
    @endphp
                        <tr>
                            <td class="text-center align-middle">{{ $i + 1 }}</td>
                            <td class="text-center align-middle">{{ $item->no_penerimaankonsinyasi }}</td>
                            <td class="text-center align-middle">{{ $item->no_konsinyasikeluar ?? '-' }}</td>
                            <td class="text-center align-middle">{{ $item->tanggal_terima ? \Carbon\Carbon::parse($item->tanggal_terima)->format('d-m-Y') : '-' }}</td>
                            <td class="text-center align-middle" style="max-width:70px; word-break:break-word;">
                                {{ $item->consignee->nama_consignee ?? '-' }}
                            </td>
                            <td class="text-center align-middle text-nowrap" style="white-space:nowrap;">
                                @if($item->details && count($item->details))
                                    <div class="d-flex flex-column align-items-center justify-content-center w-100">
                                    @foreach($item->details as $detail)
                                        <div class="text-center">
                                            <b>{{ $detail->jumlah_terjual ?? 0 }}</b> x {{ $detail->produk->nama_produk ?? '-' }}
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center align-middle">Rp{{ number_format($item->total_terima, 0, ',', '.') }}</td>
                            <td class="text-center align-middle">
                                @if(!empty($item->bukti))
                                    <a href="{{ asset('uploads/' . $item->bukti) }}" target="_blank" class="btn btn-sm btn-primary">Lihat Bukti</a>
                                @else
                                    <span class="text-danger">-</span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
    <div class="d-flex justify-content-center gap-1" style="min-width: 180px;">
    <a href="{{ route('penerimaankonsinyasi.show', $item->no_penerimaankonsinyasi) }}" class="btn btn-info btn-sm btn-icon-square" title="Detail">
        <i class="bi bi-eye"></i>
    </a>


    @if(!$alreadyRetur && !$adaReturTidakTerjual)
        <a href="{{ route('penerimaankonsinyasi.edit', $item->no_penerimaankonsinyasi) }}" class="btn btn-warning btn-sm btn-icon-square" title="Edit">
            <i class="bi bi-pencil"></i>
        </a>
    @endif

    @if($canRetur && !$adaReturTidakTerjual)
        <a href="{{ route('returconsignee.createReturTerima', [
            'no_konsinyasikeluar' => $noKonsinyasiKeluar,
            'kode_consignee' => $kodeConsignee,
            'nama_consignee' => $item->consignee->nama_consignee ?? '',
            'prefill_retur' => 1
        ]) }}" class="btn btn-dark btn-sm btn-icon-square" title="Retur">
            <i class="bi bi-arrow-counterclockwise"></i>
        </a>
    @endif

    <form action="{{ route('penerimaankonsinyasi.destroy', $item->no_penerimaankonsinyasi) }}" method="POST" style="display:inline-block; margin:0;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm btn-icon-square" title="Hapus">
            <i class="bi bi-trash"></i>
        </button>
    </form>
</div>

</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Data tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-dark.btn-icon-square { background: #111; color: #fff; border: none; }
</style>
<style>
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
.btn-icon-square i { margin: 0; }
.btn-icon-square:focus { box-shadow: 0 0 0 2px #aaa; }
</style>
@endpush