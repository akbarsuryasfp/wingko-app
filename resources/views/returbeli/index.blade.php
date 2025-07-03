@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">DAFTAR RETUR PEMBELIAN BAHAN</h4>


    @php
        use Carbon\Carbon;
        $now = Carbon::now();
        $tanggal_mulai = request('tanggal_mulai') ?? $now->copy()->startOfMonth()->format('Y-m-d');
        $tanggal_selesai = request('tanggal_selesai') ?? $now->copy()->endOfMonth()->format('Y-m-d');
    @endphp

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <label class="mb-0">Periode:</label>
            <input type="date" name="tanggal_mulai" value="{{ $tanggal_mulai }}" class="form-control form-control-sm w-auto" onchange="this.form.submit()">
            <span class="mb-0">s.d.</span>
            <input type="date" name="tanggal_selesai" value="{{ $tanggal_selesai }}" class="form-control form-control-sm w-auto" onchange="this.form.submit()">

            <a href="{{ route('returbeli.laporan.pdf', [
                    'tanggal_mulai' => $tanggal_mulai,
                    'tanggal_selesai' => $tanggal_selesai
                ]) }}"
               class="btn btn-success btn-sm d-flex align-items-center ms-2"
               target="_blank">
                <i class="bi bi-file-earmark-pdf me-1"></i> Cetak PDF
            </a>
        </form>
        <a href="{{ route('returbeli.create') }}" class="btn btn-primary btn-sm">Tambah Data</a>
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
                <?php $no = 1; ?>
                @forelse ($returList as $retur)
                    @foreach($retur->details as $index => $detail)
                        <tr>
                            @if($index === 0)
                                <td rowspan="{{ $retur->details->count() }}">{{ $no++ }}</td>
                                <td rowspan="{{ $retur->details->count() }}">{{ $retur->no_retur_beli }}</td>
                                <td rowspan="{{ $retur->details->count() }}">{{ $retur->no_pembelian }}</td>
                                <td rowspan="{{ $retur->details->count() }}">{{ $retur->tanggal_retur_beli }}</td>
                                <td rowspan="{{ $retur->details->count() }}">{{ $retur->nama_supplier }}</td>
                            @endif

                            {{-- Detail Produk --}}
                            <td>
                                <b>{{ $detail->nama_bahan }}</b> ({{ $detail->jumlah_retur }}) {{ $detail->alasan }}
                            </td>

                            @if($index === 0)
                                <td rowspan="{{ $retur->details->count() }}">
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
                            @endif
                        </tr>
                    @endforeach
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
