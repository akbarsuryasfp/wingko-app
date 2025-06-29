@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Stok Opname Produk</h4>

    <!-- Tabs kategori produk -->
    <ul class="nav nav-tabs mb-3" id="kategoriTabProduk" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="utama-tab" data-bs-toggle="tab" data-bs-target="#produk-utama" type="button" role="tab">Produk Utama</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="konsinyasi-tab" data-bs-toggle="tab" data-bs-target="#produk-konsinyasi" type="button" role="tab">Produk Konsinyasi</button>
        </li>
    </ul>

    <form action="{{ route('stokopname.storeProduk') }}" method="POST">
        @csrf
        <div class="tab-content" id="kategoriTabContentProduk">
            <!-- Tab Produk Utama -->
            <div class="tab-pane fade show active" id="produk-utama" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Produk</th>
                                <th>Nama Produk</th>
                                <th>Satuan</th>
                                <th>Stok Sistem</th>
                                <th>Stok Fisik</th>
                                <th>Selisih</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach ($produkList->where('kode_kategori', 'PU') as $produk)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $produk->kode_produk }}</td>
                                <td>{{ $produk->nama_produk }}</td>
                                <td>{{ $produk->satuan }}</td>
                                <td>
                                    <input type="number"
                                           name="stok_sistem[{{ $produk->kode_produk }}]"
                                           class="form-control text-end"
                                           value="{{ $produk->stok }}"
                                           readonly
                                           data-id="{{ $produk->kode_produk }}"
                                           data-kategori="PU">
                                </td>
                                <td>
                                    <input type="number"
                                           name="stok_fisik[{{ $produk->kode_produk }}]"
                                           class="form-control text-end stok-fisik-input"
                                           data-id="{{ $produk->kode_produk }}"
                                           data-kategori="PU">
                                </td>
                                <td>
                                    <input type="text"
                                           id="selisih_{{ $produk->kode_produk }}_PU"
                                           class="form-control text-end bg-light"
                                           readonly>
                                </td>
                                <td>
                                    <input type="text" name="keterangan[{{ $produk->kode_produk }}]" class="form-control">
                                </td>
                            </tr>
                            @endforeach
                            @if ($produkList->where('kode_kategori', 'PU')->count() == 0)
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data produk utama.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Tab Produk Konsinyasi -->
            <div class="tab-pane fade" id="produk-konsinyasi" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Produk</th>
                                <th>Nama Produk</th>
                                <th>Satuan</th>
                                <th>Stok Sistem</th>
                                <th>Stok Fisik</th>
                                <th>Selisih</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach ($produkList->where('kode_kategori', 'PK') as $produk)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $produk->kode_produk }}</td>
                                <td>{{ $produk->nama_produk }}</td>
                                <td>{{ $produk->satuan }}</td>
                                <td>
                                    <input type="number"
                                           name="stok_sistem[{{ $produk->kode_produk }}]"
                                           class="form-control text-end"
                                           value="{{ $produk->stok }}"
                                           readonly
                                           data-id="{{ $produk->kode_produk }}"
                                           data-kategori="PK">
                                </td>
                                <td>
                                    <input type="number"
                                           name="stok_fisik[{{ $produk->kode_produk }}]"
                                           class="form-control text-end stok-fisik-input"
                                           data-id="{{ $produk->kode_produk }}"
                                           data-kategori="PK">
                                </td>
                                <td>
                                    <input type="text"
                                           id="selisih_{{ $produk->kode_produk }}_PK"
                                           class="form-control text-end bg-light"
                                           readonly>
                                </td>
                                <td>
                                    <input type="text" name="keterangan[{{ $produk->kode_produk }}]" class="form-control">
                                </td>
                            </tr>
                            @endforeach
                            @if ($produkList->where('kode_kategori', 'PK')->count() == 0)
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data produk konsinyasi.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <label>No Bukti Opname</label>
                <input type="text" name="no_opname" class="form-control" value="{{ $no_opname ?? '' }}" readonly>
            </div>
            <div class="col-md-4">
                <label>Tanggal</label>
                <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
                <label>Keterangan Umum</label>
                <input type="text" name="keterangan_umum" class="form-control">
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary">Simpan Stok Opname</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('input', function(e) {
    if (e.target && e.target.classList.contains('stok-fisik-input')) {
        const tr = e.target.closest('tr');
        if (!tr) return;
        const stokSistemInput = tr.querySelector('input[name^="stok_sistem"]');
        const stokFisikInput = e.target;
        const selisihInput = tr.querySelector('input[id^="selisih_"]');
        if (!stokSistemInput || !stokFisikInput || !selisihInput) return;
        const sistem = parseFloat(stokSistemInput.value) || 0;
        const fisik = parseFloat(stokFisikInput.value) || 0;
        const selisih = fisik - sistem;
        let formatted = '';
        if (selisih > 0) {
            formatted = '+' + selisih;
        } else if (selisih < 0) {
            formatted = selisih;
        } else {
            formatted = '0';
        }
        selisihInput.value = formatted;
        selisihInput.classList.remove('text-danger', 'text-success');
        if (selisih > 0) {
            selisihInput.classList.add('text-success');
        } else if (selisih < 0) {
            selisihInput.classList.add('text-danger');
        }
    }
});
window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.stok-fisik-input').forEach(function(input) {
        input.dispatchEvent(new Event('input'));
    });
});
function setTabInputState() {
    document.querySelectorAll('.tab-pane').forEach(function(tab) {
        const isActive = tab.classList.contains('active') && tab.classList.contains('show');
        tab.querySelectorAll('input, select, textarea').forEach(function(input) {
            if (!isActive) {
                input.setAttribute('disabled', 'disabled');
            } else {
                input.removeAttribute('disabled');
            }
        });
    });
}
document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function(btn) {
    btn.addEventListener('shown.bs.tab', setTabInputState);
});
window.addEventListener('DOMContentLoaded', setTabInputState);
</script>
@endsection