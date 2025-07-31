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
            <select class="form-select" id="jadwal-select" name="no_jadwal" onchange="tampilkanProduk()">
                <option value="">-- Pilih Jadwal --</option>
                @foreach ($jadwal as $j)
                    <option value="{{ $j->no_jadwal }}" data-json='@json($j)'
                        @if(isset($jadwalTerpilih) && $jadwalTerpilih->no_jadwal == $j->no_jadwal) selected @endif>
                        {{ $j->no_jadwal }} - {{ $j->tanggal_jadwal }}
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

        // Gabungkan produk dengan kode yang sama
        const produkMap = {};
        data.details.forEach(detail => {
            const kode = detail.kode_produk;
            if (!produkMap[kode]) {
                produkMap[kode] = {
                    nama_produk: detail.produk.nama_produk,
                    kode_produk: kode,
                    jumlah: 0
                };
            }
            produkMap[kode].jumlah += detail.jumlah;
        });

        // Ambil tanggal produksi
        const tanggalProduksi = document.querySelector('input[name="tanggal_produksi"]').value;
        const expiredDate = tambahHari(new Date(tanggalProduksi), 10);
        const expiredStr = expiredDate.toISOString().split('T')[0];

        Object.values(produkMap).forEach(detail => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    ${detail.nama_produk}
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

    @if(isset($jadwalTerpilih))
        document.addEventListener('DOMContentLoaded', function() {
            tampilkanProduk();
        });
    @endif
</script>

@endsection
