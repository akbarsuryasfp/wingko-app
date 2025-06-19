@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h4 class="mb-4">KARTU PERSEDIAAN BAHAN</h4>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="kode_bahan" class="form-label">Nama Bahan</label>
            <select id="kode_bahan" name="kode_bahan" class="form-control" onchange="setSatuanOtomatis()">
                <option value="">-- Pilih Bahan --</option>
                @foreach($bahanList as $bahan)
                    <option value="{{ $bahan->kode_bahan }}" data-satuan="{{ $bahan->satuan }}">{{ $bahan->nama_bahan }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label for="satuan" class="form-label">Satuan</label>
            <input type="text" id="satuan" name="satuan" class="form-control" value="{{ $satuan ?? '' }}" readonly>
        </div>
    </form>

    <h5 class="mb-3 mt-4">PERSEDIAAN BAHAN</h5>

    <div class="table-responsive">
        <table id="tabel-persediaan" class="table table-bordered text-center">
            <thead class="table-light">
                <tr>
            <th>No</th>
            <th>No Transaksi</th>
            <th>Tanggal</th>
            <th>Harga</th>
            <th>Masuk (Qty)</th>
            <th>Keluar (Qty)</th>
            <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data persediaan.</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="table-secondary">
                    <td colspan="6" class="text-end"><strong>Saldo Qty & Harga</strong></td>
                    <td id="tfoot-saldo"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

<script>
function setSatuanOtomatis() {
    var select = document.getElementById('kode_bahan');
    var satuan = select.options[select.selectedIndex].getAttribute('data-satuan') || '';
    document.getElementById('satuan').value = satuan;

    var kode_bahan = select.value;
    if (kode_bahan) {
        fetch('/kartustok/api/' + kode_bahan)
            .then(res => res.json())
            .then(data => {
                let tbody = '';
                let fifoStack = []; // Array of {qty, harga}
                let saldoQty = 0;

                if (data.length === 0) {
                    tbody = `<tr><td colspan="7" class="text-center">Tidak ada data persediaan.</td></tr>`;
                } else {
                    data.forEach(function(row, idx) {
                        let masuk = parseFloat(row.masuk) || 0;
                        let keluar = parseFloat(row.keluar) || 0;
                        let harga = parseFloat(row.harga);

                        // Barang masuk: push ke FIFO stack
                        if (masuk > 0) {
                            fifoStack.push({qty: masuk, harga: harga});
                        }

                        // Barang keluar: keluarkan dari FIFO stack
                        let sisaKeluar = keluar;
                        while (sisaKeluar > 0 && fifoStack.length > 0) {
                            if (fifoStack[0].qty > sisaKeluar) {
                                fifoStack[0].qty -= sisaKeluar;
                                sisaKeluar = 0;
                            } else {
                                sisaKeluar -= fifoStack[0].qty;
                                fifoStack.shift();
                            }
                        }

                        // Hitung saldo qty total (akumulasi semua harga)
                        saldoQty = fifoStack.reduce((sum, item) => sum + item.qty, 0);

                        tbody += `
                            <tr>
                                <td>${idx + 1}</td>
                                <td>${row.no_transaksi}</td>
                                <td>${row.tanggal}</td>
                                <td>${harga.toLocaleString('id-ID')}</td>
                                <td>${masuk}</td>
                                <td>${keluar}</td>
                                <td>${saldoQty}</td>
                            </tr>
                        `;
                    });
                }

                // Saldo akhir FIFO per harga (untuk footer)
                let saldoAkhirMap = {};
                fifoStack.forEach(item => {
                    if (!saldoAkhirMap[item.harga]) saldoAkhirMap[item.harga] = 0;
                    saldoAkhirMap[item.harga] += item.qty;
                });
                let saldoFooter = Object.keys(saldoAkhirMap).length === 0
                    ? '0'
                    : Object.entries(saldoAkhirMap).map(([h, q]) => `${q} @ Rp${parseFloat(h).toLocaleString('id-ID')}`).join('<br>');

                document.querySelector('#tabel-persediaan tbody').innerHTML = tbody;
                document.getElementById('tfoot-saldo').innerHTML = saldoFooter;
            });
    } else {
        document.querySelector('#tabel-persediaan tbody').innerHTML = `<tr><td colspan="7" class="text-center">Tidak ada data persediaan.</td></tr>`;
    }
}
</script>
