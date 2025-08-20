@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <!-- Card Header -->
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Stok Opname Produk</h5>
            <a href="{{ route('stokopname.riwayatProduk') }}" class="btn btn-success">
                <i class="bi bi-clock-history"></i> Riwayat Stok Opname
            </a>
        </div>

        <!-- Card Body -->
        <div class="card-body">
            <form action="{{ route('stokopname.storeProduk') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Tab Content -->
                <div class="tab-content" id="kategoriTabContentProduk">
                    <div class="tab-pane fade show active" id="produk-utama" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th width="5%">No</th>
                                        <th width="15%">Kode Produk</th>
                                        <th width="20%">Nama Produk</th>
                                        <th width="10%">Satuan</th>
                                        <th width="15%">Stok Sistem</th>
                                        <th width="15%">Stok Fisik</th>
                                        <th width="10%">Selisih</th>
                                        <th width="20%">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $no = 1; @endphp
                                    @foreach ($produkList as $produk)
                                    <tr>
                                        <td class="text-center">{{ $no++ }}</td>
                                        <td class="text-center">{{ $produk->kode_produk }}</td>
                                        <td>{{ $produk->nama_produk }}</td>
                                        <td class="text-center">{{ $produk->satuan }}</td>
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
                                        <td colspan="8" class="text-center py-3">Tidak ada data produk.</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- General Information -->
                <div class="row mt-4">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">No Bukti Opname</label>
                        <input type="text" name="no_opname" class="form-control" value="{{ $no_opname ?? '' }}" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Keterangan Umum</label>
                        <input type="text" name="keterangan_umum" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="bukti_stokopname" class="form-label">Bukti Stok Opname</label>
                        <input type="file" name="bukti_stokopname" class="form-control" accept="image/*,application/pdf">
                        <small class="text-muted">File maksimal 2MB (opsional)</small>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save"></i> Simpan Stok Opname
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<style>
    /* Warna tab aktif dan tidak aktif */
    .nav-tabs .nav-link {
        background: linear-gradient(90deg, #f3f4f6 0%, #e0e7ff 100%);
        color: #1e293b;
        border: 1px solid #dbeafe;
        margin-right: 2px;
        font-weight: 500;
        transition: background 0.2s, color 0.2s;
    }
    .nav-tabs .nav-link.active {
        background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
        color: #fff !important;
        border-color: #2563eb #2563eb #fff #2563eb;
        box-shadow: 0 2px 8px rgba(37,99,235,0.08);
    }
    .nav-tabs .nav-link:focus, .nav-tabs .nav-link:hover {
        background: linear-gradient(90deg, #3b82f6 0%, #93c5fd 100%);
        color: #fff;
    }
</style>

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