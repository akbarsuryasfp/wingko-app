<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PiutangController extends Controller
{
    // Tampilkan daftar piutang
    public function index()
    {
        // Ambil data piutang dari tabel t_piutang
        $piutangs = DB::table('t_piutang')->orderBy('no_piutang')->get();

        return view('piutang.index', compact('piutangs'));
    }

    // Tampilkan form input piutang baru
    public function create()
    {
        return view('piutang.create');
    }

    // Simpan piutang baru
    public function store(Request $request)
    {
        // Status otomatis: lunas jika sisa_piutang == 0, selain itu belum lunas
        $status = ($request->sisa_piutang == 0) ? 'lunas' : 'belum lunas';

        DB::table('t_piutang')->insert([
            'no_piutang'         => $request->no_piutang,
            'no_jual'            => $request->no_jual,
            'kode_pelanggan'     => $request->kode_pelanggan,
            'total_tagihan'      => $request->total_tagihan,
            'sisa_piutang'       => $request->sisa_piutang,
            'total_bayar'        => $request->total_bayar,
            'status_piutang'     => $status,
            'tanggal_jatuh_tempo'=> $request->tanggal_jatuh_tempo,
        ]);
        return redirect()->route('piutang.index')->with('success', 'Data piutang berhasil disimpan.');
    }

    // Tampilkan detail piutang
    public function show($no_piutang)
    {
        $piutang = DB::table('t_piutang')->where('no_piutang', $no_piutang)->first();
        if (!$piutang) abort(404);

        $pelanggan = DB::table('t_pelanggan')->where('kode_pelanggan', $piutang->kode_pelanggan)->first();

        $pembayaran = [];
        if (DB::getSchemaBuilder()->hasTable('t_pembayaran_piutang')) {
            $pembayaran = DB::table('t_pembayaran_piutang')
                ->where('no_piutang', $no_piutang)
                ->orderBy('tanggal')
                ->get();
        }

        return view('piutang.detail', compact('piutang', 'pelanggan', 'pembayaran'));
    }

    // Tampilkan form edit piutang
    public function edit($no_piutang)
    {
        $piutang = DB::table('t_piutang')->where('no_piutang', $no_piutang)->first();
        if (!$piutang) abort(404);
        return view('piutang.create', compact('piutang'));
    }

    // Update data piutang
    public function update(Request $request, $no_piutang)
    {
        $status = ($request->sisa_piutang == 0) ? 'lunas' : 'belum lunas';

        DB::table('t_piutang')->where('no_piutang', $no_piutang)->update([
            'no_jual'            => $request->no_jual,
            'kode_pelanggan'     => $request->kode_pelanggan,
            'total_tagihan'      => $request->total_tagihan,
            'sisa_piutang'       => $request->sisa_piutang,
            'total_bayar'        => $request->total_bayar,
            'status_piutang'     => $status,
            'tanggal_jatuh_tempo'=> $request->tanggal_jatuh_tempo,
        ]);
        return redirect()->route('piutang.index')->with('success', 'Data piutang berhasil diupdate.');
    }

    // Hapus data piutang
    public function destroy($no_piutang)
    {
        DB::table('t_piutang')->where('no_piutang', $no_piutang)->delete();
        return redirect()->route('piutang.index')->with('success', 'Data piutang berhasil dihapus.');
    }

    // Form pembayaran piutang
    public function bayar($no_piutang)
    {
        $piutang = DB::table('t_piutang')->where('no_piutang', $no_piutang)->first();
        if (!$piutang) abort(404);

        $pelanggan = DB::table('t_pelanggan')->where('kode_pelanggan', $piutang->kode_pelanggan)->first();
        $kasList = DB::table('t_akun')->where('nama_akun', 'like', '%Kas%')->get();

        // Ambil nomor BKM terakhir dari t_jurnal_umum (nomor_bukti)
        $last = DB::table('t_jurnal_umum')
            ->where('nomor_bukti', 'like', 'BKM%')
            ->orderBy('nomor_bukti', 'desc')->first();
        if ($last && preg_match('/BKM(\d+)/', $last->nomor_bukti, $m)) {
            $next = (int)$m[1] + 1;
        } else {
            $next = 1;
        }
        $no_bkm = 'BKM' . str_pad($next, 6, '0', STR_PAD_LEFT);

        return view('piutang.create_bayar', compact('piutang', 'pelanggan', 'kasList', 'no_bkm'));
    }

    // Proses pembayaran piutang
    public function bayarStore(Request $request, $no_piutang)
    {
        $piutang = DB::table('t_piutang')->where('no_piutang', $no_piutang)->first();
        if (!$piutang) abort(404);

        $jumlah_bayar = $request->jumlah_bayar;
        $sisa_piutang_baru = $piutang->sisa_piutang - $jumlah_bayar;
        $total_bayar_baru = $piutang->total_bayar + $jumlah_bayar;
        // Status otomatis: lunas jika sisa_piutang == 0
        $status = ($sisa_piutang_baru == 0) ? 'lunas' : 'belum lunas';

        // 1. Buat id_jurnal baru
        $lastJurnal = DB::table('t_jurnal_umum')->orderBy('id_jurnal', 'desc')->first();
        $id_jurnal = $lastJurnal ? $lastJurnal->id_jurnal + 1 : 1;

        // 2. Insert ke t_jurnal_umum
        DB::table('t_jurnal_umum')->insert([
            'id_jurnal'   => $id_jurnal,
            'tanggal'     => $request->tanggal_bayar,
            'keterangan'  => $request->keterangan ?? 'Pembayaran piutang',
            'nomor_bukti' => $request->no_bkm,
        ]);

        // 3. Insert ke t_jurnal_detail
        $lastDetail = DB::table('t_jurnal_detail')->orderBy('id_jurnal_detail', 'desc')->first();
        $id_jurnal_detail = $lastDetail ? $lastDetail->id_jurnal_detail + 1 : 1;

        // Debit kas (101), Kredit piutang (102)
        DB::table('t_jurnal_detail')->insert([
            'id_jurnal_detail' => $id_jurnal_detail,
            'id_jurnal'        => $id_jurnal,
            'id_akun'          => 1, // kas (lihat id_akun kas di t_akun)
            'debit'            => $jumlah_bayar,
            'kredit'           => 0,
        ]);
        DB::table('t_jurnal_detail')->insert([
            'id_jurnal_detail' => $id_jurnal_detail + 1,
            'id_jurnal'        => $id_jurnal,
            'id_akun'          => 2, // piutang usaha (lihat id_akun piutang di t_akun)
            'debit'            => 0,
            'kredit'           => $jumlah_bayar,
        ]);

        // 4. Update t_piutang
        DB::table('t_piutang')->where('no_piutang', $no_piutang)->update([
            'sisa_piutang'   => $sisa_piutang_baru,
            'total_bayar'    => $total_bayar_baru,
            'status_piutang' => $status,
        ]);

        // 5. Update status pembayaran di t_penjualan jika sisa_piutang == 0
        if ($sisa_piutang_baru == 0) {
            $no_jual = $piutang->no_jual;
            DB::table('t_penjualan')->where('no_jual', $no_jual)->update([
                'status_pembayaran' => 'lunas',
                'piutang' => 0,
            ]);
        }

        return redirect()->route('piutang.detail', $no_piutang)->with('success', 'Pembayaran piutang & jurnal berhasil disimpan.');
    }
}