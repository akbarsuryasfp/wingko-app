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
                    <h4 class="mb-0 fw-semibold">Daftar Consignee (Mitra)</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
                    <a href="{{ route('consignee.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Consignee
                    </a>
                </div>
            </div>
            <div class="row align-items-center mb-3">
                <div class="col-md-12 text-md-end text-start">
                    <form method="GET" action="{{ route('consignee.index') }}" class="d-flex gap-2 justify-content-end flex-wrap">
                        <input type="text" name="search" id="searchConsignee" class="form-control form-control-sm" placeholder="Cari Nama Consignee..." value="{{ request('search') }}" style="max-width: 200px;" autocomplete="off">
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
                            <th class="text-center align-middle py-3" style="width:110px;">Kode Consignee</th>
                            <th class="text-center align-middle py-3" style="width:140px;">Kode Consignee Setor</th>
                            <th class="text-center align-middle py-3" style="width:150px;">Nama Consignee</th>
                            <th class="text-center align-middle py-3" style="width:180px;">Alamat</th>
                            <th class="text-center align-middle py-3" style="width:120px;">No. Telepon</th>
                            <th class="text-center align-middle py-3" style="width:180px;">Keterangan Setor Produk</th>
                            <th class="text-center align-middle py-3" style="width:110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consignee as $item)
                            <tr>
                                <td class="text-center py-3">{{ $item->kode_consignee }}</td>
                                <td class="text-center py-3">
                                    @php
                                        $kodeSetor = \DB::table('t_consignee_setor')
                                            ->where('kode_consignee', $item->kode_consignee)
                                            ->value('kode_consignee_setor');
                                    @endphp
                                    {{ $kodeSetor ?? '-' }}
                                </td>
                                <td class="text-center py-3">{{ $item->nama_consignee }}</td>
                                <td class="text-start py-3">{{ $item->alamat }}</td>
                                <td class="text-center py-3">{{ $item->no_telp }}</td>
                                <td class="text-start py-3">
                                    @php
                                        $setorList = \DB::table('t_consignee_setor')
                                            ->join('t_produk', 't_produk.kode_produk', '=', 't_consignee_setor.kode_produk')
                                            ->where('t_consignee_setor.kode_consignee', $item->kode_consignee)
                                            ->select('t_produk.nama_produk', 't_consignee_setor.jumlah_setor')
                                            ->get();
                                    @endphp
                                    @if($setorList->count())
                                        @foreach($setorList as $setor)
                                            <div>{{ $setor->nama_produk }}: <b>{{ $setor->jumlah_setor }}</b></div>
                                        @endforeach
                                    @else
                                        <span>-</span>
                                    @endif
                                </td>
                                <td class="text-center py-3">
                                    <a href="{{ route('consignee.edit', $item->kode_consignee) }}" class="btn btn-warning btn-sm me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('consignee.destroy', $item->kode_consignee) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
                                <td colspan="6" class="text-center py-3">Data tidak tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection