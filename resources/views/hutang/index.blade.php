@extends('layouts.app')

@section('content')
<div class="container">
    <h3>📋 Daftar Hutang</h3>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>No Utang</th>
                <th>No Pembelian</th>
                <th>Supplier</th>
                <th>Total Tagihan</th>
                <th>Sisa Utang</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($hutangs as $hutang)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $hutang->no_utang }}</td>
                    <td>{{ $hutang->no_pembelian }}</td>
                    <td>
                        @php
                            $nama_supplier = \DB::table('t_supplier')->where('kode_supplier', $hutang->kode_supplier)->value('nama_supplier');
                            echo $nama_supplier ?? $hutang->kode_supplier;
                        @endphp
                    </td>
                    <td class="text-end">Rp{{ number_format($hutang->total_tagihan, 0, ',', '.') }}</td>
                    <td class="text-end">
                        @if ($hutang->sisa_utang == 0)
                            <span class="text-success">Rp0 (Lunas)</span>
                        @else
                            Rp{{ number_format($hutang->sisa_utang, 0, ',', '.') }}
                        @endif
                    </td>
                    <td>
                        @if ($hutang->sisa_utang == 0)
                            Lunas
                        @else
                            Belum Lunas
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('hutang.detail', $hutang->no_utang) }}" class="btn btn-info btn-sm">Detail</a>
                        @if ($hutang->sisa_utang > 0)
                            <a href="{{ route('hutang.bayar', $hutang->no_utang) }}" class="btn btn-success btn-sm">Pembayaran</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Belum ada data hutang.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
