<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferProdukController extends Controller
{
public function index(Request $request)
{
    $start = $request->input('start_date', date('Y-m-01'));
    $end = $request->input('end_date', date('Y-m-d'));

    // Ambil header transfer (group by no_transaksi)
    $transfers = DB::table('t_kartupersproduk')
        ->whereBetween('tanggal', [$start, $end])
        ->where('keterangan', 'like', 'Transfer ke%')
        ->select(
            'no_transaksi',
            'tanggal',
            DB::raw('MIN(lokasi) as lokasi_asal'),
            DB::raw('MAX(CASE WHEN keterangan LIKE "Transfer ke%" THEN REPLACE(keterangan, "Transfer ke ", "") ELSE NULL END) as lokasi_tujuan')
        )
        ->groupBy('no_transaksi', 'tanggal')
        ->orderByDesc('tanggal')
        ->paginate(10);

    // Ambil detail produk per transfer
foreach ($transfers as $transfer) {
   $details = DB::table('t_kartupersproduk')
    ->join('t_produk', 't_kartupersproduk.kode_produk', '=', 't_produk.kode_produk')
    ->where('no_transaksi', $transfer->no_transaksi)
    ->where('keterangan', 'like', 'Transfer ke%')
    ->select(
        't_produk.nama_produk',
        't_produk.satuan', // ambil satuan dari t_produk
        DB::raw('SUM(t_kartupersproduk.keluar) as jumlah')
    )
    ->groupBy('t_produk.nama_produk', 't_produk.satuan')
    ->get();
$transfer->details = $details;
}

    return view('transferproduk.index', compact('transfers'));
}

    // Tampilkan form create
    public function create()
    {
        $lokasiAsal = 'Gudang';
        $produk = DB::table('t_produk')
            ->leftJoin('t_kartupersproduk', function($q) use ($lokasiAsal) {
                $q->on('t_produk.kode_produk', '=', 't_kartupersproduk.kode_produk')
                  ->where('t_kartupersproduk.lokasi', '=', $lokasiAsal);
            })
            ->select(
                't_produk.kode_produk',
                't_produk.nama_produk',
                't_produk.satuan',
                DB::raw('COALESCE(SUM(t_kartupersproduk.masuk - t_kartupersproduk.keluar),0) as stok'),
                DB::raw('MAX(t_kartupersproduk.tanggal_exp) as tanggal_exp')
            )
            ->groupBy(
                't_produk.kode_produk',
                't_produk.nama_produk',
                't_produk.satuan'
            )
            ->get();

        $kode_otomatis = 'TRF-' . date('Ymd') . '-' . str_pad(rand(1,999), 3, '0', STR_PAD_LEFT);

        return view('transferproduk.create', compact('produk', 'kode_otomatis', 'lokasiAsal'));
    }

    // Simpan transfer produk dengan FIFO
    public function store(Request $request)
    {
        $request->validate([
            'no_transaksi' => 'required',
            'tanggal' => 'required|date',
            'lokasi_asal' => 'required',
            'lokasi_tujuan' => 'required|different:lokasi_asal',
            'produk_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->produk_id as $idx => $kode_produk) {
                $jumlah_pindah = $request->jumlah[$idx];

                // FIFO: Ambil batch stok di lokasi asal, urut tanggal_exp paling tua
                $batches = DB::table('t_kartupersproduk')
                    ->select(
                        'kode_produk',
                        'tanggal_exp',
                        'hpp',
                        'satuan',
                        DB::raw('SUM(masuk) as total_masuk'),
                        DB::raw('SUM(keluar) as total_keluar')
                    )
                    ->where('kode_produk', $kode_produk)
                    ->where('lokasi', $request->lokasi_asal)
                    ->groupBy('kode_produk', 'tanggal_exp', 'hpp', 'satuan')
                    ->havingRaw('SUM(masuk) - SUM(keluar) > 0')
                    ->orderBy('tanggal_exp', 'asc')
                    ->get();

                $sisa = $jumlah_pindah;
                foreach ($batches as $batch) {
                    $stok_batch = $batch->total_masuk - $batch->total_keluar;
                    if ($stok_batch <= 0) continue;

                    $ambil = min($sisa, $stok_batch);

                    // Keluarkan dari lokasi asal
                    DB::table('t_kartupersproduk')->insert([
                        'no_transaksi' => $request->no_transaksi,
                        'tanggal' => $request->tanggal,
                        'kode_produk' => $kode_produk,
                        'masuk' => 0,
                        'keluar' => $ambil,
                        'hpp' => $batch->hpp,
                        'satuan' => $batch->satuan,
                        'keterangan' => 'Transfer ke ' . $request->lokasi_tujuan,
                        'tanggal_exp' => $batch->tanggal_exp,
                        'lokasi' => $request->lokasi_asal,
                    ]);

                    // Masukkan ke lokasi tujuan
                    DB::table('t_kartupersproduk')->insert([
                        'no_transaksi' => $request->no_transaksi,
                        'tanggal' => $request->tanggal,
                        'kode_produk' => $kode_produk,
                        'masuk' => $ambil,
                        'keluar' => 0,
                        'hpp' => $batch->hpp,
                        'satuan' => $batch->satuan,
                        'keterangan' => 'Transfer dari ' . $request->lokasi_asal,
                        'tanggal_exp' => $batch->tanggal_exp,
                        'lokasi' => $request->lokasi_tujuan,
                    ]);

                    $sisa -= $ambil;
                    if ($sisa <= 0) break;
                }

                if ($sisa > 0) {
                    DB::rollBack();
                    return back()->withErrors(['Stok produk ' . $kode_produk . ' tidak cukup!']);
                }
            }

            DB::commit();
            return redirect()->route('kartustok.produk')->with('success', 'Transfer produk berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }


public function edit($no_transaksi)
{
    // Ambil header transfer
    $transfer = DB::table('t_kartupersproduk')
        ->where('no_transaksi', $no_transaksi)
        ->where('keterangan', 'like', 'Transfer ke%')
        ->select(
            'no_transaksi',
            'tanggal',
            'lokasi as lokasi_asal',
            DB::raw('REPLACE(keterangan, "Transfer ke ", "") as lokasi_tujuan')
        )
        ->first();

    // Ambil semua lokasi unik
    $lokasiList = DB::table('t_kartupersproduk')->distinct()->pluck('lokasi');

    // Ambil produk dan stok di lokasi asal
    $produk = DB::table('t_produk')
        ->leftJoin('t_kartupersproduk', function($q) use ($transfer) {
            $q->on('t_produk.kode_produk', '=', 't_kartupersproduk.kode_produk')
              ->where('t_kartupersproduk.lokasi', '=', $transfer->lokasi_asal);
        })
        ->select(
            't_produk.kode_produk',
            't_produk.nama_produk',
            't_produk.satuan',
            DB::raw('COALESCE(SUM(t_kartupersproduk.masuk - t_kartupersproduk.keluar),0) as stok'),
            DB::raw('MAX(t_kartupersproduk.tanggal_exp) as tanggal_exp')
        )
        ->groupBy(
            't_produk.kode_produk',
            't_produk.nama_produk',
            't_produk.satuan'
        )
        ->get();

    // Ambil detail transfer
    $details = DB::table('t_kartupersproduk')
        ->where('no_transaksi', $no_transaksi)
        ->where('keterangan', 'like', 'Transfer ke%')
        ->get();

        $groupedDetails = collect($details)
    ->groupBy('kode_produk')
    ->map(function($items) {
        return (object)[
            'kode_produk' => $items[0]->kode_produk,
            'keluar' => $items->sum('keluar'),
            'satuan' => $items[0]->satuan,
            'tanggal_exp' => $items->max('tanggal_exp'),
        ];
    })
    ->values();
    return view('transferproduk.edit', compact('transfer', 'groupedDetails', 'produk', 'lokasiList'));}
    public function update(Request $request, $no_transaksi)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'lokasi_asal' => 'required',
            'lokasi_tujuan' => 'required|different:lokasi_asal',
            'produk_id' => 'required|array',
            'jumlah' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            // Hapus data lama
            DB::table('t_kartupersproduk')->where('no_transaksi', $no_transaksi)->delete();

            // Simpan data baru (mirip store)
            foreach ($request->produk_id as $idx => $kode_produk) {
                $jumlah_pindah = $request->jumlah[$idx];
                $satuan = $request->satuan[$idx] ?? '';
                $tanggal_exp = $request->tanggal_exp[$idx] ?? null;

                // FIFO: Ambil batch stok di lokasi asal, urut tanggal_exp paling tua
                $batches = DB::table('t_kartupersproduk')
                    ->select(
                        'kode_produk',
                        'tanggal_exp',
                        'hpp',
                        'satuan',
                        DB::raw('SUM(masuk) as total_masuk'),
                        DB::raw('SUM(keluar) as total_keluar')
                    )
                    ->where('kode_produk', $kode_produk)
                    ->where('lokasi', $request->lokasi_asal)
                    ->groupBy('kode_produk', 'tanggal_exp', 'hpp', 'satuan')
                    ->havingRaw('SUM(masuk) - SUM(keluar) > 0')
                    ->orderBy('tanggal_exp', 'asc')
                    ->get();

                $sisa = $jumlah_pindah;
                foreach ($batches as $batch) {
                    $stok_batch = $batch->total_masuk - $batch->total_keluar;
                    if ($stok_batch <= 0) continue;

                    $ambil = min($sisa, $stok_batch);

                    // Keluarkan dari lokasi asal
                    DB::table('t_kartupersproduk')->insert([
                        'no_transaksi' => $no_transaksi,
                        'tanggal' => $request->tanggal,
                        'kode_produk' => $kode_produk,
                        'masuk' => 0,
                        'keluar' => $ambil,
                        'hpp' => $batch->hpp,
                        'satuan' => $batch->satuan,
                        'keterangan' => 'Transfer ke ' . $request->lokasi_tujuan,
                        'tanggal_exp' => $batch->tanggal_exp,
                        'lokasi' => $request->lokasi_asal,
                    ]);

                    // Masukkan ke lokasi tujuan
                    DB::table('t_kartupersproduk')->insert([
                        'no_transaksi' => $no_transaksi,
                        'tanggal' => $request->tanggal,
                        'kode_produk' => $kode_produk,
                        'masuk' => $ambil,
                        'keluar' => 0,
                        'hpp' => $batch->hpp,
                        'satuan' => $batch->satuan,
                        'keterangan' => 'Transfer dari ' . $request->lokasi_asal,
                        'tanggal_exp' => $batch->tanggal_exp,
                        'lokasi' => $request->lokasi_tujuan,
                    ]);

                    $sisa -= $ambil;
                    if ($sisa <= 0) break;
                }

                if ($sisa > 0) {
                    DB::rollBack();
                    return back()->withErrors(['Stok produk ' . $kode_produk . ' tidak cukup!']);
                }
            }

            DB::commit();
            return redirect()->route('transferproduk.index')->with('success', 'Transfer produk berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function destroy($no_transaksi)
    {
        DB::table('t_kartupersproduk')->where('no_transaksi', $no_transaksi)->delete();
        return redirect()->route('transferproduk.index')->with('success', 'Transfer produk berhasil dihapus!');
    }
}