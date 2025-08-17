@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Riwayat Stok Opname Bahan</h5>
            <div class="input-group" style="width: 400px">
                <span class="input-group-text">Periode</span>
                <input type="date" name="periode_awal" id="periode_awal" class="form-control form-control-sm" value="{{ $periodeAwal }}" onchange="filterOtomatis()">
                <span class="input-group-text">s/d</span>
                <input type="date" name="periode_akhir" id="periode_akhir" class="form-control form-control-sm" value="{{ $periodeAkhir }}" onchange="filterOtomatis()">
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th width="5%">No</th>
                            <th width="15%">Kode Stok Opname</th>
                            <th width="15%">Tanggal</th>
                            <th width="45%">Keterangan</th>
                            <th width="20%">Bukti Stok Opname</th>
                        </tr>
                    </thead>
                    <tbody>
@php
    // Group data by no_penyesuaian
    $grouped = [];
    foreach ($riwayat as $row) {
        $grouped[$row->no_penyesuaian]['header'] = $row;
        $grouped[$row->no_penyesuaian]['details'][] = $row;
    }
    $no = 1;
@endphp

@forelse ($grouped as $group)
    @php
        $details = $group['details'];
        $detailCount = count($details);
    @endphp
    @foreach($details as $j => $row)
        <tr>
            @if($j == 0)
                <td class="text-center align-middle" rowspan="{{ $detailCount }}">{{ $no++ }}</td>
                <td class="text-center align-middle" rowspan="{{ $detailCount }}">{{ $group['header']->no_penyesuaian }}</td>
                <td class="text-center align-middle" rowspan="{{ $detailCount }}">{{ \Carbon\Carbon::parse($group['header']->tanggal)->format('d-m-Y') }}</td>
            @endif
            <td class="align-middle">
                @php
                    if ($row->alasan && strpos($row->alasan, 'Stok Opname:') === 0) {
                        $alasan = trim(substr($row->alasan, strlen('Stok Opname:')));
                        $info = $row->nama_bahan . ' ' . ($row->jumlah > 0 ? '+' : '') . $row->jumlah;
                        if ($alasan !== '') {
                            $info .= ' (' . ltrim($alasan, '- ') . ')';
                        }
                        echo $info;
                    } else {
                        // Jika tidak ada alasan atau tidak diawali Stok Opname:
                        echo $row->nama_bahan . ' ' . ($row->jumlah > 0 ? '+' : '') . $row->jumlah;
                    }
                @endphp
            </td>
            @if($j == 0)
                <td class="text-center align-middle" rowspan="{{ $detailCount }}">
                    @if($group['header']->bukti_stokopname)
                        <button type="button" class="btn btn-sm btn-outline-primary"
                            onclick="showBuktiModal('{{ asset('storage/' . $group['header']->bukti_stokopname) }}')">
                            <i class="bi bi-eye"></i> Lihat Bukti
                        </button>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            @endif
        </tr>
    @endforeach
@empty
    <tr>
        <td colspan="5" class="text-center text-muted py-3">Tidak ada data ditemukan</td>
    </tr>
@endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-center">
            {{ $riwayat->withQueryString()->links() }}
        </div>
    </div>
</div>



<script>

function filterOtomatis() {
    const periodeAwal = document.getElementById('periode_awal').value;
    const periodeAkhir = document.getElementById('periode_akhir').value;
    
    if(periodeAwal && periodeAkhir) {
        window.location.href = window.location.pathname + `?periode_awal=${periodeAwal}&periode_akhir=${periodeAkhir}`;
    }
}
</script>

<!-- Modal Bukti Stok Opname -->
<div class="modal fade" id="buktiModal" tabindex="-1" aria-labelledby="buktiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="buktiModalLabel">Bukti Stok Opname</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body text-center" id="buktiModalBody">
        <!-- Isi bukti akan dimuat di sini -->
      </div>
    </div>
  </div>
</div>

<script>
function showBuktiModal(url) {
    // Cek ekstensi file
    let ext = url.split('.').pop().toLowerCase();
    let html = '';
    if(['jpg','jpeg','png','gif','bmp','webp'].includes(ext)) {
        html = `<img src="${url}" alt="Bukti Stok Opname" class="img-fluid">`;
    } else {
        html = `<a href="${url}" target="_blank" class="btn btn-primary">Download File</a>`;
    }
    document.getElementById('buktiModalBody').innerHTML = html;
    var modal = new bootstrap.Modal(document.getElementById('buktiModal'));
    modal.show();
}
</script>
@endsection