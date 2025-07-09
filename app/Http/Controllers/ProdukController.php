<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        // Ambil kategori dengan kode depan "P"
        $kategoriList = Kategori::where('kode_kategori', 'like', 'P%')->get(); // Sudah benar

        // Filter produk jika ada request kode_kategori
        $query = Produk::query();
        if ($request->filled('kode_kategori')) {
            $query->where('kode_kategori', $request->kode_kategori);
        }
        $produk = $query->get();
        if ($request->filled('search')) {
            $produk = $produk->filter(function ($item) use ($request) {
                return str_contains(strtolower($item->nama_produk), strtolower($request->search));
            });
        }
        return view('produk.index', compact('produk', 'kategoriList'));
    }

    public function create()
    {
        // Generate kode_produk otomatis
        $last = Produk::orderBy('kode_produk', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_produk, 3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $kode_produk = 'BRG' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        $kategori = Kategori::all();
        return view('produk.create', compact('kode_produk', 'kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'required',
            'nama_produk' => 'required',
            'satuan' => 'required',
            'stokmin' => 'required|numeric|min:0', // tambahkan validasi stokmin
        ]);

        // Generate kode_produk otomatis
        $last = Produk::orderBy('kode_produk', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_produk, 3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $kode_produk = 'BRG' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        Produk::create([
            'kode_produk' => $kode_produk,
            'kode_kategori' => $request->kode_kategori,
            'nama_produk' => $request->nama_produk,
            'satuan' => $request->satuan,
            'stokmin' => $request->stokmin, // simpan stokmin
        ]);

        return redirect()->route('produk.index')->with('success', 'Data produk berhasil ditambahkan.');
    }

    public function edit($kode_produk)
    {
        $produk = Produk::findOrFail($kode_produk);
        $kategori = Kategori::all();
        return view('produk.edit', compact('produk', 'kategori'));
    }

    public function update(Request $request, $kode_produk)
    {
        $request->validate([
            'kode_kategori' => 'required',
            'nama_produk' => 'required',
            'satuan' => 'required',
            'stokmin' => 'required|numeric|min:0', // tambahkan validasi stokmin
        ]);

        $produk = Produk::findOrFail($kode_produk);
        $produk->update([
            'kode_kategori' => $request->kode_kategori,
            'nama_produk' => $request->nama_produk,
            'satuan' => $request->satuan,
            'stokmin' => $request->stokmin, // update stokmin
        ]);

        return redirect()->route('produk.index')->with('success', 'Data produk berhasil diupdate.');
    }

    public function destroy($kode_produk)
    {
        $produk = Produk::findOrFail($kode_produk);
        $produk->delete();

        return redirect()->route('produk.index')->with('success', 'Data produk berhasil dihapus.');
    }
}