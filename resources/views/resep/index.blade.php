@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Daftar Resep Produk</h4>
        <a href="{{ route('resep.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Resep
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('resep.index') }}" class="row g-2 mb-3 align-items-center">
                <div class="col-auto">
                    <input type="text" name="search" class="form-control" placeholder="Cari kode/produk..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-secondary">Cari</button>
                </div>
                <div class="col-auto text-end">
                    <input type="hidden" name="sort" value="{{ $sort ?? 'desc' }}">
                    <button type="submit" name="sort" value="{{ ($sort ?? 'desc') == 'desc' ? 'asc' : 'desc' }}" class="btn btn-outline-primary">
                        Urutkan: {{ ($sort ?? 'desc') == 'desc' ? 'Terlama' : 'Terbaru' }}
                    </button>
                </div>
            </form>
            <
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Resep</th>
                            <th>Produk</th>
                            <th>Keterangan</th>
                            <th>Detail Bahan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reseps as $resep)
                            <tr>
                                <td>{{ $resep->kode_resep }}</td>
                                <td>{{ $resep->produk->nama_produk ?? '-' }}</td>
                                <td>{{ $resep->keterangan }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-detail-resep"
                                        type="button"
                                        data-kode="{{ $resep->kode_resep }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalDetailResep">
                                        Lihat Bahan
                                    </button>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('resep.edit', $resep->kode_resep) }}" class="btn btn-sm btn-warning me-1">
                                        <i class="bi bi-pencil-square"></i> 
                                    </a>
                                    <form action="{{ route('resep.destroy', $resep->kode_resep) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus resep ini?')">
                                            <i class="bi bi-trash"></i> 
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Data resep belum tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $reseps->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Resep -->
<div class="modal fade" id="modalDetailResep" tabindex="-1" aria-labelledby="modalDetailResepLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDetailResepLabel">Detail Bahan Resep</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div><strong>Kode Resep:</strong> <span id="modal-kode-resep"></span></div>
        <div><strong>Produk:</strong> <span id="modal-produk-resep"></span></div>
        <div class="table-responsive mt-3">
            <table class="table table-sm">
                <thead>
                    <tr class="table-secondary">
                        <th>Kode Bahan</th>
                        <th>Nama Bahan</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                    </tr>
                </thead>
                <tbody id="modal-detail-tbody">
                </tbody>
            </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.btn-detail-resep').forEach(btn => {
    btn.addEventListener('click', function() {
        const kode = this.getAttribute('data-kode');
        fetch(`/resep/${kode}/detail`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('modal-kode-resep').textContent = data.kode_resep;
                document.getElementById('modal-produk-resep').textContent = data.produk;
                const tbody = document.getElementById('modal-detail-tbody');
                tbody.innerHTML = '';
                if(data.details.length > 0){
                    data.details.forEach(function(row){
                        tbody.innerHTML += `<tr>
                            <td>${row.kode_bahan}</td>
                            <td>${row.nama_bahan}</td>
                            <td>${row.jumlah_kebutuhan}</td>
                            <td>${row.satuan}</td>
                        </tr>`;
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">Tidak ada detail bahan.</td></tr>`;
                }
            });
    });
});
</script>
@endsection

