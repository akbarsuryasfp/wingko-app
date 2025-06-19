<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kaskeluar;
use Illuminate\Support\Facades\DB;

class KaskeluarController extends Controller
{
    public function index()
    {
        $kaskeluar = Kaskeluar::orderBy('tanggal', 'desc')->get();
        return view('kaskeluar.index', compact('kaskeluar'));
    }

    public function create()
    {
        // Generate no_BKK otomatis
        $last = \DB::table('t_kaskeluar')->orderBy('no_BKK', 'desc')->first();
        if ($last && preg_match('/BKK(\d+)/', $last->no_BKK, $m)) {
            $next = (int)$m[1] + 1;
        } else {
            $next = 1;
        }
        $no_BKK = 'BKK' . str_pad($next, 6, '0', STR_PAD_LEFT);

        // Ambil daftar akun untuk dropdown
        $akun = \DB::table('t_akun')->get();

        return view('kaskeluar.create', compact('no_BKK', 'akun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_BKK'      => 'required|string|unique:t_kaskeluar,no_BKK',
            'tanggal'     => 'required|date',
            'id_akun'     => 'required|integer',
            'jumlah'      => 'required|numeric|min:1',
            'penerima'    => 'required|string',
            'no_referensi'=> 'nullable|string',
        ]);

        // 1. Generate id_jurnal otomatis
        $lastJurnal = \DB::table('t_jurnal_umum')->orderBy('id_jurnal', 'desc')->first();
        $id_jurnal = $lastJurnal ? $lastJurnal->id_jurnal + 1 : 1;

        // 2. Insert ke t_jurnal_umum
        \DB::table('t_jurnal_umum')->insert([
            'id_jurnal'   => $id_jurnal,
            'tanggal'     => $request->tanggal,
            'keterangan'  => $request->keterangan ?? 'Kas Keluar',
            'nomor_bukti'    => $request->no_BKK,
        ]);

        // 3. Insert ke t_kaskeluar
        \DB::table('t_kaskeluar')->insert([
            'id_jurnal'    => $id_jurnal,
            'no_BKK'       => $request->no_BKK,
            'tanggal'      => $request->tanggal,
            'no_referensi' => $request->no_referensi,
            'kode_akun' => \DB::table('t_akun')->where('id_akun', $request->id_akun)->value('kode_akun'),
            'jumlah'       => $request->jumlah,
            'penerima'     => $request->penerima,
            'keterangan'   => $request->keterangan,
            'jenis_kas'    => $request->jenis_kas, // <-- pastikan baris ini ada!
        ]);

        // 4. Insert ke t_jurnal_detail
        $lastDetail = \DB::table('t_jurnal_detail')->orderBy('id_jurnal_detail', 'desc')->first();
        $id_jurnal_detail = $lastDetail ? $lastDetail->id_jurnal_detail + 1 : 1;

        // Debit akun lawan, kredit kas (id_akun = 101)
        \DB::table('t_jurnal_detail')->insert([
            'id_jurnal_detail' => $id_jurnal_detail,
            'id_jurnal'        => $id_jurnal,
            'id_akun'          => $request->id_akun,
            'debit'            => $request->jumlah,
            'kredit'           => 0,
        ]);
        \DB::table('t_jurnal_detail')->insert([
            'id_jurnal_detail' => $id_jurnal_detail + 1,
            'id_jurnal'        => $id_jurnal,
            'id_akun'          => 1, // kas
            'debit'            => 0,
            'kredit'           => $request->jumlah,
        ]);

        return redirect()->route('kaskeluar.index')->with('success', 'Kas keluar berhasil disimpan & jurnal otomatis.');
    }

    public function edit($no_BKK)
    {
        $item = Kaskeluar::findOrFail($no_BKK);
        return view('kaskeluar.edit', compact('item'));
    }

    public function update(Request $request, $no_BKK)
    {
        $request->validate([
            'tanggal'     => 'required|date',
            'jenis_kas'   => 'required|string',
            'id_akun'     => 'required|integer',
            'jumlah'      => 'required|numeric|min:1',
            'penerima'    => 'required|string',
            'no_referensi'=> 'nullable|string',
        ]);

        $item = Kaskeluar::findOrFail($no_BKK);

        // Ambil kode_akun dari id_akun
        $kode_akun = \DB::table('t_akun')->where('id_akun', $request->id_akun)->value('kode_akun');

        // Update t_kaskeluar
        $item->update([
            'tanggal'      => $request->tanggal,
            'no_referensi' => $request->no_referensi,
            'jenis_kas'    => $request->jenis_kas,
            'kode_akun'    => $kode_akun,
            'jumlah'       => $request->jumlah,
            'penerima'     => $request->penerima,
            'keterangan'   => $request->keterangan,
        ]);

        // Update t_jurnal_umum
        \DB::table('t_jurnal_umum')->where('id_jurnal', $item->id_jurnal)->update([
            'tanggal'    => $request->tanggal,
            'keterangan' => $request->keterangan ?? 'Kas Keluar',
        ]);

        // Update t_jurnal_detail (hapus lalu insert ulang)
        \DB::table('t_jurnal_detail')->where('id_jurnal', $item->id_jurnal)->delete();

        $lastDetail = \DB::table('t_jurnal_detail')->orderBy('id_jurnal_detail', 'desc')->first();
        $id_jurnal_detail = $lastDetail ? $lastDetail->id_jurnal_detail + 1 : 1;

        // Debit akun lawan, kredit kas
        \DB::table('t_jurnal_detail')->insert([
            'id_jurnal_detail' => $id_jurnal_detail,
            'id_jurnal'        => $item->id_jurnal,
            'kode_akun'        => $kode_akun,
            'debit'            => $request->jumlah,
            'kredit'           => 0,
        ]);
        \DB::table('t_jurnal_detail')->insert([
            'id_jurnal_detail' => $id_jurnal_detail + 1,
            'id_jurnal'        => $item->id_jurnal,
            'kode_akun'        => 101,
            'debit'            => 0,
            'kredit'           => $request->jumlah,
        ]);

        return redirect()->route('kaskeluar.index')->with('success', 'Kas keluar berhasil diupdate & jurnal otomatis.');
    }

    public function destroy($no_BKK)
    {
        $item = Kaskeluar::findOrFail($no_BKK);
        // Hapus jurnal detail & umum
        DB::table('t_jurnal_detail')->where('id_jurnal', $item->id_jurnal)->delete();
        DB::table('t_jurnal_umum')->where('id_jurnal', $item->id_jurnal)->delete();
        $item->delete();

        return redirect()->route('kaskeluar.index')->with('success', 'Kas keluar berhasil dihapus.');
    }
}