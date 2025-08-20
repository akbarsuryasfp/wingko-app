@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Riwayat Stok Opname Produk</h5>
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
                            <th width="20%">Kode Stok Opname</th>
                            <th width="15%">Tanggal</th>
                            <th width="45%">Keterangan</th>
                            <th width="20%">Bukti Stok Opname</th>
                        </tr>
                    </thead>
                    <tbody>

<tbody>
@php
    // Group data by no_penyesuaian
    $grouped = [];
    foreach ($riwayat as $row) {
        $grouped[$row->no_penyesuaian]['header'] = $row;
        $grouped[$row->no_penyesuaian]['details'][] = $row;
    }
    $no = $riwayat->firstItem();
@endphp
@forelse ($grouped as $group)
    @php
        // Ambil detail produk untuk no_penyesuaian ini
        $details = \DB::table('t_penyesuaian_detail as d')
            ->join('t_produk as p', 'd.kode_item', '=', 'p.kode_produk')
            ->where('d.no_penyesuaian', $group['header']->no_penyesuaian)
            ->where('d.tipe_item', 'PRODUK')
            ->select('p.nama_produk', 'd.jumlah', 'd.alasan')
            ->get();
        $detailCount = count($details);
    @endphp
    @if($detailCount > 0)
        @foreach($details as $j => $detail)
            <tr>
                @if($j == 0)
                    <td class="text-center align-middle" rowspan="{{ $detailCount }}">{{ $no++ }}</td>
                    <td class="text-center align-middle" rowspan="{{ $detailCount }}">{{ $group['header']->no_penyesuaian }}</td>
                    <td class="text-center align-middle" rowspan="{{ $detailCount }}">{{ \Carbon\Carbon::parse($group['header']->tanggal)->format('d-m-Y') }}</td>
                @endif
                <td class="align-middle">
                    @php
                        $info = $detail->nama_produk . ' ' . ($detail->jumlah > 0 ? '+' : '') . $detail->jumlah;
                        if ($detail->alasan && strpos($detail->alasan, 'Stok Opname:') === 0) {
                            $alasan = trim(substr($detail->alasan, strlen('Stok Opname:')));
                            if ($alasan !== '') {
                                $info .= ' (' . ltrim($alasan, '- ') . ')';
                            }
                        }
                        echo $info;
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
    @else
        <tr>
            <td class="text-center align-middle">{{ $no++ }}</td>
            <td class="text-center align-middle">{{ $group['header']->no_penyesuaian }}</td>
            <td class="text-center align-middle">{{ \Carbon\Carbon::parse($group['header']->tanggal)->format('d-m-Y') }}</td>
            <td class="align-middle text-muted">-</td>
            <td class="text-center align-middle">
                @if($group['header']->bukti_stokopname)
                    <button type="button" class="btn btn-sm btn-outline-primary"
                        onclick="showBuktiModal('{{ asset('storage/' . $group['header']->bukti_stokopname) }}')">
                        <i class="bi bi-eye"></i> Lihat Bukti
                    </button>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
        </tr>
    @endif
@empty
    <tr>
        <td colspan="5" class="text-center text-muted py-3">Tidak ada data ditemukan</td>
    </tr>
@endforelse
</tbody>
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