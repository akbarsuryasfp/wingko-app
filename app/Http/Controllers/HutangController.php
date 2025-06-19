<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hutang;

class HutangController extends Controller
{
    public function daftar()
    {
        $hutangs = Hutang::all();
        return view('hutang.daftar', compact('hutangs'));
    }

    public function create()
    {
        return view('hutang.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_utang' => 'required|unique:t_utang,no_utang',
            'no_pembelian' => 'required',
            'kode_supplier' => 'required',
            'total_tagihan' => 'required|numeric|min:0',
        ]);

        \App\Models\Hutang::create([
            'no_utang'      => $request->no_utang,
            'no_pembelian'  => $request->no_pembelian,
            'kode_supplier' => $request->kode_supplier,
            'total_tagihan' => $request->total_tagihan,
            'sisa_utang'    => $request->total_tagihan,
            'status'        => 'Belum Lunas',
        ]);

        return redirect()->route('hutang.index')->with('success', 'Data hutang berhasil ditambahkan.');
    }

    public function index()
    {
        $hutangs = \App\Models\Hutang::all();
        return view('hutang.index', compact('hutangs'));
    }

    public function detail($no_utang)
    {
        $hutang = \DB::table('t_utang')->where('no_utang', $no_utang)->first();
        if (!$hutang) {
            abort(404);
        }
        return view('hutang.detail', compact('hutang'));
    }

    public function bayar($no_utang)
    {
        $hutang = \DB::table('t_utang')->where('no_utang', $no_utang)->first();
        if (!$hutang) abort(404);

        // Generate No BKK otomatis
        $last = \DB::table('t_kaskeluar')->orderBy('no_BKK', 'desc')->first();
        if ($last && preg_match('/BKK(\d+)/', $last->no_BKK, $m)) {
            $next = (int)$m[1] + 1;
        } else {
            $next = 1;
        }
        $no_BKK = 'BKK' . str_pad($next, 6, '0', STR_PAD_LEFT);

        // Nama supplier otomatis dari kode supplier
        $nama_supplier = \DB::table('t_supplier')->where('kode_supplier', $hutang->kode_supplier)->value('nama_supplier');

        return view('hutang.bayar', compact('hutang', 'no_BKK', 'nama_supplier'));
    }

    public function bayarStore(Request $request, $no_utang)
    {
        $request->validate([
            'tanggal'   => 'required|date',
            'jumlah'    => 'required|numeric|min:1',
            'keterangan'=> 'nullable|string',
            'no_BKK'    => 'required|string',
            'jenis_kas' => 'required|string',
            'kode_akun' => 'required|string',
        ]);

        // Ambil data utang
        $utang = \DB::table('t_utang')->where('no_utang', $no_utang)->first();
        $kode_supplier = $utang->kode_supplier ?? '';

        // 1. Buat id_jurnal baru
        $lastJurnal = \DB::table('t_jurnal_umum')->orderBy('id_jurnal', 'desc')->first();
        $id_jurnal = $lastJurnal ? $lastJurnal->id_jurnal + 1 : 1;

        // 2. Insert ke t_jurnal_umum
        \DB::table('t_jurnal_umum')->insert([
            'id_jurnal'   => $id_jurnal,
            'tanggal'     => $request->tanggal,
            'keterangan'  => $request->keterangan ?? 'Pembayaran utang',
            'nomor_bukti'    => $request->no_BKK,
        ]);

        // 3. Insert ke t_kaskeluar
        \DB::table('t_kaskeluar')->insert([
            'id_jurnal'    => $id_jurnal,
            'no_BKK'       => $request->no_BKK,
            'tanggal'      => $request->tanggal,
            'jumlah'       => $request->jumlah,
            'no_referensi' => $no_utang,
            'keterangan'   => $request->keterangan,
            'penerima'     => $kode_supplier,
            'jenis_kas'    => $request->jenis_kas,
            'kode_akun'    => $request->kode_akun,
        ]);

        // 4. Insert ke t_jurnal_detail
        $lastDetail = \DB::table('t_jurnal_detail')->orderBy('id_jurnal_detail', 'desc')->first();
        $id_jurnal_detail = $lastDetail ? $lastDetail->id_jurnal_detail + 1 : 1;

        // Debit kas (101), Kredit utang (201)
        \DB::table('t_jurnal_detail')->insert([
            'id_jurnal_detail' => $id_jurnal_detail,
            'id_jurnal'        => $id_jurnal,
            'id_akun'        => 1, // kas
            'debit'            => $request->jumlah,
            'kredit'           => 0,
        ]);
        \DB::table('t_jurnal_detail')->insert([
            'id_jurnal_detail' => $id_jurnal_detail + 1,
            'id_jurnal'        => $id_jurnal,
            'id_akun'        => 1, // utang
            'debit'            => 0,
            'kredit'           => $request->jumlah,
        ]);

        // 5. Update t_utang
        $totalBayar = \DB::table('t_kaskeluar')
            ->where('no_referensi', $no_utang)
            ->sum('jumlah');
        $sisa = $utang->total_tagihan - $totalBayar;

        \DB::table('t_utang')->where('no_utang', $no_utang)->update([
            'total_bayar' => $totalBayar,
            'sisa_utang'  => $sisa,
            'status'      => ($sisa <= 0 ? 'Lunas' : 'Belum Lunas'),
        ]);

        return redirect()->route('hutang.detail', $no_utang)->with('success', 'Pembayaran utang & jurnal berhasil disimpan.');
    }
}
