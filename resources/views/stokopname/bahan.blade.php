@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Stok Opname Bahan</h4>

    <!-- Notifikasi -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Tabs kategori -->
    <ul class="nav nav-tabs mb-3" id="kategoriTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="baku-tab" data-bs-toggle="tab" data-bs-target="#bahan-baku" type="button" role="tab">Bahan Baku</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="penolong-tab" data-bs-toggle="tab" data-bs-target="#bahan-penolong" type="button" role="tab">Bahan Penolong</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="habis-tab" data-bs-toggle="tab" data-bs-target="#bahan-habis" type="button" role="tab">Bahan Habis Pakai</button>
        </li>
    </ul>

    <form action="{{ route('stokopname.store') }}" method="POST">
        @csrf

        <input type="hidden" name="tab_aktif" id="tab_aktif" value="BB">

        <div class="tab-content" id="kategoriTabContent">
            @php
                $kategoriMap = [
                    'baku' => 'BB',
                    'penolong' => 'BP',
                    'habis' => 'BHP',
                ];
            @endphp

            @foreach ($kategoriMap as $key => $kodeKategori)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="bahan-{{ $key }}" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-bordered text-center align-middle">
                        <thead class="table-light">
                            <tr class="text-center align-middle">
            <th>No</th>
            <th>Kode Bahan</th>
            <th>Nama Bahan</th>
            <th>Satuan</th>
            <th>Stok Sistem</th>
            <th>Stok Fisik</th>
            <th>Selisih</th>
            <th>Keterangan</th>
        </tr>
                        </thead>
                        <tbody>
                            @php $no = 1; @endphp
                            @foreach ($bahanList->where('kode_kategori', $kodeKategori) as $bahan)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $bahan->kode_bahan }}</td>
                                <td>{{ $bahan->nama_bahan }}</td>
                                <td>{{ $bahan->satuan }}</td>
                                <td>
                                    <input type="number" 
                                           name="stok_sistem[{{ $bahan->kode_bahan }}]" 
                                           class="form-control text-end" 
                                           value="{{ $bahan->stok_sistem }}" 
                                           readonly
                                           data-id="{{ $bahan->kode_bahan }}"
                                           data-kategori="{{ $kodeKategori }}"> <!-- Tambahkan data-kategori di sini -->
                                </td>
                                <td>
                                    <input type="number" 
                                           name="stok_fisik[{{ $bahan->kode_bahan }}]" 
                                           class="form-control text-end stok-fisik-input" 
                                           data-id="{{ $bahan->kode_bahan }}"
                                           data-kategori="{{ $kodeKategori }}"
                                           step="any"
                                           inputmode="decimal"
                                           placeholder="0,00">
                                </td>
                                <td>
                                    <input type="text" 
                                           id="selisih_{{ $bahan->kode_bahan }}_{{ $kodeKategori }}" 
                                           class="form-control text-end bg-light" 
                                           readonly>
                                </td>
                                <td>
                                    <input type="text" name="keterangan[{{ $bahan->kode_bahan }}]" class="form-control">
                                </td>
                            </tr>
                            @endforeach
                            @if ($bahanList->where('kode_kategori', $kodeKategori)->count() == 0)
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data bahan.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Informasi Umum -->
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
        // Ambil baris (tr) tempat input berada
        const tr = e.target.closest('tr');
        if (!tr) return;

        // Ambil input stok sistem dan selisih di baris yang sama
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

// Hitung semua selisih saat halaman dimuat
window.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.stok-fisik-input').forEach(function(input) {
        input.dispatchEvent(new Event('input'));
    });
});


// Jalankan saat tab berubah
document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function(btn) {
    btn.addEventListener('shown.bs.tab', function(e) {
        // Ambil kode kategori dari data-kategori tab yang aktif
        let tabId = e.target.getAttribute('data-bs-target');
        let kode = 'BB';
        if (tabId === '#bahan-penolong') kode = 'BP';
        if (tabId === '#bahan-habis') kode = 'BHP';
        document.getElementById('tab_aktif').value = kode;
    });
});

</script>
@endsection
