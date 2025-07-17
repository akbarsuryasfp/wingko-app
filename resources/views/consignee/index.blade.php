@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="text-center">DAFTAR CONSIGNEE (MITRA)</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif


    <div class="d-flex justify-content-between align-items-center mb-3">
        <input type="text" id="searchConsignee" class="form-control me-2" placeholder="Cari consignee..." style="max-width:300px;">
        <a href="{{ route('consignee.create') }}" class="btn btn-primary">Tambah Consignee</a>
    </div>
<script>
// Fitur search/filter baris tabel consignee
document.getElementById('searchConsignee').addEventListener('input', function() {
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
                <th>Kode Consignee</th>
                <th>Nama Consignee</th>
                <th>Alamat</th>
                <th>No. Telepon</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consignee as $item)
                <tr>
                    <td>{{ $item->kode_consignee }}</td>
                    <td>{{ $item->nama_consignee }}</td>
                    <td>{{ $item->alamat }}</td>
                    <td>{{ $item->no_telp }}</td>
                    <td>
                        <a href="{{ route('consignee.edit', $item->kode_consignee) }}" class="btn btn-warning btn-sm" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('consignee.destroy', $item->kode_consignee) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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