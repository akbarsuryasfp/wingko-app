@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Detail Hutang</h3>
    <table class="table">
        <tr>
            <th>No Utang</th>
            <td>{{ $hutang->no_utang }}</td>
        </tr>
        <tr>
            <th>No Pembelian</th>
            <td>{{ $hutang->no_pembelian }}</td>
        </tr>
        <tr>
            <th>Supplier</th>
            <td>
                @php
                    $nama_supplier = \DB::table('t_supplier')->where('kode_supplier', $hutang->kode_supplier)->value('nama_supplier');
                    echo $nama_supplier ?? $hutang->kode_supplier;
                @endphp
            </td>
        </tr>
        <tr>
    <th>Jatuh Tempo</th>
    <td>
        {{ $hutang->jatuh_tempo ? \Carbon\Carbon::parse($hutang->jatuh_tempo)->format('d-m-Y') : '-' }}
    </td>
</tr>
        <tr>
            <th>Total Tagihan</th>
            <td>Rp{{ number_format($hutang->total_tagihan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Total Bayar</th>
            <td>Rp{{ number_format($hutang->total_bayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Sisa Utang</th>
            <td>Rp{{ number_format($hutang->sisa_utang, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>
                @if ($hutang->sisa_utang == 0)
                    Lunas
                @else
                    Belum Lunas
                @endif
            </td>
        </tr>
    </table>
    <h5 class="mt-4">Daftar Pembayaran Utang</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>No BKK</th>
                <th>Tanggal</th>
                <th>Nominal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php
                $pembayaran = \DB::table('t_jurnal_umum as ju')
                    ->join('t_jurnal_detail as jd', function($join) {
                        $join->on('ju.no_jurnal', '=', 'jd.no_jurnal')
                             ->where('jd.kode_akun', '2000') // kode akun utang usaha
                             ->where('jd.debit', '>', 0);
                    })
                    ->where('ju.keterangan', 'like', $hutang->no_utang . ' |%')
                    ->orderBy('ju.tanggal', 'asc')
                    ->select('ju.nomor_bukti', 'ju.tanggal', 'jd.debit as jumlah', 'ju.no_jurnal')
                    ->get();
            @endphp
            @forelse($pembayaran as $key => $bayar)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $bayar->nomor_bukti }}</td>
                    <td>{{ $bayar->tanggal }}</td>
                    <td class="text-end">Rp{{ number_format($bayar->jumlah, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('hutang.editPembayaran', ['no_utang' => $hutang->no_utang, 'no_jurnal' => $bayar->no_jurnal]) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('hutang.hapusPembayaran', ['no_utang' => $hutang->no_utang, 'no_jurnal' => $bayar->no_jurnal]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus pembayaran ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Belum ada pembayaran</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ route('hutang.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection