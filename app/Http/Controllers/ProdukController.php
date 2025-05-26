<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        $produk = Produk::all();
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

        $kategori = Kategori::all();
        return view('produk.create', compact('kode_produk', 'kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'required',
            'nama_produk' => 'required',
            'satuan' => 'required',
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
            'kode_kategori' => $request->kode_kategori, // gunakan kode_kategori
            'nama_produk' => $request->nama_produk,
            'satuan' => $request->satuan,
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
        ]);

        $produk = Produk::findOrFail($kode_produk);
        $produk->update([
            'kode_kategori' => $request->kode_kategori, // gunakan kode_kategori
            'nama_produk' => $request->nama_produk,
            'satuan' => $request->satuan,
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