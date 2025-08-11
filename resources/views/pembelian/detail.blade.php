@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow mb-4">
        <style>
    table td, table th {
        padding-top: 0.25rem !important;
        padding-bottom: 0.25rem !important;
        vertical-align: middle !important;
    }

    .input-group-text {
        padding: 0.25rem 0.5rem !important;
    }

    .form-control.p-1 {
        padding: 0.25rem !important;
        min-height: auto !important;
        line-height: 1.2;
    }
        .table .input-group-text {
        font-size: 1 rem;
        padding: 0.15rem 0.4rem;
    }

    .table td span.small {
        font-size: 1 rem;
        line-height: 1;
    }
</style>

        <div class="card-header py-3">
            <h5 class="m-0 font-weight-bold">Detail Pembelian</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Informasi Pembelian -->
                <div class="col-md-6">
                    
                        <div class="card-body">
                            <div class="row mb-2">
                                <label class="col-sm-4 col-form-label">Kode Pembelian</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control bg-light" value="{{ $pembelian->no_pembelian }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <label class="col-sm-4 col-form-label">Tanggal Pembelian</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control bg-light" value="{{ $pembelian->tanggal_pembelian }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <label class="col-sm-4 col-form-label">No Nota</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control bg-light" value="{{ $pembelian->no_nota }}" readonly>
                                </div>
                            </div>
                           @if($pembelian->bukti_nota)
    <a href="{{ asset('storage/' . $pembelian->bukti_nota) }}" 
       target="_blank"
       class="btn btn-sm btn-outline-primary"
       title="Lihat Bukti Nota">
       <i class="fas fa-file-alt me-1"></i> Lihat Nota
    </a>
@else
    <span class="badge bg-light text-dark">-</span>
@endif
                        </div>
                  
                </div>

                <div class="col-md-6">
                   
                        <div class="card-body">
                            <div class="row mb-2">
                                <label class="col-sm-4 col-form-label">Kode Terima Bahan</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control bg-light" value="{{ $pembelian->no_terima_bahan }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <label class="col-sm-4 col-form-label">Nama Supplier</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control bg-light" value="{{ $pembelian->nama_supplier }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <label class="col-sm-4 col-form-label">Metode Bayar</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control bg-light" value="{{ $pembelian->metode_bayar }}" readonly>
                                </div>
                            </div>
                        </div>
                    
                </div>

            <!-- Detail Bahan -->
<!-- Tabel Detail Bahan -->
            <div class="card mt-4">
                <div class="card-header py-2">
                    <h5>Detail Bahan</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr class="text-center">
                                    <th class="align-middle py-1" style="width: 5%">No</th>
                                    <th class="align-middle py-1" style="width: 25%">Nama Bahan</th>
                                    <th class="align-middle py-1" style="width: 10%">Satuan</th>
                                    <th class="align-middle py-1" style="width: 10%">Jumlah</th>
                                    <th class="align-middle py-1" style="width: 15%">Harga</th>
                                    <th class="align-middle py-1" style="width: 15%">Subtotal</th>
                                    <th class="align-middle py-1" style="width: 15%">Expired</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($details as $detail)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="ps-2 align-middle">{{ $detail->nama_bahan }}</td>
                                    <td class="text-center align-middle">{{ $detail->satuan }}</td>
                                    <td class="text-center align-middle">{{ $detail->bahan_masuk }}</td>
                                    <td class="text-start align-middle">
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="input-group-text p-1">Rp</span>
                                            <span>{{ number_format($detail->harga_beli, 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                    <td class="text-start align-middle">
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="input-group-text p-1">Rp</span>
                                            <span>{{ number_format($detail->subtotal ?? $detail->bahan_masuk * $detail->harga_beli, 0, ',', '.') }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center align-middle">{{ $detail->tanggal_exp ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

<div class="col-md-6">
                   
<div class="card-body">
    <div class="row mb-2">
        <label class="col-sm-4 col-form-label">Total Harga</label>
        <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control bg-light text-end" value="{{ number_format($pembelian->total_harga, 0, ',', '.') }}" readonly>
            </div>
        </div>
    </div>

    @if($pembelian->diskon > 0)
    <div class="row mb-2">
        <label class="col-sm-4 col-form-label">Diskon</label>
        <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control bg-light text-end" value="{{ number_format($pembelian->diskon, 0, ',', '.') }}" readonly>
            </div>
        </div>
    </div>
    @endif

    @if($pembelian->ongkir > 0)
    <div class="row mb-2">
        <label class="col-sm-4 col-form-label">Ongkos Kirim</label>
        <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control bg-light text-end" value="{{ number_format($pembelian->ongkir, 0, ',', '.') }}" readonly>
            </div>
        </div>
    </div>
    @endif

    <div class="row mb-2">
        <label class="col-sm-4 col-form-label">Total Pembelian</label>
        <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control bg-light text-end" value="{{ number_format($pembelian->total_pembelian, 0, ',', '.') }}" readonly>
            </div>
        </div>
    </div>

    @if($pembelian->uang_muka > 0)
    <div class="row mb-2">
        <label class="col-sm-4 col-form-label">Uang Muka</label>
        <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control bg-light text-end" value="{{ number_format($pembelian->uang_muka, 0, ',', '.') }}" readonly>
            </div>
        </div>
    </div>
    @endif

    <div class="row mb-2">
        <label class="col-sm-4 col-form-label">Total Bayar</label>
        <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control bg-light text-end" value="{{ number_format($pembelian->total_bayar, 0, ',', '.') }}" readonly>
            </div>
        </div>
    </div>

    @if($pembelian->hutang > 0)
    <div class="row mb-2">
        <label class="col-sm-4 col-form-label">Kurang Bayar</label>
        <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="text" class="form-control bg-light text-end" value="{{ number_format($pembelian->hutang, 0, ',', '.') }}" readonly>
            </div>
        </div>
    </div>
    @endif

    @if(isset($jatuh_tempo) && ($pembelian->hutang > 0))
    <div class="row mb-2">
        <label class="col-sm-4 col-form-label">Jatuh Tempo</label>
        <div class="col-sm-8">
            <input type="text" class="form-control bg-light text-end" value="{{ $jatuh_tempo }}" readonly>
        </div>
    </div>
    @endif
</div>
          
            </div>


            <div>
                <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">
                   ← Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection