@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>INPUT PENJUALAN PESANAN</h3>
    <form action="{{ route('penjualan.storePesanan') }}" method="POST">
        @csrf
        <div class="row mb-3">
            <!-- Kolom Kiri -->
            <div class="col-md-6" style="background:#e3e3e3">
                <div class="mb-2 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Kode Penjualan</label>
                    <input type="text" name="no_jual" class="form-control" value="{{ $no_jual ?? '' }}" readonly>
                </div>
                <div class="mb-2 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Tanggal Penjualan</label>
                    <input type="date" name="tanggal_jual" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <!-- Kolom Kanan -->
            <div class="col-md-6" style="background:#c3c3ff">
                <div class="mb-2 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Kode Pesan</label>
                    <select name="no_pesanan" class="form-control" required id="no_pesanan">
                        <option value="">---Pilih Kode Terima Bahan---</option>
                        @foreach($pesanan as $psn)
                            <option value="{{ $psn->no_pesanan }}" 
                                data-tanggal="{{ $psn->tanggal_pesan }}"
                                data-pelanggan="{{ $psn->pelanggan->nama_pelanggan }}"
                                {{ old('no_pesanan') == $psn->no_pesanan ? 'selected' : '' }}>
                                {{ $psn->no_pesanan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Tanggal Pesan</label>
                    <input type="text" id="tanggal_pesan" class="form-control" readonly>
                </div>
                <div class="mb-2 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Nama Pelanggan</label>
                    <input type="text" id="nama_pelanggan" class="form-control" readonly>
                </div>
            </div>
        </div>

        <h5 class="mt-4">DAFTAR PESANAN PELANGGAN</h5>
        <table class="table table-bordered text-center align-middle" id="daftar-produk">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Jumlah Pesan</th>
                    <th>Harga Jual</th>
                    <th>Sub Total</th>
                </tr>
            </thead>
            <tbody>
                {{-- Data produk pesanan akan diisi via JS --}}
            </tbody>
        </table>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Harga</label>
                    <div class="col-sm-8">
                        <input type="text" id="total_harga" name="total_harga" class="form-control" readonly>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Ongkos Kirim</label>
                    <div class="col-sm-8">
                        <input type="number" id="ongkos_kirim" name="ongkos_kirim" class="form-control" value="0" min="0" oninput="hitungTotalJual()">
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Diskon</label>
                    <div class="col-sm-8">
                        <input type="number" id="diskon" name="diskon" class="form-control" value="0" min="0" oninput="hitungTotalJual()">
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Penjualan</label>
                    <div class="col-sm-8">
                        <input type="text" id="total_jual" name="total_jual" class="form-control" readonly>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Jenis Pembayaran</label>
                    <div class="col-sm-8">
                        <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                            <option value="">---Pilih Pembayaran---</option>
                            <option value="tunai">Tunai</option>
                            <option value="non tunai">Non Tunai</option>
                        </select>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Bayar</label>
                    <div class="col-sm-8">
                        <input type="number" id="total_bayar" name="total_bayar" class="form-control" value="0" min="0" oninput="hitungTotalJual()">
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Kembalian</label>
                    <div class="col-sm-8">
                        <input type="text" id="kembalian" name="kembalian" class="form-control" readonly>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <div class="w-100">
                    <label class="col-form-label">Piutang</label>
                    <input type="number" id="piutang" name="piutang" class="form-control mb-2" readonly>
                    <label class="col-form-label">Status Pembayaran</label>
                    <input type="text" id="status_pembayaran" name="status_pembayaran" class="form-control mb-2" readonly tabindex="-1">
                    <label class="col-form-label">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control mb-2">
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <div>
                <a href="{{ route('penjualan.index') }}" class="btn btn-secondary">Back</a>
                <button type="reset" class="btn btn-warning">Reset</button>
            </div>
            <button type="submit" class="btn btn-success">Submit</button>
        </div>
        <input type="hidden" name="detail_json" id="detail_json">
    </form>
</div>

<script>
    // Data pesanan dari backend (detail produk per pesanan)
    let pesananDetails = @json($pesananDetails ?? []);
    let daftarProduk = [];

    // Saat kode pesanan dipilih, isi tanggal pesan, nama pelanggan, dan detail produk
    document.getElementById('no_pesanan').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        document.getElementById('tanggal_pesan').value = selected.getAttribute('data-tanggal') || '';
        document.getElementById('nama_pelanggan').value = selected.getAttribute('data-pelanggan') || '';

        // Ambil detail produk dari pesananDetails
        const noPesanan = this.value;
        daftarProduk = pesananDetails[noPesanan] || [];
        updateTabel();
    });

    function updateTabel() {
        const tbody = document.querySelector('#daftar-produk tbody');
        tbody.innerHTML = '';
        let totalHarga = 0;
        daftarProduk.forEach((item, index) => {
            const subtotal = item.jumlah * item.harga_jual;
            item.subtotal = subtotal;
            totalHarga += subtotal;
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_bahan || item.nama_produk || '-'}</td>
                    <td><input type="text" class="form-control satuan-edit" value="${item.satuan || ''}" data-index="${index}" readonly></td>
                    <td><input type="number" class="form-control jumlah-edit" value="${item.jumlah}" data-index="${index}" min="1"></td>
                    <td><input type="number" class="form-control harga-edit" value="${item.harga_jual}" data-index="${index}" min="0"></td>
                    <td class="subtotal-edit">${subtotal.toLocaleString('id-ID')}</td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
        document.getElementById('total_harga').value = totalHarga;
        document.getElementById('detail_json').value = JSON.stringify(daftarProduk);
        hitungTotalJual();
    }

    // Event delegation untuk input jumlah/harga_satuan
    // agar update subtotal, total, piutang, status otomatis
    $(document).on('input', '.jumlah-edit, .harga-edit', function() {
        const idx = $(this).data('index');
        const tr = $(this).closest('tr');
        const jumlah = parseInt(tr.find('.jumlah-edit').val()) || 0;
        const harga = parseInt(tr.find('.harga-edit').val()) || 0;
        daftarProduk[idx].jumlah = jumlah;
        daftarProduk[idx].harga_jual = harga;
        daftarProduk[idx].subtotal = jumlah * harga;
        tr.find('.subtotal-edit').text((jumlah * harga).toLocaleString('id-ID'));
        let totalHarga = daftarProduk.reduce((sum, item) => sum + (item.jumlah * item.harga_jual), 0);
        $('#total_harga').val(totalHarga);
        $('#detail_json').val(JSON.stringify(daftarProduk));
        hitungTotalJual();
    });

    // Event untuk input total bayar, diskon, ongkos kirim, metode pembayaran
    $(document).on('input', '#total_bayar, #diskon, #ongkos_kirim', function() {
        hitungTotalJual();
    });
    $(document).on('change', '#metode_pembayaran', function() {
        hitungTotalJual();
    });
    function hitungTotalJual() {
        let totalHarga = parseInt($('#total_harga').val()) || 0;
        let ongkosKirim = parseInt($('#ongkos_kirim').val()) || 0;
        let diskon = parseInt($('#diskon').val()) || 0;
        let totalJual = totalHarga + ongkosKirim - diskon;
        if (totalJual < 0) totalJual = 0;
        $('#total_jual').val(totalJual);
        let totalBayar = parseInt($('#total_bayar').val()) || 0;
        let kembalian = 0, piutang = 0;
        if ($('#metode_pembayaran').val() === 'tunai') {
            kembalian = totalBayar > totalJual ? totalBayar - totalJual : 0;
            piutang = 0;
        } else {
            kembalian = 0;
            piutang = totalJual - totalBayar > 0 ? totalJual - totalBayar : 0;
        }
        $('#kembalian').val(kembalian);
        $('#piutang').val(piutang);
        // Status pembayaran otomatis
        let status = 'belum lunas';
        if (totalBayar === totalJual && totalJual > 0) {
            status = 'lunas';
        }
        $('#status_pembayaran').val(status);
    }

    // Inisialisasi jika sudah ada pesanan terpilih
    @if(old('no_pesanan'))
        document.getElementById('no_pesanan').dispatchEvent(new Event('change'));
    @endif
</script>
@endsection