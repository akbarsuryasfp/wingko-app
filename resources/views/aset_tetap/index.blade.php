@extends('layouts.app')
@section('content')
<div class="container">
    <h4>Daftar Aset Tetap</h4>
    <a href="{{ route('aset-tetap.create') }}" class="btn btn-primary mb-3">Tambah Aset Tetap</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Tanggal Beli</th>
                <th>Harga Perolehan</th>
                <th>Umur Ekonomis</th>
                <th>Nilai Sisa</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $aset)
            <tr>
                <td>{{ $aset->kode_aset_tetap }}</td>
                <td>{{ $aset->nama_aset }}</td>
                <td>{{ $aset->tanggal_beli }}</td>
                <td>{{ number_format($aset->harga_perolehan,0,',','.') }}</td>
                <td>{{ $aset->umur_ekonomis }} tahun</td>
                <td>{{ number_format($aset->nilai_sisa,0,',','.') }}</td>
                <td>{{ $aset->keterangan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection