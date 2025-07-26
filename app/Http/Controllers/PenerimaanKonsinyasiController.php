<?php
namespace App\Http\Controllers;

use App\Models\PenerimaanKonsinyasi;
use App\Models\PenerimaanKonsinyasiDetail;
use App\Models\Consignee;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanKonsinyasiController extends Controller
{
    public function cetakLaporan(Request $request)
    {
        $query = \App\Models\PenerimaanKonsinyasi::with(['consignee', 'details.produk']);

        // Filter periode
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_terima', [$request->tanggal_awal, $request->tanggal_akhir]);
        } elseif ($request->filled('tanggal_awal')) {
            $query->where('tanggal_terima', '>=', $request->tanggal_awal);
        } elseif ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_terima', '<=', $request->tanggal_akhir);
        }

        // Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_penerimaankonsinyasi', 'like', "%$search%")
                  ->orWhereHas('consignee', function($qc) use ($search) {
                      $qc->where('nama_consignee', 'like', "%$search%");
                  });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'asc');
        $query->orderBy('no_penerimaankonsinyasi', $sort === 'desc' ? 'desc' : 'asc');

        $penerimaanKonsinyasiList = $query->get();
        return view('penerimaankonsinyasi.cetak_laporan', compact('penerimaanKonsinyasiList'));
    }
    public function index(Request $request)
    {
        $query = PenerimaanKonsinyasi::with(['consignee', 'details.produk']);

        // Filter periode
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_terima', [$request->tanggal_awal, $request->tanggal_akhir]);
        } elseif ($request->filled('tanggal_awal')) {
            $query->where('tanggal_terima', '>=', $request->tanggal_awal);
        } elseif ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_terima', '<=', $request->tanggal_akhir);
        }

        // Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_penerimaankonsinyasi', 'like', "%$search%")
                  ->orWhereHas('consignee', function($qc) use ($search) {
                      $qc->where('nama_consignee', 'like', "%$search%");
                  });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'asc');
        $query->orderBy('no_penerimaankonsinyasi', $sort === 'desc' ? 'desc' : 'asc');

        $penerimaanKonsinyasiList = $query->get();
        return view('penerimaankonsinyasi.index', compact('penerimaanKonsinyasiList'));
    }

    public function create()
    {
        $consigneeList = Consignee::all();
        $produkList = Produk::all();
        $no_penerimaankonsinyasi = $this->generateNoPenerimaan();
        // Ambil semua konsinyasi keluar (beserta consignee)
        $konsinyasiKeluarList = \App\Models\KonsinyasiKeluar::with('consignee')->orderBy('tanggal_setor', 'desc')->get();
        // Ambil no_konsinyasikeluar yang sudah dipakai di penerimaan konsinyasi
        $sudahDipakaiKonsinyasiKeluar = \App\Models\PenerimaanKonsinyasi::pluck('no_konsinyasikeluar')->toArray();
        return view('penerimaankonsinyasi.create', compact('consigneeList', 'produkList', 'no_penerimaankonsinyasi', 'konsinyasiKeluarList', 'sudahDipakaiKonsinyasiKeluar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_terima' => 'required|date',
            'detail_json' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $header = PenerimaanKonsinyasi::create([
                'no_penerimaankonsinyasi' => $this->generateNoPenerimaan(),
                'no_konsinyasikeluar' => $request->no_konsinyasikeluar,
                'tanggal_terima' => $request->tanggal_terima,
                'kode_consignee' => $request->kode_consignee,
                'metode_pembayaran' => $request->metode_pembayaran,
                'total_terima' => $request->total_terima,
                'keterangan' => $request->keterangan,
            ]);
            $details = json_decode($request->detail_json, true);
            foreach ($details as $idx => $d) {
                $no_detail = $header->no_penerimaankonsinyasi . '-' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                PenerimaanKonsinyasiDetail::create([
                    'no_detailpenerimaankonsinyasi' => $no_detail,
                    'no_penerimaankonsinyasi' => $header->no_penerimaankonsinyasi,
                    'kode_produk' => $d['kode_produk'],
                    'jumlah_setor' => $d['jumlah_setor'],
                    'jumlah_terjual' => $d['jumlah_terjual'],
                    'satuan' => $d['satuan'],
                    'harga_satuan' => $d['harga_satuan'],
                    'subtotal' => $d['subtotal'],
                ]);
            }
            DB::commit();
            return redirect()->route('penerimaankonsinyasi.index')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Gagal simpan: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $header = PenerimaanKonsinyasi::with(['consignee', 'details.produk'])->findOrFail($id);
        return view('penerimaankonsinyasi.detail', compact('header'));
    }

    public function edit($id)
    {
        $header = PenerimaanKonsinyasi::with(['consignee', 'details.produk'])->findOrFail($id);
        $consigneeList = Consignee::all();
        $produkList = Produk::all();
        $detailList = $header->details;
        return view('penerimaankonsinyasi.edit', compact('header', 'consigneeList', 'produkList', 'detailList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal_terima' => 'required|date',
            'metode_pembayaran' => 'required',
            'keterangan' => 'nullable',
            'detail' => 'required|array',
        ]);
        DB::beginTransaction();
        try {
            $header = PenerimaanKonsinyasi::findOrFail($id);
            $header->update([
                'tanggal_terima' => $request->tanggal_terima,
                'metode_pembayaran' => $request->metode_pembayaran,
                'keterangan' => $request->keterangan,
                // total_terima tetap dari input (readonly di form)
                'total_terima' => $request->total_terima,
            ]);
            // Update detail: hanya update jumlah_terjual
            foreach ($request->detail as $d) {
                PenerimaanKonsinyasiDetail::where('no_detailpenerimaankonsinyasi', $d['no_detailpenerimaankonsinyasi'])
                    ->update(['jumlah_terjual' => $d['jumlah_terjual']]);
            }
            DB::commit();
            return redirect()->route('penerimaankonsinyasi.index')->with('success', 'Data berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Gagal update: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $header = PenerimaanKonsinyasi::findOrFail($id);
            PenerimaanKonsinyasiDetail::where('no_penerimaankonsinyasi', $header->no_penerimaankonsinyasi)->delete();
            $header->delete();
            DB::commit();
            return redirect()->route('penerimaankonsinyasi.index')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Gagal hapus: ' . $e->getMessage()]);
        }
    }

    private function generateNoPenerimaan()
    {
        $prefix = 'PK' . date('Ymd');
        $last = PenerimaanKonsinyasi::where('no_penerimaankonsinyasi', 'like', $prefix.'%')
            ->orderBy('no_penerimaankonsinyasi', 'desc')->first();
        if ($last) {
            $num = (int)substr($last->no_penerimaankonsinyasi, -4) + 1;
        } else {
            $num = 1;
        }
        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    // API: Ambil Konsinyasi Keluar & detail berdasarkan Consignee
    public function apiKonsinyasiKeluarByConsignee($kode_consignee)
    {
        $konsinyasi = \App\Models\KonsinyasiKeluar::with(['details.produk'])
            ->where('kode_consignee', $kode_consignee)
            ->orderBy('tanggal_setor', 'desc')
            ->first();
        if (!$konsinyasi) {
            return response()->json(['no_konsinyasikeluar' => null, 'produkList' => []]);
        }
        $produkList = $konsinyasi->details->map(function($d) {
            return [
                'kode_produk' => $d->kode_produk,
                'nama_produk' => $d->produk ? $d->produk->nama_produk : '',
                'jumlah_setor' => $d->jumlah_setor,
                'satuan' => $d->satuan,
                'harga_satuan' => $d->harga_setor,
            ];
        });
        return response()->json([
            'no_konsinyasikeluar' => $konsinyasi->no_konsinyasikeluar,
            'produkList' => $produkList,
        ]);
    }

    // API: Ambil detail konsinyasi keluar & detail produk berdasarkan no konsinyasi keluar
    public function apiKonsinyasiKeluarDetail($no_konsinyasikeluar)
    {
        $konsinyasi = \App\Models\KonsinyasiKeluar::with(['consignee', 'details.produk'])
            ->where('no_konsinyasikeluar', $no_konsinyasikeluar)
            ->first();
        if (!$konsinyasi) {
            return response()->json(['success' => false, 'msg' => 'Data tidak ditemukan']);
        }
        $produkList = $konsinyasi->details->map(function($d) {
            return [
                'kode_produk' => $d->kode_produk,
                'nama_produk' => $d->produk ? $d->produk->nama_produk : '',
                'jumlah_setor' => $d->jumlah_setor,
                'satuan' => $d->satuan,
                'harga_satuan' => $d->harga_setor,
            ];
        });
        return response()->json([
            'success' => true,
            'no_konsinyasikeluar' => $konsinyasi->no_konsinyasikeluar,
            'tanggal_setor' => $konsinyasi->tanggal_setor,
            'kode_consignee' => $konsinyasi->kode_consignee,
            'nama_consignee' => $konsinyasi->consignee ? $konsinyasi->consignee->nama_consignee : '',
            'produkList' => $produkList,
        ]);
    }
}
