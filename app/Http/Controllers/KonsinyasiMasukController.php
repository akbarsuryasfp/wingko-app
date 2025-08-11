<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\KonsinyasiMasuk;
use App\Models\ProdukKonsinyasi; 
use App\Models\Consignor; 

class KonsinyasiMasukController extends Controller
{
    public function index(Request $request)
    {
        $query = KonsinyasiMasuk::with(['consignor', 'details']);

        // Filter periode tanggal_masuk
        if ($request->filled('tanggal_awal')) {
            $query->where('tanggal_masuk', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_masuk', '<=', $request->tanggal_akhir);
        }

        // Search by no_konsinyasimasuk or nama_consignor
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_konsinyasimasuk', 'like', "%$search%")
                  ->orWhereHas('consignor', function($qc) use ($search) {
                      $qc->where('nama_consignor', 'like', "%$search%");
                  });
            });
        }

        // Urutkan no_konsinyasimasuk ASC/DESC
        $sort = $request->get('sort', 'asc');
        $query->orderBy('no_konsinyasimasuk', $sort);

        $konsinyasiMasukList = $query->get();
        return view('konsinyasimasuk.index', compact('konsinyasiMasukList'));
    }

    public function create()
    {
        // Ambil nomor terakhir dari t_konsinyasimasuk
        $last = DB::table('t_konsinyasimasuk')
            ->orderBy('no_konsinyasimasuk', 'desc')
            ->value('no_konsinyasimasuk');

        if ($last) {
            $num = (int)substr($last, 2); // Misal: KM00012 -> 12
            $newNum = $num + 1;
            $no_konsinyasimasuk = 'KM' . str_pad($newNum, 5, '0', STR_PAD_LEFT);
        } else {
            $no_konsinyasimasuk = 'KM00001';
        }

        // Ambil data consignor dan produk konsinyasi (ganti sesuai kebutuhan)
        $consignor = \App\Models\Consignor::all();
        $produkKonsinyasi = \App\Models\ProdukKonsinyasi::all();

        return view('konsinyasimasuk.create', [
            'no_konsinyasimasuk' => $no_konsinyasimasuk,
            'consignor' => $consignor,
            'produkKonsinyasi' => $produkKonsinyasi,
        ]);
    }

    public function store(Request $request)
    {
        // Validasi
        $request->validate([
            'no_konsinyasimasuk' => 'required|unique:t_konsinyasimasuk,no_konsinyasimasuk',
            'no_surat_titip_jual' => 'required|unique:t_konsinyasimasuk,no_surattitipjual',
            'kode_consignor' => 'required',
            'tanggal_masuk' => 'required|date',
            'total_titip' => 'required|numeric',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json'
        ]);

        DB::transaction(function () use ($request) {
            DB::table('t_konsinyasimasuk')->insert([
                'no_konsinyasimasuk' => $request->no_konsinyasimasuk,
                'no_surattitipjual' => $request->no_surat_titip_jual,
                'kode_consignor' => $request->kode_consignor,
                'tanggal_masuk' => $request->tanggal_masuk,
                'total_titip' => $request->total_titip,
                'keterangan' => $request->keterangan,
            ]);

            $details = json_decode($request->detail_json, true);
            foreach ($details as $i => $detail) {
                DB::table('t_konsinyasimasuk_detail')->insert([
                    'no_konsinyasimasuk' => $request->no_konsinyasimasuk,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah_stok' => $detail['jumlah_stok'],
                    'harga_titip' => $detail['harga_titip'],
                    'subtotal' => $detail['subtotal'],
                ]);
                // Catat ke kartu stok konsinyasi
                DB::table('t_kartuperskonsinyasi')->insert([
                    'tanggal' => $request->tanggal_masuk,
                    'kode_produk' => $detail['kode_produk'],
                    'masuk' => $detail['jumlah_stok'],
                    'keluar' => 0,
                    'sisa' => $detail['jumlah_stok'],
                    'harga_konsinyasi' => $detail['harga_titip'],
                    'lokasi' => 'Gudang',
                    'keterangan' => 'Konsinyasi Masuk',
                    'no_transaksi' => $request->no_konsinyasimasuk
                ]);
            }
        });

        return redirect()->route('konsinyasimasuk.index')->with('success', 'Konsinyasi masuk berhasil disimpan!');
    }

    public function show($no_konsinyasimasuk)
    {
        $konsinyasi = KonsinyasiMasuk::with(['consignor'])->where('no_konsinyasimasuk', $no_konsinyasimasuk)->firstOrFail();
        $details = DB::table('t_konsinyasimasuk_detail')
            ->leftJoin('t_produk_konsinyasi', 't_konsinyasimasuk_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->where('t_konsinyasimasuk_detail.no_konsinyasimasuk', $no_konsinyasimasuk)
            ->select(
                't_konsinyasimasuk_detail.*',
                't_produk_konsinyasi.nama_produk',
                't_produk_konsinyasi.satuan'
            )
            ->get();
        $produkMaster = DB::table('t_produk')->select('kode_produk', 'nama_produk')->get();
        return view('konsinyasimasuk.detail', compact('konsinyasi', 'details', 'produkMaster'));
    }

    public function getProdukByConsignor($kode_consignor)
    {
        $produk = ProdukKonsinyasi::where('kode_consignor', $kode_consignor)->get();
        return response()->json($produk);
    }

    // Method cetak untuk print konsinyasi masuk
    public function cetak($no_konsinyasimasuk)
    {
        $konsinyasi = KonsinyasiMasuk::with(['consignor'])->where('no_konsinyasimasuk', $no_konsinyasimasuk)->firstOrFail();
        $details = DB::table('t_konsinyasimasuk_detail')
            ->join('t_produk', 't_konsinyasimasuk_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_konsinyasimasuk_detail.no_konsinyasimasuk', $no_konsinyasimasuk)
            ->select(
                't_konsinyasimasuk_detail.*',
                't_produk.nama_produk'
            )
            ->get();
        // Anda bisa membuat view khusus untuk cetak, misal: konsinyasimasuk.cetak
        return view('konsinyasimasuk.cetak', compact('konsinyasi', 'details'));
    }

    public function edit($no_konsinyasimasuk)
    {
        $konsinyasi = KonsinyasiMasuk::with(['consignor', 'details'])->where('no_konsinyasimasuk', $no_konsinyasimasuk)->firstOrFail();
        $consignor = Consignor::all();
        $produkKonsinyasi = ProdukKonsinyasi::all();
        $details = $konsinyasi->details;
        return view('konsinyasimasuk.edit', compact('konsinyasi', 'consignor', 'produkKonsinyasi', 'details'));
    }

    public function update(Request $request, $id)
        // Logging data detail yang dikirim dari form edit
      
    {

        $request->validate([
            'tanggal_masuk' => 'required|date',
            'kode_consignor' => 'required',
            'detail_json' => 'required',
        ]);
        Log::info('DATA UPDATE KONSINYASI MASUK', [
            'id' => $id,
            'detail_json' => $request->detail_json,
            'parsed_detail' => json_decode($request->detail_json, true),
            'all_request' => $request->all()
        ]);

        // Ambil header konsinyasi masuk berdasarkan no_konsinyasimasuk
        $konsinyasi = DB::table('t_konsinyasimasuk')->where('no_konsinyasimasuk', $id)->first();
        if (!$konsinyasi) {
            return redirect()->back()->withErrors('Data tidak ditemukan!');
        }

        // Hitung total titip dari detail_json
        $details = json_decode($request->detail_json, true);
        $total_titip = 0;
        foreach ($details as $d) {
            $total_titip += $d['subtotal'];
        }

        // Update header (hanya kolom yang ada di tabel)
        DB::table('t_konsinyasimasuk')->where('no_konsinyasimasuk', $id)->update([
            'tanggal_masuk' => $request->tanggal_masuk,
            'kode_consignor' => $request->kode_consignor,
            'keterangan' => $request->keterangan,
            'total_titip' => $total_titip,
        ]);

        // Hapus detail lama
        DB::table('t_konsinyasimasuk_detail')->where('no_konsinyasimasuk', $id)->delete();

        // Insert detail baru dan catat ke kartu stok konsinyasi
        foreach ($details as $d) {
            DB::table('t_konsinyasimasuk_detail')->insert([
                'no_konsinyasimasuk' => $id,
                'kode_produk' => $d['kode_produk'],
                'jumlah_stok' => $d['jumlah_stok'],
                'harga_titip' => $d['harga_titip'],
                'subtotal' => $d['subtotal'],
                'harga_jual' => isset($d['harga_jual']) ? $d['harga_jual'] : null,
                'komisi' => isset($d['komisi']) ? $d['komisi'] : null,
            ]);
            // Catat ke kartu stok konsinyasi (barang masuk)
            $lastStok = DB::table('t_kartuperskonsinyasi')
                ->where('kode_produk', $d['kode_produk'])
                ->where('lokasi', 'Gudang')
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->value('sisa');
            $lastStok = $lastStok ?? 0;
            $sisaBaru = $lastStok + $d['jumlah_stok'];
            DB::table('t_kartuperskonsinyasi')->insert([
                'tanggal' => $request->tanggal_masuk,
                'kode_produk' => $d['kode_produk'],
                'masuk' => $d['jumlah_stok'],
                'keluar' => 0,
                'sisa' => $sisaBaru,
                'harga_konsinyasi' => $d['harga_titip'],
                'lokasi' => 'Gudang',
                'keterangan' => 'Konsinyasi Masuk (Update)',
                'no_transaksi' => $id
            ]);
        }

        return redirect()->route('konsinyasimasuk.index')->with('success', 'Data berhasil diupdate!');
    }

    public function destroy($id)
    {
        // Hapus data konsinyasi masuk utama
        DB::table('t_konsinyasimasuk')->where('no_konsinyasimasuk', $id)->delete();
        // Hapus detail terkait
        DB::table('t_konsinyasimasuk_detail')->where('no_konsinyasimasuk', $id)->delete();
        return redirect()->route('konsinyasimasuk.index')->with('success', 'Data konsinyasi masuk berhasil dihapus!');
    }

    // Endpoint untuk ambil detail produk konsinyasi masuk (JSON, untuk modal input harga jual)
    public function detailJson($no_konsinyasimasuk)
    {
        try {
            $details = DB::table('t_konsinyasimasuk_detail')
                ->leftJoin('t_produk_konsinyasi', 't_konsinyasimasuk_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
                ->where('t_konsinyasimasuk_detail.no_konsinyasimasuk', $no_konsinyasimasuk)
                ->select(
                    't_konsinyasimasuk_detail.kode_produk',
                    't_produk_konsinyasi.nama_produk',
                    't_produk_konsinyasi.satuan',
                    't_konsinyasimasuk_detail.jumlah_stok',
                    't_konsinyasimasuk_detail.harga_titip',
                    't_konsinyasimasuk_detail.harga_jual',
                    DB::raw('(COALESCE(t_konsinyasimasuk_detail.harga_jual,0) * t_konsinyasimasuk_detail.jumlah_stok) as subtotal')
                )
                ->get();

            if ($details->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data detail tidak ditemukan'
                ], 404);
            }

            return response()->json($details);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Endpoint untuk update harga_jual pada detail produk konsinyasi masuk
    public function updateHargaJual(Request $request)
    {
        $request->validate([
            'no_konsinyasimasuk' => 'required',
            'kode_produk' => 'required',
            'harga_jual' => 'required|numeric|min:0',
        ]);

        try {
            // Ambil harga_titip dari database
            $detail = DB::table('t_konsinyasimasuk_detail')
                ->where('no_konsinyasimasuk', $request->no_konsinyasimasuk)
                ->where('kode_produk', $request->kode_produk)
                ->first();

            if (!$detail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail produk tidak ditemukan'
                ], 404);
            }

            $harga_titip = $detail->harga_titip ?? 0;
            $komisi = $request->harga_jual - $harga_titip;

            DB::table('t_konsinyasimasuk_detail')
                ->where('no_konsinyasimasuk', $request->no_konsinyasimasuk)
                ->where('kode_produk', $request->kode_produk)
                ->update([
                    'harga_jual' => $request->harga_jual,
                    'komisi' => $komisi
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Harga jual dan komisi berhasil diperbarui'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui harga jual dan komisi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getHargaJualKonsinyasi($kode_produk)
    {
        $harga = DB::table('t_konsinyasimasuk_detail')
            ->where('kode_produk', $kode_produk)
            ->whereNotNull('harga_jual')
            ->orderByDesc('no_detailkonsinyasimasuk')
            ->value('harga_jual');
        return response()->json(['harga_jual' => $harga]);
    }

    // Cetak laporan konsinyasi masuk (untuk cetak_laporan.blade.php)
    public function cetakLaporan(Request $request)
    {
        $query = \App\Models\KonsinyasiMasuk::with(['consignor', 'details']);

        // Filter periode tanggal_masuk
        if ($request->filled('tanggal_awal')) {
            $query->where('tanggal_masuk', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_masuk', '<=', $request->tanggal_akhir);
        }

        // Search by no_konsinyasimasuk or nama_consignor
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_konsinyasimasuk', 'like', "%$search%")
                  ->orWhereHas('consignor', function($qc) use ($search) {
                      $qc->where('nama_consignor', 'like', "%$search%");
                  });
            });
        }

        // Urutkan no_konsinyasimasuk ASC/DESC
        $sort = $request->get('sort', 'asc');
        $query->orderBy('no_konsinyasimasuk', $sort);

        $konsinyasiMasukList = $query->get();
        return view('konsinyasimasuk.cetak_laporan', compact('konsinyasiMasukList'));
    }

    // Cetak laporan konsinyasi masuk format PDF (stream)
    public function cetakLaporanPdf(Request $request)
    {
        $query = \App\Models\KonsinyasiMasuk::with(['consignor', 'details']);

        // Filter periode tanggal_masuk
        if ($request->filled('tanggal_awal')) {
            $query->where('tanggal_masuk', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_masuk', '<=', $request->tanggal_akhir);
        }

        // Search by no_konsinyasimasuk or nama_consignor
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_konsinyasimasuk', 'like', "%$search%")
                  ->orWhereHas('consignor', function($qc) use ($search) {
                      $qc->where('nama_consignor', 'like', "%$search%");
                  });
            });
        }

        // Urutkan no_konsinyasimasuk ASC/DESC
        $sort = $request->get('sort', 'asc');
        $query->orderBy('no_konsinyasimasuk', $sort);

        $konsinyasiMasukList = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('konsinyasimasuk.cetak_laporan', compact('konsinyasiMasukList'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('laporan-konsinyasi-masuk.pdf');
    }
}