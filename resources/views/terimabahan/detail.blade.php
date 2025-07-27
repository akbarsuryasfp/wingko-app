@extends('layouts.app')

@section('content')
<style>
    .card {
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 20px;
    }
    .table-detail th {
        background-color: #f8f9fa;
    }
    .info-table th {
        width: 180px;
    }
</style>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3>Detail Penerimaan Bahan</h3>
            
            @if($terima)
            <div class="mb-4">
                <table class="table table-bordered info-table">
                    <tr>
                        <th>No Terima Bahan</th>
                        <td>{{ $terima->no_terima_bahan }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Terima</th>
                        <td>{{ \Carbon\Carbon::parse($terima->tanggal_terima)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>No Order Beli</th>
                        <td>{{ $terima->no_order_beli }}</td>
                    </tr>
                    <tr>
                        <th>Supplier</th>
                        <td>{{ $terima->nama_supplier ?? '-' }}</td>
                    </tr>
                </table>
            </div>

            <hr>
            <h5>DAFTAR BAHAN DITERIMA</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-detail">
                    <thead class="text-center">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Kode Bahan</th>
                            <th>Nama Bahan</th>
                            <th>Satuan</th>
                            <th>Jumlah Diterima</th>
                            <th>Harga Beli</th>
                            <th>Total</th>
                            <th>Tanggal Exp</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($details as $i => $d)
                        <tr>
                            <td class="text-center">{{ $i+1 }}</td>
                            <td class="text-center">{{ $d->kode_bahan }}</td>
                            <td>{{ $d->nama_bahan ?? '-' }}</td>
                            <td class="text-center">{{ $d->satuan ?? '-' }}</td>
                            <td class="text-center">{{ number_format($d->bahan_masuk, 0, ',', '.') }} {{ $d->satuan ?? '' }}</td>
                            <td class="text-end">Rp {{ number_format($d->harga_beli, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
                            <td class="text-center">{{ $d->tanggal_exp ? \Carbon\Carbon::parse($d->tanggal_exp)->format('d/m/Y') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <a href="{{ route('terimabahan.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            @else
            <div class="alert alert-danger">Data tidak ditemukan.</div>
            @endif
        </div>
    </div>
</div>
@endsection