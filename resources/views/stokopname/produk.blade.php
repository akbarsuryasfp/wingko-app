
@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Stok Opname Produk</h4>


    <form action="{{ route('stokopname.storeProduk') }}" method="POST">
        @csrf
        <div class="tab-content" id="kategoriTabContentProduk">
            <!-- Tab Produk Utama -->
            <div class="tab-pane fade show active" id="produk-utama" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                          <tr class="text-center align-middle">
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
    @foreach ($produkList as $produk)
    <tr>
        <td>{{ $no++ }}</td>
        <td>{{ $produk->kode_produk }}</td>
        <td>{{ $produk->nama_produk }}</td>
        <td>{{ $produk->satuan }}</td>
        <td>
            <input type="number"
                   name="stok_sistem[{{ $produk->kode_produk }}]"
                   class="form-control text-end"
                   value="{{ $produk->stok_sistem ?? 0 }}"
                   readonly
                   data-id="{{ $produk->kode_produk }}">
        </td>
        <td>
            <input type="number"
                   name="stok_fisik[{{ $produk->kode_produk }}]"
                   class="form-control text-end stok-fisik-input"
                   data-id="{{ $produk->kode_produk }}">
        </td>
        <td>
            <input type="text"
                   id="selisih_{{ $produk->kode_produk }}"
                   class="form-control text-end bg-light"
                   readonly>
        </td>
        <td>
            <input type="text" name="keterangan[{{ $produk->kode_produk }}]" class="form-control">
        </td>
    </tr>
    @endforeach
    @if ($produkList->count() == 0)
    <tr>
        <td colspan="9" class="text-center">Tidak ada data produk.</td>
    </tr>
    @endif
</tbody>
                    </table>
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

</script>
@endsection