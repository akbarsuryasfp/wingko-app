@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Detail Jadwal Produksi</h3>

<p><strong>Kode:</strong> {{ $jadwal->no_jadwal }}</p>
<p><strong>Tanggal:</strong> {{ $jadwal->tanggal_jadwal }}</p>
<p><strong>Keterangan:</strong> {{ $jadwal->keterangan }}</p>

    <hr>
    <h5>Produk yang Akan Diproduksi</h5>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Produk</th>
            <th>Jumlah</th>
            <th>Sumber</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($jadwal->details as $d)
        <tr>
            <td>{{ $d->produk->nama_produk ?? $d->kode_produk }}</td>
            <td>{{ $d->jumlah }}</td>
            <td>{{ $d->no_sumber }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

    <hr>

<h5>Estimasi Kebutuhan Bahan</h5>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Bahan</th>
            <th>Jumlah</th>
            <th>Satuan</th>
            <th>Stok</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse($kebutuhan as $bahan)
        <tr>
            <td>{{ $bahan['nama_bahan'] }}</td>
            <td>{{ $bahan['jumlah'] }}</td>
            <td>{{ $bahan['satuan'] }}</td>
            <td>{{ $bahan['stok'] }}</td>
            <td>
                @if($bahan['status'] == 'Cukup')
                    <span class="badge bg-success">Cukup</span>
                @else
                    <span class="badge bg-danger">Kurang</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">Tidak ada kebutuhan bahan</td>
        </tr>
        @endforelse
    </tbody>
</table>

    @php
        // Ambil bahan yang kurang
        $bahanKurang = collect($kebutuhan)->filter(fn($b) => ($b['status'] ?? '') === 'Kurang')->values();
    @endphp

    @if($bahanKurang->count())
        <form action="{{ route('orderbeli.create') }}" method="GET" id="orderBahanForm">
            <input type="hidden" name="bahan_kurang" id="bahan_kurang_input">
            <button type="button" class="btn btn-danger" onclick="orderBahan()">Order Bahan Kurang</button>
        </form>
        @php
            // Siapkan array bahan kurang untuk dikirim ke JS
            $bahanKurangArr = [];
            foreach ($kebutuhan as $kode_bahan => $b) {
                if (($b['status'] ?? '') === 'Kurang') {
                    $bahanKurangArr[] = [
                        'kode_bahan' => $kode_bahan,
                        'nama_bahan' => $b['nama_bahan'],
                        'satuan' => $b['satuan'],
                        'jumlah_beli' => $b['jumlah'] - ($b['stok'] ?? 0),
                    ];
                }
            }
        @endphp
        <script>
            function orderBahan() {
                const bahanKurang = @json($bahanKurangArr);
                document.getElementById('bahan_kurang_input').value = JSON.stringify(bahanKurang);
                document.getElementById('orderBahanForm').submit();
            }
        </script>
    @endif

    <a href="{{ route('produksi.create', ['jadwal' => $jadwal->no_jadwal]) }}" class="btn btn-primary mt-3">Proses Produksi</a>
</div>
@endsection
