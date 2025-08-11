@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3>INPUT PENJUALAN LANGSUNG</h3>
            <form action="{{ route('penjualan.store') }}" method="POST">
        @csrf
        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <!-- Kolom Kiri: Data Penjualan -->
            <div style="flex: 1;">
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">No Jual</label>
                    <input type="text" name="no_jual" class="form-control" value="{{ $no_jual }}" readonly style="pointer-events: none; background: #e9ecef;">
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Tanggal Jual</label>
                    <input type="date" name="tanggal_jual" class="form-control" required>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 140px;">Nama Pelanggan</label>
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
                            <option value="{{ $pr->kode_produk }}" data-nama="{{ $pr->nama_produk }}" data-satuan="{{ $pr->satuan ?? '' }}" data-harga="{{ $pr->nama_produk == 'Moaci' ? 25000 : ($pr->nama_produk == 'Wingko Babat' ? 20000 : 0) }}">
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
                    <label class="me-2" style="width: 120px;">Satuan</label>
                    <input type="text" id="satuan_produk" class="form-control" style="" readonly>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Harga/Satuan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="harga_satuan" class="form-control" autocomplete="off">
                    </div>
                </div>
                <div class="mb-3 d-flex align-items-center">
                    <label class="me-2" style="width: 120px;">Diskon/Satuan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" id="diskon_satuan" class="form-control" value="0" min="0" autocomplete="off">
                    </div>
                </div>
                <!-- Tambahkan di dekat input produk -->
                <div id="stok-info" class="mb-2 text-muted"></div>
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
                    <th>Satuan</th>
                    <th>Harga/Satuan</th>
                    <th>Diskon/Satuan</th>
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
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="total_harga" name="total_harga" value="0" readonly style="background: #e9ecef; pointer-events: none;">
                        </div>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Diskon (Rp)</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="diskon" name="diskon" value="0" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Jual</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="total_jual" name="total_jual" value="0" readonly style="background: #e9ecef; pointer-events: none;">
                        </div>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Total Bayar</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="total_bayar" name="total_bayar" value="0" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Kembalian</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="kembalian" name="kembalian" value="0" readonly style="background: #e9ecef; pointer-events: none;">
                        </div>
                    </div>
                </div>
                <div class="mb-2 row align-items-center">
                    <label class="col-sm-4 col-form-label">Piutang</label>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="piutang" name="piutang" value="0" readonly style="background: #e9ecef; pointer-events: none;">
                        </div>
                    </div>
                </div>
                <div class="mb-2 row align-items-center" id="row-jatuh-tempo" style="display: none;">
                    <label class="col-sm-4 col-form-label">Tanggal Jatuh Tempo</label>
                    <div class="col-sm-8">
                        <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" class="form-control">
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
    </div>
</div>

<script>
    // Helper untuk format angka ribuan (titik) saat mengetik
    function formatNumberInput(val) {
        val = val.replace(/[^\d]/g, '');
        if (!val) return '';
        return val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function parseNumberInput(val) {
        return parseInt(val.replace(/\D/g, '')) || 0;
    }

    // Helper: format input ribuan saat mengetik (untuk input text/number)
    function addLiveRibuanFormat(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.addEventListener('input', function(e) {
            const cursor = this.selectionStart;
            const oldLength = this.value.length;
            let val = this.value;
            this.value = formatNumberInput(val);
            // Kembalikan posisi kursor
            const newLength = this.value.length;
            this.setSelectionRange(cursor + (newLength - oldLength), cursor + (newLength - oldLength));
        });
    }

    // Terapkan ke semua input yang perlu live ribuan
    addLiveRibuanFormat('harga_satuan');
    addLiveRibuanFormat('diskon_satuan');
    addLiveRibuanFormat('diskon');
    addLiveRibuanFormat('total_bayar');
    addLiveRibuanFormat('kembalian');

    // Untuk field readonly: tampilkan format ribuan di value-nya
    function setReadonlyRibuan(id, val) {
        const el = document.getElementById(id);
        if (el) el.value = formatNumberInput(String(val));
    }
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
        const satuan = produkSelect.options[produkSelect.selectedIndex].dataset.satuan || '';
        const jumlah = parseFloat(document.getElementById('jumlah').value);
        const harga_satuan = parseNumberInput(document.getElementById('harga_satuan').value);
        let diskon_satuan = parseNumberInput(document.getElementById('diskon_satuan').value) || 0;

        // Diskon/satuan sekarang bisa diisi manual oleh user, tidak otomatis

        if (!kode_produk || !jumlah || !harga_satuan || jumlah <= 0 || harga_satuan <= 0) {
            alert("Silakan lengkapi data produk.");
            return;
        }

        // Cek apakah produk sudah ada di daftar
        if (daftarProduk.some(item => item.kode_produk === kode_produk)) {
            alert("Produk sudah ada di daftar!");
            return;
        }

        const total_diskon = diskon_satuan * jumlah;
        const subtotal = (harga_satuan * jumlah) - total_diskon;
        daftarProduk.push({ kode_produk, nama_produk, satuan, jumlah, harga_satuan, diskon_satuan, total_diskon, subtotal });
        updateTabel();

        // Reset input produk
        produkSelect.selectedIndex = 0;
        document.getElementById('jumlah').value = '';
        document.getElementById('harga_satuan').value = '';
        document.getElementById('diskon_satuan').value = 0;
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
        let totalDiskon = 0;

        daftarProduk.forEach((item, index) => {
            totalHarga += item.subtotal;
            totalDiskon += item.total_diskon || 0;
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.nama_produk}</td>
                    <td>${item.jumlah}</td>
                    <td>${item.satuan}</td>
                    <td>Rp${item.harga_satuan.toLocaleString('id-ID')}</td>
                    <td>(Rp${(item.diskon_satuan || 0).toLocaleString('id-ID')})</td>
                    <td>Rp${item.subtotal.toLocaleString('id-ID')}</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(${index})" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        // Set total harga dan total diskon (readonly, tampilkan ribuan)
        setReadonlyRibuan('total_harga', totalHarga);
        if(document.getElementById('total_diskon')) document.getElementById('total_diskon').value = totalDiskon;
        document.getElementById('detail_json').value = JSON.stringify(daftarProduk);
        hitungTotalLain();
    }

    function hitungTotalLain() {
        let totalHarga = daftarProduk.reduce((sum, item) => sum + item.subtotal, 0);
        let totalDiskon = daftarProduk.reduce((sum, item) => sum + (item.total_diskon || 0), 0);
        let diskonTambahan = parseNumberInput(document.getElementById('diskon').value) || 0;
        let tipeDiskon = document.getElementById('tipe_diskon') ? document.getElementById('tipe_diskon').value : 'rupiah';
        let diskonValue = diskonTambahan;
        if (tipeDiskon === 'persen') {
            diskonValue = totalHarga * (diskonTambahan / 100);
        }
        // Total jual = totalHarga - diskon tambahan
        let totalJual = totalHarga - diskonValue;
        if (totalJual < 0) totalJual = 0;
        setReadonlyRibuan('total_harga', totalHarga);
        if(document.getElementById('total_diskon')) document.getElementById('total_diskon').value = totalDiskon;
        setReadonlyRibuan('total_jual', totalJual);
        let totalBayar = parseNumberInput(document.getElementById('total_bayar').value) || 0;
        let kembalian = 0, piutang = 0;
        if (totalBayar > totalJual) {
            kembalian = totalBayar - totalJual;
            piutang = 0;
        } else {
            kembalian = 0;
            piutang = totalJual - totalBayar > 0 ? totalJual - totalBayar : 0;
        }
        setReadonlyRibuan('kembalian', kembalian);
        setReadonlyRibuan('piutang', piutang);

        // Tampilkan input tanggal jatuh tempo jika piutang > 0
        var rowJatuhTempo = document.getElementById('row-jatuh-tempo');
        var inputJatuhTempo = document.getElementById('tanggal_jatuh_tempo');
        if (piutang > 0) {
            rowJatuhTempo.style.display = '';
            inputJatuhTempo.required = true;
        } else {
            rowJatuhTempo.style.display = 'none';
            inputJatuhTempo.required = false;
            inputJatuhTempo.value = '';
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

    // Restore/fix: auto-fill satuan & harga_satuan when product selected
    document.getElementById('kode_produk').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const kode_produk = selected.value;
        const jenis = selected.getAttribute('data-jenis');
        const satuan = selected.getAttribute('data-satuan') || '';
        document.getElementById('satuan_produk').value = satuan;
        // Untuk universal, selalu coba fetch harga jual dari konsinyasi masuk detail
        if (kode_produk) {
            fetch('/api/harga-jual-konsinyasi/' + encodeURIComponent(kode_produk))
                .then(res => res.json())
                .then(data => {
                    if (data && data.harga_jual) {
                        document.getElementById('harga_satuan').value = formatNumberInput(String(parseInt(data.harga_jual)));
                    } else {
                        const harga = selected.getAttribute('data-harga');
                        document.getElementById('harga_satuan').value = harga ? formatNumberInput(String(parseInt(harga))) : '';
                    }
                })
                .catch(() => {
                    const harga = selected.getAttribute('data-harga');
                    document.getElementById('harga_satuan').value = harga ? formatNumberInput(String(parseInt(harga))) : '';
                });

            // Ambil stok dari kedua sumber
            const stokProdukUrl = '/api/stok-produk/' + encodeURIComponent(kode_produk);
            const stokKonsinyasiUrl = '/api/stok-produk-konsinyasi/' + encodeURIComponent(kode_produk);
            Promise.all([
                fetch(stokProdukUrl).then(res => res.json()).catch(() => ({stok: null})),
                fetch(stokKonsinyasiUrl).then(res => res.json()).catch(() => ({stok_akhir: null}))
            ]).then(([stokProduk, stokKonsinyasi]) => {
                let info = '';
                // Handle stokProduk (bisa object {stok:...} atau array)
                let stokSendiri = null;
                if (Array.isArray(stokProduk)) {
                    stokSendiri = stokProduk.reduce((sum, item) => sum + (parseFloat(item.sisa || item.stok || 0)), 0);
                } else if (stokProduk && typeof stokProduk.stok !== 'undefined') {
                    stokSendiri = stokProduk.stok;
                }
                // Handle stok konsinyasi: hitung total sisa dari seluruh data (total masuk - keluar)
                let stokKons = null;
                if (stokKonsinyasi && typeof stokKonsinyasi.stok_akhir !== 'undefined') {
                    stokKons = stokKonsinyasi.stok_akhir;
                } else if (Array.isArray(stokKonsinyasi) && stokKonsinyasi.length > 0) {
                    stokKons = stokKonsinyasi.reduce((sum, item) => sum + (parseFloat(item.sisa || 0)), 0);
                }
                // Only show if stok > 0
                if (stokSendiri !== null && stokSendiri > 0) {
                    info += 'Stok Produk Sendiri: ' + stokSendiri + ' ' + satuan + '\n';
                }
                if (stokKons !== null && stokKons > 0) {
                    info += 'Stok Konsinyasi: ' + stokKons + ' ' + satuan;
                }
                if (!info) info = 'Stok tersedia: -';
                document.getElementById('stok-info').innerText = info.trim();
            });
        } else {
            document.getElementById('harga_satuan').value = '';
            document.getElementById('stok-info').innerText = '';
            document.getElementById('satuan_produk').value = '';
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

