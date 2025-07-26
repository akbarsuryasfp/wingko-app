@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center">DAFTAR PELANGGAN</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif


    <div class="d-flex justify-content-between align-items-center mb-3">
        <input type="text" id="searchPelanggan" class="form-control me-2" placeholder="Cari pelanggan..." style="max-width:300px;">
        <a href="{{ route('pelanggan.create') }}" class="btn btn-primary">Tambah Pelanggan</a>
    </div>
<script>
// Fitur search/filter baris tabel pelanggan
document.getElementById('searchPelanggan').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    const rows = document.querySelectorAll('table tbody tr');
    rows.forEach(row => {
        // Gabungkan semua kolom jadi satu string
        const text = row.innerText.toLowerCase();
        if (q === '' || text.includes(q)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode Pelanggan</th>
                <th>Nama Pelanggan</th>
                <th>Alamat</th>
                <th>No. Telepon</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelanggan as $item)
                <tr>
                    <td>{{ $item->kode_pelanggan }}</td>
                    <td>{{ $item->nama_pelanggan }}</td>
                    <td>{{ $item->alamat }}</td>
                    <td>{{ $item->no_telp }}</td>
                    <td>
                        <a href="{{ route('pelanggan.edit', $item->kode_pelanggan) }}" class="btn btn-warning btn-sm" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('pelanggan.destroy', $item->kode_pelanggan) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Data tidak tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection