@extends('layouts.app')

@section('content')
<div class="container mt-5" style="max-width:700px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-4 text-center">
                FORM PEMBAYARAN PIUTANG
            </h5>
            <form action="{{ route('piutang.bayar.store', $piutang->no_piutang) }}" method="POST" class="p-2">
                @csrf
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th style="width:250px;">Kas yang Digunakan</th>
                            <td>
                                <select name="kas" class="form-control" required>
                                    <option value="">-- Pilih Kas --</option>
                                    @foreach($kasList as $kas)
                                        <option value="{{ $kas->kode_akun }}">{{ $kas->nama_akun }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>Nomor BKM</th>
                            <td>
                                <input type="text" name="no_bkm" class="form-control" value="{{ $no_bkm }}" readonly style="background:#e9ecef;pointer-events:none;">
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>
                                <input type="date" name="tanggal_bayar" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </td>
                        </tr>
                        <tr>
                            <th>Nama Pelanggan</th>
                            <td>
                                <input type="text" class="form-control" value="{{ $pelanggan ? $pelanggan->nama_pelanggan : '-' }}" readonly style="background:#e9ecef;pointer-events:none;">
                            </td>
                        </tr>
                        <tr>
                            <th>No Piutang</th>
                            <td>
                                <input type="text" class="form-control" value="{{ $piutang->no_piutang }}" readonly style="background:#e9ecef;pointer-events:none;">
                            </td>
                        </tr>
                        <tr>
                            <th>Jumlah Piutang</th>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" value="{{ number_format($piutang->total_tagihan,0,',','.') }}" readonly style="background:#e9ecef;pointer-events:none;">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Pembayaran Sebelumnya</th>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" value="{{ number_format($piutang->total_bayar,0,',','.') }}" readonly style="background:#e9ecef;pointer-events:none;">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Sisa Piutang</th>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" class="form-control" value="{{ number_format($piutang->sisa_piutang,0,',','.') }}" readonly style="background:#e9ecef;pointer-events:none;">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Nominal Pembayaran</th>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="jumlah_bayar_display" class="form-control" autocomplete="off" required>
                                    <input type="hidden" name="jumlah_bayar" id="jumlah_bayar" required>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>Keterangan</th>
                            <td>
                                <input type="text" name="keterangan" class="form-control" value="Pembayaran piutang">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('piutang.index') }}" class="btn btn-secondary">Back</a>
                    <button type="submit" class="btn btn-success">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ambil nilai awal dari blade
    const sisaPiutangAwal = {{ $piutang->sisa_piutang }};
    const inputBayarDisplay = document.getElementById('jumlah_bayar_display');
    const inputBayarHidden = document.getElementById('jumlah_bayar');
    // Ambil input sisa piutang (readonly) dengan selector yang tepat (berdasarkan label th)
    let sisaPiutangInput = null;
    document.querySelectorAll('tr').forEach(function(row) {
        if(row.querySelector('th') && row.querySelector('th').innerText.trim().toLowerCase() === 'sisa piutang') {
            sisaPiutangInput = row.querySelector('input.form-control');
        }
    });

    function formatRupiah(angka) {
        if (isNaN(angka) || angka === null) return '0';
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    function parseNumberInput(val) {
        return parseInt(String(val).replace(/\D/g, '')) || 0;
    }

    if(inputBayarDisplay && inputBayarHidden && sisaPiutangInput) {
        inputBayarDisplay.addEventListener('input', function() {
            let bayar = parseNumberInput(this.value);
            // Format display
            this.value = bayar ? formatRupiah(bayar) : '';
            // Set hidden input for backend
            inputBayarHidden.value = bayar;
            // Update sisa piutang
            let sisa = sisaPiutangAwal - bayar;
            if(sisa < 0) sisa = 0;
            sisaPiutangInput.value = formatRupiah(sisa);
        });
        // Pastikan value hidden input tetap required
        inputBayarDisplay.setAttribute('min', 1);
        inputBayarDisplay.setAttribute('max', sisaPiutangAwal);
        // On form submit, pastikan value hidden input sudah benar
        inputBayarDisplay.form.addEventListener('submit', function(e) {
            let bayar = parseNumberInput(inputBayarDisplay.value);
            inputBayarHidden.value = bayar;
        });
    }
});
</script>