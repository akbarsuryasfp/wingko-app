@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detail Hutang</h3>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr>
                    <th width="25%">No Utang</th>
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
                    <td><span class="text-nowrap">Rp{{ number_format($hutang->total_tagihan, 0, ',', '.') }}</span></td>
                </tr>
                <tr>
                    <th>Total Bayar</th>
                    <td><span class="text-nowrap">Rp{{ number_format($hutang->total_bayar, 0, ',', '.') }}</span></td>
                </tr>
                <tr>
                    <th>Sisa Utang</th>
                    <td><span class="text-nowrap">Rp{{ number_format($hutang->sisa_utang, 0, ',', '.') }}</span></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if ($hutang->sisa_utang == 0)
                            <span class="badge bg-success">Lunas</span>
                        @else
                            <span class="badge bg-warning text-dark">Belum Lunas</span>
                        @endif
                    </td>
                </tr>
            </table>
            
<div class="card mt-4" style="width: 60%;">
    <div class="card-header">
        <h5 class="card-title mb-0">Daftar Pembayaran Utang</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">No BKK</th>
                        <th width="10%">Tanggal</th>
                        <th width="15%">Nominal</th>
                        <th width="8%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $pembayaran = \DB::table('t_jurnal_umum as ju')
                            ->join('t_jurnal_detail as jd', function($join) {
                                $join->on('ju.no_jurnal', '=', 'jd.no_jurnal')
                                     ->where('jd.kode_akun', '2000')
                                     ->where('jd.debit', '>', 0);
                            })
                            ->where('ju.keterangan', 'like', $hutang->no_utang . ' |%')
                            ->orderBy('ju.tanggal', 'asc')
                            ->select('ju.nomor_bukti', 'ju.tanggal', 'jd.debit as jumlah', 'ju.no_jurnal')
                            ->get();
                    @endphp
                    @forelse($pembayaran as $key => $bayar)
                        <tr>
                            <td class="text-center">{{ $key + 1 }}</td>
                            <td class="text-center">{{ $bayar->nomor_bukti }}</td>
                            <td class="text-center">{{ $bayar->tanggal }}</td>
                            <td class="text-end">
                                <span class="text-nowrap">Rp{{ number_format($bayar->jumlah, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('hutang.editPembayaran', ['no_utang' => $hutang->no_utang, 'no_jurnal' => $bayar->no_jurnal]) }}"
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('hutang.hapusPembayaran', ['no_utang' => $hutang->no_utang, 'no_jurnal' => $bayar->no_jurnal]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus pembayaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada pembayaran</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

            
            <div class="mt-3">
                <a href="{{ route('hutang.index') }}" class="btn btn-secondary"> ← Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection