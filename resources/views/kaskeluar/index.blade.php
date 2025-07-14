@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Data Pengeluaran Kas Lain Lain</h4>

    </div>

<div class="card mb-4">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="d-flex align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center">
                    <span class="fw-medium me-2">Periode:</span>
                    <input type="date" class="form-control form-control-sm" 
                           value="2025-07-01" style="width: 120px">
                    <span class="mx-2 fw-medium">s.d.</span>
                    <input type="date" class="form-control form-control-sm" 
                           value="2025-07-14" style="width: 120px">
                </div>
                
                <button type="button" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-sync-alt me-1"></i> Reset
                </button>
            </div>
            
            <div class="ms-auto">
                <a href="{{ route('kaskeluar.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Tambah Pengeluaran
                </a>
            </div>
        </div>
    </div>
</div>

    <!-- Table content would go here -->
</div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>No Bukti</th>
                    <th>Jumlah</th>
                    <th>Penerima</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kaskeluar as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}</td>
                    <td>{{ $item->nomor_bukti }}</td>
                    <td class="text-end">{{ $item->jumlah_rupiah }}</td>
                    <td>
                        @if($item->penerima !== '-')
                            {{ $item->penerima }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-start">{{ $item->keterangan_teks }}</td>
                    <td class="text-nowrap">
                        <!-- Edit Button -->
                        <a href="{{ route('kaskeluar.edit', $item->no_jurnal) }}" 
                           class="btn btn-sm btn-warning me-1"
                           data-bs-toggle="tooltip"
                           title="Edit Data">
                           <i class="bi bi-pencil-square"></i>
                        </a>
                        
                        <!-- Delete Button -->
                        <form action="{{ route('kaskeluar.destroy', $item->no_jurnal) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="btn btn-sm btn-danger"
                                    data-bs-toggle="tooltip"
                                    title="Hapus Data">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data kas keluar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection