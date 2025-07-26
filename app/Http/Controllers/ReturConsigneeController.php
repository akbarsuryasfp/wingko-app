<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ReturConsignee;
use App\Models\ReturConsigneeDetail;
use App\Models\KonsinyasiKeluar;
use App\Models\KonsinyasiKeluarDetail;
use App\Models\Consignee;
use App\Models\Produk;

class ReturConsigneeController extends Controller
{
    public function cetakLaporan(Request $request)
    {
        $query = \App\Models\ReturConsignee::with(['consignee', 'konsinyasikeluar', 'details.produk']);

        // Filter periode
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');
        if ($tanggal_awal && $tanggal_akhir) {
            $query->whereBetween('tanggal_returconsignee', [$tanggal_awal, $tanggal_akhir]);
        } elseif ($tanggal_awal) {
            $query->where('tanggal_returconsignee', '>=', $tanggal_awal);
        } elseif ($tanggal_akhir) {
            $query->where('tanggal_returconsignee', '<=', $tanggal_akhir);
        }

        // Filter search
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('no_returconsignee', 'like', "%$search%")
                  ->orWhereHas('consignee', function($qc) use ($search) {
                      $qc->where('nama_consignee', 'like', "%$search%");
                  });
            });
        }

        // Sorting
        $sort = $request->input('sort', 'asc');
        $query->orderBy('no_returconsignee', $sort === 'desc' ? 'desc' : 'asc');

        $returconsignees = $query->get();
        return view('returconsignee.cetak_laporan', compact('returconsignees'));
    }
    // Form khusus create retur dari penerimaan konsinyasi
public function createReturTerima(Request $request)
{
    $noSudahRetur = ReturConsignee::pluck('no_konsinyasikeluar')->toArray();
    $konsinyasikeluar = KonsinyasiKeluar::with('consignee')
        ->whereNotIn('no_konsinyasikeluar', $noSudahRetur)
        ->get();
    $last = ReturConsignee::orderBy('no_returconsignee', 'desc')->first();
    $newNumber = $last ? intval(substr($last->no_returconsignee, 2)) + 1 : 1;
    $no_returconsignee = 'RC' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

    $urlNoKonsinyasi = $request->input('no_konsinyasikeluar');
    $urlKodeConsignee = $request->input('kode_consignee');
    $prefillRetur = $request->input('prefill_retur');

    $produk_konsinyasi = [];

if ($urlNoKonsinyasi && $prefillRetur) {
    // Cari no_penerimaankonsinyasi yang sesuai
    $no_penerimaankonsinyasi = DB::table('t_penerimaankonsinyasi')
        ->where('no_konsinyasikeluar', $urlNoKonsinyasi)
        ->value('no_penerimaankonsinyasi');

    if ($no_penerimaankonsinyasi) {
        $produk_konsinyasi = DB::table('t_penerimaankonsinyasi_detail as pd')
            ->join('t_produk as p', 'pd.kode_produk', '=', 'p.kode_produk')
            ->select(
                'pd.kode_produk',
                'p.nama_produk',
                'p.satuan',
                'pd.jumlah_setor',
                'pd.jumlah_terjual',
                'pd.harga_satuan as harga_setor'
            )
            ->where('pd.no_penerimaankonsinyasi', $no_penerimaankonsinyasi)
            ->get();
    } else {
        $produk_konsinyasi = collect(); // kosongkan jika tidak ada
    }
}


    return view('returconsignee.create_returterima', compact(
        'no_returconsignee',
        'konsinyasikeluar',
        'produk_konsinyasi',
        'urlNoKonsinyasi',
        'urlKodeConsignee',
        'prefillRetur'
    ));
}
    public function index(Request $request)
    {
        $query = ReturConsignee::with(['consignee', 'konsinyasikeluar', 'details.produk']);

        // Filter periode
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');
        if ($tanggal_awal && $tanggal_akhir) {
            $query->whereBetween('tanggal_returconsignee', [$tanggal_awal, $tanggal_akhir]);
        } elseif ($tanggal_awal) {
            $query->where('tanggal_returconsignee', '>=', $tanggal_awal);
        } elseif ($tanggal_akhir) {
            $query->where('tanggal_returconsignee', '<=', $tanggal_akhir);
        }

        // Filter search
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('no_returconsignee', 'like', "%$search%")
                  ->orWhereHas('consignee', function($qc) use ($search) {
                      $qc->where('nama_consignee', 'like', "%$search%");
                  });
            });
        }

        // Sorting
        $sort = $request->input('sort', 'asc');
        $query->orderBy('no_returconsignee', $sort === 'desc' ? 'desc' : 'asc');

        $returconsignees = $query->get();
        return view('returconsignee.index', compact('returconsignees'));
    }
    // Endpoint AJAX: cek asal produk dan jumlah_terjual dari penerimaankonsinyasi_detail
    public function cekAsalProduk(Request $request)
{
    $no_konsinyasikeluar = $request->no_konsinyasikeluar;
    $kode_produk = $request->kode_produk;
    
    // Cek apakah produk berasal dari penerimaankonsinyasi_detail
    $asal = DB::table('t_konsinyasikeluar_detail')
        ->where('no_konsinyasikeluar', $no_konsinyasikeluar)
        ->where('kode_produk', $kode_produk)
        ->first();
    
    $berasal_penerimaan = false;
    $jumlah_terjual = 0;
    
    if ($asal && $asal->sumber_data === 'penerimaan') {
        $berasal_penerimaan = true;
        $no_detail_penerimaan = $asal->kode_sumber;
        
        // Ambil jumlah_terjual dari t_penerimaankonsinyasi_detail
        $penerimaan = DB::table('t_penerimaankonsinyasi_detail')
            ->where('no_detail_penerimaan', $no_detail_penerimaan)
            ->where('kode_produk', $kode_produk)
            ->first();
            
        if ($penerimaan) {
            $jumlah_terjual = $penerimaan->jumlah_terjual ?? 0;
        }
    }
    
    return response()->json([
        'berasal_penerimaan' => $berasal_penerimaan,
        'jumlah_terjual' => $jumlah_terjual
    ]);
}

    public function create()
    {
        // Ambil no_konsinyasikeluar yang sudah ada di t_penerimaankonsinyasi
        $sudahAda = DB::table('t_penerimaankonsinyasi')->pluck('no_konsinyasikeluar')->toArray();

        // Ambil konsinyasi keluar yang belum ada di t_penerimaankonsinyasi
        $konsinyasikeluar = KonsinyasiKeluar::with('consignee')
            ->whereNotIn('no_konsinyasikeluar', $sudahAda)
            ->get();

        $last = ReturConsignee::orderBy('no_returconsignee', 'desc')->first();
        $newNumber = $last ? intval(substr($last->no_returconsignee, 2)) + 1 : 1;
        $no_returconsignee = 'RC' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        return view('returconsignee.create', compact('no_returconsignee', 'konsinyasikeluar'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'no_returconsignee' => 'required|unique:t_returconsignee,no_returconsignee',
            'no_konsinyasikeluar' => 'required',
            'tanggal_returconsignee' => 'required|date',
            'kode_consignee' => 'required',
            'total_nilai_retur' => 'required|numeric',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json',
        ]);

        $details = json_decode($request->detail_json, true);
        // Ambil data konsinyasi keluar detail
        $konsinyasiDetail = KonsinyasiKeluarDetail::where('no_konsinyasikeluar', $request->no_konsinyasikeluar)->get()->keyBy('kode_produk');
        foreach ($details as $detail) {
            $max = $konsinyasiDetail[$detail['kode_produk']]->jumlah_setor ?? 0;
            if ($detail['jumlah_retur'] > $max) {
                return back()->withErrors(['Jumlah retur untuk produk ' . $detail['kode_produk'] . ' melebihi jumlah setor (' . $max . ')'])->withInput();
            }
        }

        DB::transaction(function () use ($request, $details) {
            $retur = ReturConsignee::create([
                'no_returconsignee' => $request->no_returconsignee,
                'no_konsinyasikeluar' => $request->no_konsinyasikeluar,
                'tanggal_returconsignee' => $request->tanggal_returconsignee,
                'kode_consignee' => $request->kode_consignee,
                'total_nilai_retur' => $request->total_nilai_retur,
                'keterangan' => $request->keterangan,
            ]);
            foreach ($details as $i => $detail) {
                ReturConsigneeDetail::create([
                    'no_detailreturconsignee' => $request->no_returconsignee . '-' . ($i+1),
                    'no_returconsignee' => $request->no_returconsignee,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah_retur' => $detail['jumlah_retur'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'alasan' => $detail['alasan'] ?? '',
                    'subtotal' => $detail['subtotal'] ?? ($detail['jumlah_retur'] * $detail['harga_satuan']),
                ]);
            }
        });
        return redirect()->route('returconsignee.index')->with('success', 'Retur Consignee berhasil disimpan!');
    }

    public function edit($no_returconsignee)
    {
        $returconsignee = ReturConsignee::with(['details', 'consignee', 'konsinyasikeluar'])->where('no_returconsignee', $no_returconsignee)->firstOrFail();
        $konsinyasikeluar = \App\Models\KonsinyasiKeluar::with('consignee')->get();
        // Data detail untuk JS
        $detailsArray = $returconsignee->details->map(function($d) {
            // Ambil satuan dari relasi produk jika belum ada
            $satuan = $d->satuan;
            if (!$satuan && $d->produk) {
                $satuan = $d->produk->satuan;
            }
            return [
                'kode_produk' => $d->kode_produk,
                'nama_produk' => $d->nama_produk ?? ($d->produk->nama_produk ?? ''),
                'satuan' => $satuan,
                'jumlah_retur' => $d->jumlah_retur,
                'harga_satuan' => $d->harga_satuan,
                'alasan' => $d->alasan,
                'subtotal' => $d->subtotal
            ];
        })->values()->toArray();
        return view('returconsignee.edit', compact('returconsignee', 'konsinyasikeluar', 'detailsArray'));
    }

    public function update(Request $request, $no_returconsignee)
    {
        $request->validate([
            'no_konsinyasikeluar' => 'required',
            'tanggal_returconsignee' => 'required|date',
            'kode_consignee' => 'required',
            'total_nilai_retur' => 'required|numeric',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json',
        ]);
        $details = json_decode($request->detail_json, true);
        $konsinyasiDetail = KonsinyasiKeluarDetail::where('no_konsinyasikeluar', $request->no_konsinyasikeluar)->get()->keyBy('kode_produk');
        foreach ($details as $detail) {
            $max = $konsinyasiDetail[$detail['kode_produk']]->jumlah_setor ?? 0;
            if ($detail['jumlah_retur'] > $max) {
                return back()->withErrors(['Jumlah retur untuk produk ' . $detail['kode_produk'] . ' melebihi jumlah setor (' . $max . ')'])->withInput();
            }
        }
        DB::transaction(function () use ($request, $no_returconsignee, $details) {
            ReturConsignee::where('no_returconsignee', $no_returconsignee)->update([
                'no_konsinyasikeluar' => $request->no_konsinyasikeluar,
                'tanggal_returconsignee' => $request->tanggal_returconsignee,
                'kode_consignee' => $request->kode_consignee,
                'total_nilai_retur' => $request->total_nilai_retur,
                'keterangan' => $request->keterangan,
            ]);
            ReturConsigneeDetail::where('no_returconsignee', $no_returconsignee)->delete();
            foreach ($details as $i => $detail) {
                ReturConsigneeDetail::create([
                    'no_detailreturconsignee' => $no_returconsignee . '-' . ($i+1),
                    'no_returconsignee' => $no_returconsignee,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah_retur' => $detail['jumlah_retur'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'alasan' => $detail['alasan'] ?? '',
                    'subtotal' => $detail['subtotal'] ?? ($detail['jumlah_retur'] * $detail['harga_satuan']),
                ]);
            }
        });
        return redirect()->route('returconsignee.index')->with('success', 'Retur Consignee berhasil diupdate!');
    }

    public function destroy($no_returconsignee)
    {
        DB::transaction(function () use ($no_returconsignee) {
            ReturConsigneeDetail::where('no_returconsignee', $no_returconsignee)->delete();
            ReturConsignee::where('no_returconsignee', $no_returconsignee)->delete();
        });
        return redirect()->route('returconsignee.index')->with('success', 'Retur Consignee berhasil dihapus!');
    }

    public function show($no_returconsignee)
    {
        $returconsignee = ReturConsignee::with(['consignee', 'konsinyasikeluar'])->where('no_returconsignee', $no_returconsignee)->firstOrFail();
        $details = ReturConsigneeDetail::where('no_returconsignee', $no_returconsignee)
            ->join('t_produk', 't_returconsignee_detail.kode_produk', '=', 't_produk.kode_produk')
            ->select('t_returconsignee_detail.*', 't_produk.nama_produk', 't_produk.satuan')
            ->get();
        return view('returconsignee.detail', compact('returconsignee', 'details'));
    }

    //public function cetak($no_returconsignee)
    //{
    //    $returconsignee = ReturConsignee::with('consignee')->where('no_returconsignee', $no_returconsignee)->firstOrFail();
    //    $details = ReturConsigneeDetail::where('no_returconsignee', $no_returconsignee)
    //        ->join('t_produk', 't_returconsignee_detail.kode_produk', '=', 't_produk.kode_produk')
    //        ->select('t_returconsignee_detail.*', 't_produk.nama_produk')
    //        ->get();
    //    return view('returconsignee.cetak', compact('returconsignee', 'details'));
    //}

    // Endpoint untuk AJAX produk konsinyasi keluar
    public function getProdukKonsinyasiKeluar(Request $request)
    {
        $no_konsinyasikeluar = $request->no_konsinyasikeluar;
        $produk = KonsinyasiKeluarDetail::where('no_konsinyasikeluar', $no_konsinyasikeluar)
            ->join('t_produk', 't_konsinyasikeluar_detail.kode_produk', '=', 't_produk.kode_produk')
            ->select(array(
                't_konsinyasikeluar_detail.kode_produk as kode_produk',
                't_produk.nama_produk as nama_produk',
                't_produk.satuan as satuan',
                't_konsinyasikeluar_detail.jumlah_setor as jumlah_setor',
                't_konsinyasikeluar_detail.harga_setor as harga_setor'
            ))
            ->get();
        // pastikan hasilnya array biasa
        return response()->json(['produk' => $produk->map(function($item){
            return [
                'kode_produk' => $item->kode_produk,
                'nama_produk' => $item->nama_produk,
                'satuan' => $item->satuan,
                'jumlah_setor' => $item->jumlah_setor,
                'harga_setor' => $item->harga_setor
            ];
        })]);
    }
}
// Hapus tag PHP penutup di akhir file
