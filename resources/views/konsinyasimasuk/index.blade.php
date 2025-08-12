{{-- filepath: resources/views/konsinyasimasuk/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid px-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">Daftar Konsinyasi Masuk</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0 d-flex justify-content-md-end justify-content-center gap-2">
                    <a href="{{ route('konsinyasimasuk.cetak_laporan_pdf') . '?' . http_build_query(request()->all()) }}" target="_blank" class="btn btn-sm btn-success d-flex align-items-center gap-2">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </a>
                    <a href="{{ route('konsinyasimasuk.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle"></i> Tambah Konsinyasi Masuk
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
                        <a href="{{ route('konsinyasimasuk.index', array_merge(request()->except('page','sort'), ['sort' => $nextSort])) }}"
                           class="btn btn-sm btn-outline-secondary ms-2">
                            Urutkan No Konsinyasi Masuk {!! $icon !!}
                        </a>
                    </form>
                </div>
                <div class="col-md-4 col-12 text-md-end text-start">
                    <form method="GET" action="{{ route('konsinyasimasuk.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari No Konsinyasi/Nama Consignor..." value="{{ request('search') }}" style="max-width: 220px;" autocomplete="off">
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
                    <th class="text-center align-middle" style="width:40px;">No</th>
                    <th class="text-center align-middle" style="width:140px;">No Konsinyasi Masuk</th>
                    <th class="text-center align-middle" style="width:140px;">No Surat Titip Jual</th>
                    <th class="text-center align-middle" style="width:110px;">Tanggal Masuk</th>
                    <th class="text-center align-middle" style="width:180px;">Nama Consignor (Pemilik Barang)</th>
                    <th class="text-center align-middle" style="width:250px;">Jumlah Stok & Nama Produk</th>
                    <th class="text-center align-middle" style="width:120px;">Harga Titip/Satuan</th>
                    <th class="text-center align-middle" style="width:120px;">Harga Jual/Satuan</th>
                    <th class="text-center align-middle" style="width:120px;">Komisi/Satuan</th>
                    <th class="text-center align-middle" style="width:110px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($konsinyasiMasukList as $i => $konsinyasi)
                    <tr>
                        <td class="text-center align-middle">{{ $i + 1 }}</td>
                        <td class="text-center align-middle">{{ $konsinyasi->no_konsinyasimasuk ?? '-' }}</td>
                        <td class="text-center align-middle">{{ $konsinyasi->no_surattitipjual ?? '-' }}</td>
                        <td class="text-center align-middle">{{ \Carbon\Carbon::parse($konsinyasi->tanggal_titip ?? $konsinyasi->tanggal_masuk)->format('d-m-Y') }}</td>
                        <td class="text-center align-middle">{{ $konsinyasi->consignor->nama_consignor ?? '-' }}</td>
                        <td class="text-center align-middle">
                            @php $produkList = $konsinyasi->details; @endphp
                            @if($produkList && count($produkList))
                                @foreach($produkList as $produk)
                                    <div>
                                        <b>{{ $produk->jumlah_stok }}</b> x {{ $produk->produk->nama_produk ?? $produk->nama_produk ?? '-' }}
                                    </div>
                                @endforeach
                            @else - @endif
                        </td>
                        <td class="text-center align-middle">
                            @php $produkList = $konsinyasi->details; @endphp
                            @if($produkList && count($produkList))
                                @foreach($produkList as $produk)
                                    <div>
                                        Rp{{ number_format($produk->harga_titip, 0, ',', '.') }}
                                    </div>
                                @endforeach
                            @else - @endif
                        </td>
                        <td class="text-center align-middle">
                            @php $produkList = $konsinyasi->details; @endphp
                            @if($produkList && count($produkList))
                                @foreach($produkList as $produk)
                                    <div data-konsinyasi="{{ $konsinyasi->no_konsinyasimasuk }}" data-produk="{{ $produk->id }}">
                                        Rp{{ number_format($produk->harga_jual, 0, ',', '.') }}
                                    </div>
                                @endforeach
                            @else - @endif
                        </td>
                        <td class="text-center align-middle">
                            @php $produkList = $konsinyasi->details; @endphp
                            @if($produkList && count($produkList))
                                @foreach($produkList as $produk)
                                    <div>
                                        Rp{{ number_format(($produk->harga_jual ?? 0) - ($produk->harga_titip ?? 0), 0, ',', '.') }}
                                    </div>
                                @endforeach
                            @else - @endif
                        </td>
                        <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-1" style="min-width: 180px;">
                                <button type="button" class="btn btn-success btn-sm btn-icon-square" title="Input Harga Jual" onclick="openInputHargaJual('{{ $konsinyasi->no_konsinyasimasuk }}')">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                                <a href="{{ route('konsinyasimasuk.show', $konsinyasi->no_konsinyasimasuk ?? $konsinyasi->no_surattitipjual) }}" class="btn btn-info btn-sm btn-icon-square" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('konsinyasimasuk.edit', $konsinyasi->no_konsinyasimasuk ?? $konsinyasi->no_surattitipjual) }}" class="btn btn-warning btn-sm btn-icon-square" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('konsinyasimasuk.destroy', $konsinyasi->no_konsinyasimasuk ?? $konsinyasi->no_surattitipjual) }}" method="POST" style="display:inline-block; margin:0;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
                        <td colspan="10" class="text-center py-3">Data tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
            </div>
        </div>
    </div>
</div>
@include('konsinyasimasuk.create_komisi')

@push('scripts')
<script>

    // Misal di index.blade.php
function openInputHargaJual(noKonsinyasi) {
    fetch(`{{ url('/konsinyasimasuk') }}/${noKonsinyasi}/detail-json`)
        .then(res => {
            if (!res.ok) {
                return res.text().then(text => { throw new Error(text || 'Gagal mengambil data detail produk') });
            }
            return res.json();
        })
        .then(data => {
            if (data && data.length > 0) {
                console.log('openInputHargaJual - noKonsinyasi:', noKonsinyasi);
                console.log('openInputHargaJual - data:', data);
                showModalDetailProduk(data, noKonsinyasi);
            } else {
                alert('Tidak ada data produk ditemukan');
            }
        })
        .catch(err => {
            alert('Terjadi error: ' + err.message);
            console.error(err);
        });
}

// Fungsi untuk update harga jual produk via AJAX dan update kolom tanpa refresh
function updateHargaJualProduk(noKonsinyasi, produkId, hargaJualBaru) {
    fetch(`{{ url('/konsinyasimasuk') }}/${noKonsinyasi}/update-harga-jual`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ produk_id: produkId, harga_jual: hargaJualBaru })
    })
    .then(res => res.json())
    .then(response => {
        if (response.success) {
            // Update kolom harga jual/produk di tabel utama
            const selector = `div[data-konsinyasi='${noKonsinyasi}'][data-produk='${produkId}']`;
            const row = document.querySelector(selector);
            if (row) {
                row.innerHTML = 'Rp' + Number(hargaJualBaru).toLocaleString('id-ID');
            }
        } else {
            alert('Gagal update harga jual: ' + (response.message || ''));
        }
    })
    .catch(err => {
        alert('Terjadi error: ' + err.message);
        console.error(err);
    });
}

</script>
@endpush
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" />
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