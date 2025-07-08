@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">EDIT KONSINYASI KELUAR</h4>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('konsinyasikeluar.update', $header->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row mb-3">
            <div class="col-md-4">
                <label>No Konsinyasi Keluar</label>
                <input type="text" name="kode_setor" class="form-control" required value="{{ old('kode_setor', $header->kode_setor) }}">
            </div>
            <div class="col-md-4">
                <label>Nama Consignee (Mitra)</label>
                <select name="kode_consignee" class="form-control" required>
                    <option value="">---Pilih Consignee---</option>
                    @foreach($consignees as $c)
                        <option value="{{ $c->kode_consignee }}" {{ old('kode_consignee', $header->kode_consignee)==$c->kode_consignee?'selected':'' }}>{{ $c->nama_consignee }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>Tanggal Setor</label>
                <input type="date" name="tanggal_setor" class="form-control" required value="{{ old('tanggal_setor', $header->tanggal_setor) }}">
            </div>
        </div>
        <hr>
        <h5 class="mb-3">DAFTAR PRODUK KONSINYASI KELUAR</h5>
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle" id="produk-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Produk</th>
                        <th>Jumlah Setor</th>
                        <th>Satuan</th>
                        <th>Harga Setor</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($header->details as $i => $d)
                    <tr>
                        <td>{{ $i+1 }}</td>
                        <td><select name="produk[][kode_produk]" class="form-control" required>
                            <option value="">---Pilih Produk---</option>
                            @foreach($produkList as $p)
                            <option value="{{ $p->kode_produk }}" {{ $d->kode_produk==$p->kode_produk?'selected':'' }}>{{ $p->nama_produk }}</option>
                            @endforeach
                        </select></td>
                        <td><input type="number" name="produk[][jumlah_setor]" class="form-control jumlah" min="1" required value="{{ $d->jumlah_setor }}"></td>
                        <td><input type="text" name="produk[][satuan]" class="form-control" required value="{{ $d->satuan }}"></td>
                        <td><input type="number" name="produk[][harga_setor]" class="form-control harga" min="0" required value="{{ $d->harga_setor }}"></td>
                        <td class="subtotal">{{ $d->jumlah_setor * $d->harga_setor }}</td>
                        <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)"><i class='bi bi-trash'></i></button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="tambahProduk()">Tambah Produk</button>
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('konsinyasikeluar.index') }}" class="btn btn-secondary">Back</a>
            <button type="reset" class="btn btn-warning">Reset</button>
            <button type="submit" class="btn btn-success">Update</button>
        </div>
    </form>
</div>
<script>
    let produkList = @json($produkList);
    function tambahProduk() {
        let row = `<tr>
            <td></td>
            <td><select name=\"produk[][kode_produk]\" class=\"form-control\" required>
                <option value=\"\">---Pilih Produk---</option>
                ${produkList.map(p=>`<option value='${p.kode_produk}'>${p.nama_produk}</option>`).join('')}
            </select></td>
            <td><input type=\"number\" name=\"produk[][jumlah_setor]\" class=\"form-control jumlah\" min=\"1\" required></td>
            <td><input type=\"text\" name=\"produk[][satuan]\" class=\"form-control\" required></td>
            <td><input type=\"number\" name=\"produk[][harga_setor]\" class=\"form-control harga\" min=\"0\" required></td>
            <td class=\"subtotal\">0</td>
            <td><button type=\"button\" class=\"btn btn-danger btn-sm\" onclick=\"hapusBaris(this)\"><i class='bi bi-trash'></i></button></td>
        </tr>`;
        document.querySelector('#produk-table tbody').insertAdjacentHTML('beforeend', row);
        updateNoUrut();
    }
    function hapusBaris(btn) {
        btn.closest('tr').remove();
        updateNoUrut();
    }
    function updateNoUrut() {
        document.querySelectorAll('#produk-table tbody tr').forEach((tr, i) => {
            tr.querySelector('td').innerText = i+1;
        });
    }
    document.addEventListener('input', function(e) {
        if(e.target.classList.contains('jumlah') || e.target.classList.contains('harga')) {
            let tr = e.target.closest('tr');
            let jumlah = tr.querySelector('.jumlah').value || 0;
            let harga = tr.querySelector('.harga').value || 0;
            tr.querySelector('.subtotal').innerText = jumlah * harga;
        }
    });
    updateNoUrut();
</script>
@endsection
