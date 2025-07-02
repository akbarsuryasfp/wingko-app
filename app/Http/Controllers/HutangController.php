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

        // 1. Buat no_jurnal baru
        $no_jurnal = \App\Helpers\JurnalHelper::generateNoJurnal();

        // 2. Buat no_jurnal_detail untuk masing-masing detail
        $no_jurnal_detail1 = \App\Helpers\JurnalHelper::generateNoJurnalDetail();
        $no_jurnal_detail2 = \App\Helpers\JurnalHelper::generateNoJurnalDetail();

        // 3. Gabungkan keterangan: [no_referensi] | [keterangan] | [penerima]
        $keterangan = $no_utang . ' | ' . ($request->keterangan ?? '') . ' | ' . $kode_supplier;

        // 4. Insert ke t_jurnal_umum
        \DB::table('t_jurnal_umum')->insert([
            'no_jurnal'   => $no_jurnal,
            'tanggal'     => now()->toDateString(),
            'keterangan'  => $keterangan,
            'nomor_bukti' => $request->no_BKK,
        ]);
        \DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => $no_jurnal_detail1,
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => '101',
            'debit'            => 0,
            'kredit'           => $request->jumlah,
        ]);
        \DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => $no_jurnal_detail2,
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => '201',
            'debit'            => $request->jumlah,
            'kredit'           => 0,
        ]);

        // 5. Update t_utang
        $totalBayar = \DB::table('t_jurnal_umum as ju')
    ->join('t_jurnal_detail as jd', 'ju.no_jurnal', '=', 'jd.no_jurnal')
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
