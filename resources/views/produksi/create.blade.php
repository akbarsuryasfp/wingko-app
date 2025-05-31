@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Input Produksi</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Bagian atas -->
    <form action="{{ route('produksi.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="tanggal_produksi">Tanggal Produksi</label>
            <input type="date" name="tanggal_produksi" class="form-control" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="mb-3">
            <label for="jadwal">Pilih Jadwal Produksi</label>
            <select class="form-select" id="jadwal-select" onchange="tampilkanProduk()">
                <option value="">-- Pilih Jadwal --</option>
                @foreach ($jadwal as $j)
                    <option value="{{ $j->kode_jadwal }}" data-json='@json($j)'>
                        {{ $j->kode_jadwal }} - {{ $j->tanggal_jadwal }}
                    </option>
                @endforeach
            </select>
        </div>

        <h5>Produk yang Diproduksi</h5>
        <table class="table table-bordered" id="produk-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Jumlah Direncanakan</th>
                    <th>Jumlah Aktual</th>
                    <th>Expired</th>
                </tr>
            </thead>
            <tbody>
                <!-- Baris produk akan ditambahkan dengan JavaScript -->
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">Simpan Produksi</button>
    </form>
</div>

<script>
    function tampilkanProduk() {
        const select = document.getElementById('jadwal-select');
        const selectedOption = select.options[select.selectedIndex];
        const data = JSON.parse(selectedOption.dataset.json);
        const tbody = document.querySelector('#produk-table tbody');
        tbody.innerHTML = '';
        let index = 0;

        // Ambil tanggal produksi
        const tanggalProduksi = document.querySelector('input[name="tanggal_produksi"]').value;
        const expiredDate = tambahHari(new Date(tanggalProduksi), 10); // 10 hari ke depan
        const expiredStr = expiredDate.toISOString().split('T')[0];

        data.details.forEach(detail => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    ${detail.produk.nama_produk}
                    <input type="hidden" name="produk[${index}][kode_produk]" value="${detail.kode_produk}">
                </td>
                <td>${detail.jumlah}</td>
                <td>
                    <input type="number" name="produk[${index}][jumlah_unit]" value="${detail.jumlah}" class="form-control" required min="1">
                </td>
                <td>
                    <input type="date" name="produk[${index}][tanggal_expired]" class="form-control" value="${expiredStr}" required>
                </td>
            `;
            tbody.appendChild(tr);
            index++;
        });
    }

    function tambahHari(tanggal, hari) {
        const result = new Date(tanggal);
        result.setDate(result.getDate() + hari);
        return result;
    }
</script>

@endsection
