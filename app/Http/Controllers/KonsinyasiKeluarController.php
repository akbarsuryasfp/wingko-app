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
    public function index()
    {
        $konsinyasiKeluarList = KonsinyasiKeluar::with('consignee')->orderByDesc('tanggal_setor')->get();
        return view('konsinyasikeluar.index', compact('konsinyasiKeluarList'));
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
        $noSuratOtomatis = $kodeOtomatis; // Atur sesuai kebutuhan

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
            $total = 0;
            foreach ($request->produk as $prod) {
                $total += $prod['jumlah_setor'] * $prod['harga_setor'];
            }
            // Simpan header ke t_konsinyasikeluar
            DB::table('t_konsinyasikeluar')->insert([
                'no_konsinyasikeluar' => $request->kode_setor,
                'kode_consignee' => $request->kode_consignee,
                'tanggal_setor' => $request->tanggal_setor,
                'total_setor' => $total
            ]);
            // Simpan detail ke t_konsinyasikeluar_detail
            foreach ($request->produk as $i => $prod) {
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
        $header = KonsinyasiKeluar::with(['consignee', 'details.produk'])->findOrFail($id);
        return view('konsinyasikeluar.detail', compact('header'));
    }

    public function edit($id)
    {
        $header = KonsinyasiKeluar::with(['details'])->findOrFail($id);
        $consignees = Consignee::all();
        $produkList = ProdukKonsinyasi::all();
        return view('konsinyasikeluar.edit', compact('header', 'consignees', 'produkList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kode_setor' => 'required',
            'tanggal_setor' => 'required|date',
            'kode_consignee' => 'required',
            'produk.*.kode_produk' => 'required',
            'produk.*.jumlah_setor' => 'required|numeric|min:1',
            'produk.*.satuan' => 'required',
            'produk.*.harga_setor' => 'required|numeric|min:0',
        ]);
        DB::beginTransaction();
        try {
            $header = KonsinyasiKeluar::findOrFail($id);
            $total = 0;
            foreach ($request->produk as $prod) {
                $total += $prod['jumlah_setor'] * $prod['harga_setor'];
            }
            $header->update([
                'kode_setor' => $request->kode_setor,
                'tanggal_setor' => $request->tanggal_setor,
                'kode_consignee' => $request->kode_consignee,
                'total_setor' => $total
            ]);
            // Hapus detail lama
            KonsinyasiKeluarDetail::where('konsinyasikeluar_id', $header->id)->delete();
            // Simpan detail baru
            foreach ($request->produk as $prod) {
                KonsinyasiKeluarDetail::create([
                    'konsinyasikeluar_id' => $header->id,
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

    public function destroy($id)
    {
        $header = KonsinyasiKeluar::findOrFail($id);
        $header->details()->delete();
        $header->delete();
        return redirect()->route('konsinyasikeluar.index')->with('success', 'Data berhasil dihapus');
    }
}
