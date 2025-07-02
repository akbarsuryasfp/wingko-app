@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center">DAFTAR CONSIGNOR (PEMILIK BARANG)</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('consignor.create') }}" class="btn btn-primary mb-3">Tambah Consignor</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Consignor</th>
                <th>Nama Consignor</th>
                <th>Alamat</th>
                <th>No. Telepon</th>
                <th>Rekening</th> <!-- Ubah label -->
                <th class="text-center align-middle">Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consignor as $item)
                <tr>
                    <td>{{ $item->kode_consignor }}</td>
                    <td>{{ $item->nama_consignor }}</td>
                    <td>{{ $item->alamat }}</td>
                    <td>{{ $item->no_telp }}</td>
                    <td>{{ $item->rekening }}</td> <!-- Ubah field -->
                    <td class="text-center align-middle">
                        @if($item->produkKonsinyasi->count())
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailProdukModal{{ $item->kode_consignor }}" title="Detail Produk">
                                <i class="bi bi-eye"></i>
                            </button>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-2">
                            <!-- Button Tambah Produk Konsinyasi Masuk -->
                            <a href="{{ route('produk-konsinyasi.create', ['kode_consignor' => $item->kode_consignor]) }}"
                               class="btn btn-success btn-sm" title="Tambah Produk Konsinyasi Masuk">
                                <i class="bi bi-plus-circle"></i>
                            </a>
                            <!-- Edit -->
                            <a href="{{ route('consignor.edit', $item->kode_consignor) }}" class="btn btn-warning btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <!-- Hapus -->
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
                    <td colspan="7" class="text-center">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
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