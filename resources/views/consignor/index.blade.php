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
                    <h4 class="mb-0 fw-semibold">Daftar Consignor (Pemilik Barang)</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
                    <a href="{{ route('consignor.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Consignor
                    </a>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-md-12 text-md-end text-start">
                    <form method="GET" action="{{ route('consignor.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        <input type="text" name="search" id="searchConsignor" class="form-control form-control-sm" placeholder="Cari Nama Consignor..." value="{{ request('search') }}" style="max-width: 200px;" autocomplete="off">
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
                            <th class="text-center align-middle py-3" style="width:110px;">Kode Consignor</th>
                            <th class="text-center align-middle py-3" style="width:150px;">Nama Consignor</th>
                            <th class="text-center align-middle py-3" style="width:180px;">Alamat</th>
                            <th class="text-center align-middle py-3" style="width:120px;">No. Telepon</th>
                            <th class="text-center align-middle py-3" style="width:180px;">Rekening</th>
                            <th class="text-center align-middle py-3" style="width:110px;">Keterangan</th>
                            <th class="text-center align-middle py-3" style="width:110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consignor as $item)
                            <tr>
                                <td class="text-center py-3">{{ $item->kode_consignor }}</td>
                                <td class="text-center py-3">{{ $item->nama_consignor }}</td>
                                <td class="text-start py-3">{{ $item->alamat }}</td>
                                <td class="text-center py-3">{{ $item->no_telp }}</td>
                                <td class="text-center py-3">{{ $item->rekening }}</td>
                                <td class="text-center align-middle">
                                    @if($item->produkKonsinyasi->count())
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailProdukModal{{ $item->kode_consignor }}" title="Detail Produk">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center py-3">
                                    <div class="d-flex gap-2 flex-wrap align-items-center justify-content-center">
                                        <a href="{{ route('produk-konsinyasi.create', ['kode_consignor' => $item->kode_consignor]) }}" class="btn btn-success btn-sm" title="Tambah Produk Konsinyasi Masuk">
                                            <i class="bi bi-plus-circle"></i>
                                        </a>
                                        <a href="{{ route('consignor.edit', $item->kode_consignor) }}" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('consignor.destroy', $item->kode_consignor) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-3">Data tidak tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@foreach($consignor as $item)
@if($item->produkKonsinyasi->count())
<!-- Modal Detail Produk Konsinyasi -->
<div class="modal fade" id="detailProdukModal{{ $item->kode_consignor }}" tabindex="-1" aria-labelledby="detailProdukLabel{{ $item->kode_consignor }}" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailProdukLabel{{ $item->kode_consignor }}">Produk Konsinyasi: {{ $item->nama_consignor }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered text-center">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode Produk</th>
                    <th>Nama Produk</th>
                    <th>Satuan</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($item->produkKonsinyasi as $i => $produk)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $produk->kode_produk }}</td>
                    <td>{{ $produk->nama_produk }}</td>
                    <td>{{ $produk->satuan }}</td>
                    <td>{{ $produk->keterangan ?? '-' }}</td>
                    <td>
                        <div class="d-flex gap-2 justify-content-center">
                            <!-- Edit -->
                            <a href="{{ route('produk-konsinyasi.edit', $produk->kode_produk) }}" class="btn btn-warning btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <!-- Hapus -->
                            <form action="{{ route('produk-konsinyasi.destroy', $produk->kode_produk) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endif
@endforeach