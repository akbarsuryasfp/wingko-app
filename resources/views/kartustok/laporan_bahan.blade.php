@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card shadow">
<div class="card-header d-flex justify-content-end">
    <button onclick="generatePDF()" class="btn btn-success">
        <i class="bi bi-printer"></i> Print Halaman
    </button>
</div>
        
        <div class="card-body">
            <div id="screen-content">
                    <h4 class="mb-3 text-center" style="font-size:1.5rem; font-weight:bold;">LAPORAN STOK BAHAN BAKU</h4>
                <table class="table table-bordered" style="table-layout: fixed;">
                    <thead class="table-secondary">
                        <tr>
                            <th style="width: 5%; text-align: center; vertical-align: middle;">No</th>
                            <th style="width: 8%; text-align: center; vertical-align: middle;">Kode</th>
                            <th style="width: 20%; text-align: center; vertical-align: middle;">Nama Bahan</th>
                            <th style="width: 5%; text-align: center; vertical-align: middle;">Satuan</th>
                            <th style="width: 10%; text-align: center; vertical-align: middle;">Stok Min</th>
                            <th style="width: 25%; text-align: center; vertical-align: middle;">Stok Akhir</th>
                            <th style="width: 10%; text-align: center; vertical-align: middle;">Total</th>
                            <th style="width: 10%; text-align: center; vertical-align: middle;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bahanList as $i => $bahan)
                        @php
                            $totalStok = $bahan->stok_akhir->sum('stok');
                            $status = $totalStok > $bahan->stokmin ? 'Aman' : 'Perlu Beli';
                            $statusClass = $status == 'Aman' ? 'text-success' : 'text-danger';
                        @endphp
                        <tr>
                            <td style="padding: 0; border: 1px solid #000; text-align: center; height: 40px; line-height: 40px;">
                                {{ $i+1 }}
                            </td>
                            <td style="padding: 0; border: 1px solid #000; text-align: center; height: 40px; line-height: 40px;">
                                {{ $bahan->kode_bahan }}
                            </td>
                            <td style="padding: 0; border: 1px solid #000; text-align: center; height: 40px; line-height: 40px;">
                                {{ $bahan->nama_bahan }}
                            </td>
                            <td style="padding: 0; border: 1px solid #000; text-align: center; height: 40px; line-height: 40px;">
                                {{ $bahan->satuan }}
                            </td>
                            <td style="padding: 0; border: 1px solid #000; text-align: center; height: 40px; line-height: 40px;">
                                {{ $bahan->stokmin }}
                            </td>
                            <td style="padding: 4px; border: 1px solid #000; text-align: center;">
                                @if($bahan->stok_akhir->count())
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px;">
                                    @foreach($bahan->stok_akhir as $stok)
                                        <div style="font-size: 10pt; line-height: 1.3;">
                                            <span style="font-weight: bold;">{{ number_format($stok->stok, 3) }}</span> {{ $bahan->satuan }} 
                                            @if(isset($stok->harga))
                                                @ <span style="font-weight: bold; color: #0d6efd;">Rp{{ number_format($stok->harga,0,',','.') }}</span>/{{ $bahan->satuan }}
                                            @endif
                                        </div>
                                    @endforeach
                                    </div>
                                @else
                                    <span style="color: #dc3545;">Kosong</span>
                                @endif
                            </td>
                            <td style="padding: 0; border: 1px solid #000; text-align: center; height: 40px; line-height: 40px;">
                                <strong>{{ $totalStok }}</strong> {{ $bahan->satuan }}
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
    table.table-bordered {
        border: 2px solid #000 !important;
    }
    table.table-bordered th,
    table.table-bordered td {
        border: 1px solid #000 !important;
    }
table.table-bordered thead th {
    background-color: #e9ecef !important; /* abu-abu Bootstrap table-secondary */
    color: #212529 !important;
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
        filename: 'Laporan_Stok_Bahan_Baku.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
    };
    html2pdf().set(opt).from(element).save();
}
</script>
@endsection