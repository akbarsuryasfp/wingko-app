<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PiutangController extends Controller
{

    // Cetak laporan piutang
    public function cetakLaporan(Request $request)
    {
        $status = $request->input('status_piutang');
        $sort = $request->input('sort', 'asc');
        $search = $request->input('search');
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');
        $query = \DB::table('t_piutang')
            ->leftJoin('t_penjualan', 't_piutang.no_jual', '=', 't_penjualan.no_jual')
            ->select(
                't_piutang.*',
                't_penjualan.piutang as sisa_piutang_penjualan',
                't_penjualan.tanggal_jual'
            );
        if ($status === null || $status === '') {
            $query->where('t_piutang.status_piutang', '!=', 'lunas');
        } else {
            $query->where('t_piutang.status_piutang', $status);
        }
        if ($tanggal_awal && $tanggal_akhir) {
            $query->whereBetween('t_penjualan.tanggal_jual', [$tanggal_awal, $tanggal_akhir]);
        } elseif ($tanggal_awal) {
            $query->where('t_penjualan.tanggal_jual', '>=', $tanggal_awal);
        } elseif ($tanggal_akhir) {
            $query->where('t_penjualan.tanggal_jual', '<=', $tanggal_akhir);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('t_piutang.no_piutang', 'like', "%$search%")
                  ->orWhere('t_piutang.no_jual', 'like', "%$search%");
            });
        }
        $piutangs = $query->orderBy('t_piutang.no_piutang', $sort)->get();
        return view('piutang.cetak_laporan', compact('piutangs'));
    }
    // Tampilkan daftar piutang
    public function index(Request $request)
    {
        // Ambil filter dari request
        $status = $request->input('status_piutang');
        $sort = $request->input('sort', 'asc');
        $search = $request->input('search');
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');
        $query = DB::table('t_piutang')
            ->leftJoin('t_penjualan', 't_piutang.no_jual', '=', 't_penjualan.no_jual')
            ->select(
                't_piutang.*',
                't_penjualan.piutang as sisa_piutang_penjualan',
                't_penjualan.tanggal_jual'
            );
        // Filter status
        if ($status === null || $status === '') {
            $query->where('t_piutang.status_piutang', '!=', 'lunas');
        } else {
            $query->where('t_piutang.status_piutang', $status);
        }
        // Filter periode berdasarkan tanggal_jual
        if ($tanggal_awal && $tanggal_akhir) {
            $query->whereBetween('t_penjualan.tanggal_jual', [$tanggal_awal, $tanggal_akhir]);
        } elseif ($tanggal_awal) {
            $query->where('t_penjualan.tanggal_jual', '>=', $tanggal_awal);
        } elseif ($tanggal_akhir) {
            $query->where('t_penjualan.tanggal_jual', '<=', $tanggal_akhir);
        }
        // Search no piutang / no jual
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('t_piutang.no_piutang', 'like', "%$search%")
                  ->orWhere('t_piutang.no_jual', 'like', "%$search%");
            });
        }
        $piutangs = $query->orderBy('t_piutang.no_piutang', $sort)->get();
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

        // Generate no_piutang otomatis dengan awalan PI dan format PI000001 dst
        $last = DB::table('t_piutang')->orderBy('no_piutang', 'desc')->first();
        if ($last && preg_match('/PI(\d+)/', $last->no_piutang, $m)) {
            $next = (int)$m[1] + 1;
        } else {
            $next = 1;
        }
        $no_piutang = 'PI' . str_pad($next, 6, '0', STR_PAD_LEFT);

        DB::table('t_piutang')->insert([
            'no_piutang'         => $no_piutang,
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

        // Sinkronisasi ke t_penjualan jika ada
        $no_jual = $request->no_jual;
        if ($no_jual) {
            DB::table('t_penjualan')->where('no_jual', $no_jual)->update([
                'piutang' => $request->sisa_piutang,
                'total_bayar' => $request->total_bayar,
                'status_pembayaran' => $status,
            ]);
        }
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
        $kasList = DB::table('t_akun')->where('nama_akun', 'like', '%Kas%')->get(['kode_akun', 'nama_akun']);

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

        $jumlah_bayar = (int) $request->jumlah_bayar;
        $sisa_piutang_lama = (int) $piutang->sisa_piutang;
        $total_bayar_lama = (int) $piutang->total_bayar;
        $sisa_piutang_baru = $sisa_piutang_lama - $jumlah_bayar;
        $total_bayar_baru = $total_bayar_lama + $jumlah_bayar;
        // Status otomatis: lunas jika sisa_piutang == 0
        $status = ($sisa_piutang_baru == 0) ? 'lunas' : 'belum lunas';

        // 1. Buat no_jurnal baru (string JU00001 dst)
        $lastJurnal = DB::table('t_jurnal_umum')->orderBy('no_jurnal', 'desc')->first();
        if ($lastJurnal && preg_match('/JU(\d+)/', $lastJurnal->no_jurnal, $m)) {
            $next = (int)$m[1] + 1;
        } else {
            $next = 1;
        }
        $no_jurnal = 'JU' . str_pad($next, 5, '0', STR_PAD_LEFT);

        // 2. Insert ke t_jurnal_umum
        DB::table('t_jurnal_umum')->insert([
            'no_jurnal'   => $no_jurnal,
            'tanggal'     => $request->tanggal_bayar,
            'keterangan'  => $request->keterangan ?? 'Pembayaran piutang',
            'nomor_bukti' => $request->no_bkm,
        ]);

        // 3. Insert ke t_jurnal_detail
        $lastDetail = DB::table('t_jurnal_detail')->orderBy('no_jurnal_detail', 'desc')->first();
        if ($lastDetail && preg_match('/JD(\d+)/', $lastDetail->no_jurnal_detail, $m)) {
            $nextDetail = (int)$m[1] + 1;
        } else {
            $nextDetail = 1;
        }
        $no_jurnal_detail = 'JD' . str_pad($nextDetail, 6, '0', STR_PAD_LEFT);

        // Debit kas (kode_akun), Kredit piutang (kode_akun)
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => $no_jurnal_detail,
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => $request->kas, // kas
            'debit'            => $jumlah_bayar,
            'kredit'           => 0,
        ]);
        $no_jurnal_detail2 = 'JD' . str_pad($nextDetail + 1, 6, '0', STR_PAD_LEFT);
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => $no_jurnal_detail2,
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => 104, // piutang usaha (kode_akun 104 sesuai t_akun)
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

        // Redirect ke index piutang agar user langsung melihat update
        return redirect()->route('piutang.index')->with('success', 'Pembayaran piutang & jurnal berhasil disimpan.');
    }
}