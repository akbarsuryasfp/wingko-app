@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4>Data Kas Keluar</h4>
    <a href="{{ route('kaskeluar.create') }}" class="btn btn-primary mb-3">+ Tambah Kas Keluar</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>No BKK</th>
                <th>Tanggal</th>
                <th>Jenis Kas</th>
                <th>Kode Akun</th>
                <th>Jumlah</th>
                <th>Penerima</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($kaskeluar as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->no_BKK }}</td>
                <td>{{ $item->tanggal }}</td>
                <td>{{ ucfirst($item->jenis_kas) }}</td>
                <td>{{ $item->kode_akun }}</td>
                <td>Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                <td>
                    @php
                        $namaSupplier = null;
                        if (preg_match('/^S\d+/', $item->penerima)) {
                            $namaSupplier = \DB::table('t_supplier')->where('kode_supplier', $item->penerima)->value('nama_supplier');
                        }
                    @endphp
                    {{ $namaSupplier ?? $item->penerima }}
                </td>
                <td>{{ $item->keterangan }}</td>
                <td>
                    <a href="{{ route('kaskeluar.edit', $item->no_BKK) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('kaskeluar.destroy', $item->no_BKK) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Hapus data ini?')" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
