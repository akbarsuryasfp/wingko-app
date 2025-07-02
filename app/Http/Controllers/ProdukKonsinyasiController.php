<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProdukKonsinyasi;
use App\Models\Consignor;

class ProdukKonsinyasiController extends Controller
{
    public function create(Request $request)
    {
        // Generate kode produk otomatis
        $kode_produk = $this->generateKodeProduk(); // pastikan method ini ada
        $consignors = Consignor::all();
        $selectedConsignor = $request->get('kode_consignor', null);

        return view('consignor.create_produk', [
            'kode_produk' => $kode_produk,
            'consignors' => $consignors,
            'selectedConsignor' => $selectedConsignor,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_produk' => 'required|unique:t_produk_konsinyasi,kode_produk',
            'nama_produk' => 'required',
            'satuan' => 'required',
            'harga_konsinyasi' => 'required|numeric|min:0',
            'kode_consignor' => 'required',
        ]);

        ProdukKonsinyasi::create($request->all());

        return redirect()->route('consignor.index')->with('success', 'Produk konsinyasi berhasil ditambahkan.');
    }

    public function index()
    {
        $produkKonsinyasi = ProdukKonsinyasi::with('consignor')->get();
        return view('produk_konsinyasi.index', compact('produkKonsinyasi'));
    }

    /**
     * Generate kode produk konsinyasi otomatis (format: PKM0001, PKM0002, dst)
     */
    private function generateKodeProduk()
    {
        $last = \App\Models\ProdukKonsinyasi::orderBy('kode_produk', 'desc')->first();
        $lastNumber = $last ? intval(substr($last->kode_produk, 3)) : 0;
        return 'PKM' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }

    // Tampilkan form edit
    public function edit($id)
    {
        $produk = \App\Models\ProdukKonsinyasi::findOrFail($id);
        return view('consignor.edit_produk', compact('produk'));
    }

    // Proses update
    public function update(Request $request, $id)
    {
        $produk = \App\Models\ProdukKonsinyasi::findOrFail($id);

        $request->validate([
            'kode_produk' => 'required|string|max:50',
            'nama_produk' => 'required|string|max:100',
            'satuan' => 'required|string|max:30',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $produk->update([
            'kode_produk' => $request->kode_produk,
            'nama_produk' => $request->nama_produk,
            'satuan' => $request->satuan,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('consignor.index')->with('success', 'Produk konsinyasi berhasil diupdate.');
    }

    public function destroy($kode_produk)
    {
        $produk = \App\Models\ProdukKonsinyasi::findOrFail($kode_produk);
        $produk->delete();

        return redirect()->route('consignor.index')->with('success', 'Produk konsinyasi berhasil dihapus.');
    }

    public function getByConsignor($kode_consignor)
    {
        $produk = \App\Models\ProdukKonsinyasi::where('kode_consignor', $kode_consignor)->get();
        // dd($produk); // untuk debug di backend
        return response()->json($produk);
    }
}