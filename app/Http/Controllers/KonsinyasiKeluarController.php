<?php

namespace App\Http\Controllers;

use App\Models\KonsinyasiKeluar;
use App\Models\KonsinyasiKeluarDetail;
use App\Models\Consignee;
use App\Models\ProdukKonsinyasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class KonsinyasiKeluarController extends Controller
{

    /**
     * Cetak laporan konsinyasi keluar (keseluruhan)
     */
    public function cetakLaporan(Request $request)
    {
        $query = \App\Models\KonsinyasiKeluar::with(['consignee', 'details.produk']);
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_setor', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_setor', '<=', $request->tanggal_akhir);
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('no_konsinyasikeluar', 'like', "%$search%")
                  ->orWhereHas('consignee', function($qc) use ($search) {
                      $qc->where('nama_consignee', 'like', "%$search%" );
                  });
            });
        }
        $sort = $request->get('sort', 'asc');
        $query->orderBy('no_konsinyasikeluar', $sort);
        $konsinyasiKeluarList = $query->get();
        $tanggal_awal = $request->tanggal_awal;
        $tanggal_akhir = $request->tanggal_akhir;
        return view('konsinyasikeluar.cetak_laporan', compact('konsinyasiKeluarList', 'tanggal_awal', 'tanggal_akhir'));
    }
    public function index(Request $request)
    {
        $query = KonsinyasiKeluar::with('consignee');
        // Filter tanggal setor
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_setor', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_setor', '<=', $request->tanggal_akhir);
        }
        // Filter search: no konsinyasi keluar atau nama consignee
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('no_konsinyasikeluar', 'like', "%$search%")
                  ->orWhereHas('consignee', function($qc) use ($search) {
                      $qc->where('nama_consignee', 'like', "%$search%" );
                  });
            });
        }
        // Sorting
        $sort = $request->get('sort', 'asc');
        $query->orderBy('no_konsinyasikeluar', $sort);
        $konsinyasiKeluarList = $query->get();
        // Untuk dropdown filter
        $allNoKonsinyasi = KonsinyasiKeluar::orderBy('no_konsinyasikeluar')->pluck('no_konsinyasikeluar');
        return view('konsinyasikeluar.index', compact('konsinyasiKeluarList', 'allNoKonsinyasi'));
    }

    public function create()
    {
        $consignees = \App\Models\Consignee::all();
        // Ambil produk dari t_produk, sertakan satuan
        $produkList = DB::table('t_produk')->select('kode_produk', 'nama_produk', 'satuan')->get();

        // Penomoran otomatis no_konsinyasikeluar
        $last = DB::table('t_konsinyasikeluar')->orderBy('no_konsinyasikeluar', 'desc')->first();
        if ($last) {
            $lastNum = intval(substr($last->no_konsinyasikeluar, 2));
            $kodeOtomatis = 'KK' . str_pad($lastNum + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $kodeOtomatis = 'KK000001';
        }
        // Penomoran otomatis no_suratpengiriman: ambil 3 digit awal dari no_suratpengiriman terakhir, increment
        $lastSurat = DB::table('t_konsinyasikeluar')
            ->whereNotNull('no_suratpengiriman')
            ->where('no_suratpengiriman', '!=', '')
            ->orderBy('no_suratpengiriman', 'desc')
            ->first();
        if ($lastSurat && preg_match('/^(\d{3})\//', $lastSurat->no_suratpengiriman, $m)) {
            $lastSuratNum = intval($m[1]);
            $noSuratOtomatis = str_pad($lastSuratNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $noSuratOtomatis = '001';
        }
        // Format default: 001/KONS-KELUAR/WBP-SMG/VII/2025 (bulan dan tahun diisi via JS di form)
        $noSuratOtomatis .= '/KONS-KELUAR/WBP-SMG/';

        return view('konsinyasikeluar.create', compact('consignees', 'produkList', 'kodeOtomatis', 'noSuratOtomatis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_setor' => 'required|unique:t_konsinyasikeluar,no_konsinyasikeluar',
            'tanggal_setor' => 'required|date',
            'kode_consignee' => 'required',
            'produk.*.kode_produk' => 'required',
            'produk.*.jumlah_setor' => 'required|numeric|min:1',
            'produk.*.satuan' => 'required',
            'produk.*.harga_setor' => 'required|numeric|min:0',
        ]);
        DB::beginTransaction();
        try {
            // Ambil data produk dari detail_json jika field produk tidak ada
            $produk = $request->produk;
            if ((!$produk || !is_array($produk) || count($produk) == 0) && $request->detail_json) {
                $produk = json_decode($request->detail_json, true);
            }
            if (!is_array($produk) || count($produk) == 0) {
                throw new \Exception('Data produk tidak valid atau kosong');
            }
            $total = 0;
            foreach ($produk as $prod) {
                $total += $prod['jumlah_setor'] * $prod['harga_setor'];
            }
            // Simpan header ke t_konsinyasikeluar
            DB::table('t_konsinyasikeluar')->insert([
                'no_konsinyasikeluar' => $request->kode_setor,
                'kode_consignee' => $request->kode_consignee,
                'tanggal_setor' => $request->tanggal_setor,
                'total_setor' => $total,
                'no_suratpengiriman' => $request->no_suratpengiriman,
                'keterangan' => $request->keterangan,
            ]);
            // Simpan detail ke t_konsinyasikeluar_detail
            foreach ($produk as $i => $prod) {
                $no_detail = $request->kode_setor . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
                DB::table('t_konsinyasikeluar_detail')->insert([
                    'no_detailkonsinyasikeluar' => $no_detail,
                    'no_konsinyasikeluar' => $request->kode_setor,
                    'kode_produk' => $prod['kode_produk'],
                    'jumlah_setor' => $prod['jumlah_setor'],
                    'satuan' => $prod['satuan'],
                    'harga_setor' => $prod['harga_setor'],
                    'subtotal' => $prod['jumlah_setor'] * $prod['harga_setor']
                ]);
            }
            DB::commit();
            return redirect()->route('konsinyasikeluar.index')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Gagal simpan: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $header = KonsinyasiKeluar::with(['consignee', 'details.produk'])->where('no_konsinyasikeluar', $id)->firstOrFail();
        return view('konsinyasikeluar.detail', compact('header'));
    }

    public function edit($id)
    {
        $header = KonsinyasiKeluar::with(['details'])->where('no_konsinyasikeluar', $id)->firstOrFail();
        $consignees = Consignee::all();
        $produkList = \DB::table('t_produk')->get();
        return view('konsinyasikeluar.edit', compact('header', 'consignees', 'produkList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'no_konsinyasikeluar' => 'required',
            'tanggal_setor' => 'required|date',
            'kode_consignee' => 'required',
            'keterangan' => 'nullable|string',
            'no_suratpengiriman' => 'nullable|string',
            // validasi produk bisa kosong, karena bisa dari detail_json
        ]);
        DB::beginTransaction();
        try {
            // Ambil data produk dari detail_json jika field produk tidak ada
            $produk = $request->produk;
            if ((!$produk || !is_array($produk) || count($produk) == 0) && $request->detail_json) {
                $produk = json_decode($request->detail_json, true);
            }
            if (!is_array($produk) || count($produk) == 0) {
                throw new \Exception('Data produk tidak valid atau kosong');
            }
            $header = KonsinyasiKeluar::where('no_konsinyasikeluar', $id)->firstOrFail();
            $total = 0;
            foreach ($produk as $prod) {
                $total += $prod['jumlah_setor'] * $prod['harga_setor'];
            }
            $header->update([
                'no_konsinyasikeluar' => $request->no_konsinyasikeluar,
                'tanggal_setor' => $request->tanggal_setor,
                'kode_consignee' => $request->kode_consignee,
                'total_setor' => $total,
                'no_suratpengiriman' => $request->no_suratpengiriman,
                'keterangan' => $request->keterangan,
            ]);
            // Hapus detail lama
            \DB::table('t_konsinyasikeluar_detail')->where('no_konsinyasikeluar', $header->no_konsinyasikeluar)->delete();
            // Simpan detail baru
            foreach ($produk as $i => $prod) {
                $no_detail = $header->no_konsinyasikeluar . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
                \DB::table('t_konsinyasikeluar_detail')->insert([
                    'no_detailkonsinyasikeluar' => $no_detail,
                    'no_konsinyasikeluar' => $header->no_konsinyasikeluar,
                    'kode_produk' => $prod['kode_produk'],
                    'jumlah_setor' => $prod['jumlah_setor'],
                    'satuan' => $prod['satuan'],
                    'harga_setor' => $prod['harga_setor'],
                    'subtotal' => $prod['jumlah_setor'] * $prod['harga_setor']
                ]);
            }
            DB::commit();
            return redirect()->route('konsinyasikeluar.index')->with('success', 'Data berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Gagal update: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($no_konsinyasikeluar)
    {
        $header = KonsinyasiKeluar::where('no_konsinyasikeluar', $no_konsinyasikeluar)->firstOrFail();
        $header->details()->delete();
        $header->delete();
        return redirect()->route('konsinyasikeluar.index')->with('success', 'Data berhasil dihapus');
    }

    public function cetak($id)
    {
        $header = KonsinyasiKeluar::with(['consignee', 'details.produk'])->where('no_konsinyasikeluar', $id)->firstOrFail();
        return view('konsinyasikeluar.cetak', compact('header'));
    }
}
