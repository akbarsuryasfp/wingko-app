@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Edit Penerimaan Bahan</h3>
    <form action="{{ route('terimabahan.update', $terimaBahan->no_terima_bahan) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3 d-flex align-items-center">
            <label for="no_terima_bahan" class="form-label mb-0" style="width:180px;">No Terima Bahan</label>
            <input type="text" class="form-control" id="no_terima_bahan" name="no_terima_bahan" value="{{ $terimaBahan->no_terima_bahan }}" readonly style="width:300px;">
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="no_order_beli" class="form-label mb-0" style="width:180px;">No Order Beli</label>
            <select class="form-control" id="no_order_beli" name="no_order_beli" required onchange="ambilDetailOrderBeli()" style="width:300px;">
                <option value="">-- Pilih Order Beli --</option>
                @foreach($orderbeli as $order)
                    <option value="{{ $order->no_order_beli }}"
                        data-kode_supplier="{{ $order->kode_supplier }}"
                        data-nama_supplier="{{ $order->nama_supplier }}"
                        data-tanggal="{{ $order->tanggal_order }}"
                        data-bahan="{{ $order->ringkasan_bahan }}"
                        {{ $terimaBahan->no_order_beli == $order->no_order_beli ? 'selected' : '' }}>
                        {{ $order->no_order_beli }} | {{ $order->tanggal_order }} | {{ $order->ringkasan_bahan }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="tanggal_terima" class="form-label mb-0" style="width:180px;">Tanggal Terima</label>
            <input type="date" class="form-control" id="tanggal_terima" name="tanggal_terima" value="{{ $terimaBahan->tanggal_terima }}" required style="width:300px;">
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="nama_supplier" class="form-label mb-0" style="width:180px;">Supplier</label>
            <input type="text" id="nama_supplier" class="form-control" value="{{ $terimaBahan->supplier->nama_supplier }}" readonly style="width:300px;">
            <input type="hidden" id="kode_supplier" name="kode_supplier" value="{{ $terimaBahan->supplier->kode_supplier }}">
        </div>

        <hr>
        <h5>Detail Bahan Diterima</h5>
        <table class="table table-bordered" id="tabel-detail">
            <thead>
                <tr>
                    <th>Kode Bahan</th>
                    <th>Nama Bahan</th>
                    <th>Jumlah Order</th>
                    <th>Bahan Masuk</th>
                    <th>Harga Beli</th>
                    <th>Total</th>
                    <th>Tanggal Exp</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($terimaBahan->details as $index => $detail)
                <tr>
                    <td>{{ $detail->kode_bahan }}</td>
                    <td>{{ $detail->nama_bahan }}</td>
                    <td>{{ $detail->jumlah_order }}</td>
                    <td>
                        <input type="number" class="form-control" min="0" value="{{ $detail->bahan_masuk }}" onchange="setBahanMasuk({{ $index }}, this.value)">
                    </td>
                    <td>{{ $detail->harga_beli }}</td>
                    <td>{{ $detail->bahan_masuk * $detail->harga_beli }}</td>
                    <td>
                        <input type="date" class="form-control" value="{{ $detail->tanggal_exp }}" onchange="setTanggalExp({{ $index }}, this.value)">
                    </td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusDetail({{ $index }})">Hapus</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <input type="hidden" name="detail_json" id="detail_json" value="{{ json_encode($terimaBahan->details) }}">

        <div class="mt-4">
            <a href="{{ route('terimabahan.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>

    <div id="order-detail-info" class="mb-2" style="font-size: 90%; color: #555;"></div>
</div>

<script>
let daftarDetail = @json($terimaBahan->details);

function updateTabel() {
    const tbody = document.querySelector('#tabel-detail tbody');
    tbody.innerHTML = '';
    daftarDetail.forEach((item, idx) => {
        const total = item.bahan_masuk * item.harga_beli;
        const row = `
            <tr>
                <td>${item.kode_bahan}</td>
                <td>${item.nama_bahan}</td>
                <td>${item.jumlah_order}</td>
                <td>
                    <input type="number" class="form-control" min="0" value="${item.bahan_masuk}" onchange="setBahanMasuk(${idx}, this.value)">
                </td>
                <td>${item.harga_beli}</td>
                <td>${total}</td>
                <td>
                    <input type="date" class="form-control" value="${item.tanggal_exp || ''}" onchange="setTanggalExp(${idx}, this.value)">
                </td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusDetail(${idx})">Hapus</button></td>
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

updateTabel();
</script>
@endsection