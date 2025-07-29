@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-4 text-center">Edit Data Supplier</h4>

                    <form action="{{ route('supplier.update', $supplier->kode_supplier) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Kode Supplier --}}
                        <div class="row mb-3 align-items-center">
                            <label for="kode_supplier" class="col-sm-3 col-form-label">Kode Supplier</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control bg-light" id="kode_supplier" name="kode_supplier" 
                                       value="{{ $supplier->kode_supplier }}" readonly>
                            </div>
                        </div>

                        {{-- Nama Supplier --}}
                        <div class="row mb-3 align-items-center">
                            <label for="nama_supplier" class="col-sm-3 col-form-label">Nama Supplier</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="nama_supplier" name="nama_supplier" 
                                       value="{{ old('nama_supplier', $supplier->nama_supplier) }}" required>
                                @error('nama_supplier')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Alamat --}}
                        <div class="row mb-3 align-items-center">
                            <label for="alamat" class="col-sm-3 col-form-label">Alamat</label>
                            <div class="col-sm-9">
                                <textarea class="form-control" id="alamat" name="alamat" rows="2" required>{{ old('alamat', $supplier->alamat) }}</textarea>
                                @error('alamat')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- No. Telp --}}
                        <div class="row mb-3 align-items-center">
                            <label for="no_telp" class="col-sm-3 col-form-label">No. Telepon</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" id="no_telp" name="no_telp" 
                                       value="{{ old('no_telp', $supplier->no_telp) }}" required>
                                @error('no_telp')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Rekening --}}
                        @php
                            $jenis_bank = '';
                            $no_rek = '';
                            if (!empty($supplier->rekening)) {
                                $parts = explode(' ', $supplier->rekening, 2);
                                $jenis_bank = $parts[0] ?? '';
                                $no_rek = $parts[1] ?? '';
                            }
                        @endphp
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 col-form-label">Rekening</label>
                            <div class="col-sm-9">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="text" class="form-control flex-grow-1" placeholder="Jenis Bank" 
                                           id="jenis_bank" name="jenis_bank" value="{{ old('jenis_bank', $jenis_bank) }}" required>
                                    <input type="text" class="form-control flex-grow-1" placeholder="No Rekening" 
                                           id="no_rek" name="no_rek" value="{{ old('no_rek', $no_rek) }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- Pengiriman --}}
                        @php
                            $jarak_kirim = '';
                            $waktu_kirim = '';
                            if (!empty($supplier->keterangan)) {
                                preg_match('/Jarak kirim ke gudang ([\d.,]+) km/', $supplier->keterangan, $matchJarak);
                                preg_match('/Waktu kirim (\d+) hari/', $supplier->keterangan, $matchWaktu);
                                $jarak_kirim = $matchJarak[1] ?? '';
                                $waktu_kirim = $matchWaktu[1] ?? '';
                            }
                        @endphp
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-3 col-form-label" data-bs-toggle="tooltip" title="Jarak kirim ke gudang dan estimasi pengiriman.">Pengiriman</label>
                            <div class="col-sm-9">
                                <div class="row g-2">
                                    <div class="col-md-6 d-flex align-items-center">
                                        <div class="input-group" style="width: 150px;">
                                            <input type="number" step="0.01" class="form-control" placeholder="Jarak" 
                                                   id="jarak_kirim" name="jarak_kirim" value="{{ old('jarak_kirim', $jarak_kirim) }}" required>
                                            <span class="input-group-text">km</span>
                                        </div>
                                        <span class="ms-2">ke Gudang</span>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center">
                                        <div class="input-group" style="width: 150px;">
                                            <input type="number" class="form-control" placeholder="Waktu" 
                                                   id="waktu_kirim" name="waktu_kirim" value="{{ old('waktu_kirim', $waktu_kirim) }}" required>
                                            <span class="input-group-text">hari</span>
                                        </div>
                                        <span class="ms-2">setelah pesan</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="row mt-4">
                            <div class="col-sm-6">
                                <a href="{{ route('supplier.index') }}" class="btn btn-secondary me-2">
                                    ← Kembali
                                </a>
                                <button type="reset" class="btn btn-warning">
                                    Reset
                                </button>
                            </div>
                            <div class="col-sm-6 text-end">
                                <button type="submit" class="btn btn-success">
                                    Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    input[readonly] {
        background-color: #f8f9fa !important;
        color: #495057;
        border-color: #dee2e6;
    }
    .card {
        border-radius: 8px;
    }
    .form-control {
        border-radius: 6px;
    }
</style>

<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => {
        new bootstrap.Tooltip(el);
    });
</script>
@endsection