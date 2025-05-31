@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Daftar Penerimaan Bahan</h3>

    <a href="{{ route('terimabahan.create') }}" class="btn btn-primary mb-3">Tambah Penerimaan Bahan</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No Terima Bahan</th>
                <th>No Order Beli</th>
                <th>Tanggal Terima</th>
                <th>Kode Supplier</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($terimabahan as $item)
                <tr>
                    <td>{{ $item->no_terima_bahan }}</td>
                    <td>{{ $item->no_order_beli }}</td>
                    <td>{{ $item->tanggal_terima }}</td>
                    <td>{{ $item->kode_supplier }}</td>
                    <td>
                        @if($item->details && count($item->details))
                            @foreach($item->details as $detail)
                                <div>
                                    {{ $detail->kode_bahan }} - Masuk: {{ $detail->bahan_masuk }}, Harga: {{ number_format($detail->harga_beli,0,',','.') }}, Exp: {{ $detail->tanggal_exp }}
                                </div>
                            @endforeach
                        @else
                            <em>Tidak ada detail</em>
                        @endif
                    </td>
                    <td>{{ $item->status ?? '-' }}</td>
                    <td>
                        <a href="{{ route('terimabahan.show', $item->no_terima_bahan) }}" class="btn btn-info btn-sm">Detail</a>
                        <a href="{{ route('terimabahan.edit', $item->no_terima_bahan) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('terimabahan.destroy', $item->no_terima_bahan) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
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