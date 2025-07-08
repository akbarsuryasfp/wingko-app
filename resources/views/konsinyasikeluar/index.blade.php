@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">DAFTAR KONSINYASI KELUAR</h4>
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('konsinyasikeluar.create') }}" class="btn btn-primary">Tambah Data</a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 15%;">No Konsinyasi Keluar</th>
                        <th style="width: 20%;">No Surat Konsinyasi Keluar</th>
                        <th style="width: 15%;">Tanggal Setor</th>
                        <th style="width: 25%;">Nama Consignee (Mitra)</th>
                        <th style="width: 10%;">Total Setor</th>
                        <th style="width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($konsinyasiKeluarList as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->kode_setor }}</td>
                            <td>{{ $item->no_surat ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal_setor)->format('d-m-Y') }}</td>
                            <td>{{ $item->consignee->nama_consignee ?? '-' }}</td>
                            <td>Rp{{ number_format($item->total_setor, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('konsinyasikeluar.show', $item->id) }}" class="btn btn-info btn-sm">Detail</a>
                                <a href="{{ route('konsinyasikeluar.edit', $item->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Data tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
