@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>INPUT PENJUALAN LANGSUNG</h3>
    <form action="{{ route('penjualan.store') }}" method="POST">
        @csrf
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Penjualan -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">No Jual</label>
                    <input type="text" name="no_jual" class="form-control" value="{{ $no_jual }}" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Tanggal Jual</label>
                    <input type="date" name="tanggal_jual" class="form-control" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Pelanggan</label>
                    <select name="kode_pelanggan" class="form-control" required>
                        <option value="">---Pilih Pelanggan---</option>
                        @foreach($pelanggan as $p)
                            <option value="{{ $p->kode_pelanggan }}">{{ $p->nama_pelanggan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Metode Pembayaran</label>
                    <select name="metode_pembayaran" id="metode_pembayaran" class="form-control" required>
                        <option value="">---Pilih Metode---</option>
                        <option value="tunai">Tunai</option>
                        <option value="non tunai">Non Tunai</option>
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control">
                </div>
            </div>
            <!-- Kolom Kanan: Data Produk -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Nama Produk</label>
                    <select id="kode_produk" class="form-control">
                        <option value="">---Pilih Produk---</option>
                        @foreach($produk as $pr)
                            <option value="{{ $pr->kode_produk }}" data-nama="{{ $pr->nama_produk }}"
                                data-harga="{{ $pr->nama_produk == 'Moaci' ? 25000 : ($pr->nama_produk == 'Wingko Babat' ? 20000 : 0) }}">
                                {{ $pr->nama_produk }} 
                                @if(isset($pr->jenis) && $pr->jenis == 'konsinyasi') (Konsinyasi) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Jumlah</label>
                    <input type="number" id="jumlah" class="form-control">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Harga/Satuan</label>
                    <input type="number" id="harga_satuan" class="form-control">
                </div>
                <div class="mb-3 d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary w-100" onclick="tambahProduk()">Tambah Produk</button>
                </div>
            </div>
        </div>

        <hr>

        <h4 class="text-center">DAFTAR PENJUALAN PRODUK</h4>
        <table class="table table-bordered text-center align-middle" id="daftar-produk">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Jumlah</th>
                    <th>Harga/Satuan</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <!-- Tambahan: Total dan Lain-lain -->
        <div class="row justify-content-start">
            <div class="col-md-6">
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Harga</label>
                    <div class="col-sm-8">
                        <input type="text" id="total_harga" name="total_harga" class="form-control" readonly>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Diskon</label>
                    <div class="col-sm-4">
                        <input type="number" id="diskon" name="diskon" class="form-control" value="0" min="0" oninput="hitungTotalLain()">
                    </div>
                    <div class="col-sm-4">
                        <select id="tipe_diskon" name="tipe_diskon" class="form-control" onchange="hitungTotalLain()">
                            <option value="rupiah">Rp</option>
                            <option value="persen">%</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <span id="diskon_label">Rp0</span>
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
                        <input type="text" id="piutang" name="piutang" class="form-control" readonly>
                    </div>
                </div>
                <!-- Hapus/komentari bagian status pembayaran berikut -->
                <!--
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Status Pembayaran</label>
                    <div class="col-sm-8">
                        <select name="status_pembayaran" id="status_pembayaran" class="form-control" required readonly>
                            <option value="lunas">Lunas</option>
                            <option value="belum lunas">Belum Lunas</option>
                        </select>
                    </div>
                </div>
                -->
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
    let daftarProduk = [];
    const defaultForm = {
        tanggal_jual: '',
        kode_pelanggan: '',
        metode_pembayaran: '',
        keterangan: '',
        diskon: 0,
        total_bayar: 0
    };

    function tambahProduk() {
        const produkSelect = document.getElementById('kode_produk');
        const kode_produk = produkSelect.value;
        const nama_produk = produkSelect.options[produkSelect.selectedIndex].dataset.nama;
        const jumlah = parseFloat(document.getElementById('jumlah').value);
        const harga_satuan = parseFloat(document.getElementById('harga_satuan').value);

        if (!kode_produk || !jumlah || !harga_satuan || jumlah <= 0 || harga_satuan <= 0) {
            alert("Silakan lengkapi data produk.");
            return;
        }

        // Cek apakah produk sudah ada di daftar
        if (daftarProduk.some(item => item.kode_produk === kode_produk)) {
            alert("Produk sudah ada di daftar!");
            return;
        }

        const subtotal = jumlah * harga_satuan;
        daftarProduk.push({ kode_produk, nama_produk, jumlah, harga_satuan, subtotal });
        updateTabel();

        // Reset input produk
        produkSelect.selectedIndex = 0;
        document.getElementById('jumlah').value = '';
        document.getElementById('harga_satuan').value = '';
    }

    function hapusBaris(index) {
        daftarProduk.splice(index, 1);
        updateTabel();
    }

    function formatRupiah(angka) {
        if (isNaN(angka)) return '-';
        return 'Rp' + angka.toLocaleString('id-ID');
    }
    function parseRupiah(str) {
        if (!str) return 0;
        return parseInt(String(str).replace(/[^\d]/g, '')) || 0;
    }

    function updateTabel() {
        const tbody = document.querySelector('#daftar-produk tbody');
        tbody.innerHTML = '';

        let totalHarga = 0;

        daftarProduk.forEach((item, index) => {
            totalHarga += item.subtotal;

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_produk}</td>
                    <td>${item.jumlah}</td>
                    <td>${formatRupiah(item.harga_satuan)}</td>
                    <td>${formatRupiah(item.subtotal)}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(${index})" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('total_harga').value = totalHarga;
        document.getElementById('detail_json').value = JSON.stringify(daftarProduk);
        hitungTotalLain();
    }

    function hitungTotalLain() {
        let totalHarga = daftarProduk.reduce((sum, item) => sum + item.subtotal, 0);
        let diskon = parseFloat(document.getElementById('diskon').value) || 0;
        let tipeDiskon = document.getElementById('tipe_diskon') ? document.getElementById('tipe_diskon').value : 'rupiah';
        let diskonValue = diskon;
        if (tipeDiskon === 'persen') {
            diskonValue = totalHarga * (diskon / 100);
        }
        let totalJual = totalHarga - diskonValue;
        if (totalJual < 0) totalJual = 0;
        document.getElementById('total_harga').value = totalHarga;
        document.getElementById('total_jual').value = totalJual;
        let totalBayar = parseFloat(document.getElementById('total_bayar').value) || 0;
        let kembalian = 0, piutang = 0;
        if (totalBayar > totalJual) {
            kembalian = totalBayar - totalJual;
            piutang = 0;
        } else {
            kembalian = 0;
            piutang = totalJual - totalBayar > 0 ? totalJual - totalBayar : 0;
        }
        document.getElementById('kembalian').value = kembalian;
        document.getElementById('piutang').value = piutang;

        // Tampilkan format Rupiah di field readonly
        document.getElementById('total_harga').setAttribute('data-view', formatRupiah(totalHarga));
        document.getElementById('total_jual').setAttribute('data-view', formatRupiah(totalJual));
        document.getElementById('kembalian').setAttribute('data-view', formatRupiah(kembalian));
        document.getElementById('piutang').setAttribute('data-view', formatRupiah(piutang));

        // Tampilkan diskon dengan format sesuai tipe
        let diskonLabel = document.getElementById('diskon_label');
        if (diskonLabel) {
            if (tipeDiskon === 'persen') {
                diskonLabel.innerText = diskon + '%';
            } else {
                diskonLabel.innerText = formatRupiah(diskon);
            }
        }
    }

    document.getElementById('diskon').addEventListener('input', hitungTotalLain);
    document.getElementById('total_bayar').addEventListener('input', hitungTotalLain);
    if(document.getElementById('metode_pembayaran')) {
        document.getElementById('metode_pembayaran').addEventListener('change', hitungTotalLain);
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        if (daftarProduk.length === 0) {
            alert('Minimal 1 produk harus ditambahkan!');
            e.preventDefault();
            return false;
        }

        // Pisahkan produk sendiri dan konsinyasi
        const produkSendiri = daftarProduk.filter(p => !p.jenis || p.jenis !== 'konsinyasi');
        const produkKonsinyasi = daftarProduk.filter(p => p.jenis === 'konsinyasi');

        // Kirim produk sendiri ke PenjualanController
        if (produkSendiri.length > 0) {
            // Buat form dinamis untuk produk sendiri
            const formSendiri = document.createElement('form');
            formSendiri.method = 'POST';
            formSendiri.action = "{{ route('penjualan.store') }}";
            formSendiri.style.display = 'none';
            // CSRF
            formSendiri.innerHTML = `<input type='hidden' name='_token' value='${document.querySelector('input[name="_token"]').value}'>`;
            // Field lain
            ['no_jual','tanggal_jual','kode_pelanggan','metode_pembayaran','keterangan','total_harga','diskon','total_jual','total_bayar','kembalian','piutang','jenis_penjualan'].forEach(f => {
                const el = document.querySelector(`[name='${f}']`);
                if (el) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = f;
                    if(['total_harga','total_jual','kembalian','piutang'].includes(f)) {
                        input.value = parseRupiah(el.value);
                    } else {
                        input.value = el.value;
                    }
                    formSendiri.appendChild(input);
                }
            });
            // Detail produk sendiri
            const detailSendiri = document.createElement('input');
            detailSendiri.type = 'hidden';
            detailSendiri.name = 'detail_json';
            detailSendiri.value = JSON.stringify(produkSendiri);
            formSendiri.appendChild(detailSendiri);
            document.body.appendChild(formSendiri);
            formSendiri.submit();
        }

        // Kirim produk konsinyasi ke JualKonsinyasiMasukController
        if (produkKonsinyasi.length > 0) {
            // Buat form dinamis untuk produk konsinyasi
            const formKonsinyasi = document.createElement('form');
            formKonsinyasi.method = 'POST';
            formKonsinyasi.action = "{{ route('jualkonsinyasimasuk.store') }}";
            formKonsinyasi.style.display = 'none';
            // CSRF
            formKonsinyasi.innerHTML = `<input type='hidden' name='_token' value='${document.querySelector('input[name="_token"]').value}'>`;
            // Field lain (gunakan field yang sesuai kebutuhan controller konsinyasi)
            ['tanggal_jual','keterangan'].forEach(f => {
                const el = document.querySelector(`[name='${f}']`);
                if (el) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = f;
                    input.value = el.value;
                    formKonsinyasi.appendChild(input);
                }
            });
            // Field khusus konsinyasi
            // Misal: kode_consignor (bisa diambil dari produkKonsinyasi[0] jika satu consignor, atau tambahkan input di form)
            if (produkKonsinyasi.length > 0 && produkKonsinyasi[0].kode_consignor) {
                const inputConsignor = document.createElement('input');
                inputConsignor.type = 'hidden';
                inputConsignor.name = 'kode_consignor';
                inputConsignor.value = produkKonsinyasi[0].kode_consignor;
                formKonsinyasi.appendChild(inputConsignor);
            }
            // Total jual konsinyasi
            const totalJualKonsinyasi = produkKonsinyasi.reduce((sum, p) => sum + (p.subtotal || 0), 0);
            const inputTotalJual = document.createElement('input');
            inputTotalJual.type = 'hidden';
            inputTotalJual.name = 'total_jual';
            inputTotalJual.value = totalJualKonsinyasi;
            formKonsinyasi.appendChild(inputTotalJual);
            // Detail produk konsinyasi
            const detailKonsinyasi = document.createElement('input');
            detailKonsinyasi.type = 'hidden';
            detailKonsinyasi.name = 'detail_json';
            detailKonsinyasi.value = JSON.stringify(produkKonsinyasi);
            formKonsinyasi.appendChild(detailKonsinyasi);
            document.body.appendChild(formKonsinyasi);
            formKonsinyasi.submit();
        }

        // Cegah submit form utama
        e.preventDefault();
        return false;
    });

    document.getElementById('kode_produk').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const kode_produk = selected.value;
        // Cek apakah produk konsinyasi (bisa pakai data attribute atau cek ke backend jika perlu)
        // Untuk universal, selalu coba fetch harga jual dari konsinyasi masuk detail
        if (kode_produk) {
            fetch('/api/harga-jual-konsinyasi/' + encodeURIComponent(kode_produk))
                .then(res => res.json())
                .then(data => {
                    if (data && data.harga_jual) {
                        document.getElementById('harga_satuan').value = data.harga_jual;
                    } else {
                        // fallback ke data-harga jika tidak ada harga jual di konsinyasi masuk
                        const harga = selected.getAttribute('data-harga');
                        document.getElementById('harga_satuan').value = harga ? harga : '';
                    }
                })
                .catch(() => {
                    // fallback ke data-harga jika error
                    const harga = selected.getAttribute('data-harga');
                    document.getElementById('harga_satuan').value = harga ? harga : '';
                });
        } else {
            document.getElementById('harga_satuan').value = '';
        }
    });

    document.querySelector('form').addEventListener('reset', function(e) {
        setTimeout(function() {
            // Reset field ke nilai awal
            document.querySelector("input[name='tanggal_jual']").value = defaultForm.tanggal_jual;
            document.querySelector("select[name='kode_pelanggan']").value = defaultForm.kode_pelanggan;
            document.querySelector("select[name='metode_pembayaran']").value = defaultForm.metode_pembayaran;
            document.querySelector("input[name='keterangan']").value = defaultForm.keterangan;
            document.getElementById('diskon').value = defaultForm.diskon;
            document.getElementById('total_bayar').value = defaultForm.total_bayar;
            // Reset produk
            daftarProduk = [];
            updateTabel();
            // Reset field total
            document.getElementById('kembalian').value = 0;
            document.getElementById('piutang').value = 0;
        }, 10);
    });
</script>
@endsection

