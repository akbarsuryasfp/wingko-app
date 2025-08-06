<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ReturConsignor;
use App\Models\ReturConsignorDetail;
use App\Models\KonsinyasiMasuk;
use App\Models\KonsinyasiMasukDetail;
use App\Models\Consignor;
use App\Models\Produk;

class ReturConsignorController extends Controller
{
    /**
     * Cetak nota retur consignor (pemilik barang) per transaksi
     */
    public function cetak($no_returconsignor)
    {
        $returconsignor = \App\Models\ReturConsignor::with(['consignor', 'konsinyasimasuk'])->where('no_returconsignor', $no_returconsignor)->firstOrFail();
        $details = \App\Models\ReturConsignorDetail::where('no_returconsignor', $no_returconsignor)
            ->join('t_produk_konsinyasi', 't_returconsignor_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->select('t_returconsignor_detail.*', 't_produk_konsinyasi.nama_produk', 't_produk_konsinyasi.satuan')
            ->get();
        return view('returconsignor.cetak', compact('returconsignor', 'details'));
    }
    /**
     * Cetak laporan retur consignor (keseluruhan)
     */
    public function cetakLaporan(Request $request)
    {
        $sort = $request->get('sort', 'asc');
        $query = ReturConsignor::with(['details.produk', 'consignor', 'konsinyasimasuk']);
        $tanggal_awal = $request->tanggal_awal;
        $tanggal_akhir = $request->tanggal_akhir;
        if ($tanggal_awal) {
            $query->whereDate('tanggal_returconsignor', '>=', $tanggal_awal);
        }
        if ($tanggal_akhir) {
            $query->whereDate('tanggal_returconsignor', '<=', $tanggal_akhir);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_returconsignor', 'like', "%$search%")
                  ->orWhereHas('consignor', function($qc) use ($search) {
                      $qc->where('nama_consignor', 'like', "%$search%");
                  });
            });
        }
        $returconsignor = $query->orderBy('no_returconsignor', $sort)->get();
        // Pastikan setiap detail ada field satuan (ambil dari relasi produk jika ada)
        foreach ($returconsignor as $rc) {
            if ($rc->details) {
                foreach ($rc->details as $detail) {
                    if (isset($detail->produk) && isset($detail->produk->satuan)) {
                        $detail->satuan = $detail->produk->satuan;
                    } else {
                        $detail->satuan = DB::table('t_produk_konsinyasi')->where('kode_produk', $detail->kode_produk)->value('satuan') ?? '-';
                    }
                }
            }
        }
        return view('returconsignor.cetak_laporan', compact('returconsignor', 'tanggal_awal', 'tanggal_akhir'));
    }
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'asc');
        $query = ReturConsignor::with(['details.produk', 'consignor', 'konsinyasimasuk']);
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_returconsignor', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_returconsignor', '<=', $request->tanggal_akhir);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_returconsignor', 'like', "%$search%")
                  ->orWhereHas('consignor', function($qc) use ($search) {
                      $qc->where('nama_consignor', 'like', "%$search%");
                  });
            });
        }
        $returconsignor = $query->orderBy('no_returconsignor', $sort)->get();
        return view('returconsignor.index', compact('returconsignor'));
    }

    public function create()
    {
        $noSudahRetur = ReturConsignor::pluck('no_konsinyasimasuk')->toArray();
        $konsinyasimasuk = KonsinyasiMasuk::with('consignor')
            ->whereNotIn('no_konsinyasimasuk', $noSudahRetur)
            ->get();
        $last = ReturConsignor::orderBy('no_returconsignor', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->no_returconsignor, 2));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $no_returconsignor = 'RN' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        // Produk konsinyasi default: ambil dari konsinyasi masuk pertama jika ada
        $produk_konsinyasi = [];
        $default_no_konsinyasimasuk = null;
        if ($konsinyasimasuk->count() > 0) {
            $default_no_konsinyasimasuk = $konsinyasimasuk[0]->no_konsinyasimasuk;
            $produk_konsinyasi = \App\Models\KonsinyasiMasukDetail::where('no_konsinyasimasuk', $default_no_konsinyasimasuk)
                ->join('t_produk', 't_konsinyasimasuk_detail.kode_produk', '=', 't_produk.kode_produk')
                ->select('t_konsinyasimasuk_detail.*', 't_produk.nama_produk')
                ->get();
        }
        return view('returconsignor.create', compact('no_returconsignor', 'konsinyasimasuk', 'produk_konsinyasi', 'default_no_konsinyasimasuk'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_returconsignor' => 'required|unique:t_returconsignor,no_returconsignor',
            'no_konsinyasimasuk' => 'required',
            'tanggal_returconsignor' => 'required|date',
            'kode_consignor' => 'required',
            'total_nilai_retur' => 'required|numeric',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json',
        ]);

        $details = json_decode($request->detail_json, true);
        $konsinyasiDetail = KonsinyasiMasukDetail::where('no_konsinyasimasuk', $request->no_konsinyasimasuk)->get()->keyBy('kode_produk');
        foreach ($details as $detail) {
            $jumlah_stok = $konsinyasiDetail[$detail['kode_produk']]->jumlah_stok ?? 0;
            $keluar = \DB::table('t_penjualan_detail')
                ->where('kode_produk', $detail['kode_produk'])
                ->where('no_batch', $request->no_konsinyasimasuk)
                ->sum('jumlah');
            $retur_sebelumnya = \DB::table('t_returconsignor_detail')
                ->join('t_returconsignor', 't_returconsignor_detail.no_returconsignor', '=', 't_returconsignor.no_returconsignor')
                ->where('t_returconsignor.no_konsinyasimasuk', $request->no_konsinyasimasuk)
                ->where('t_returconsignor_detail.kode_produk', $detail['kode_produk'])
                ->sum('t_returconsignor_detail.jumlah_retur');
            $maks_retur = max(0, ($jumlah_stok - $keluar - $retur_sebelumnya));
            if ($detail['jumlah_retur'] > $maks_retur) {
                return back()->withErrors(['Jumlah retur untuk produk ' . $detail['kode_produk'] . ' melebihi batas retur (' . $maks_retur . ')'])->withInput();
            }
        }

        DB::transaction(function () use ($request, $details) {
            ReturConsignor::create([
                'no_returconsignor' => $request->no_returconsignor,
                'no_konsinyasimasuk' => $request->no_konsinyasimasuk,
                'tanggal_returconsignor' => $request->tanggal_returconsignor,
                'kode_consignor' => $request->kode_consignor,
                'total_nilai_retur' => $request->total_nilai_retur,
                'keterangan' => $request->keterangan,
            ]);
            foreach ($details as $i => $detail) {
                // Generate kode unik untuk no_detailreturconsignor
                $lastDetail = \DB::table('t_returconsignor_detail')->orderByDesc('no_detailreturconsignor')->first();
                if ($lastDetail && isset($lastDetail->no_detailreturconsignor)) {
                    // Jika format RNDT000001 dst
                    if (preg_match('/^RNDT(\d{6})$/', $lastDetail->no_detailreturconsignor, $m)) {
                        $newNumber = intval($m[1]) + 1;
                    } else {
                        $newNumber = 1;
                    }
                } else {
                    $newNumber = 1;
                }
                $no_detailreturconsignor = 'RNDT' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

                ReturConsignorDetail::create([
                    'no_detailreturconsignor' => $no_detailreturconsignor,
                    'no_returconsignor' => $request->no_returconsignor,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah_retur' => $detail['jumlah_retur'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'alasan' => $detail['alasan'],
                    'subtotal' => $detail['subtotal'],
                ]);
                // Catat ke kartu persediaan konsinyasi
                $stok_terakhir = \DB::table('t_kartuperskonsinyasi')
                    ->where('kode_produk', $detail['kode_produk'])
                    ->orderByDesc('id')
                    ->value('sisa');
                $sisa_baru = max(0, ($stok_terakhir ?? 0) - $detail['jumlah_retur']);
                \DB::table('t_kartuperskonsinyasi')->insert([
                    'no_transaksi' => $request->no_returconsignor,
                    'kode_produk' => $detail['kode_produk'],
                    'tanggal' => $request->tanggal_returconsignor,
                    'masuk' => 0,
                    'keluar' => $detail['jumlah_retur'],
                    'sisa' => $sisa_baru,
                    'harga_konsinyasi' => $detail['harga_satuan'],
                    'lokasi' => 'Gudang',
                    'keterangan' => 'Retur Consignor',
                ]);
            }
        });
        return redirect()->route('returconsignor.index')->with('success', 'Retur Consignor berhasil disimpan!');
    }

    public function edit($no_returconsignor)
    {
        $returconsignor = ReturConsignor::with(['details', 'consignor', 'konsinyasimasuk'])->where('no_returconsignor', $no_returconsignor)->firstOrFail();
        $konsinyasimasuk = \App\Models\KonsinyasiMasuk::with('consignor')->get();
        $detailsArray = $returconsignor->details->map(function($d) {
            return [
                'kode_produk' => $d->kode_produk,
                'nama_produk' => $d->produk->nama_produk ?? '',
                'satuan' => $d->produk->satuan ?? '-',
                'jumlah_retur' => $d->jumlah_retur,
                'harga_satuan' => $d->harga_satuan,
                'alasan' => $d->alasan,
                'subtotal' => $d->subtotal,
            ];
        })->values()->toArray();
        return view('returconsignor.edit', compact('returconsignor', 'konsinyasimasuk', 'detailsArray'));
    }

    public function update(Request $request, $no_returconsignor)
    {
        $request->validate([
            'no_konsinyasimasuk' => 'required',
            'tanggal_returconsignor' => 'required|date',
            'kode_consignor' => 'required',
            'total_nilai_retur' => 'required|numeric',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json',
        ]);
        $details = json_decode($request->detail_json, true);
        $konsinyasiDetail = KonsinyasiMasukDetail::where('no_konsinyasimasuk', $request->no_konsinyasimasuk)->get()->keyBy('kode_produk');
        foreach ($details as $detail) {
            $jumlah_stok = $konsinyasiDetail[$detail['kode_produk']]->jumlah_stok ?? 0;
            $keluar = \DB::table('t_penjualan_detail')
                ->where('kode_produk', $detail['kode_produk'])
                ->where('no_batch', $request->no_konsinyasimasuk)
                ->sum('jumlah');
            $retur_sebelumnya = \DB::table('t_returconsignor_detail')
                ->join('t_returconsignor', 't_returconsignor_detail.no_returconsignor', '=', 't_returconsignor.no_returconsignor')
                ->where('t_returconsignor.no_konsinyasimasuk', $request->no_konsinyasimasuk)
                ->where('t_returconsignor_detail.kode_produk', $detail['kode_produk'])
                ->where('t_returconsignor_detail.no_returconsignor', '!=', $no_returconsignor)
                ->sum('t_returconsignor_detail.jumlah_retur');
            $maks_retur = max(0, ($jumlah_stok - $keluar - $retur_sebelumnya));
            if ($detail['jumlah_retur'] > $maks_retur) {
                return back()->withErrors(['Jumlah retur untuk produk ' . $detail['kode_produk'] . ' melebihi batas retur (' . $maks_retur . ')'])->withInput();
            }
        }
        DB::transaction(function () use ($request, $no_returconsignor, $details) {
            ReturConsignor::where('no_returconsignor', $no_returconsignor)->update([
                'no_konsinyasimasuk' => $request->no_konsinyasimasuk,
                'tanggal_returconsignor' => $request->tanggal_returconsignor,
                'kode_consignor' => $request->kode_consignor,
                'total_nilai_retur' => $request->total_nilai_retur,
                'keterangan' => $request->keterangan,
            ]);
            ReturConsignorDetail::where('no_returconsignor', $no_returconsignor)->delete();
            foreach ($details as $i => $detail) {
                // Generate kode unik untuk no_detailreturconsignor
                $lastDetail = \DB::table('t_returconsignor_detail')->orderByDesc('no_detailreturconsignor')->first();
                if ($lastDetail && isset($lastDetail->no_detailreturconsignor)) {
                    if (preg_match('/^RNDT(\d{6})$/', $lastDetail->no_detailreturconsignor, $m)) {
                        $newNumber = intval($m[1]) + 1;
                    } else {
                        $newNumber = 1;
                    }
                } else {
                    $newNumber = 1;
                }
                $no_detailreturconsignor = 'RNDT' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

                ReturConsignorDetail::create([
                    'no_detailreturconsignor' => $no_detailreturconsignor,
                    'no_returconsignor' => $no_returconsignor,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah_retur' => $detail['jumlah_retur'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'alasan' => $detail['alasan'],
                    'subtotal' => $detail['subtotal'],
                ]);
                // Catat ke kartu persediaan konsinyasi
                $stok_terakhir = \DB::table('t_kartuperskonsinyasi')
                    ->where('kode_produk', $detail['kode_produk'])
                    ->orderByDesc('id')
                    ->value('sisa');
                $sisa_baru = max(0, ($stok_terakhir ?? 0) - $detail['jumlah_retur']);
                \DB::table('t_kartuperskonsinyasi')->insert([
                    'no_transaksi' => $no_returconsignor,
                    'kode_produk' => $detail['kode_produk'],
                    'tanggal' => $request->tanggal_returconsignor,
                    'masuk' => 0,
                    'keluar' => $detail['jumlah_retur'],
                    'sisa' => $sisa_baru,
                    'harga_konsinyasi' => $detail['harga_satuan'],
                    'lokasi' => 'Gudang',
                    'keterangan' => 'Retur Consignor',
                ]);
            }
        });
        return redirect()->route('returconsignor.index')->with('success', 'Retur Consignor berhasil diupdate!');
    }

    public function destroy($no_returconsignor)
    {
        DB::transaction(function () use ($no_returconsignor) {
            ReturConsignorDetail::where('no_returconsignor', $no_returconsignor)->delete();
            ReturConsignor::where('no_returconsignor', $no_returconsignor)->delete();
        });
        return redirect()->route('returconsignor.index')->with('success', 'Retur Consignor berhasil dihapus!');
    }

    public function show($no_returconsignor)
    {
        $returconsignor = ReturConsignor::with(['consignor', 'konsinyasimasuk'])->where('no_returconsignor', $no_returconsignor)->firstOrFail();
        $details = ReturConsignorDetail::where('no_returconsignor', $no_returconsignor)
            ->join('t_produk_konsinyasi', 't_returconsignor_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->select('t_returconsignor_detail.*', 't_produk_konsinyasi.nama_produk', 't_produk_konsinyasi.satuan')
            ->get();
        return view('returconsignor.detail', compact('returconsignor', 'details'));
    }

    // Endpoint untuk AJAX produk konsinyasi masuk
    public function getProdukKonsinyasiMasuk(Request $request)
    {
        $no_konsinyasimasuk = $request->no_konsinyasimasuk;
        $produk = KonsinyasiMasukDetail::where('no_konsinyasimasuk', $no_konsinyasimasuk)
            ->join('t_produk_konsinyasi', 't_konsinyasimasuk_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->select([
                't_konsinyasimasuk_detail.kode_produk',
                't_produk_konsinyasi.nama_produk',
                't_produk_konsinyasi.satuan',
                't_konsinyasimasuk_detail.jumlah_stok',
                't_konsinyasimasuk_detail.harga_titip',
                't_konsinyasimasuk_detail.subtotal',
            ])
            ->get();

        // Hitung maks retur per produk berdasarkan penjualan detail dengan no_batch = no_konsinyasimasuk
        $produk = $produk->map(function($item) use ($no_konsinyasimasuk) {
            $keluar = \DB::table('t_penjualan_detail')
                ->where('kode_produk', $item->kode_produk)
                ->where('no_batch', $no_konsinyasimasuk)
                ->sum('jumlah');
            // Hitung retur sebelumnya (selain retur yang sedang dibuat)
            $retur_sebelumnya = \DB::table('t_returconsignor_detail')
                ->join('t_returconsignor', 't_returconsignor_detail.no_returconsignor', '=', 't_returconsignor.no_returconsignor')
                ->where('t_returconsignor.no_konsinyasimasuk', $no_konsinyasimasuk)
                ->where('t_returconsignor_detail.kode_produk', $item->kode_produk)
                ->sum('t_returconsignor_detail.jumlah_retur');
            $item->maks_retur = max(0, ($item->jumlah_stok - $keluar - $retur_sebelumnya));
            return $item;
        });
        return response()->json(['produk' => $produk]);
    }
}
