<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hutang;
use App\Helpers\JurnalHelper;

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

public function index(Request $request)
{
    try {
        $query = \DB::table('t_utang')
            ->join('t_supplier', 't_utang.kode_supplier', '=', 't_supplier.kode_supplier')
            ->select('t_utang.*', 't_supplier.nama_supplier')
            ->where('t_utang.sisa_utang', '>', 0);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('t_utang.no_utang', 'like', "%{$search}%")
                  ->orWhere('t_supplier.nama_supplier', 'like', "%{$search}%");
            });
        }

        $hutangs = $query->get();
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

        $nama_supplier = \DB::table('t_supplier')->where('kode_supplier', $hutang->kode_supplier)->value('nama_supplier');

        // Generate nomor BKK: BKK+date+(i+1)
        $today = date('Ymd');
        $last = \DB::table('t_jurnal_umum')
            ->where('nomor_bukti', 'like', 'BKK'.$today.'-%')
            ->selectRaw('MAX(CAST(SUBSTRING_INDEX(nomor_bukti, "-", -1) AS UNSIGNED)) as max_bkk')
            ->first();
        $next = ($last && $last->max_bkk) ? $last->max_bkk + 1 : 1;
        $no_BKK = 'BKK' . $today . '-' . $next;

        return view('hutang.bayar', compact('hutang', 'no_BKK', 'nama_supplier'));
    }

    public function bayarStore(Request $request, $no_utang)
    {
        $request->validate([
            'tanggal'   => 'required|date',
            'jumlah'    => 'required|numeric|min:1',
            'keterangan'=> 'nullable|string',
            'no_BKK'    => 'required|string',
            'kode_akun' => 'required|string', // akun kas yang digunakan (misal 1010/1000/1011)
        ]);

        // Ambil data utang
        $utang = \DB::table('t_utang')->where('no_utang', $no_utang)->first();
        $kode_supplier = $utang->kode_supplier ?? '';

            // CEK: Nominal pembayaran tidak boleh melebihi sisa hutang
    if ($request->jumlah > $utang->sisa_utang) {
        return back()->withInput()->withErrors(['jumlah' => 'Nominal pembayaran tidak boleh melebihi sisa hutang!']);
    }
        // 1. Buat no_jurnal baru
        $no_jurnal = JurnalHelper::generateNoJurnal();

        // 2. Gabungkan keterangan: [no_referensi] | [keterangan] | [penerima]
        $keterangan = $no_utang . ' | ' . ($request->keterangan ?? '') . ' | ' . $kode_supplier;

        // 3. Insert ke t_jurnal_umum (PASTIKAN INI DULU)
        \DB::table('t_jurnal_umum')->insert([
            'no_jurnal'   => $no_jurnal,
            'tanggal'     => now()->toDateString(),
            'keterangan'  => $keterangan,
            'nomor_bukti' => $request->no_BKK,
        ]);

        // 4. Insert detail pertama
        $no_jurnal_detail1 = JurnalHelper::generateNoJurnalDetail($no_jurnal);
        \DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => $no_jurnal_detail1,
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => JurnalHelper::getKodeAkun('kas_bank'),
            'debit'            => 0,
            'kredit'           => $request->jumlah,
        ]);

        // 5. Insert detail kedua
        $no_jurnal_detail2 = JurnalHelper::generateNoJurnalDetail($no_jurnal);
        \DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => $no_jurnal_detail2,
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => JurnalHelper::getKodeAkun('utang_usaha'),
            'debit'            => $request->jumlah,
            'kredit'           => 0,
        ]);

        // 6. Update t_utang
        $totalBayar = \DB::table('t_jurnal_umum as ju')
            ->join('t_jurnal_detail as jd', 'ju.no_jurnal', '=', 'jd.no_jurnal')
            ->where('ju.keterangan', 'like', $no_utang . ' |%')
            ->where('jd.kode_akun', JurnalHelper::getKodeAkun('utang_usaha'))
            ->sum('jd.debit');
        $sisa = $utang->total_tagihan - $totalBayar;

        \DB::table('t_utang')->where('no_utang', $no_utang)->update([
            'total_bayar' => $totalBayar,
            'sisa_utang'  => $sisa,
            'status'      => ($sisa <= 0 ? 'Lunas' : 'Belum Lunas'),
        ]);

        return redirect()->route('hutang.detail', $no_utang)->with('success', 'Pembayaran utang & jurnal berhasil disimpan.');
    }

    public function editPembayaran($no_utang, $no_jurnal)
    {
        $hutang = \DB::table('t_utang')->where('no_utang', $no_utang)->first();
        $nama_supplier = \DB::table('t_supplier')->where('kode_supplier', $hutang->kode_supplier)->value('nama_supplier');
        $pembayaran = \DB::table('t_jurnal_umum')->where('no_jurnal', $no_jurnal)->first();

        // Ambil detail kas (kredit) dan nominal (debit utang)
        $kas = \DB::table('t_jurnal_detail')
            ->where('no_jurnal', $no_jurnal)
            ->where('kredit', '>', 0)
            ->first();
        $utang = \DB::table('t_jurnal_detail')
            ->where('no_jurnal', $no_jurnal)
            ->where('debit', '>', 0)
            ->first();

        // Siapkan data untuk form
        $form = [
            'tanggal'    => $pembayaran->tanggal ?? '',
            'no_BKK'     => $pembayaran->nomor_bukti ?? '',
            'jumlah'     => $utang->debit ?? '',
            'kode_akun'  => $kas->kode_akun ?? '',
            'keterangan' => $pembayaran->keterangan ?? '',
        ];

        return view('hutang.edit', compact('hutang', 'nama_supplier', 'form', 'no_jurnal'));
    }

    public function hapusPembayaran($no_utang, $no_jurnal)
    {
        // Hapus jurnal detail dan jurnal umum
        \DB::table('t_jurnal_detail')->where('no_jurnal', $no_jurnal)->delete();
        \DB::table('t_jurnal_umum')->where('no_jurnal', $no_jurnal)->delete();

        // Update total bayar & sisa utang
        $utang = \DB::table('t_utang')->where('no_utang', $no_utang)->first();
        $totalBayar = \DB::table('t_jurnal_umum as ju')
            ->join('t_jurnal_detail as jd', 'ju.no_jurnal', '=', 'jd.no_jurnal')
            ->where('ju.keterangan', 'like', $no_utang . ' |%')
            ->where('jd.kode_akun', JurnalHelper::getKodeAkun('utang_usaha'))
            ->sum('jd.debit');
        $sisa = $utang->total_tagihan - $totalBayar;
        \DB::table('t_utang')->where('no_utang', $no_utang)->update([
            'total_bayar' => $totalBayar,
            'sisa_utang'  => $sisa,
            'status'      => ($sisa <= 0 ? 'Lunas' : 'Belum Lunas'),
        ]);

        return redirect()->route('hutang.detail', $no_utang)->with('success', 'Pembayaran berhasil dihapus.');
    }

    public function updatePembayaran(Request $request, $no_utang, $no_jurnal)
    {
        $request->validate([
            'kode_akun'  => 'required',
            'no_BKK'     => 'required',
            'tanggal'    => 'required|date',
            'jumlah'     => 'required|numeric|min:1',
            'keterangan' => 'nullable|string',
        ]);
    // Ambil data hutang
    $hutang = \DB::table('t_utang')->where('no_utang', $no_utang)->first();

    // Hitung sisa utang yang boleh dibayar (tambahkan jumlah pembayaran lama)
    $pembayaranLama = \DB::table('t_jurnal_detail')
        ->where('no_jurnal', $no_jurnal)
        ->where('debit', '>', 0)
        ->value('debit') ?? 0;
    $batasMaksimal = $hutang->sisa_utang + $pembayaranLama;

    // Validasi: jumlah tidak boleh melebihi sisa hutang + pembayaran lama
    if ($request->jumlah > $batasMaksimal) {
        return back()->withInput()->withErrors(['jumlah' => 'Nominal pembayaran tidak boleh melebihi sisa hutang!']);
    }
        // Update t_jurnal_umum
        \DB::table('t_jurnal_umum')
            ->where('no_jurnal', $no_jurnal)
            ->update([
                'tanggal'     => $request->tanggal,
                'nomor_bukti' => $request->no_BKK,
                'keterangan'  => $request->keterangan,
            ]);

        // Update t_jurnal_detail (kas/bank)
        \DB::table('t_jurnal_detail')
            ->where('no_jurnal', $no_jurnal)
            ->where('kredit', '>', 0)
            ->update([
                'kode_akun' => $request->kode_akun,
                'kredit'    => $request->jumlah,
            ]);

        // Update t_jurnal_detail (utang usaha)
        \DB::table('t_jurnal_detail')
            ->where('no_jurnal', $no_jurnal)
            ->where('debit', '>', 0)
            ->update([
                'debit' => $request->jumlah,
            ]);

        // Update summary di t_utang
        $totalBayar = \DB::table('t_jurnal_umum as ju')
            ->join('t_jurnal_detail as jd', 'ju.no_jurnal', '=', 'jd.no_jurnal')
            ->where('ju.keterangan', 'like', $no_utang . ' |%')
            ->where('jd.kode_akun', '2000') // kode akun utang usaha
            ->sum('jd.debit');
        $utang = \DB::table('t_utang')->where('no_utang', $no_utang)->first();
        $sisa = $utang->total_tagihan - $totalBayar;
        \DB::table('t_utang')->where('no_utang', $no_utang)->update([
            'total_bayar' => $totalBayar,
            'sisa_utang'  => $sisa,
            'status'      => ($sisa <= 0 ? 'Lunas' : 'Belum Lunas'),
        ]);

        return redirect()->route('hutang.detail', $no_utang)->with('success', 'Pembayaran berhasil diupdate.');
    }


public function laporanPdf(Request $request)
{
    $query = \DB::table('t_utang')
        ->join('t_supplier', 't_utang.kode_supplier', '=', 't_supplier.kode_supplier')
        ->select('t_utang.*', 't_supplier.nama_supplier')
        ->where('t_utang.sisa_utang', '>', 0); // Samakan dengan index

    // Filter/search jika ada
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('t_utang.no_utang', 'like', "%{$search}%")
              ->orWhere('t_supplier.nama_supplier', 'like', "%{$search}%");
        });
    }

    $hutangs = $query->orderBy('t_utang.no_utang', 'desc')->get();

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('hutang.cetak', compact('hutangs'));
    return $pdf->stream('laporan_hutang.pdf');
}}
