@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h3>Edit Pembelian Bahan</h3>
    <form action="{{ route('pembelian.update', $pembelian->no_pembelian) }}" method="POST">
    @csrf
    @method('PUT')

        <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-md-6">
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Kode Pembelian</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" value="{{ $pembelian->no_pembelian }}" readonly>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Tanggal Pembelian</label>
                    <div class="col-sm-8">
                        <input type="date" class="form-control" name="tanggal_pembelian" value="{{ $pembelian->tanggal_pembelian }}" required>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Metode Bayar</label>
                    <div class="col-sm-8">
                        <select class="form-control" name="metode_bayar" required>
                            <option value="Tunai" {{ $pembelian->metode_bayar == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                            <option value="Hutang" {{ $pembelian->metode_bayar == 'Hutang' ? 'selected' : '' }}>Hutang</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">No Nota</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="no_nota" value="{{ $pembelian->no_nota }}">
                    </div>
                </div>
            </div>
            <!-- Kolom Kanan -->
            <div class="col-md-6">
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Kode Terima Bahan</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" value="{{ $pembelian->no_terima_bahan }}" readonly>
                    </div>
                </div>
                <div class="row mb-3 align-items-center">
                    <label class="col-sm-4 col-form-label">Nama Supplier</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" value="{{ $nama_supplier }}" readonly>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="mt-4">Daftar Pembelian Bahan</h5>
        {{-- Tampilkan detail bahan di sini, readonly/table --}}
        <h5 class="mt-4">Detail Bahan</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $detail)
                <tr>
                    <td>{{ $detail->nama_bahan }}</td>
                    <td>{{ $detail->satuan }}</td>
                    <td>{{ $detail->bahan_masuk }}</td>
                    <td>{{ $detail->harga_beli }}</td>
                    <td>{{ $detail->subtotal ?? $detail->bahan_masuk * $detail->harga_beli }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Total Harga</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" name="total_harga" value="{{ $pembelian->total_harga }}" readonly>
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Diskon</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" name="diskon" value="{{ $pembelian->diskon }}">
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Ongkos Kirim</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" name="ongkir" value="{{ $pembelian->ongkir }}">
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Total Pembelian</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" name="total_pembelian" id="total_pembelian" value="{{ $pembelian->total_pembelian }}" readonly>
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Uang Muka</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" name="uang_muka" id="uang_muka" value="{{ $pembelian->uang_muka }}" readonly>
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Total Bayar</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" name="total_bayar" id="total_bayar" value="{{ $pembelian->total_bayar }}">
            </div>
        </div>
        <div class="row mb-2 align-items-center">
            <label class="col-sm-4 col-form-label">Kurang Bayar</label>
            <div class="col-sm-8">
                <input type="number" class="form-control" name="hutang" id="hutang" value="{{ $pembelian->hutang }}" readonly>
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalPembelianInput = document.getElementById('total_pembelian');
    const uangMukaInput = document.getElementById('uang_muka');
    const totalBayarInput = document.getElementById('total_bayar');
    const kurangBayarInput = document.getElementById('hutang');

    function hitungKurangBayar() {
        let totalPembelian = parseFloat(totalPembelianInput.value) || 0;
        let uangMuka = parseFloat(uangMukaInput.value) || 0;
        let totalBayar = parseFloat(totalBayarInput.value) || 0;
        let kurangBayar = totalPembelian - uangMuka - totalBayar;
        if (kurangBayar < 0) kurangBayar = 0;
        kurangBayarInput.value = kurangBayar;
    }

    totalBayarInput.addEventListener('input', hitungKurangBayar);
});
</script>