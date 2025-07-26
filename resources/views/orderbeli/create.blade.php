@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">INPUT PERMINTAAN PEMBELIAN</h3>
    <style>
    .table th {
        text-align: center;
        vertical-align: middle !important;
    }

    .col-nama-bahan {
        width: 250px;
    }
    .col-jumlah-order {
        width: 180px;
    }
</style>
    <form action="{{ route('orderbeli.store') }}" method="POST">
        @csrf
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Order -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Kode Order Pembelian</label>
                    <input type="text" name="no_order_beli" class="form-control" value="{{ $no_order_beli }}" readonly>
                </div>
<div class="mb-3 d-flex align-items-center">
    <label class="me-2" style="width: 160px;">Tanggal Order</label>
    <input type="date" name="tanggal_order" class="form-control" 
           value="{{ date('Y-m-d') }}" required>
</div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 160px;">Nama Supplier</label>
                    <select name="kode_supplier" class="form-control" required>
                        <option value="">---Pilih Supplier---</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->kode_supplier }}">{{ $supplier->nama_supplier }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalKekurangan">
                Kekurangan Bahan
                </button>
    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#prediksiModal">
        Kebutuhan Produksi
    </button>

            </div>

            <!-- Kolom Kanan: Data Bahan -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Nama Bahan</label>
                    <select id="kode_bahan" class="form-control">
                        <option value="">---Pilih Bahan---</option>
@foreach($bahans as $bahan)
    <option value="{{ $bahan->kode_bahan }}" data-satuan="{{ $bahan->satuan }}">
        {{ $bahan->nama_bahan }} ({{ $bahan->satuan }})
    </option>
@endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Jumlah Beli</label>
                    <input type="number" id="jumlah_beli" class="form-control">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Harga/Satuan</label>
                    <input type="number" id="harga_beli" class="form-control">
                </div>
                <div class="mb-3 d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary w-100" onclick="tambahBahan()">Tambah Bahan</button>
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PERMINTAAN PEMBELIAN</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-bahan">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Jumlah Order</th>
                    <th>Harga/Satuan</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="d-flex justify-content-between mt-4">
            <!-- Tombol kiri -->
            <div>
                <a href="{{ route('orderbeli.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning">Reset</button>
            </div>

            <!-- Total dan Submit kanan -->
            <div class="d-flex align-items-center gap-3">
                <label class="mb-0">Total Harga</label>
                <input type="text" id="total_order" name="total_order" readonly class="form-control" style="width: 160px;">
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
        </div>

        <input type="hidden" name="detail_json" id="detail_json">
    </form>
</div>

<!-- Modal Kekurangan Bahan -->
<div class="modal fade" id="modalKekurangan" tabindex="-1" aria-labelledby="modalKekuranganLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalKekuranganLabel">Daftar Bahan Kurang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="list-group" id="listKekurangan">
          <!-- Akan diisi via JS -->
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Modal Prediksi Kebutuhan -->
<div class="modal fade" id="prediksiModal" tabindex="-1" aria-labelledby="prediksiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="prediksiModalLabel">Prediksi Kebutuhan Bahan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs" id="prediksiTab" role="tablist"></ul>
        <div class="tab-content" id="prediksiTabContent"></div>
      </div>
    </div>
  </div>
</div>


<script>
    let daftarBahan = [];

    function tambahBahan() {
        const bahanSelect = document.getElementById('kode_bahan');
        const kode_bahan = bahanSelect.value;
        const nama_bahan = bahanSelect.options[bahanSelect.selectedIndex].text;
        const satuan = bahanSelect.options[bahanSelect.selectedIndex].dataset.satuan;
        const jumlah_beli = parseFloat(document.getElementById('jumlah_beli').value);
        const harga_beli = parseFloat(document.getElementById('harga_beli').value);

        if (!kode_bahan || !jumlah_beli || !harga_beli) {
            alert("Silakan lengkapi data bahan.");
            return;
        }

        const total = jumlah_beli * harga_beli;

        daftarBahan.push({ kode_bahan, nama_bahan, satuan, jumlah_beli, harga_beli, total });
        updateTabel();

        // Reset input bahan
        bahanSelect.selectedIndex = 0;
        document.getElementById('jumlah_beli').value = '';
        document.getElementById('harga_beli').value = '';
    }

    function hapusBaris(index) {
        daftarBahan.splice(index, 1);
        updateTabel();
    }

    function updateTabel() {
        const tbody = document.querySelector('#daftar-bahan tbody');
        tbody.innerHTML = '';

        let totalOrder = 0;

        daftarBahan.forEach((item, index) => {
            totalOrder += item.total;

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_bahan}</td>
                    <td>${item.satuan}</td>
                    <td>${item.jumlah_beli}</td>
                    <td>${item.harga_beli}</td>
                    <td>${item.total}</td>
                    <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(${index})">X</button></td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('total_order').value = totalOrder;
        document.getElementById('detail_json').value = JSON.stringify(daftarBahan);
    }

    // Data bahan kurang dari controller (jika ada)
    let bahanKurangList = [];
    @if(isset($bahanKurang) && count($bahanKurang))
        bahanKurangList = @json($bahanKurang);
    @endif

    // Tampilkan daftar bahan kurang di modal
    document.addEventListener('DOMContentLoaded', function() {
        const listKekurangan = document.getElementById('listKekurangan');
        if (listKekurangan && bahanKurangList.length) {
            listKekurangan.innerHTML = '';
            bahanKurangList.forEach((item, idx) => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.style.cursor = 'pointer';
                li.innerHTML = `
                    <span>
                        <strong>${item.nama_bahan}</strong> (${item.satuan})<br>
                        <small>Kurang: ${item.jumlah_beli}</small>
                    </span>
                    <button class="btn btn-sm btn-primary" onclick="isiInputBahan('${item.kode_bahan}', '${item.nama_bahan}', '${item.satuan}', ${item.jumlah_beli})" data-bs-dismiss="modal">Pilih</button>
                `;
                listKekurangan.appendChild(li);
            });
        } else if(listKekurangan) {
            listKekurangan.innerHTML = '<li class="list-group-item text-center text-muted">Tidak ada bahan yang kurang</li>';
        }
    });

    // Fungsi untuk mengisi input bahan dari modal
    function isiInputBahan(kode, nama, satuan, jumlah) {
        const bahanSelect = document.getElementById('kode_bahan');
        for (let i = 0; i < bahanSelect.options.length; i++) {
            if (bahanSelect.options[i].value == kode) {
                bahanSelect.selectedIndex = i;
                break;
            }
        }
        document.getElementById('jumlah_beli').value = jumlah;
    }
    // Data stok minimal dari controller
let stokMinList = [];
@if(isset($stokMinList) && count($stokMinList))
    stokMinList = @json($stokMinList);
@endif

// Tampilkan daftar stok minimal di modal
document.addEventListener('DOMContentLoaded', function() {
    const listStokMin = document.getElementById('listStokMin');
    if (listStokMin && stokMinList.length) {
        listStokMin.innerHTML = '';
        stokMinList.forEach((item, idx) => {
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.style.cursor = 'pointer';
            li.innerHTML = `
                <span>
                    <strong>${item.nama_bahan}</strong> (${item.satuan})<br>
                    <small>Stok Saat Ini: ${item.stok}</small>
                </span>
                <button class="btn btn-sm btn-primary" onclick="isiInputBahan('${item.kode_bahan}', '${item.nama_bahan}', '${item.satuan}', 1)" data-bs-dismiss="modal">Pilih</button>
            `;
            listStokMin.appendChild(li);
        });
    } else if(listStokMin) {
        listStokMin.innerHTML = '<li class="list-group-item text-center text-muted">Tidak ada bahan di bawah stok minimal</li>';
    }
});

// Data prediksi dari controller
let bahansPrediksi = [];
@if(isset($bahansPrediksi) && count($bahansPrediksi))
    bahansPrediksi = @json($bahansPrediksi);
@endif

function groupByFrekuensi(bahans) {
    const group = {};
    bahans.forEach(b => {
        const freq = b.frekuensi_pembelian || 'Lainnya';
        if (!group[freq]) group[freq] = [];
        group[freq].push(b);
    });
    return group;
}

document.addEventListener('DOMContentLoaded', function() {
    // Modal Prediksi
    const prediksiTab = document.getElementById('prediksiTab');
    const prediksiTabContent = document.getElementById('prediksiTabContent');
    if (prediksiTab && prediksiTabContent) {
        const grouped = groupByFrekuensi(bahansPrediksi);

        // Daftar frekuensi tetap
        const freqs = ['Mingguan', 'Dua Mingguan', 'Bulanan', 'Tiga Bulanan'];
        let first = true;
        prediksiTab.innerHTML = '';
        prediksiTabContent.innerHTML = '';

        freqs.forEach((freq, idx) => {
            const bahanList = grouped[freq] || [];
            // Tab header
            prediksiTab.innerHTML += `
                <li class="nav-item" role="presentation">
                  <button class="nav-link ${first ? 'active' : ''}" id="tab-${idx}" data-bs-toggle="tab" data-bs-target="#tab-content-${idx}" type="button" role="tab">${freq}</button>
                </li>
            `;
            // Tab content
            let rows = '';
if (bahanList.length) {
    bahanList.forEach((bahan, i) => {
        const isStokKurang = parseFloat(bahan.stok ?? 0) < parseFloat(bahan.stokmin ?? 0);
        rows += `
            <tr style="background-color: ${isStokKurang ? '#fff3cd' : 'inherit'}">
                <td class="text-center">${i + 1}</td>
                <td>${bahan.nama_bahan}</td>
                <td class="text-center">${bahan.interval ? bahan.interval + 'x' : '-'}</td>
                <td class="text-center">${bahan.jumlah_per_order ?? '-'}</td>
                <td class="text-center" style="color:${isStokKurang ? 'red' : 'inherit'}; font-weight:${isStokKurang ? 'bold' : 'normal'}">
                    ${(bahan.stokmin != null) ? parseFloat(bahan.stokmin).toFixed(2) : '-'}
                </td>
                <td class="text-center">
                    ${(bahan.stok != null) ? parseFloat(bahan.stok).toFixed(2) : '-'}
                </td>
                <td class="text-center">${bahan.satuan}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary"
                            onclick="isiInputBahan('${bahan.kode_bahan}', '${bahan.nama_bahan}', '${bahan.satuan}', ${bahan.jumlah_per_order ?? 1})"
                            data-bs-dismiss="modal">
                        Pilih
                    </button>
                </td>
            </tr>
        `;
    });
} else {
    rows = `<tr><td colspan="8" class="text-center text-muted">Tidak ada bahan untuk periode ini</td></tr>`;
}

prediksiTabContent.innerHTML += `
    <div class="tab-pane fade ${first ? 'show active' : ''}" id="tab-content-${idx}" role="tabpanel">
        <table class="table table-bordered mt-3">
            <thead class="table-light">
                <tr class="text-center">
                    <th>No</th>
                    <th class="col-nama-bahan">Nama Bahan</th>
                    <th>Frekuensi Pembelian</th>
                    <th class="col-jumlah-order">Jumlah Dibeli per Periode</th>
                    <th>Stok Minimum</th>
                    <th>Stok Tersedia</th>
                    <th>Satuan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                ${rows}
            </tbody>
        </table>
    </div>
`;
first = false;

        });
    }
});
</script>
@endsection