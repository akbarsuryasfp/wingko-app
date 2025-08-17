@extends('layouts.app')

@section('content')
<div class="container py-2">
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <h4 class="mb-0 fw-semibold">Form Transfer Produk</h4>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('transferproduk.store') }}" method="POST">
                @csrf

                {{-- Header Form --}}
                <div class="row mb-2 align-items-center">
                    <label class="col-sm-2 col-form-label">No Transaksi</label>
                    <div class="col-sm-4">
                        <input type="text" name="no_transaksi" class="form-control bg-light" value="{{ $kode_otomatis }}" readonly>
                    </div>
                </div>
                <div class="row mb-2 align-items-center">
                    <label class="col-sm-2 col-form-label">Tanggal</label>
                    <div class="col-sm-4">
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="row mb-2 align-items-center">
                    <label class="col-sm-2 col-form-label">Lokasi Asal</label>
                    <div class="col-sm-4">
                        <select name="lokasi_asal" id="lokasi_asal" class="form-control"
                            @if(!auth()->user() || !auth()->user()->hasRole('admin')) disabled @endif required>
                            @foreach($listLokasi as $kode => $nama)
                                <option value="{{ $kode }}" {{ $kode == $lokasiAsal ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                        @if(!auth()->user() || !auth()->user()->hasRole('admin'))
                            <input type="hidden" name="lokasi_asal" value="{{ $lokasiAsal }}">
                        @endif
                    </div>
                </div>
                <div class="row mb-2 align-items-center">
                    <label class="col-sm-2 col-form-label">Lokasi Tujuan</label>
                    <div class="col-sm-4">
                        <select name="lokasi_tujuan" id="lokasi_tujuan" class="form-control" required>
                            <option value="">-- Pilih Lokasi Tujuan --</option>
                            @foreach($listLokasi as $kode => $nama)
                                @if($kode != $lokasiAsal)
                                    <option value="{{ $kode }}">{{ $nama }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <hr class="my-4">
                <div>
                    <h5 class="fw-semibold">Daftar Produk</h5>
                </div>
                <div class="table-responsive mb-2">
                    <table class="table table-bordered table-sm align-middle" id="produk-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:5%;">No</th>
                                <th class="text-center" style="width:30%;">Produk</th>
                                <th class="text-center" style="width:12%;">Jumlah Kirim</th>
                                <th class="text-center" style="width:12%;">Satuan</th>
                                <th class="text-center" style="width:18%;">Tanggal Exp</th>
                                <th class="text-center" style="width:5%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="produk-list">
                            <tr class="produk-item">
                                <td class="text-center">1</td>
                                <td>
                                    <select name="produk_id[]" class="form-select produk-select" required>
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach ($produk as $item)
                                            <option value="{{ $item->kode_produk }}"
                                                data-satuan="{{ $item->satuan }}"
                                                data-exp="{{ $item->tanggal_exp }}">
                                                {{ $item->nama_produk }} ({{ $item->stok }} {{ $item->satuan }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input type="number" name="jumlah[]" class="form-control text-center" placeholder="Jumlah" min="1" required>
                                </td>
                                <td class="text-center">
                                    <input type="text" name="satuan[]" class="form-control bg-light text-center" readonly placeholder="Satuan">
                                </td>
                                <td class="text-center">
                                    <input type="date" name="tanggal_exp[]" class="form-control text-center" readonly placeholder="Tanggal Exp">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm remove-produk" title="Hapus">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-start mb-2">
                    <button type="button" class="btn btn-sm btn-success" id="tambah-produk">
                        <i class="bi bi-plus"></i> Tambah Produk
                    </button>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="{{ route('transferproduk.index') }}" class="btn btn-secondary">← Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan Transfer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const lokasiAsalSelect = document.getElementById('lokasi_asal');
    const lokasiTujuanSelect = document.getElementById('lokasi_tujuan');
    const allLokasi = @json($listLokasi);

    function updateLokasiTujuan() {
        const asal = lokasiAsalSelect.value;
        lokasiTujuanSelect.innerHTML = '<option value="">-- Pilih Lokasi Tujuan --</option>';
        Object.entries(allLokasi).forEach(function([kode, nama]) {
            if(kode != asal) {
                lokasiTujuanSelect.innerHTML += `<option value="${kode}">${nama}</option>`;
            }
        });
    }

    // Update produk list sesuai lokasi asal
    function updateProdukList() {
        const lokasi = lokasiAsalSelect.value;
        fetch("{{ url('transferproduk/produk-by-lokasi') }}?lokasi=" + encodeURIComponent(lokasi))
            .then(res => res.json())
            .then(data => {
                // Untuk setiap baris produk, update option produk
                document.querySelectorAll('#produk-list .produk-item').forEach(function(row, idx) {
                    const select = row.querySelector('.produk-select');
                    const satuanInput = row.querySelector('input[name="satuan[]"]');
                    const expInput = row.querySelector('input[name="tanggal_exp[]"]');
                    const selected = select.value;

                    // Build ulang option produk
                    let html = '<option value="">-- Pilih Produk --</option>';
                    data.forEach(function(item) {
                        html += `<option value="${item.kode_produk}" data-satuan="${item.satuan}" data-exp="${item.tanggal_exp || ''}" data-stok="${item.stok}"`
                            + (item.kode_produk == selected ? ' selected' : '')
                            + `>${item.nama_produk} (${item.stok} ${item.satuan})</option>`;
                    });
                    select.innerHTML = html;

                    // Reset satuan & exp jika produk tidak ditemukan
                    const found = data.find(d => d.kode_produk == selected);
                    satuanInput.value = found ? found.satuan : '';
                    expInput.value = found && found.tanggal_exp ? found.tanggal_exp : '';
                });
            });
    }

    // Saat produk dipilih, update satuan & exp
    function produkSelectChange(e) {
        const opt = e.target.selectedOptions[0];
        const row = e.target.closest('tr');
        row.querySelector('input[name="satuan[]"]').value = opt.dataset.satuan || '';
        row.querySelector('input[name="tanggal_exp[]"]').value = opt.dataset.exp || '';
    }

    // Event binding
    @if(auth()->user() && auth()->user()->hasRole('admin'))
        lokasiAsalSelect.addEventListener('change', function() {
            updateLokasiTujuan();
            updateProdukList();
        });
    @endif

    // Inisialisasi awal
    updateLokasiTujuan();
    updateProdukList();

    // Event produk select change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('produk-select')) {
            produkSelectChange(e);
        }
    });

    // Tambah produk: clone baris, lalu update produk list
    document.getElementById('tambah-produk').addEventListener('click', function() {
        const tbody = document.getElementById('produk-list');
        const row = tbody.querySelector('.produk-item');
        const clone = row.cloneNode(true);

        // Reset value input
        clone.querySelectorAll('input, select').forEach(function(input) {
            if (input.tagName === 'SELECT') input.selectedIndex = 0;
            else input.value = '';
        });
        tbody.appendChild(clone);

        // Update nomor urut
        tbody.querySelectorAll('.produk-item').forEach(function(tr, i) {
            tr.querySelector('td').textContent = i + 1;
        });

        updateProdukList();
    });

    // Hapus produk
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-produk')) {
            const row = e.target.closest('tr');
            const tbody = document.getElementById('produk-list');
            if (tbody.rows.length > 1) {
                row.remove();
                // Update nomor urut
                tbody.querySelectorAll('.produk-item').forEach(function(tr, i) {
                    tr.querySelector('td').textContent = i + 1;
                });
            }
        }
    });
});
</script>
@endsection