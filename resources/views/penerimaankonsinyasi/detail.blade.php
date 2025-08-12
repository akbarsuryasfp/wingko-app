@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4>DETAIL PENERIMAAN KONSINYASI</h4>
            <table class="table">
                <tr>
                    <th>No Penerimaan Konsinyasi</th>
                    <td>{{ $header->no_penerimaankonsinyasi ?? '-' }}</td>
                </tr>
                <tr>
                    <th>No Konsinyasi Keluar</th>
                    <td>{{ $header->no_konsinyasikeluar ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Nama Consignee (Mitra)</th>
                    <td>{{ $header->consignee->nama_consignee ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Tanggal Terima</th>
                    <td>{{ $header->tanggal_terima ? \Carbon\Carbon::parse($header->tanggal_terima)->format('d-m-Y') : '-' }}</td>
                </tr>
                <tr>
                    <th>Metode Pembayaran</th>
                    <td>{{ $header->metode_pembayaran ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Total Terima</th>
                    <td>Rp{{ number_format($header->total_terima,0,',','.') }}</td>
                </tr>
                <tr>
                    <th>Keterangan</th>
                    <td>{{ $header->keterangan ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Bukti Pembayaran</th>
                    <td>
                        @if(!empty($header->bukti))
                            <a href="{{ asset('uploads/' . $header->bukti) }}" target="_blank" class="btn btn-sm btn-primary">Lihat Bukti</a>
                            <span class="text-muted ms-2">({{ $header->bukti }})</span>
                        @else
                            <span class="text-danger">Belum ada bukti</span>
                        @endif
                    </td>
                </tr>
            </table>

            <h5 class="text-center">DETAIL PRODUK PENERIMAAN KONSINYASI</h5>
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Nama Produk</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Jumlah Setor</th>
                        <th class="text-center">Jumlah Terjual</th>
                        <th class="text-center">Harga/Satuan</th>
                        <th class="text-center">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($header->details as $i => $d)
                    <tr>
                        <td class="text-center">{{ $i+1 }}</td>
                        <td class="text-center">{{ $d->produk->nama_produk ?? $d->nama_produk ?? '-' }}</td>
                        <td class="text-center">{{ $d->satuan }}</td>
                        <td class="text-center">{{ $d->jumlah_setor }}</td>
                        <td class="text-center">{{ $d->jumlah_terjual }}</td>
                        <td class="text-center">Rp{{ number_format($d->harga_satuan,0,',','.') }}</td>
                        <td class="text-center">Rp{{ number_format($d->subtotal,0,',','.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada detail produk</td>
                    </tr>
                    @endforelse
                    <tr>
                        <td colspan="6" class="text-end fw-bold">Total Terima</td>
                        <td class="fw-bold text-center">Rp{{ number_format($header->total_terima,0,',','.') }}</td>
                    </tr>
                </tbody>
            </table>
            <a href="{{ route('penerimaankonsinyasi.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
</div>
@endsection
