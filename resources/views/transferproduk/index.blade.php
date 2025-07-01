@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-700">Daftar Transfer Produk</h1>
        <a href="{{ route('transferproduk.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            + Transfer Baru
        </a>
    </div>

    <div class="bg-white shadow-md rounded overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-100 text-left text-sm uppercase text-gray-600">
                <tr>
                    <th class="px-4 py-3">No</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Dari Lokasi</th>
                    <th class="px-4 py-3">Ke Lokasi</th>
                    <th class="px-4 py-3">Jumlah Item</th>
                    <th class="px-4 py-3">Total Qty</th>
                    <th class="px-4 py-3">Petugas</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-sm text-gray-700">
                @forelse ($transfers as $index => $transfer)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($transfer->tanggal)->format('d/m/Y') }}</td>
                    <td class="px-4 py-2">{{ $transfer->lokasi_asal }}</td>
                    <td class="px-4 py-2">{{ $transfer->lokasi_tujuan }}</td>
                    <td class="px-4 py-2">{{ $transfer->jumlah_item }}</td>
                    <td class="px-4 py-2">{{ $transfer->total_qty }}</td>
                    <td class="px-4 py-2">{{ $transfer->petugas }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('transferproduk.show', $transfer->id) }}" class="text-blue-600 hover:underline">Detail</a>
                        |
                        <form action="{{ route('transferproduk.destroy', $transfer->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('Yakin hapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-gray-500">Belum ada data transfer produk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
