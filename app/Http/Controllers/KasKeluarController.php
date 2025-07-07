<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JurnalHelper;

class KasKeluarController extends Controller
{
    public function index()
    {
        // Ambil data kas keluar dari jurnal umum dan detail
        $kaskeluar = DB::table('t_jurnal_umum as ju')
            ->join('t_jurnal_detail as jd', function($join) {
                $join->on('ju.no_jurnal', '=', 'jd.no_jurnal')
                     ->where('jd.kredit', '>', 0)
                     ->where('jd.kode_akun', '=', JurnalHelper::getKodeAkun('kas_bank'));
            })
            ->select(
                'ju.*',
                'jd.kredit as jumlah'
            )
            ->orderBy('ju.tanggal', 'desc')
            ->get();

        $data = [];
        foreach ($kaskeluar as $row) {
            $parts = explode(' | ', $row->keterangan);

            // Format hutang: [no_referensi] | [keterangan] | [penerima]
            if (preg_match('/^PBL|^OB|^RB/', $parts[0] ?? '')) {
                $row->no_referensi    = $parts[0] ?? '';
                $row->keterangan_teks = $parts[1] ?? '';
                $row->penerima        = $parts[2] ?? '';
                // Nama supplier dari kode penerima jika ada
                $row->nama_penerima = $row->penerima
                    ? DB::table('t_supplier')->where('kode_supplier', $row->penerima)->value('nama_supplier')
                    : null;
            } else {
                // Format order/pembelian/retur: [keterangan] | [no_referensi]
                $row->keterangan_teks = $parts[0] ?? '';
                $row->no_referensi    = $parts[1] ?? '';
                $row->penerima        = null;
                $row->nama_penerima   = null;

                // Cek sumber transaksi dari no_referensi
                if (preg_match('/^OB/', $row->no_referensi)) {
                    $kode_supplier = DB::table('t_order_beli')->where('no_order_beli', $row->no_referensi)->value('kode_supplier');
                    $row->nama_penerima = $kode_supplier
                        ? DB::table('t_supplier')->where('kode_supplier', $kode_supplier)->value('nama_supplier')
                        : null;
                } elseif (preg_match('/^PBL/', $row->no_referensi)) {
                    $kode_supplier = DB::table('t_pembelian')->where('no_pembelian', $row->no_referensi)->value('kode_supplier');
                    $row->nama_penerima = $kode_supplier
                        ? DB::table('t_supplier')->where('kode_supplier', $kode_supplier)->value('nama_supplier')
                        : null;
                } elseif (preg_match('/^RB/', $row->no_referensi)) {
                    $kode_supplier = DB::table('t_returbeli')->where('no_retur_beli', $row->no_referensi)->value('kode_supplier');
                    $row->nama_penerima = $kode_supplier
                        ? DB::table('t_supplier')->where('kode_supplier', $kode_supplier)->value('nama_supplier')
                        : null;
                }
            }

            $data[] = $row;
        }

        return view('kaskeluar.index', ['kaskeluar' => $data]);
    }

    public function create()
    {
        // Ambil nomor_bukti terakhir, urutkan berdasarkan angka setelah 'BKK'
        $last = DB::table('t_jurnal_umum')
            ->where('nomor_bukti', 'like', 'BKK%')
            ->selectRaw('MAX(CAST(SUBSTRING(nomor_bukti, 4) AS UNSIGNED)) as max_bkk')
            ->first();

        $next = ($last && $last->max_bkk) ? $last->max_bkk + 1 : 1;
        $no_BKK = 'BKK' . str_pad($next, 6, '0', STR_PAD_LEFT);

        $akun = DB::table('t_akun')->where('kode_akun', '!=', JurnalHelper::getKodeAkun('kas_bank'))->get();
        return view('kaskeluar.create', compact('no_BKK', 'akun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_BKK'      => 'required|string|unique:t_jurnal_umum,nomor_bukti',
            'tanggal'     => 'required|date',
            'kode_akun'   => 'required|string',
            'jumlah'      => 'required|numeric|min:1',
            'penerima'    => 'required|string',
            'no_referensi'=> 'nullable|string',
            'keterangan'  => 'nullable|string',
        ]);

        // Generate no_jurnal dan no_jurnal_detail otomatis (string format)
        $no_jurnal = 'JU-' . date('YmdHis') . '-' . rand(100,999);

        // Gabungkan keterangan: [no_referensi] | [keterangan] | [penerima]
        $keterangan = ($request->no_referensi ?? '') . ' | ' . ($request->keterangan ?? '') . ' | ' . $request->penerima;

        // Simpan ke t_jurnal_umum
        DB::table('t_jurnal_umum')->insert([
            'no_jurnal'   => $no_jurnal,
            'tanggal'     => $request->tanggal,
            'keterangan'  => $keterangan,
            'nomor_bukti' => $request->no_BKK,
        ]);

        // Debit akun lawan
        $no_jurnal_detail1 = 'JD-' . date('YmdHis') . '-' . rand(100,999);
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => $no_jurnal_detail1,
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => $request->kode_akun,
            'debit'            => $request->jumlah,
            'kredit'           => 0,
        ]);
        // Kredit kas (mapping)
        $no_jurnal_detail2 = 'JD-' . date('YmdHis') . '-' . rand(100,999);
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => $no_jurnal_detail2,
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => JurnalHelper::getKodeAkun('kas_bank'),
            'debit'            => 0,
            'kredit'           => $request->jumlah,
        ]);

        return redirect()->route('kaskeluar.index')->with('success', 'Kas keluar berhasil disimpan.');
    }

    public function edit($id)
    {
        // Ambil data utama
        $kas = DB::table('t_jurnal_umum')->where('no_jurnal', $id)->first();

        // Ambil detail akun lawan (debit) dan jumlah
        $detail = DB::table('t_jurnal_detail')
            ->where('no_jurnal', $id)
            ->where('kredit', 0)
            ->first();

        // Ekstrak info tambahan dari keterangan
        $parts = explode(' | ', $kas->keterangan);
        $kas->no_referensi = $parts[0] ?? '';
        $kas->keterangan_teks = $parts[1] ?? '';
        $kas->penerima = $parts[2] ?? '';
        $kas->kode_akun = $detail->kode_akun ?? '';
        $kas->jumlah = $detail->debit ?? 0;

        $akun = DB::table('t_akun')->where('kode_akun', '!=', JurnalHelper::getKodeAkun('kas_bank'))->get();

        return view('kaskeluar.edit', compact('kas', 'akun'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal'     => 'required|date',
            'kode_akun'   => 'required|string',
            'jumlah'      => 'required|numeric|min:1',
            'penerima'    => 'required|string',
            'no_referensi'=> 'nullable|string',
            'keterangan'  => 'nullable|string',
        ]);

        // Gabungkan keterangan: [no_referensi] | [keterangan] | [penerima]
        $keterangan = ($request->no_referensi ?? '') . ' | ' . ($request->keterangan ?? '') . ' | ' . $request->penerima;

        // Update t_jurnal_umum
        DB::table('t_jurnal_umum')->where('no_jurnal', $id)->update([
            'tanggal'    => $request->tanggal,
            'keterangan' => $keterangan,
        ]);

        // Update t_jurnal_detail (debit akun lawan)
        DB::table('t_jurnal_detail')
            ->where('no_jurnal', $id)
            ->where('kredit', 0)
            ->update([
                'kode_akun' => $request->kode_akun,
                'debit'     => $request->jumlah,
            ]);

        // Update t_jurnal_detail (kredit kas)
        DB::table('t_jurnal_detail')
            ->where('no_jurnal', $id)
            ->where('kode_akun', JurnalHelper::getKodeAkun('kas_bank'))
            ->update([
                'kredit' => $request->jumlah,
                'debit'  => 0,
            ]);

        return redirect()->route('kaskeluar.index')->with('success', 'Kas keluar berhasil diupdate.');
    }

    public function destroy($id)
    {
        // Hapus detail dulu
        DB::table('t_jurnal_detail')->where('no_jurnal', $id)->delete();
        // Hapus header
        DB::table('t_jurnal_umum')->where('no_jurnal', $id)->delete();

        return redirect()->route('kaskeluar.index')->with('success', 'Kas keluar berhasil dihapus.');
    }
}