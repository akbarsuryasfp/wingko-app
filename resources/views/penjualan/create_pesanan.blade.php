@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>INPUT PENJUALAN PESANAN</h3>
    <form action="{{ route('penjualan.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">No Jual</label>
                    <input type="text" name="no_jual" class="form-control" value="{{ $no_jual ?? '' }}" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">No Pesanan</label>
                    <select name="no_pesanan" id="no_pesanan" class="form-control" required>
                        <option value="">---Pilih Pesanan---</option>
                        @foreach($pesanan as $psn)
                            <option value="{{ $psn->no_pesanan }}"
                                data-tanggal="{{ $psn->tanggal_pesanan }}"
                                data-pelanggan="{{ $psn->nama_pelanggan }}"
                                data-kodepelanggan="{{ $psn->kode_pelanggan }}">
                                {{ $psn->no_pesanan }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Tanggal Jual</label>
                    <input type="date" name="tanggal_jual" class="form-control" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Tanggal Pesanan</label>
                    <input type="text" id="tanggal_pesanan" class="form-control" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <!-- Kosong atau bisa diisi field lain jika ada -->
            </div>
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Pelanggan</label>
                    <input type="text" id="nama_pelanggan" class="form-control" readonly>
                    <input type="hidden" name="kode_pelanggan" id="kode_pelanggan">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <!-- Kosong atau bisa diisi field lain jika ada -->
            </div>
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                        <option value="">---Pilih Metode---</option>
                        <option value="tunai">Tunai</option>
                        <option value="non tunai">Non Tunai</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <!-- Kosong atau bisa diisi field lain jika ada -->
            </div>
            <div class="col-md-6">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control">
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PESANAN PELANGGAN</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga/Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                {{-- Akan diisi otomatis oleh JS --}}
            </tbody>
        </table>

        <!-- Total dan Lain-lain -->
        <div class="row justify-content-start">
            <div class="col-md-6">
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Harga</label>
                    <div class="col-sm-8">
                        <input type="text" id="total_harga" name="total_harga" class="form-control" readonly>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Diskon (Rp)</label>
                    <div class="col-sm-8">
                        <input type="number" id="diskon" name="diskon" class="form-control" value="0" min="0" oninput="hitungTotalLain()">
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Jual</label>
                    <div class="col-sm-8">
                        <input type="text" id="total_jual" name="total_jual" class="form-control" readonly>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Bayar</label>
                    <div class="col-sm-8">
                        <input type="number" id="total_bayar" name="total_bayar" class="form-control" value="0" min="0" oninput="hitungTotalLain()">
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Kembalian</label>
                    <div class="col-sm-8">
                        <input type="text" id="kembalian" name="kembalian" class="form-control" readonly>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Piutang</label>
                    <div class="col-sm-8">
                        <input type="text" id="piutang" class="form-control" readonly>
                        <input type="hidden" name="piutang" id="piutang_hidden">
                    </div>
                </div>
                <div class="mb-2 row align-items-center" id="row-jatuh-tempo" style="display:none;">
                    <label class="col-sm-4 col-form-label">Tanggal Jatuh Tempo</label>
                    <div class="col-sm-8">
                        <input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" class="form-control">
                    </div>
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
        <input type="hidden" name="jenis_penjualan" value="{{ $jenis_penjualan }}">
    </form>
</div>

<script>
    // Data detail pesanan dari backend (controller harus mengirim $pesananDetails)
    let pesananDetails = @json($pesananDetails ?? []);
    let daftarProduk = [];

    document.getElementById('no_pesanan').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        // Cek jika option valid (bukan placeholder)
        if (!selected || !selected.value) {
            document.getElementById('tanggal_pesanan').value = '';
            document.getElementById('nama_pelanggan').value = '';
            document.getElementById('kode_pelanggan').value = '';
            daftarProduk = [];
            updateTabel();
            return;
        }
        // Ambil data dari attribute option
        const tanggal = selected.getAttribute('data-tanggal') || '';
        const pelanggan = selected.getAttribute('data-pelanggan') || '';
        const kodePelanggan = selected.getAttribute('data-kodepelanggan') || '';
        // Format tanggal pesanan ke dd-mm-yyyy jika ada
        if (tanggal) {
            const tglObj = new Date(tanggal);
            if (!isNaN(tglObj)) {
                const dd = String(tglObj.getDate()).padStart(2, '0');
                const mm = String(tglObj.getMonth() + 1).padStart(2, '0');
                const yyyy = tglObj.getFullYear();
                document.getElementById('tanggal_pesanan').value = `${dd}-${mm}-${yyyy}`;
            } else {
                document.getElementById('tanggal_pesanan').value = tanggal;
            }
        } else {
            document.getElementById('tanggal_pesanan').value = '';
        }
        document.getElementById('nama_pelanggan').value = pelanggan;
        document.getElementById('kode_pelanggan').value = kodePelanggan;

        // Ambil detail produk dari pesananDetails
        const noPesanan = selected.value;
        daftarProduk = pesananDetails[noPesanan] || [];
        updateTabel();
    });

    // Trigger otomatis jika hanya ada satu pesanan (atau sudah terpilih)
    window.addEventListener('DOMContentLoaded', function() {
        var select = document.getElementById('no_pesanan');
        if(select.value) {
            select.dispatchEvent(new Event('change'));
        }
    });

    function formatRupiah(angka) {
        if (!angka && angka !== 0) return '';
        return 'Rp ' + parseFloat(angka).toLocaleString('id-ID');
    }

    function updateTabel() {
        const tbody = document.querySelector('#daftar-produk tbody');
        tbody.innerHTML = '';
        let totalHarga = 0;

        daftarProduk.forEach((item, index) => {
            // Pastikan kode_produk ada di setiap item
            if (!item.kode_produk && item.kode) item.kode_produk = item.kode;
            item.subtotal = item.jumlah * item.harga_satuan;
            totalHarga += item.subtotal;
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_produk}</td>
                    <td>${item.jumlah}</td>
                    <td>${formatRupiah(item.harga_satuan)}</td>
                    <td>${formatRupiah(item.subtotal)}</td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('total_harga').value = formatRupiah(totalHarga);
        // Kirim detail_json tanpa format Rp
        document.getElementById('detail_json').value = JSON.stringify(daftarProduk.map(item => ({
            kode_produk: item.kode_produk,
            nama_produk: item.nama_produk,
            jumlah: Number(item.jumlah),
            harga_satuan: Number(item.harga_satuan),
            subtotal: Number(item.subtotal)
        })));
        hitungTotalLain();
    }

    function hapusBaris(index) {
        daftarProduk.splice(index, 1);
        updateTabel();
    }

    function hitungTotalLain() {
        let totalHarga = parseFloat((document.getElementById('total_harga').value || '0').replace(/[^\d]/g, '')) || 0;
        let diskon = parseFloat(document.getElementById('diskon').value || '0') || 0;
        let totalJual = totalHarga - diskon;
        if (totalJual < 0) totalJual = 0;
        document.getElementById('total_jual').value = formatRupiah(totalJual);

        let totalBayar = parseFloat(document.getElementById('total_bayar').value || '0') || 0;
        let kembalian = 0, piutang = 0;

        if (totalBayar > totalJual) {
            kembalian = totalBayar - totalJual;
            piutang = 0;
        } else {
            kembalian = 0;
            piutang = totalJual - totalBayar > 0 ? totalJual - totalBayar : 0;
        }
        document.getElementById('kembalian').value = formatRupiah(kembalian);
        document.getElementById('piutang').value = formatRupiah(piutang);
        document.getElementById('piutang_hidden').value = piutang;

        // Tampilkan input tanggal jatuh tempo jika piutang > 0
        document.getElementById('row-jatuh-tempo').style.display = (piutang > 0) ? '' : 'none';
        document.getElementById('tanggal_jatuh_tempo').required = (piutang > 0);
    }

    // HAPUS event input yang mem-format diskon dan total bayar ke format Rp
    // document.getElementById('diskon').addEventListener('input', function() {
    //     let val = this.value.replace(/[^\d]/g, '');
    //     this.value = val ? formatRupiah(val) : '';
    //     hitungTotalLain();
    // });
    // document.getElementById('total_bayar').addEventListener('input', function() {
    //     let val = this.value.replace(/[^\d]/g, '');
    //     this.value = val ? formatRupiah(val) : '';
    //     hitungTotalLain();
    // });
    // Ganti dengan event input biasa agar tetap hitung ulang tanpa format Rp
    document.getElementById('diskon').addEventListener('input', hitungTotalLain);
    document.getElementById('total_bayar').addEventListener('input', hitungTotalLain);

    document.querySelector('form').addEventListener('submit', function(e) {
        if (daftarProduk.length === 0) {
            alert('Minimal 1 produk harus ditambahkan!');
            e.preventDefault();
            return false;
        }
        // Pastikan hidden piutang ikut terkirim
        document.getElementById('piutang_hidden').value = parseFloat((document.getElementById('piutang').value || '0').replace(/[^\\d]/g, '')) || 0;
        // Pastikan total_harga dan total_jual juga dalam bentuk angka
        document.getElementById('total_harga').value = parseFloat((document.getElementById('total_harga').value || '0').replace(/[^ -9]/g, '')) || 0;
        document.getElementById('total_jual').value = parseFloat((document.getElementById('total_jual').value || '0').replace(/[^ -9]/g, '')) || 0;
    });
</script>
@endsection