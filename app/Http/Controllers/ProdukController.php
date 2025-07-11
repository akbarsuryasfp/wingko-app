<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::query(); 
        $produk = $query->get();
        if ($request->filled('search')) {
            $produk = $produk->filter(function ($item) use ($request) {
                return str_contains(strtolower($item->nama_produk), strtolower($request->search));
            });
        }
        return view('produk.index', compact('produk'));
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

        return view('produk.create', compact('kode_produk'));
    }

public function store(Request $request)
{
    $request->validate([
        'nama_produk' => 'required',
        'satuan' => 'required',
        'stokmin' => 'required|numeric|min:0',
        'harga_jual' => 'nullable|numeric|min:0',
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
        'nama_produk' => $request->nama_produk,
        'satuan' => $request->satuan,
        'stokmin' => $request->stokmin,
        'harga_jual' => $request->harga_jual,
    ]);

    return redirect()->route('produk.index')->with('success', 'Data produk berhasil ditambahkan.');
}

    public function edit($kode_produk)
    {
        $produk = Produk::findOrFail($kode_produk);
        return view('produk.edit', compact('produk'));
    }

public function update(Request $request, $kode_produk)
{
    $request->validate([
        'nama_produk' => 'required',
        'satuan' => 'required',
        'stokmin' => 'required|numeric|min:0',
        'harga_jual' => 'nullable|numeric|min:0',
    ]);

    $produk = Produk::findOrFail($kode_produk);
    $produk->update([
        'nama_produk' => $request->nama_produk,
        'satuan' => $request->satuan,
        'stokmin' => $request->stokmin,
        'harga_jual' => $request->harga_jual,
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