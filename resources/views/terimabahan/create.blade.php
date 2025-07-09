@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Input Penerimaan Pembelian Bahan</h3>
    <form action="{{ route('terimabahan.store') }}" method="POST">
        @csrf
        <div class="mb-3 d-flex align-items-center">
            <label for="no_terima_bahan" class="form-label mb-0" style="width:180px;">Kode Terima Bahan</label>
            <input type="text" class="form-control" id="no_terima_bahan" name="no_terima_bahan" value="{{ $kode }}" readonly style="width:300px;">
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="no_order_beli" class="form-label mb-0" style="width:180px;">Kode Order</label>
            <select class="form-control" id="no_order_beli" name="no_order_beli" required onchange="ambilDetailOrderBeli()" style="width:300px;">
                <option value="">-- Pilih Order Beli --</option>
                @foreach($orderbeli as $order)
                    <option value="{{ $order->no_order_beli }}"
                        data-kode_supplier="{{ $order->kode_supplier }}"
                        data-nama_supplier="{{ $order->nama_supplier }}"
                        {{ (isset($order_selected) && $order_selected->no_order_beli == $order->no_order_beli) ? 'selected' : '' }}>
                        {{ $order->no_order_beli }} | {{ $order->tanggal_order }} | {{ $order->nama_supplier }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mb-3 d-flex align-items-center">
            <label for="tanggal_terima" class="form-label mb-0" style="width:180px;">Tanggal Terima</label>
            <input type="date" class="form-control" id="tanggal_terima" name="tanggal_terima" required style="width:300px;">
        </div>
            <div class="mb-3 d-flex align-items-center">
                <label for="nama_supplier" class="form-label mb-0" style="width:180px;">Supplier</label>
                <input type="text" id="nama_supplier" class="form-control" readonly style="width:300px;" value="{{ $order_selected->nama_supplier ?? '' }}">
                <input type="hidden" id="kode_supplier" name="kode_supplier" value="{{ $order_selected->kode_supplier ?? '' }}">
            </div>

        <hr>
        <h5>DAFTAR PENERIMAAN BAHAN</h5>
        <table class="table table-bordered" id="tabel-detail">
            <thead>
                <tr>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Jumlah Order</th>
                    <th>Jumlah Masuk</th>
                    <th>Harga Beli</th>
                    <th>Sub Total</th>
                    <th>Tanggal Expired</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                    @foreach(($order_details ?? []) as $detail)
                    <tr>
                        <td>
                            <input type="hidden" name="detail[{{ $loop->index }}][kode_bahan]" value="{{ $detail->kode_bahan }}">
                            {{ $detail->nama_bahan }}
                        </td>
                        <td>{{ $detail->satuan }}</td>
                        <td>
                            <input type="number" class="form-control" name="detail[{{ $loop->index }}][jumlah_beli]" value="{{ $detail->jumlah_beli }}" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control" name="detail[{{ $loop->index }}][bahan_masuk]" value="{{ $detail->jumlah_beli }}">
                        </td>
            <td>
                <input type="number" class="form-control" name="detail[{{ $loop->index }}][harga_beli]" value="{{ $detail->harga_beli }}" readonly>
            </td>
            <td>
                <input type="number" class="form-control" value="{{ $detail->jumlah_beli * $detail->harga_beli }}" readonly>
            </td>
            <td>
    <input type="date" class="form-control" name="detail[{{ $loop->index }}][tanggal_exp]">
</td>
<td>
    <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">Hapus</button>
</td>
</tr>
                    @endforeach
            </tbody>
        </table>

        <input type="hidden" name="detail_json" id="detail_json">

        <div class="mt-4">
            <a href="{{ route('terimabahan.index') }}" class="btn btn-secondary">Back</a>
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>

    <div id="order-detail-info" class="mb-2" style="font-size: 90%; color: #555;"></div>
</div>

<script>
let daftarDetail = [];

function tambahDetail() {
    const bahanSelect = document.getElementById('kode_bahan');
    const kode_bahan = bahanSelect.value;
    const nama_bahan = bahanSelect.options[bahanSelect.selectedIndex]?.getAttribute('data-nama') || '';
    const bahan_masuk = parseFloat(document.getElementById('bahan_masuk').value);
    const harga_beli = parseFloat(document.getElementById('harga_beli').value);
    const tanggal_exp = document.getElementById('tanggal_exp').value;

    if (!kode_bahan || !bahan_masuk || !harga_beli || !tanggal_exp) {
        alert('Lengkapi semua data detail!');
        return;
    }

    const total = bahan_masuk * harga_beli;

    daftarDetail.push({ kode_bahan, nama_bahan, bahan_masuk, harga_beli, total, tanggal_exp });
    updateTabel();
    bahanSelect.selectedIndex = 0;
    document.getElementById('bahan_masuk').value = '';
    document.getElementById('harga_beli').value = '';
    document.getElementById('tanggal_exp').value = '';
}

function hapusDetail(index) {
    daftarDetail.splice(index, 1);
    updateTabel();
}

function hapusBaris(btn) {
    const row = btn.closest('tr');
    row.remove();
}

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
                    <input type="number" class="form-control" min="0" max="${item.sisa}" value="${item.bahan_masuk}" onchange="setBahanMasuk(${idx}, this.value)">
                    <div style="font-size:11px;color:#888;">Sisa: ${item.sisa}</div>
                </td>
                <td>${item.harga_beli}</td>
                <td>${total}</td>
                <td>
                    <input type="date" class="form-control" value="${item.tanggal_exp || ''}" onchange="setTanggalExp(${idx}, this.value)">
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="hapusDetail(${idx})">Hapus</button>
                </td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', row);
        daftarDetail[idx].total = total;
    });
    const detailDikirim = daftarDetail.filter(item => parseFloat(item.bahan_masuk) > 0);
    document.getElementById('detail_json').value = JSON.stringify(detailDikirim);
}

function setBahanMasuk(idx, value) {
    const max = daftarDetail[idx].sisa;
    let val = parseFloat(value) || 0;
    if (val > max) {
        alert('Bahan masuk tidak boleh lebih dari sisa order!');
        val = max;
    }
    daftarDetail[idx].bahan_masuk = val;
    updateTabel();
}

function setJumlahOrder(idx, value) {
    daftarDetail[idx].jumlah_order = value;
    document.getElementById('detail_json').value = JSON.stringify(daftarDetail);
}

function setTanggalExp(idx, value) {
    daftarDetail[idx].tanggal_exp = value;
    document.getElementById('detail_json').value = JSON.stringify(daftarDetail);
}

function ambilDetailOrderBeli() {
    const orderSelect = document.getElementById('no_order_beli');
    const selectedOption = orderSelect.options[orderSelect.selectedIndex];
    const kodeSupplier = selectedOption.getAttribute('data-kode_supplier') || '';
    const namaSupplier = selectedOption.getAttribute('data-nama_supplier') || '';
    const tanggalOrder = selectedOption.getAttribute('data-tanggal') || '';
    const bahanOrder = selectedOption.getAttribute('data-bahan') || '';

    document.getElementById('kode_supplier').value = kodeSupplier;
    document.getElementById('nama_supplier').value = namaSupplier;
    document.getElementById('order-detail-info').innerHTML = 
        tanggalOrder || bahanOrder
        ? `<b>Tanggal Order:</b> ${tanggalOrder} <br><b>Bahan:</b> ${bahanOrder}`
        : '';

    const noOrder = orderSelect.value;
    if (!noOrder) {
        daftarDetail = [];
        updateTabel();
        return;
    }

    // Ambil detail order dan sisa
    fetch(`/orderbeli/${noOrder}/detail`)
        .then(res => res.json())
        .then(data => {
            // Ambil sisa order
            fetch(`/terimabahan/sisa-order/${noOrder}`)
                .then(res2 => res2.json())
                .then(sisaArr => {
                    daftarDetail = data.map(item => {
    // Cari sisa untuk bahan ini
    const sisaObj = sisaArr.find(s => s.kode_bahan === item.kode_bahan);
    const sisa = sisaObj ? sisaObj.sisa : item.jumlah_beli;
    return {
        kode_bahan: item.kode_bahan,
        nama_bahan: item.nama_bahan,
        jumlah_order: item.jumlah_beli,
        bahan_masuk: sisa, // default: sisa
        sisa: sisa,
        harga_beli: item.harga_beli,
        total: sisa * item.harga_beli,
        tanggal_exp: ''
    };
});
                    updateTabel();
                });
        });
}

@if(isset($order_details) && count($order_details))
    daftarDetail = [
        @foreach($order_details as $detail)
        {
            kode_bahan: "{{ $detail->kode_bahan }}",
            nama_bahan: "{{ $detail->nama_bahan }}",
            satuan: "{{ $detail->satuan ?? '' }}",
            jumlah_order: {{ $detail->jumlah_beli }},
            bahan_masuk: {{ $detail->jumlah_beli }},
            sisa: {{ $detail->jumlah_beli }},
            harga_beli: {{ $detail->harga_beli }},
            total: {{ $detail->jumlah_beli * $detail->harga_beli }},
            tanggal_exp: ""
        }@if(!$loop->last),@endif
        @endforeach
    ];
@endif
document.addEventListener('DOMContentLoaded', function() {
    // Jika ada order yang sudah terpilih (misal dari parameter ?order=...), panggil ambilDetailOrderBeli()
    var orderSelect = document.getElementById('no_order_beli');
    if (orderSelect && orderSelect.value) {
        ambilDetailOrderBeli();
    }
    updateTabel();
});

document.querySelector('form').addEventListener('submit', function(e) {
    let detailJson = document.getElementById('detail_json');
    // Jika daftarDetail (JS) ada dan tidak kosong, gunakan itu
    if (typeof daftarDetail !== 'undefined' && Array.isArray(daftarDetail) && daftarDetail.length > 0) {
        detailJson.value = JSON.stringify(daftarDetail.filter(item => parseFloat(item.bahan_masuk) > 0));
    } else {
        // Jika tidak, ambil dari input form (PHP)
        let rows = document.querySelectorAll('#tabel-detail tbody tr');
        let arr = [];
        rows.forEach(function(row, idx) {
            let kode_bahan = row.querySelector('input[name^="detail["][name$="[kode_bahan]"]');
            if (!kode_bahan) return; // skip baris kosong
            arr.push({
                kode_bahan: kode_bahan.value,
                nama_bahan: row.cells[0].innerText.trim(),
                satuan: row.cells[1].innerText.trim(),
                jumlah_beli: row.querySelector('input[name^="detail["][name$="[jumlah_beli]"]').value,
                bahan_masuk: row.querySelector('input[name^="detail["][name$="[bahan_masuk]"]').value,
                harga_beli: row.querySelector('input[name^="detail["][name$="[harga_beli]"]').value,
                total: row.cells[5].querySelector('input') ? row.cells[5].querySelector('input').value : row.cells[5].innerText.trim(),
                tanggal_exp: row.querySelector('input[name^="detail["][name$="[tanggal_exp]"]').value
            });
        });
        detailJson.value = JSON.stringify(arr);
    }
});
</script>
@endsection