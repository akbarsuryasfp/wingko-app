<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermintaanProduksi;
use App\Models\PermintaanProduksiDetail;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

class PermintaanProduksiController extends Controller
{
    // Form input permintaan produksi
    public function create()
    {
        // Ambil semua produk
        $produk = DB::table('t_produk')->get();

        // Ambil stok akhir produk di lokasi gudang (misal kode_lokasi = '1')
        $stokAkhir = DB::table('t_kartupersproduk')
            ->select('kode_produk', DB::raw('SUM(masuk) - SUM(keluar) as stok'))
            ->where('lokasi', '1') // lokasi gudang
            ->groupBy('kode_produk')
            ->get()
            ->keyBy('kode_produk');

        // Tambahkan properti stok dan selisih ke produk
        $produkKurangStok = $produk->filter(function($p) use ($stokAkhir) {
            $stok = $stokAkhir[$p->kode_produk]->stok ?? 0;
            $p->stok_gudang = $stok;
            $p->selisih = max($p->stokmin - $stok, 0);
            return $stok < $p->stokmin;
        });

        return view('permintaan_produksi.create', ['produk' => $produkKurangStok]);
    }
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'desc');
        $search = $request->get('search');
        $tanggal_awal = $request->get('tanggal_awal');
        $tanggal_akhir = $request->get('tanggal_akhir');

        $query = PermintaanProduksi::with('details.produk');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('no_permintaan_produksi', 'like', "%$search%")
                  ->orWhere('keterangan', 'like', "%$search%");
            });
        }
        if ($tanggal_awal) {
            $query->whereDate('tanggal', '>=', $tanggal_awal);
        }
        if ($tanggal_akhir) {
            $query->whereDate('tanggal', '<=', $tanggal_akhir);
        }

        $permintaanProduksi = $query->orderBy('tanggal', $sort)
            ->paginate(10)
            ->appends([
                'search' => $search,
                'tanggal_awal' => $tanggal_awal,
                'tanggal_akhir' => $tanggal_akhir,
                'sort' => $sort,
            ]);

        return view('permintaan_produksi.index', compact('permintaanProduksi', 'sort'));
    }

    


    // Simpan permintaan produksi dan detailnya
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
            'produk.*.kode_produk' => 'required|exists:t_produk,kode_produk',
            'produk.*.unit' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // Generate kode permintaan produksi (boleh kamu ganti logikanya)
            $kode = 'PP' . now()->format('YmdHis');

            // Simpan ke t_permintaan_produksi
            $permintaan = PermintaanProduksi::create([
                'no_permintaan_produksi' => $kode,
                'tanggal' => $request->tanggal,
                'keterangan' => $request->keterangan,
                'status' => 'Menunggu', // Ubah dari 'Diproses' ke 'Menunggu'
            ]);

            // Simpan detail
            foreach ($request->produk as $i => $item) {
                PermintaanProduksiDetail::create([
                    'no_detail_permintaan_produksi' => $kode . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                    'no_permintaan_produksi' => $kode,
                    'kode_produk' => $item['kode_produk'],
                    'unit' => $item['unit'],
                ]);
            }
        });

        return redirect()->route('permintaan_produksi.index')->with('success', 'Permintaan produksi berhasil disimpan!');
    }
    public function destroy($no_permintaan_produksi)
    {
        DB::transaction(function () use ($no_permintaan_produksi) {
            // Hapus detail terlebih dahulu
            PermintaanProduksiDetail::where('no_permintaan_produksi', $no_permintaan_produksi)->delete();
            // Hapus master
            PermintaanProduksi::where('no_permintaan_produksi', $no_permintaan_produksi)->delete();
        });

        return redirect()->route('permintaan_produksi.index')->with('success', 'Permintaan produksi berhasil dihapus!');
    }
}
