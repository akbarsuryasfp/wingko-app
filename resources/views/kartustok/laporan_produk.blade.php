@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Laporan Stok Akhir Produk per {{ $tanggal }}</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Produk</th>
                <th>Nama Produk</th>
                <th>Satuan</th>
                <th>Stok Gudang</th>
                <th>Stok Toko 1</th>
                <th>Stok Toko 2</th>
                <th>Stok Akhir</th>
                <th>Status</th> <!-- Tambahkan kolom status -->
            </tr>
        </thead>
        <tbody>
            @foreach($produkList as $i => $produk)
            @php
                $gudangList = $produk->stok_akhir->where('lokasi', 'Gudang');
                $toko1List  = $produk->stok_akhir->where('lokasi', 'Toko 1');
                $toko2List  = $produk->stok_akhir->where('lokasi', 'Toko 2');
                $totalStok = $produk->stok_akhir->sum('stok');
                $stokmin = $produk->stokmin ?? 0;
                // Status: jika stok akhir <= stokmin, perlu produksi
                $status = ($totalStok <= $stokmin) ? 'Perlu Produksi' : 'Aman';
            @endphp
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $produk->kode_produk }}</td>
                <td>{{ $produk->nama_produk }}</td>
                <td>{{ $produk->satuan }}</td>
                <td>
                    @forelse($gudangList as $row)
                        <div>{{ $row->stok }} @ Rp{{ number_format($row->hpp, 0, ',', '.') }}</div>
                    @empty
                        <span class="text-danger">-</span>
                    @endforelse
                </td>
                <td>
                    @forelse($toko1List as $row)
                        <div>{{ $row->stok }} @ Rp{{ number_format($row->hpp, 0, ',', '.') }}</div>
                    @empty
                        <span class="text-danger">-</span>
                    @endforelse
                </td>
                <td>
                    @forelse($toko2List as $row)
                        <div>{{ $row->stok }} @ Rp{{ number_format($row->hpp, 0, ',', '.') }}</div>
                    @empty
                        <span class="text-danger">-</span>
                    @endforelse
                </td>
                <td>
                    @if($totalStok > 0)
                        @php
                            // Gabungkan stok akhir dengan HPP yang sama
                            $grouped = $produk->stok_akhir
                                ->groupBy(function($item) {
                                    return $item->hpp;
                                });
                        @endphp
                        @foreach($grouped as $hpp => $rows)
                            <div>{{ $rows->sum('stok') }} @ Rp{{ number_format($hpp, 0, ',', '.') }}</div>
                        @endforeach
                    @else
                        <span class="text-danger">Kosong</span>
                    @endif
                </td>
                <td>
                    @if($status == 'Aman')
                        <span class="badge bg-success">Aman</span>
                    @else
                        <span class="badge bg-danger">Perlu Produksi</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection