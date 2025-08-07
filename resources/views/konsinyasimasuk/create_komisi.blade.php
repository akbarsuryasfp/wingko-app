<!-- Modal Detail Produk Konsinyasi Masuk -->
<div class="modal fade" id="modalDetailProduk" tabindex="-1" aria-labelledby="modalDetailProdukLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDetailProdukLabel">Input Harga Jual Produk Konsinyasi Masuk</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body bg-light p-0">
        <div class="card border-0 shadow-sm mb-0">
          <div class="card-body p-3">
            <div class="table-responsive">
              <table class="table table-bordered align-middle mb-0" id="tabel-produk-konsinyasi" style="min-width: 650px; border:1.5px solid #adb5bd;">
                <thead style="position: sticky; top: 0; z-index: 2; background: #f8f9fa;">
                  <tr style="background: #f8f9fa;">
                    <th class="text-center fw-bold align-middle" style="width:36px; border-top:2px solid #adb5bd; border-bottom:3px solid #495057; border-right:2px solid #adb5bd; border-left:2px solid #adb5bd; font-size:1.05em;">No</th>
                    <th class="text-center fw-bold align-middle" style="width:120px; border-top:2px solid #adb5bd; border-bottom:3px solid #495057; border-right:2px solid #adb5bd; font-size:1.05em;">No Konsinyasi</th>
                    <th class="text-center fw-bold align-middle" style="min-width:40px; border-top:2px solid #adb5bd; border-bottom:3px solid #495057; border-right:2px solid #adb5bd; font-size:1.05em;">Nama Produk</th>
                    <th class="text-center fw-bold align-middle" style="width:60px; border-top:2px solid #adb5bd; border-bottom:3px solid #495057; border-right:2px solid #adb5bd; font-size:1.05em;">Satuan</th>
                    <th class="text-center fw-bold align-middle" style="width:70px; border-top:2px solid #adb5bd; border-bottom:3px solid #495057; border-right:2px solid #adb5bd; font-size:1.05em;">Stok</th>
                    <th class="text-center fw-bold align-middle" style="width:150px; border-top:2px solid #adb5bd; border-bottom:3px solid #495057; border-right:2px solid #adb5bd; font-size:1.05em;">Harga Titip/Satuan</th>
                    <th class="text-center fw-bold align-middle" style="width:150px; border-top:2px solid #adb5bd; border-bottom:3px solid #495057; border-right:2px solid #adb5bd; font-size:1.05em;">Harga Jual/Satuan</th>
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
        let hargaJualStr = '';
        if (item.harga_jual !== undefined && item.harga_jual !== null && item.harga_jual !== '') {
            // Only show integer part, no decimals
            hargaJualStr = formatNumberInput(String(parseInt(item.harga_jual)));
        }
        tbody.innerHTML += `
        <tr>
            <td class="text-center">${idx+1}</td>
            <td class="text-center">${item.no_konsinyasimasuk || '-'}<\/td>
            <td class="text-center">${item.nama_produk || '-'}<\/td>
            <td class="text-center">${item.satuan || '-'}<\/td>
            <td class="text-center">${item.jumlah_stok}<\/td>
            <td class="text-center">Rp${parseInt(item.harga_titip).toLocaleString('id-ID')}<\/td>
            <td class="text-center">
                <div class="input-group">
                    <span class="input-group-text">Rp<\/span>
                    <input type="text" class="form-control form-control-sm text-center harga-jual-edit" data-idx="${idx}" value="${hargaJualStr}" autocomplete="off" inputmode="numeric">
                <\/div>
            <\/td>
            <td class="text-center"><\/td>
        <\/tr>`;
    });
    // Tambahkan event listener untuk input harga jual agar live format ribuan
    document.querySelectorAll('.harga-jual-edit').forEach(input => {
        input.addEventListener('input', function(e) {
            const idx = this.dataset.idx;
            const cursor = this.selectionStart;
            const oldLength = this.value.length;
            let val = this.value;
            // Only allow integer input, no decimals
            this.value = formatNumberInput(val);
            let newLength = this.value.length;
            this.setSelectionRange(cursor + (newLength - oldLength), cursor + (newLength - oldLength));
            // Update data
            const harga = parseNumberInput(this.value);
            produkKonsinyasi[idx].harga_jual = harga;
        });
        input.addEventListener('blur', function() {
            if (this.value) {
                this.value = formatNumberInput(this.value);
            }
        });
        input.addEventListener('focus', function() {
            const idx = this.dataset.idx;
            let val = produkKonsinyasi[idx].harga_jual !== undefined && produkKonsinyasi[idx].harga_jual !== null && produkKonsinyasi[idx].harga_jual !== '' ? String(parseInt(produkKonsinyasi[idx].harga_jual)) : '';
            this.value = val;
            this.setSelectionRange(this.value.length, this.value.length);
        });
    });
// Helper format ribuan
function formatNumberInput(val) {
    val = String(val).replace(/[^\d]/g, '');
    if (!val) return '';
    // Only show integer, no decimals or trailing zeros
    return val.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
function parseNumberInput(val) {
    return parseInt(String(val).replace(/\D/g, '')) || 0;
}

    // Tambahkan tombol simpan di bawah tabel
    let table = document.getElementById('tabel-produk-konsinyasi');
    let tfoot = table.querySelector('tfoot');
    if (!tfoot) {
        tfoot = document.createElement('tfoot');
        table.appendChild(tfoot);
    }
    tfoot.innerHTML = `
        <tr>
            <td colspan="7" class="text-end p-3">
                <button class="btn btn-primary px-4 py-2 fw-bold shadow-sm" style="font-size:1.1em;" onclick="simpanSemuaHargaJual()">
                  <i class="bi bi-save me-1"></i> Simpan Harga Jual
                </button>
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

    fetch('{{ url('/konsinyasimasuk') }}/' + item.no_konsinyasimasuk + '/update-harga-jual', {
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
        return fetch('{{ url('/konsinyasimasuk') }}/' + item.no_konsinyasimasuk + '/update-harga-jual', {
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
