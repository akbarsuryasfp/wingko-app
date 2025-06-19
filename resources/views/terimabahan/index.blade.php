@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Daftar Penerimaan Bahan</h3>

    <a href="{{ route('terimabahan.create') }}" class="btn btn-primary mb-3">Tambah Penerimaan Bahan</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Bahan</th>
                <th>Kode Referensi</th>
                <th>Tanggal</th>
                <th>Nama Supplier</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($terimabahan as $item)
                <tr>
                    <td>{{ $item->no_terima_bahan }}</td>
                    <td>
                        {{-- No Referensi: no_order_beli jika ada, jika tidak pakai no_pembelian --}}
                        {{ $item->no_order_beli ?? $item->no_pembelian ?? '-' }}
                    </td>
                    <td>{{ $item->tanggal_terima }}</td>
                    <td>{{ $item->nama_supplier ?? '-' }}</td>
                    <td>
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
                    <td>
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
                    <td>
                        <a href="{{ route('terimabahan.show', $item->no_terima_bahan) }}" class="btn btn-info btn-sm">Detail</a>
                        @php
                            // Cek apakah sudah tercatat di pembelian
                            $sudahPembelian = \DB::table('t_pembelian')
                                ->where('no_terima_bahan', $item->no_terima_bahan)
                                ->exists();
                        @endphp
                        @if(!$sudahPembelian)
                            <a href="{{ route('terimabahan.edit', $item->no_terima_bahan) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form action="{{ route('terimabahan.destroy', $item->no_terima_bahan) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                            <a href="{{ route('pembelian.create', ['terima' => $item->no_terima_bahan]) }}" class="btn btn-success btn-sm">Pembayaran</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection