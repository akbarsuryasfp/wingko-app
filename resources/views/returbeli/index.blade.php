@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">DAFTAR RETUR PEMBELIAN BAHAN</h4>

    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('returbeli.create') }}" class="btn btn-primary">Tambah Data</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode Retur</th>
                    <th>Kode Pembelian</th>
                    <th>Tanggal Retur</th>
                    <th>Supplier</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($returList as $retur)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $retur->no_retur_beli }}</td>
                        <td>{{ $retur->no_pembelian }}</td>
                        <td>{{ $retur->tanggal_retur_beli }}</td>
                        <td>{{ $retur->nama_supplier }}</td>
                        <td>
                            @foreach($retur->details as $detail)
                                <b>{{ $detail->nama_bahan }}</b>
                                ({{ $detail->jumlah_retur }})<br>
                                Alasan: {{ $detail->alasan }}<br>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('returbeli.cetak', $retur->no_retur_beli) }}" class="btn btn-success btn-sm" title="Cetak" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>
                            <a href="{{ route('returbeli.show', $retur->no_retur_beli) }}" class="btn btn-info btn-sm" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('returbeli.edit', $retur->no_retur_beli) }}" class="btn btn-warning btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('returbeli.destroy', $retur->no_retur_beli) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus" onclick="return confirm('Yakin hapus?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Belum ada data retur pembelian bahan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
