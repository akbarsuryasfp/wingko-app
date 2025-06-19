@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h4>DAFTAR PEMBELIAN BAHAN</h4>
    <div class="mb-2 d-flex justify-content-between align-items-center">
        <form method="GET" class="d-flex align-items-center">
            <label class="me-2 mb-0">Filter:</label>
            <select name="jenis_pembelian" class="form-select form-select-sm w-auto me-2" onchange="this.form.submit()">
                <option value="">Semua Jenis</option>
                <option value="pembelian langsung" {{ request('jenis_pembelian') == 'pembelian langsung' ? 'selected' : '' }}>Pembelian Langsung</option>
                <option value="pembelian berdasarkan order" {{ request('jenis_pembelian') == 'pembelian berdasarkan order' ? 'selected' : '' }}>Pembelian Berdasarkan Order</option>
            </select>
        </form>
        <div>
            <a href="{{ route('pembelian.langsung') }}" class="btn btn-primary btn-sm">Pembelian Langsung</a>    
            <a href="{{ route('pembelian.create') }}" class="btn btn-primary btn-sm">Pembelian berdasarkan order</a>
        </div>
    </div>
    <table class="table table-bordered text-center">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>No Pembelian</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Total Pembelian</th>
                <th>Uang Muka</th>
                <th>Total Bayar</th>
                <th>Hutang</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pembelian as $no => $p)
            <tr>
                <td>{{ $no+1 }}</td>
                <td>{{ $p->no_pembelian }}</td>
                <td>{{ $p->tanggal_pembelian }}</td>
                <td>{{ $p->nama_supplier }}</td>
                <td>{{ number_format($p->total_pembelian,0,',','.') }}</td>
                <td>{{ number_format($p->uang_muka ?? 0,0,',','.') }}</td>
                <td>{{ number_format($p->total_bayar,0,',','.') }}</td>
                <td>{{ number_format($p->hutang,0,',','.') }}</td>
                <td>
                    @if($p->hutang > 0)
                        <span class="badge bg-warning text-dark">Belum Lunas</span>
                    @else
                        <span class="badge bg-success">Lunas</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('pembelian.show', $p->no_pembelian) }}" class="btn btn-secondary btn-sm" title="Detail">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('pembelian.edit', $p->no_pembelian) }}" class="btn btn-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('pembelian.destroy', $p->no_pembelian) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection