<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferProdukController extends Controller
{
public function index(Request $request)
{
    $query = DB::table('t_kartupersproduk')
        ->select(
            'no_transaksi',
            'tanggal',
            'lokasi as lokasi_asal',
            DB::raw('TRIM(SUBSTRING(keterangan, 11)) as lokasi_tujuan')
        )
        ->where('keterangan', 'LIKE', 'Transfer ke %')
        ->where('keluar', '>', 0);

    // Add date filter if provided
    if ($request->has('start_date') && $request->start_date != '') {
        $query->whereDate('tanggal', '>=', $request->start_date);
    }

    if ($request->has('end_date') && $request->end_date != '') {
        $query->whereDate('tanggal', '<=', $request->end_date);
    }

    $transfers = $query->groupBy('no_transaksi', 'tanggal', 'lokasi_asal', 'lokasi_tujuan')
        ->orderBy('tanggal', 'asc')
        ->paginate(10);

    // Get details for each transfer
    foreach ($transfers as $transfer) {
        $transfer->details = DB::table('t_kartupersproduk')
            ->join('t_produk', 't_kartupersproduk.kode_produk', '=', 't_produk.kode_produk')
            ->select(
                't_produk.nama_produk',
                't_kartupersproduk.keluar as jumlah',
                't_kartupersproduk.satuan',
                't_kartupersproduk.hpp'
            )
            ->where('no_transaksi', $transfer->no_transaksi)
            ->where('keluar', '>', 0)
            ->get();
    }

    return view('transferproduk.index', compact('transfers'));
}


    public function create()
    {
        // Default origin location is Gudang
        $lokasiAsal = 'Gudang';
        
        // Get products from Gudang by default
        $produkList = $this->getProductsByLocation($lokasiAsal);

        // Buat kode otomatis sederhana
        $last = DB::table('t_kartupersproduk')->orderByDesc('id')->first();
        $next = $last ? $last->id + 1 : 1;
        $kode_otomatis = 'TRF-' . date('Ymd') . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);

        return view('transferproduk.create', [
            'produk' => $produkList,
            'kode_otomatis' => $kode_otomatis,
            'lokasiAsal' => $lokasiAsal
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_transaksi'   => 'required',
            'tanggal'        => 'required|date',
            'lokasi_asal'    => 'required|in:Gudang,Toko 1,Toko 2',
            'lokasi_tujuan'  => 'required',
            'produk_id'      => 'required|array',
            'jumlah'         => 'required|array',
            'satuan'         => 'required|array',
            'harga'          => 'required|array',
        ]);

        // Validate destination based on origin
        if ($request->lokasi_asal === 'Gudang' && !in_array($request->lokasi_tujuan, ['Toko 1', 'Toko 2'])) {
            return back()->withErrors(['lokasi_tujuan' => 'Lokasi tujuan harus Toko 1 atau Toko 2 ketika berasal dari Gudang']);
        }

        if (($request->lokasi_asal === 'Toko 1' || $request->lokasi_asal === 'Toko 2') && $request->lokasi_tujuan !== 'Gudang') {
            return back()->withErrors(['lokasi_tujuan' => 'Lokasi tujuan harus Gudang ketika berasal dari Toko']);
        }

        DB::beginTransaction();
        try {
            foreach ($request->produk_id as $i => $produk_id) {
                // Keluarkan stok dari lokasi asal (keluar)
                DB::table('t_kartupersproduk')->insert([
                    'no_transaksi' => $request->no_transaksi,
                    'tanggal'      => $request->tanggal,
                    'tanggal_exp'  => $request->tanggal_exp[$i] ?? null,
                    'kode_produk'  => $produk_id,
                    'masuk'        => 0,
                    'keluar'       => $request->jumlah[$i],
                    'hpp'          => $request->harga[$i],
                    'satuan'       => $request->satuan[$i],
                    'keterangan'   => 'Transfer ke ' . $request->lokasi_tujuan,
                    'lokasi'       => $request->lokasi_asal,
                ]);

                // Masukkan stok ke lokasi tujuan (masuk)
                DB::table('t_kartupersproduk')->insert([
                    'no_transaksi' => $request->no_transaksi,
                    'tanggal'      => $request->tanggal,
                    'tanggal_exp'  => $request->tanggal_exp[$i] ?? null,
                    'kode_produk'  => $produk_id,
                    'masuk'        => $request->jumlah[$i],
                    'keluar'       => 0,
                    'hpp'          => $request->harga[$i],
                    'satuan'       => $request->satuan[$i],
                    'keterangan'   => 'Transfer dari ' . $request->lokasi_asal,
                    'lokasi'       => $request->lokasi_tujuan,
                ]);
            }

            DB::commit();
            return redirect()->route('transferproduk.index')->with('success', 'Transfer produk berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()]);
        }
    }

    public function getProductsByLocationAjax(Request $request)
    {
        $location = $request->query('location', 'Gudang');
        $products = $this->getProductsByLocation($location);
        return response()->json($products);
    }

    private function getProductsByLocation($location)
    {
        // Subquery stok produk di lokasi tertentu
        $produkLokasi = DB::table('t_kartupersproduk')
            ->select('kode_produk', DB::raw('SUM(masuk) - SUM(keluar) as stok'))
            ->where('lokasi', $location)
            ->groupBy('kode_produk')
            ->havingRaw('stok > 0');

        // Subquery HPP terakhir per produk di lokasi tertentu
        $hppTerakhir = DB::table('t_kartupersproduk as hpp')
            ->select('hpp.kode_produk', 'hpp.hpp')
            ->where('hpp.lokasi', $location)
            ->whereRaw('hpp.id = (SELECT MAX(id) FROM t_kartupersproduk WHERE kode_produk = hpp.kode_produk AND lokasi = ?)', [$location]);

        // Subquery tanggal_exp terakhir per produk di lokasi tertentu
        $tglExpTerakhir = DB::table('t_kartupersproduk as exp')
            ->select('exp.kode_produk', 'exp.tanggal_exp')
            ->where('exp.lokasi', $location)
            ->whereRaw('exp.id = (SELECT MAX(id) FROM t_kartupersproduk WHERE kode_produk = exp.kode_produk AND lokasi = ?)', [$location]);

        // Join ke t_produk untuk ambil nama_produk, satuan, stok, hpp, dan tanggal_exp terakhir
        return DB::table('t_produk')
            ->joinSub($produkLokasi, 'stok_lokasi', function ($join) {
                $join->on('t_produk.kode_produk', '=', 'stok_lokasi.kode_produk');
            })
            ->leftJoinSub($hppTerakhir, 'hpp_lokasi', function ($join) {
                $join->on('t_produk.kode_produk', '=', 'hpp_lokasi.kode_produk');
            })
            ->leftJoinSub($tglExpTerakhir, 'exp_lokasi', function ($join) {
                $join->on('t_produk.kode_produk', '=', 'exp_lokasi.kode_produk');
            })
            ->select(
                't_produk.kode_produk',
                't_produk.nama_produk',
                't_produk.satuan',
                'stok_lokasi.stok',
                'hpp_lokasi.hpp',
                'exp_lokasi.tanggal_exp'
            )
            ->get();
    }
        public function edit($no_transaksi)
    {
        $transfer = DB::table('t_kartupersproduk')
            ->where('no_transaksi', $no_transaksi)
            ->first();

        if (!$transfer) {
            abort(404);
        }

        $details = DB::table('t_kartupersproduk')
            ->where('no_transaksi', $no_transaksi)
            ->where('keluar', '>', 0)
            ->get();

        $produkList = $this->getProductsByLocation($transfer->lokasi);

        return view('transferproduk.edit', [
            'transfer' => $transfer,
            'details' => $details,
            'produk' => $produkList,
            'lokasiAsal' => $transfer->lokasi
        ]);
    }

    public function update(Request $request, $no_transaksi)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'produk_id' => 'required|array',
            'jumlah' => 'required|array',
            'satuan' => 'required|array',
            'harga' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            // Delete existing transfer records
            DB::table('t_kartupersproduk')
                ->where('no_transaksi', $no_transaksi)
                ->delete();

            // Insert new records
            foreach ($request->produk_id as $i => $produk_id) {
                DB::table('t_kartupersproduk')->insert([
                    'no_transaksi' => $no_transaksi,
                    'tanggal' => $request->tanggal,
                    'tanggal_exp' => $request->tanggal_exp[$i] ?? null,
                    'kode_produk' => $produk_id,
                    'masuk' => 0,
                    'keluar' => $request->jumlah[$i],
                    'hpp' => $request->harga[$i],
                    'satuan' => $request->satuan[$i],
                    'keterangan' => 'Transfer ke ' . $request->lokasi_tujuan,
                    'lokasi' => $request->lokasi_asal,
                ]);

                DB::table('t_kartupersproduk')->insert([
                    'no_transaksi' => $no_transaksi,
                    'tanggal' => $request->tanggal,
                    'tanggal_exp' => $request->tanggal_exp[$i] ?? null,
                    'kode_produk' => $produk_id,
                    'masuk' => $request->jumlah[$i],
                    'keluar' => 0,
                    'hpp' => $request->harga[$i],
                    'satuan' => $request->satuan[$i],
                    'keterangan' => 'Transfer dari ' . $request->lokasi_asal,
                    'lokasi' => $request->lokasi_tujuan,
                ]);
            }

            DB::commit();
            return redirect()->route('transferproduk.index')->with('success', 'Transfer produk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memperbarui: ' . $e->getMessage()]);
        }
    }

    public function destroy($no_transaksi)
    {
        DB::beginTransaction();
        try {
            DB::table('t_kartupersproduk')
                ->where('no_transaksi', $no_transaksi)
                ->delete();

            DB::commit();
            return redirect()->route('transferproduk.index')->with('success', 'Transfer produk berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menghapus: ' . $e->getMessage()]);
        }
    }
}