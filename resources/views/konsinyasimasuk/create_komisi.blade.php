<!-- Modal Detail Produk Konsinyasi Masuk -->
<div class="modal fade" id="modalDetailProduk" tabindex="-1" aria-labelledby="modalDetailProdukLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDetailProdukLabel">Input Harga Jual Produk Konsinyasi Masuk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-bordered align-middle" id="tabel-produk-konsinyasi">
            <thead>
              <tr>
                <th class="text-center">No</th>
                <th class="text-center">No Konsinyasi Masuk</th>
                <th class="text-center">Nama Produk</th>
                <th class="text-center">Jumlah Stok</th>
                <th class="text-center">Harga Titip/Produk</th>
                <th class="text-center">Harga Jual/Produk</th>
              </tr>
            </thead>
            <tbody>
              <!-- Akan diisi JS -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let produkKonsinyasi = [];
let currentNoKonsinyasi = null;

// Fungsi untuk menampilkan modal dan mengisi data produk
function showModalDetailProduk(data, noKonsinyasi) {
    currentNoKonsinyasi = noKonsinyasi;
    produkKonsinyasi = data.map(item => ({
        ...item,
        no_konsinyasimasuk: noKonsinyasi
    }));
    renderTabelProduk();
    var modal = new bootstrap.Modal(document.getElementById('modalDetailProduk'));
    modal.show();
}

// Fungsi untuk render tabel produk di modal
function renderTabelProduk() {
    let tbody = document.querySelector('#tabel-produk-konsinyasi tbody');
    tbody.innerHTML = '';
    produkKonsinyasi.forEach((item, idx) => {
        tbody.innerHTML += `
        <tr>
            <td>${idx+1}</td>
            <td>${item.no_konsinyasimasuk || '-'}</td>
            <td>${item.nama_produk || '-'}</td>
            <td>${item.jumlah_stok}</td>
            <td>Rp${parseInt(item.harga_titip).toLocaleString('id-ID')}</td>
            <td>
                <input type="number" class="form-control form-control-sm" value="${item.harga_jual ?? ''}" min="0"
                    onchange="onHargaJualChange(${idx}, this.value)">
            </td>
            <td></td>
        </tr>`;
    });

    // Tambahkan tombol simpan di bawah tabel
    let table = document.getElementById('tabel-produk-konsinyasi');
    let tfoot = table.querySelector('tfoot');
    if (!tfoot) {
        tfoot = document.createElement('tfoot');
        table.appendChild(tfoot);
    }
    tfoot.innerHTML = `
        <tr>
            <td colspan="7" class="text-end">
                <button class="btn btn-primary" onclick="simpanSemuaHargaJual()">Simpan</button>
            </td>
        </tr>
    `;
}

// Fungsi untuk update harga jual di array produkKonsinyasi
function onHargaJualChange(idx, val) {
    produkKonsinyasi[idx].harga_jual = val;
}

// Fungsi untuk simpan harga jual ke backend
function simpanHargaJual(idx) {
    let item = produkKonsinyasi[idx];

    if (!item.harga_jual || item.harga_jual <= 0) {
        alert('Harga jual harus diisi dan lebih dari 0');
        return;
    }

    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Menyimpan...';
    button.disabled = true;

    console.log('simpanHargaJual - kirim:', {
        no_konsinyasimasuk: item.no_konsinyasimasuk,
        kode_produk: item.kode_produk,
        harga_jual: item.harga_jual
    });

    fetch('{{ route("konsinyasimasuk.update-harga-jual") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ 
            no_konsinyasimasuk: produkKonsinyasi[idx].no_konsinyasimasuk,
            kode_produk: produkKonsinyasi[idx].kode_produk,
            harga_jual: produkKonsinyasi[idx].harga_jual
        })
    })
    .then(async res => {
        if (!res.ok) {
            const error = await res.json();
            throw new Error(error.message || 'Gagal menyimpan');
        }
        return res.json();
    })
    .then(res => {
        if(res.success) {
            alert('Harga jual berhasil disimpan!');
        } else {
            throw new Error(res.message || 'Gagal menyimpan');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Gagal menyimpan: ' + err.message);
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

function simpanSemuaHargaJual() {
    // Validasi semua harga jual
    for (let i = 0; i < produkKonsinyasi.length; i++) {
        if (!produkKonsinyasi[i].harga_jual || produkKonsinyasi[i].harga_jual <= 0) {
            alert('Harga jual harus diisi dan lebih dari 0 untuk semua produk!');
            return;
        }
    }

    // Kirim satu per satu (atau bisa diubah ke batch jika endpoint mendukung)
    let promises = produkKonsinyasi.map(item => {
        return fetch('{{ route("konsinyasimasuk.update-harga-jual") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                no_konsinyasimasuk: item.no_konsinyasimasuk,
                kode_produk: item.kode_produk,
                harga_jual: item.harga_jual
            })
        }).then(res => res.json());
    });

    Promise.all(promises)
        .then(results => {
            if (results.every(r => r.success)) {
                alert('Semua harga jual berhasil disimpan!');
            } else {
                alert('Ada data yang gagal disimpan!');
            }
        })
        .catch(err => {
            alert('Gagal menyimpan: ' + err.message);
        });
}
</script>
