<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BahanController extends Controller
{
    public function index(Request $request)
    {
        $query = Bahan::query();

        if ($request->filled('kode_kategori')) {
            $query->where('kode_kategori', $request->kode_kategori);
        }

        // Ambil hanya kategori dengan kode depan B
        $kategoriList = \App\Models\Kategori::where('kode_kategori', 'like', 'B%')->get();

        $bahan = $query->get();

        return view('bahan.index', compact('bahan', 'kategoriList'));
    }

    public function create()
    {
        $last = \App\Models\Bahan::orderBy('kode_bahan', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_bahan, 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $kode_bahan = 'B' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        $kategori = \App\Models\Kategori::all(); // Ambil semua kategori

        return view('bahan.create', compact('kode_bahan', 'kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'required',
            'nama_bahan' => 'required',
            'satuan' => 'required',
            'stokmin' => 'required|integer',
        ]);

        // Generate kode_bahan otomatis
        $last = Bahan::orderBy('kode_bahan', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_bahan, 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $kode_bahan = 'B' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        Bahan::create([
            'kode_bahan' => $kode_bahan,
            'kode_kategori' => $request->kode_kategori,
            'nama_bahan' => $request->nama_bahan,
            'satuan' => $request->satuan,
            'stokmin' => $request->stokmin,
        ]);

        return redirect()->route('bahan.index')->with('success', 'Data bahan berhasil ditambahkan.');
    }

    public function edit($kode_bahan)
    {
        $bahan = Bahan::findOrFail($kode_bahan);
        $kategori = \App\Models\Kategori::all();
        return view('bahan.edit', compact('bahan', 'kategori'));
    }

    public function update(Request $request, $kode_bahan)
    {
        $request->validate([
            'kode_kategori' => 'required',
            'nama_bahan' => 'required',
            'satuan' => 'required',
            'stokmin' => 'required|integer',
        ]);

        $bahan = Bahan::findOrFail($kode_bahan);
        $bahan->update($request->all());

        return redirect()->route('bahan.index')->with('success', 'Data bahan berhasil diupdate.');
    }

    public function destroy($kode_bahan)
    {
        $bahan = Bahan::findOrFail($kode_bahan);
        $bahan->delete();

        return redirect()->route('bahan.index')->with('success', 'Data bahan berhasil dihapus.');
    }
}
