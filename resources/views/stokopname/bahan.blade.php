@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">STOK OPNAME BAHAN</h4>

    <form action="{{ route('stokopname.store') }}" method="POST">
        @csrf

        <table class="table table-bordered">
            <thead class="table-secondary">
                <tr>
                    <th>No</th>
                    <th>Kode Bahan</th>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Stok pada Sistem</th>
                    <th>Stok Fisik</th>
                    <th>Selisih</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bahans as $index => $bahan)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <input type="hidden" name="bahan[{{ $index }}][kode_bahan]" value="{{ $bahan->kode_bahan }}">
                        {{ $bahan->kode_bahan }}
                    </td>
                    <td>{{ $bahan->nama_bahan }}</td>
                    <td>{{ $bahan->satuan }}</td>
                    <td>
                        <input type="number" class="form-control" name="bahan[{{ $index }}][stok_sistem]" value="{{ $bahan->stok }}" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control" name="bahan[{{ $index }}][stok_fisik]" required oninput="updateSelisih(this, {{ $index }})">
                    </td>
                    <td>
                        <input type="number" class="form-control" name="bahan[{{ $index }}][selisih]" id="selisih-{{ $index }}" readonly>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Bukti Stok Opname</label>
                <input type="text" class="form-control" name="bukti_stok">
            </div>
            <div class="col-md-4">
                <label>Tanggal</label>
                <input type="date" class="form-control" name="tanggal" required>
            </div>
            <div class="col-md-4">
                <label>Akun Kontra</label>
                <select class="form-control" name="akun_kontra" required>
                    <option value="">-- pilih akun kontra --</option>
                    @foreach ($akunKontra as $akun)
                    <option value="{{ $akun->kode }}">{{ $akun->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label>Keterangan</label>
            <textarea class="form-control" name="keterangan"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<script>
    function updateSelisih(input, index) {
        let row = input.closest('tr');
        let stokSistem = row.querySelector(`input[name="bahan[${index}][stok_sistem]"]`).value;
        let stokFisik = input.value;
        let selisih = stokFisik - stokSistem;
        document.getElementById(`selisih-${index}`).value = selisih;
    }
</script>
@endsection
