@extends('layouts.app')

@section('content')
<style>
    .table-fixed-height thead th,
    .table-fixed-height tbody td {
        height: 35px;
        vertical-align: middle;
        padding-top: 0;
        padding-bottom: 0;
    }
    .card {
        border-radius: 8px;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .action-buttons {
        display: flex;
        gap: 0.25rem;
        justify-content: center;
    }
</style>

<div class="container-fluid px-3">
    @if(session('success'))
        <div class="alert alert-success text-center">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            {{-- Header --}}
            <div class="row align-items-center mb-2">
                <div class="col-md-6 col-12 text-md-start text-center">
                    <h4 class="mb-0 fw-semibold">Daftar Permintaan Pembelian</h4>
                </div>
                <div class="col-md-6 col-12 text-md-end text-center mt-2 mt-md-0">
                    <a href="{{ route('orderbeli.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle"></i> Tambah Order
                    </a>
                </div>
            </div>

            {{-- Filter & Search --}}
            @php
                $now = Carbon\Carbon::now();
                $tanggal_mulai = request('tanggal_mulai') ?? $now->copy()->startOfMonth()->format('Y-m-d');
                $tanggal_selesai = request('tanggal_selesai') ?? $now->copy()->endOfMonth()->format('Y-m-d');
            @endphp
            <div class="row align-items-end mb-3">
                <div class="col-md-8 col-12 mb-2 mb-md-0">
    <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
        <span class="small">Periode:</span>
        <input type="date" name="tanggal_mulai"
               value="{{ $tanggal_mulai }}"
               class="form-control form-control-sm"
               style="width: 140px;"
               onchange="this.form.submit()">
        <span class="small">s.d.</span>
        <input type="date" name="tanggal_selesai"
               value="{{ $tanggal_selesai }}"
               class="form-control form-control-sm"
               style="width: 140px;"
               onchange="this.form.submit()">
        <select name="status" class="form-select form-select-sm" style="width: 160px;" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="Menunggu Persetujuan" {{ request('status') == 'Menunggu Persetujuan' ? 'selected' : '' }}>Menunggu</option>
            <option value="Disetujui" {{ request('status') == 'Disetujui' ? 'selected' : '' }}>Disetujui</option>
            <option value="Diterima Sebagian" {{ request('status') == 'Diterima Sebagian' ? 'selected' : '' }}>Diterima Sebagian</option>
            <option value="Diterima Sepenuhnya" {{ request('status') == 'Diterima Sepenuhnya' ? 'selected' : '' }}>Diterima Sepenuhnya</option>
        </select>
    </form>
</div>
                <div class="col-md-4 col-12 text-md-end text-center">
                    <form method="GET" action="{{ route('orderbeli.index') }}" class="d-inline-flex gap-2 flex-wrap justify-content-end w-100">
                        <input type="text" name="search"
                               class="form-control form-control-sm"
                               placeholder="Cari No Order / Supplier"
                               value="{{ request('search') }}"
                               style="max-width: 250px;">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </form>
                </div>
            </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm text-center align-middle mb-0">
                    <thead class="table-light">
    <tr>
        <th style="width: 30px;" class="text-center align-middle">No</th>
        <th style="width: 120px;" class="text-center align-middle">Kode Permintaan</th>
        <th style="width: 90px;" class="text-center align-middle">Tanggal</th>
        <th style="width: 180px;" class="text-center align-middle">Nama Supplier</th>
        <th style="width: 120px;" class="text-center align-middle">Total Order</th>
        <th style="width: 160px;" class="text-center align-middle">Status</th>
        <th style="width: 100px;" class="text-center align-middle">Uang Muka</th>
        <th style="width: 100px;" class="text-center align-middle">Metode Bayar</th>
        <th style="width: 120px;" class="text-center align-middle">Aksi</th>
    </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $order->no_order_beli }}</td>
                            <td>{{ $order->tanggal_order }}</td>
                            <td class="text-start">{{ $order->supplier->nama_supplier ?? '-' }}</td>
<td class="text-center align-middle">
            <div class="d-inline-flex align-items-center justify-content-center">
                <span>Rp</span>
                <span class="ms-1">{{ number_format($order->total_order, 0, ',', '.') }}</span>
            </div>
        </td>

<td class="text-center">
    {{ $order->status ?? $order->status_penerimaan ?? '-' }}
</td>

<td class="text-center align-middle">
            @if($order->uang_muka)
                <div class="d-inline-flex align-items-center justify-content-center">
                    <span>Rp</span>
                    <span class="ms-1">{{ number_format($order->uang_muka, 0, ',', '.') }}</span>
                </div>
            @else
                -
            @endif
        </td>
                            <td>{{ $order->metode_bayar ?? '-' }}</td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <button type="button"
                                        class="btn btn-info btn-sm"
                                        title="Detail"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailModal{{ $order->no_order_beli }}">
                                        <i class="bi bi-info-circle"></i>
                                    </button>
                                    @if(($order->status_penerimaan === 'Disetujui' || $order->status_penerimaan === 'Diterima Sebagian') && $order->status !== 'Diterima Sepenuhnya')
                                        <a href="{{ route('terimabahan.create') }}?order={{ $order->no_order_beli }}" class="btn btn-success btn-sm" title="Terima Bahan">
                                            <i class="bi bi-box-arrow-in-down"></i>
                                        </a>
                                    @endif
                                    @if($order->status !== 'Disetujui' && $order->status !== 'Diterima Sebagian' && $order->status !== 'Diterima Sepenuhnya')
                                        <a href="{{ route('orderbeli.edit', $order->no_order_beli) }}" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endif
                                    @if($order->status_penerimaan !== 'Diterima Sebagian' && $order->status_penerimaan !== 'Diterima Sepenuhnya')
                                        <a href="{{ route('orderbeli.cetak', $order->no_order_beli) }}" target="_blank" class="btn btn-secondary btn-sm" title="Cetak">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <form action="{{ route('orderbeli.destroy', $order->no_order_beli) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Data tidak tersedia.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


{{-- Modal detail --}}
@foreach ($orders as $order)
<div class="modal fade" id="detailModal{{ $order->no_order_beli }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $order->no_order_beli }}" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailModalLabel{{ $order->no_order_beli }}">Detail Permintaan Pembelian: {{ $order->no_order_beli }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {{-- Tabel detail bahan --}}
        <table class="table table-bordered text-center">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Jumlah</th>
                    <th>Harga/Satuan</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @foreach($order->details as $i => $detail)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td class="text-start align-middle">{{ $detail->nama_bahan }}</td>
                    <td>{{ $detail->satuan }}</td>
                    <td>{{ $detail->jumlah_beli }}</td>
                    <td>Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->total, 0, ',', '.') }}</td>
                </tr>
                @php $grandTotal += $detail->total; @endphp
                @endforeach
                <tr>
                    <td colspan="5" class="text-end fw-bold">Grand Total</td>
                    <td class="fw-bold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Form pengaturan uang muka & metode bayar --}}
        <form action="{{ route('orderbeli.updatePembayaran', $order->no_order_beli) }}" method="POST" class="mt-3" onsubmit="return validateUangMuka{{ $order->no_order_beli }}();">
            @csrf
            <div class="mb-3 d-flex align-items-center">
                <label for="uang_muka{{ $order->no_order_beli }}" class="form-label mb-0" style="width:150px;">Uang Muka</label>
                <input type="text" class="form-control uang-muka-input" id="uang_muka{{ $order->no_order_beli }}" name="uang_muka"
                    value="{{ old('uang_muka', $order->uang_muka ? 'Rp ' . number_format($order->uang_muka, 0, ',', '.') : '') }}"
                    style="width:300px;" placeholder="Rp 0" autocomplete="off">
            </div>
            <div class="mb-3 d-flex align-items-center">
                <label for="metode_bayar{{ $order->no_order_beli }}" class="form-label mb-0" style="width:150px;">Metode Bayar</label>
                <select class="form-control" id="metode_bayar{{ $order->no_order_beli }}" name="metode_bayar" style="width:300px;">
                    <option value="">-- Pilih Metode --</option>
                    <option value="Transfer" {{ old('metode_bayar', $order->metode_bayar) == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="Tunai" {{ old('metode_bayar', $order->metode_bayar) == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                </select>
            </div>
            <div class="d-flex gap-2 justify-content-end mt-3">
                @if(empty($order->status) || $order->status === 'Menunggu Persetujuan')
                    <button type="submit" name="action" value="setujui" class="btn btn-primary">Setujui Order</button>
                @elseif($order->status === 'Disetujui')
                    <button type="submit" name="action" value="update" class="btn btn-success">Update Pembayaran</button>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
        <script>
        function validateUangMuka{{ $order->no_order_beli }}() {
            var uangMuka = parseFloat(document.getElementById('uang_muka{{ $order->no_order_beli }}').value) || 0;
            var grandTotal = {{ $grandTotal }};
            if(uangMuka > grandTotal) {
                alert('Uang muka tidak boleh melebihi Grand Total!');
                return false;
            }
            return true;
        }
        document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.uang-muka-input').forEach(function(input) {
        // Format saat load
        if (input.value && !input.value.startsWith('Rp')) {
            let val = input.value.replace(/\D/g, '');
            input.value = val ? 'Rp ' + parseInt(val, 10).toLocaleString('id-ID') : '';
        }
        // Format saat input
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            this.value = value ? 'Rp ' + parseInt(value, 10).toLocaleString('id-ID') : '';
        });
        // Saat submit, kirim hanya angka
        input.form && input.form.addEventListener('submit', function() {
            input.value = input.value.replace(/\D/g, '');
        });
    });
});
        </script>
      </div>
    </div>
  </div>
</div>
@endforeach
@endsection