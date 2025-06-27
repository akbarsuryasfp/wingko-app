@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Daftar Permintaan Produksi</h2>

    <a href="{{ route('permintaan_produksi.create') }}" class="btn btn-success mb-3">
        + Tambah Permintaan Produksi
    </a>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Keterangan</th>
                <th>Status</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($permintaanProduksi as $item)
                <tr>
                    <td>{{ $item->kode_permintaan_produksi }}</td>
                    <td>{{ $item->tanggal }}</td>
                    <td>{{ $item->keterangan }}</td>
                    <td><span class="badge bg-{{ $item->status === 'Selesai' ? 'success' : 'warning' }}">{{ $item->status }}</span></td>
                    <td>
                        <button class="btn btn-sm btn-primary btn-toggle-detail" 
                                type="button"
                                data-detail="{{ $item->kode_permintaan_produksi }}">
                            Lihat
                        </button>
                    </td>
                </tr>
                <tr class="detail-row" id="detail-{{ $item->kode_permintaan_produksi }}" style="display: none;">
                    <td colspan="5" class="p-0">
                        <div class="p-3">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr class="table-secondary">
                                        <th>Kode Produk</th>
                                        <th>Nama Produk</th>
                                        <th>Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($item->details as $detail)
                                        <tr>
                                            <td>{{ $detail->kode_produk }}</td>
                                            <td>{{ $detail->produk->nama_produk ?? '-' }}</td>
                                            <td>{{ $detail->unit }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">Tidak ada detail permintaan</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleDetailButtons = document.querySelectorAll('.btn-toggle-detail');

        toggleDetailButtons.forEach(button => {
            button.addEventListener('click', function () {
                const detailId = this.getAttribute('data-detail');
                const detailRow = document.getElementById('detail-' + detailId);

                if (detailRow.style.display === 'none' || detailRow.style.display === '') {
                    detailRow.style.display = 'table-row';
                } else {
                    detailRow.style.display = 'none';
                }
            });
        });
    });
</script>
@endpush