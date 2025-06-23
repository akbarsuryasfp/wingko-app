<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasKeluarController extends Controller
{
    public function index()
    {
        // Ambil data kas keluar dari jurnal umum dan detail
        $kaskeluar = DB::table('t_jurnal_umum as ju')
            ->join('t_jurnal_detail as jd', function($join) {
                $join->on('ju.id_jurnal', '=', 'jd.id_jurnal')
                     ->where('jd.kredit', '>', 0)
                     ->where('jd.kode_akun', '=', '101');
            })
            ->join('t_jurnal_detail as jd2', function($join) {
                $join->on('ju.id_jurnal', '=', 'jd2.id_jurnal')
                     ->where('jd2.debit', '>', 0);
            })
            ->select(
                'ju.id_jurnal',
                'ju.nomor_bukti',
                'ju.tanggal',
                'ju.keterangan',
                'jd2.kode_akun',
                'jd2.debit as jumlah'
            )
            ->orderBy('ju.tanggal', 'desc')
            ->get();

        // Ekstrak info tambahan dari keterangan
        $data = [];
        foreach ($kaskeluar as $row) {
            // Format: [no_referensi] | [keterangan] | [penerima]
            $parts = explode(' | ', $row->keterangan);
            $row->no_referensi = $parts[0] ?? '';
            $row->keterangan_teks = $parts[1] ?? '';
            $row->penerima = $parts[2] ?? '';
            $row->jenis_kas = 'Kas'; // default, karena kode akun 101
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

        $akun = DB::table('t_akun')->where('kode_akun', '!=', '101')->get();
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

        // Generate id_jurnal otomatis
        $lastJurnal = DB::table('t_jurnal_umum')->orderBy('id_jurnal', 'desc')->first();
        $id_jurnal = $lastJurnal ? $lastJurnal->id_jurnal + 1 : 1;

        // Gabungkan keterangan: [no_referensi] | [keterangan] | [penerima]
        $keterangan = ($request->no_referensi ?? '') . ' | ' . ($request->keterangan ?? '') . ' | ' . $request->penerima;

        // Simpan ke t_jurnal_umum
        DB::table('t_jurnal_umum')->insert([
            'id_jurnal'   => $id_jurnal,
            'tanggal'     => $request->tanggal,
            'keterangan'  => $keterangan,
            'nomor_bukti' => $request->no_BKK,
        ]);

        // Generate id_jurnal_detail otomatis
        $lastDetail = DB::table('t_jurnal_detail')->orderBy('id_jurnal_detail', 'desc')->first();
        $id_jurnal_detail = $lastDetail ? $lastDetail->id_jurnal_detail + 1 : 1;

        // Debit akun lawan
        DB::table('t_jurnal_detail')->insert([
            'id_jurnal_detail' => $id_jurnal_detail,
            'id_jurnal'        => $id_jurnal,
            'kode_akun'        => $request->kode_akun,
            'debit'            => $request->jumlah,
            'kredit'           => 0,
        ]);
        // Kredit kas (101)
        DB::table('t_jurnal_detail')->insert([
            'id_jurnal_detail' => $id_jurnal_detail + 1,
            'id_jurnal'        => $id_jurnal,
            'kode_akun'        => '101',
            'debit'            => 0,
            'kredit'           => $request->jumlah,
        ]);

        return redirect()->route('kaskeluar.index')->with('success', 'Kas keluar berhasil disimpan.');
    }

    public function edit($id)
    {
        // Ambil data utama
        $kas = DB::table('t_jurnal_umum')->where('id_jurnal', $id)->first();

        // Ambil detail akun lawan (debit) dan jumlah
        $detail = DB::table('t_jurnal_detail')
            ->where('id_jurnal', $id)
            ->where('kredit', 0)
            ->first();

        // Ekstrak info tambahan dari keterangan
        $parts = explode(' | ', $kas->keterangan);
        $kas->no_referensi = $parts[0] ?? '';
        $kas->keterangan_teks = $parts[1] ?? '';
        $kas->penerima = $parts[2] ?? '';
        $kas->kode_akun = $detail->kode_akun ?? '';
        $kas->jumlah = $detail->debit ?? 0;

        $akun = DB::table('t_akun')->where('kode_akun', '!=', '101')->get();

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
        DB::table('t_jurnal_umum')->where('id_jurnal', $id)->update([
            'tanggal'    => $request->tanggal,
            'keterangan' => $keterangan,
        ]);

        // Update t_jurnal_detail (debit akun lawan)
        DB::table('t_jurnal_detail')
            ->where('id_jurnal', $id)
            ->where('kredit', 0)
            ->update([
                'kode_akun' => $request->kode_akun,
                'debit'     => $request->jumlah,
            ]);

        // Update t_jurnal_detail (kredit kas)
        DB::table('t_jurnal_detail')
            ->where('id_jurnal', $id)
            ->where('kode_akun', '101')
            ->update([
                'kredit' => $request->jumlah,
                'debit'  => 0,
            ]);

        return redirect()->route('kaskeluar.index')->with('success', 'Kas keluar berhasil diupdate.');
    }

    public function destroy($id)
    {
        // Hapus detail dulu
        DB::table('t_jurnal_detail')->where('id_jurnal', $id)->delete();
        // Hapus header
        DB::table('t_jurnal_umum')->where('id_jurnal', $id)->delete();

        return redirect()->route('kaskeluar.index')->with('success', 'Kas keluar berhasil dihapus.');
    }
}