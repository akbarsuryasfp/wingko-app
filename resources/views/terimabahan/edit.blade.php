@extends('layouts.app')

@section('content')
<style>
    .card {
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 20px;
    }
    input[readonly], input[readonly]:focus {
        background-color: #f5f5f5;
    }
</style>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h3>Edit Penerimaan Pembelian Bahan</h3>
            <form action="{{ route('terimabahan.update', $terimaBahan->no_terima_bahan) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3 d-flex align-items-center">
                    <label for="no_terima_bahan" class="form-label mb-0" style="width:180px;">Kode Terima Bahan</label>
                    <input type="text" class="form-control" id="no_terima_bahan" name="no_terima_bahan" value="{{ $terimaBahan->no_terima_bahan }}" readonly style="width:300px;">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label for="no_order_beli" class="form-label mb-0" style="width:180px;">Kode Order</label>
                    <select class="form-control" id="no_order_beli" name="no_order_beli" required onchange="ambilDetailOrderBeli()" style="width:300px;">
                        <option value="">-- Pilih Order Beli --</option>
                        @foreach($orderbeli as $order)
                            <option value="{{ $order->no_order_beli }}"
                                data-kode_supplier="{{ $order->kode_supplier }}"
                                data-nama_supplier="{{ $order->nama_supplier }}"
                                {{ $terimaBahan->no_order_beli == $order->no_order_beli ? 'selected' : '' }}>
                                {{ $order->no_order_beli }} | {{ $order->tanggal_order }} | {{ $order->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label for="tanggal_terima" class="form-label mb-0" style="width:180px;">Tanggal Terima</label>
                    <input type="date" class="form-control" id="tanggal_terima" name="tanggal_terima" 
                           value="{{ $terimaBahan->tanggal_terima }}" required style="width:300px;">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label for="nama_supplier" class="form-label mb-0" style="width:180px;">Supplier</label>
                    <input type="text" id="nama_supplier" class="form-control" readonly style="width:300px;" value="{{ $terimaBahan->supplier->nama_supplier }}">
                    <input type="hidden" id="kode_supplier" name="kode_supplier" value="{{ $terimaBahan->supplier->kode_supplier }}">
                </div>

                <hr>
                <h5>DAFTAR PENERIMAAN BAHAN</h5>
                <table class="table table-bordered" id="tabel-detail">
                    <thead>
                        <tr class="text-center">
                            <th style="width: 50px;">No</th>
                            <th>Nama Bahan</th>
                            <th>Satuan</th>
                            <th>Jumlah Order</th>
                            <th>Jumlah Masuk</th>
                            <th>Harga Beli</th>
                            <th>Sub Total</th>
                            <th>Tanggal Expired</th>
                            <th style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="order-details">
                        @foreach($terimaBahan->details as $index => $detail)
                        <tr style="height: 40px;">
                            <td class="text-center align-middle">{{ $index + 1 }}</td>
                            <td class="text-center align-middle">{{ $detail->kode_bahan }}</td>
                            <td class="text-start align-middle">{{ $detail->nama_bahan }}</td>
                            <td class="text-center align-middle">
                                <span class="d-block">{{ number_format($detail->jumlah_order) }}</span>
                            </td>
                            <td class="text-center align-middle" style="padding-top: 4px; padding-bottom: 4px;">
                                <input type="number" class="form-control form-control-sm text-center py-1" 
                                       min="0" value="{{ $detail->bahan_masuk }}" 
                                       onchange="setBahanMasuk({{ $index }}, this.value)">
                            </td>
                            <td class="text-end align-middle">
                                <span class="d-block">Rp {{ number_format($detail->harga_beli) }}</span>
                            </td>
                            <td class="text-end align-middle">
                                <span class="d-block">Rp {{ number_format($detail->bahan_masuk * $detail->harga_beli) }}</span>
                            </td>
                            <td class="text-center align-middle" style="padding-top: 4px; padding-bottom: 4px;">
                                <input type="date" class="form-control form-control-sm py-1" 
                                       value="{{ $detail->tanggal_exp }}" 
                                       onchange="setTanggalExp({{ $index }}, this.value)">
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-danger btn-sm py-0 px-2" 
                                        style="font-size: 2 rem; line-height: 1.5;" 
                                        onclick="hapusDetail({{ $index }})">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <input type="hidden" name="detail_json" id="detail_json" value="{{ json_encode($terimaBahan->details) }}">

                <div class="row mt-4">
                    <div class="col-sm-6">
                        <a href="{{ route('terimabahan.index') }}" class="btn btn-secondary me-2">
                            ← Kembali
                        </a>
                    </div>
                    <div class="col-sm-6 text-end">
                        <button type="submit" class="btn btn-success">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>

            <div id="order-detail-info" class="mb-2" style="font-size: 90%; color: #555;"></div>
        </div>
    </div>
</div>

<script>
let daftarDetail = @json($terimaBahan->details);

function updateTabel() {
    const tbody = document.querySelector('#tabel-detail tbody');
    tbody.innerHTML = '';
    daftarDetail.forEach((item, idx) => {
        const total = item.bahan_masuk * item.harga_beli;
        const row = `
            <tr style="height: 40px;">
                <td class="text-center align-middle">${idx + 1}</td>
                <td class="text-center align-middle">${item.kode_bahan}</td>
                <td class="text-start align-middle">${item.nama_bahan}</td>
                <td class="text-center align-middle">
                    <span class="d-block">${new Intl.NumberFormat('id-ID').format(item.jumlah_order)}</span>
                </td>
                <td class="text-center align-middle" style="padding-top: 4px; padding-bottom: 4px;">
                    <input type="number" class="form-control form-control-sm text-center py-1" 
                           min="0" value="${item.bahan_masuk}" 
                           onchange="setBahanMasuk(${idx}, this.value)">
                </td>
                <td class="text-end align-middle">
                    <span class="d-block">Rp ${new Intl.NumberFormat('id-ID').format(item.harga_beli)}</span>
                </td>
                <td class="text-end align-middle">
                    <span class="d-block">Rp ${new Intl.NumberFormat('id-ID').format(total)}</span>
                </td>
                <td class="text-center align-middle" style="padding-top: 4px; padding-bottom: 4px;">
                    <input type="date" class="form-control form-control-sm py-1" 
                           value="${item.tanggal_exp || ''}" 
                           onchange="setTanggalExp(${idx}, this.value)">
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-danger btn-sm py-0 px-2" 
                            style="font-size: 2 rem; line-height: 1.5;" 
                            onclick="hapusDetail(${idx})">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', row);
    });
    document.getElementById('detail_json').value = JSON.stringify(daftarDetail);
}

function setBahanMasuk(idx, value) {
    daftarDetail[idx].bahan_masuk = parseFloat(value) || 0;
    updateTabel();
}

function setTanggalExp(idx, value) {
    daftarDetail[idx].tanggal_exp = value;
    updateTabel();
}

function hapusDetail(index) {
    daftarDetail.splice(index, 1);
    updateTabel();
}

document.addEventListener('DOMContentLoaded', function() {
    updateTabel();
});

document.querySelector('form').addEventListener('submit', function(e) {
    document.getElementById('detail_json').value = JSON.stringify(daftarDetail);
});
</script>
@endsection