@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-end">
            <a href="{{ route('kartustok.laporan_produk_pdf') }}" target="_blank" class="btn btn-success">
                <i class="bi bi-printer"></i> Cetak PDF
            </a>
        </div>
        <div class="card-body">
            <div id="screen-content">
                <h4 class="mb-3 text-center" style="font-size:1.5rem; font-weight:bold;">LAPORAN STOK AKHIR PRODUK</h4>
                <table class="table table-bordered" style="table-layout: fixed;">
                    <thead class="table-secondary">
                        <tr>
                            <th style="width: 5%; text-align: center; vertical-align: middle;">No</th>
                            <th style="width: 10%; text-align: center; vertical-align: middle;">Kode Produk</th>
                            <th style="width: 20%; text-align: center; vertical-align: middle;">Nama Produk</th>
                            <th style="width: 8%; text-align: center; vertical-align: middle;">Satuan</th>
                            <th style="width: 15%; text-align: center; vertical-align: middle;">Stok Gudang</th>
                            <th style="width: 15%; text-align: center; vertical-align: middle;">Stok Toko 1</th>
                            <th style="width: 15%; text-align: center; vertical-align: middle;">Stok Toko 2</th>
                            <th style="width: 12%; text-align: center; vertical-align: middle;">Total Stok</th>
                            <th style="width: 10%; text-align: center; vertical-align: middle;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($produkList as $i => $produk)
                        @php
                            $gudangList = $produk->stok_akhir->where('lokasi', 'Gudang');
                            $toko1List  = $produk->stok_akhir->where('lokasi', 'Toko 1');
                            $toko2List  = $produk->stok_akhir->where('lokasi', 'Toko 2');
                            $totalStok = $produk->stok_akhir->sum('stok');
                            $stokmin = $produk->stokmin ?? 0;
                            $status = ($totalStok <= $stokmin) ? 'Perlu Produksi' : 'Aman';
                            $statusClass = $status == 'Aman' ? 'text-success' : 'text-danger';
                        @endphp
                        <tr>
                            <td style="padding: 0; border: 1px solid #000; text-align: center; height: 40px; line-height: 40px;">
                                {{ $i+1 }}
                            </td>
                            <td style="padding: 0; border: 1px solid #000; text-align: center; height: 40px; line-height: 40px;">
                                {{ $produk->kode_produk }}
                            </td>
                            <td style="padding: 0; border: 1px solid #000; text-align: center; height: 40px; line-height: 40px;">
                                {{ $produk->nama_produk }}
                            </td>
                            <td style="padding: 0; border: 1px solid #000; text-align: center; height: 40px; line-height: 40px;">
                                {{ $produk->satuan }}
                            </td>
                            <td style="padding: 4px; border: 1px solid #000; text-align: center;">
                                @if($gudangList->count())
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px;">
                                    @foreach($gudangList as $stok)
                                        <div style="font-size: 10pt; line-height: 1.3;">
                                            <span style="font-weight: bold;">{{ number_format($stok->stok, 3) }}</span> {{ $produk->satuan }}
                                            @if(isset($stok->hpp))
                                                @ <span style="font-weight: bold; color: #0d6efd;">Rp{{ number_format($stok->hpp,0,',','.') }}</span>/{{ $produk->satuan }}
                                            @endif
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    <span style="color: #dc3545;">Kosong</span>
                                @endif
                            </td>
                            <td style="padding: 4px; border: 1px solid #000; text-align: center;">
                                @if($toko1List->count())
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px;">
                                    @foreach($toko1List as $stok)
                                        <div style="font-size: 10pt; line-height: 1.3;">
                                            <span style="font-weight: bold;">{{ number_format($stok->stok, 3) }}</span> {{ $produk->satuan }}
                                            @if(isset($stok->hpp))
                                                @ <span style="font-weight: bold; color: #0d6efd;">Rp{{ number_format($stok->hpp,0,',','.') }}</span>/{{ $produk->satuan }}
                                            @endif
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    <span style="color: #dc3545;">Kosong</span>
                                @endif
                            </td>
                            <td style="padding: 4px; border: 1px solid #000; text-align: center;">
                                @if($toko2List->count())
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px;">
                                    @foreach($toko2List as $stok)
                                        <div style="font-size: 10pt; line-height: 1.3;">
                                            <span style="font-weight: bold;">{{ number_format($stok->stok, 3) }}</span> {{ $produk->satuan }}
                                            @if(isset($stok->hpp))
                                                @ <span style="font-weight: bold; color: #0d6efd;">Rp{{ number_format($stok->hpp,0,',','.') }}</span>/{{ $produk->satuan }}
                                            @endif
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    <span style="color: #dc3545;">Kosong</span>
                                @endif
                            </td>
                            <td style="padding: 0; border: 1px solid #000; text-align: center; height: 40px; line-height: 40px;">
                                <strong>{{ number_format($totalStok, 3) }}</strong> {{ $produk->satuan }}
                            </td>
                            <td style="padding: 0; border: 1px solid #000; text-align: center; height: 40px; line-height: 40px; font-weight: bold;">
                                <span class="{{ $statusClass }}">{{ $status }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-end mt-3">
                    <small>Terakhir diperbarui: {{ now()->format('d-m-Y H:i:s') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Card Styles */
    .card {
        border-radius: 10px;
        overflow: hidden;
    }
    
    /* Table Styles */
    table {
        font-size: 0.9rem;
        width: 100%;
        margin-bottom: 0;
    }
table.table-bordered thead th {
    background-color: #e9ecef !important; /* abu-abu Bootstrap table-secondary */
    color: #212529 !important;
    border-bottom: 2px solid #000 !important;
}
    table.table-bordered th,
    table.table-bordered td {
        border: 1px solid #000 !important;
    }
    table.table-bordered thead th {
        border-bottom: 2px solid #000 !important;
    }
    table.table-bordered tr:last-child td {
        border-bottom: 2px solid #000 !important;
    }
    table.table-bordered tr td:first-child,
    table.table-bordered tr th:first-child {
        border-left: 2px solid #000 !important;
    }
    table.table-bordered tr td:last-child,
    table.table-bordered tr th:last-child {
        border-right: 2px solid #000 !important;
    }
    
    th, td {
        vertical-align: middle !important;
    }
    
    /* Status Styles */
    .text-success {
        color: #28a745 !important;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function generatePDF() {
    const element = document.getElementById('screen-content');
    const opt = {
        margin: 0.5,
        filename: 'Laporan_Stok_Akhir_Produk.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
    };
    html2pdf().set(opt).from(element).save();
}
</script>
@endsection