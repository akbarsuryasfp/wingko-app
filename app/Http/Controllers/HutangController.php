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
        try {
            $hutangs = \App\Models\Hutang::all();
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
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

        // Nama supplier otomatis dari kode supplier
        $nama_supplier = \DB::table('t_supplier')->where('kode_supplier', $hutang->kode_supplier)->value('nama_supplier');

        // Generate nomor BKK otomatis
        $last = \DB::table('t_jurnal_umum')
            ->where('nomor_bukti', 'like', 'BKK%')
            ->selectRaw('MAX(CAST(SUBSTRING(nomor_bukti, 4) AS UNSIGNED)) as max_bkk')
            ->first();
        $next = ($last && $last->max_bkk) ? $last->max_bkk + 1 : 1;
        $no_BKK = 'BKK' . str_pad($next, 6, '0', STR_PAD_LEFT);

        return view('hutang.bayar', compact('hutang', 'no_BKK', 'nama_supplier'));
    }

    public function bayarStore(Request $request, $no_utang)
    {
        $request->validate([
            'tanggal'   => 'required|date',
            'jumlah'    => 'required|numeric|min:1',
            'keterangan'=> 'nullable|string',
            'no_BKK'    => 'required|string',
            'kode_akun' => 'required|string', // akun kas yang digunakan (misal 101)
        ]);

        // Ambil data utang
        $utang = \DB::table('t_utang')->where('no_utang', $no_utang)->first();
        $kode_supplier = $utang->kode_supplier ?? '';

        // 1. Buat id_jurnal baru
        $lastJurnal = \DB::table('t_jurnal_umum')->orderBy('id_jurnal', 'desc')->first();
        $id_jurnal = $lastJurnal ? $lastJurnal->id_jurnal + 1 : 1;

        // 2. Gabungkan keterangan: [no_referensi] | [keterangan] | [penerima]
        $keterangan = $no_utang . ' | ' . ($request->keterangan ?? '') . ' | ' . $kode_supplier;

        // 3. Insert ke t_jurnal_umum
        \DB::table('t_jurnal_umum')->insert([
            'id_jurnal'   => $id_jurnal,
            'tanggal'     => $request->tanggal,
            'keterangan'  => $keterangan,
            'nomor_bukti' => $request->no_BKK,
        ]);

        // 4. Insert ke t_jurnal_detail
        $lastDetail = \DB::table('t_jurnal_detail')->orderBy('id_jurnal_detail', 'desc')->first();
        $id_jurnal_detail = $lastDetail ? $lastDetail->id_jurnal_detail + 1 : 1;

        // Kredit kas (kode_akun kas, misal 101)
        \DB::table('t_jurnal_detail')->insert([
            'id_jurnal_detail' => $id_jurnal_detail,
            'id_jurnal'        => $id_jurnal,
            'kode_akun'        => $request->kode_akun, // kas
            'debit'            => 0,
            'kredit'           => $request->jumlah,
        ]);
        // Debit utang (kode_akun utang, misal 201)
        \DB::table('t_jurnal_detail')->insert([
            'id_jurnal_detail' => $id_jurnal_detail + 1,
            'id_jurnal'        => $id_jurnal,
            'kode_akun'        => '201', // kode akun utang
            'debit'            => $request->jumlah,
            'kredit'           => 0,
        ]);

        // 5. Update t_utang
        $totalBayar = \DB::table('t_jurnal_detail as jd')
            ->join('t_jurnal_umum as ju', 'jd.id_jurnal', '=', 'ju.id_jurnal')
            ->where('ju.keterangan', 'like', $no_utang . ' |%')
            ->where('jd.kode_akun', '201') // utang
            ->sum('jd.debit');
        $sisa = $utang->total_tagihan - $totalBayar;

        \DB::table('t_utang')->where('no_utang', $no_utang)->update([
            'total_bayar' => $totalBayar,
            'sisa_utang'  => $sisa,
            'status'      => ($sisa <= 0 ? 'Lunas' : 'Belum Lunas'),
        ]);

        return redirect()->route('hutang.detail', $no_utang)->with('success', 'Pembayaran utang & jurnal berhasil disimpan.');
    }
}
