<?php
namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = Kategori::all();
        return view('kategori.index', compact('kategori'));
    }

    public function create()
    {
        return view('kategori.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_kategori' => 'required|unique:t_kategori,kode_kategori',
            'jenis_kategori' => 'required',
        ]);

        Kategori::create([
            'kode_kategori' => $request->kode_kategori,
            'jenis_kategori' => $request->jenis_kategori,
        ]);

        return redirect()->route('kategori.index')->with('success', 'Data kategori berhasil ditambahkan.');
    }

    public function edit($kode_kategori)
    {
        $kategori = Kategori::findOrFail($kode_kategori);
        return view('kategori.edit', compact('kategori'));
    }

    public function update(Request $request, $kode_kategori)
    {
        $request->validate([
            'kode_kategori' => 'required|unique:t_kategori,kode_kategori,' . $kode_kategori . ',kode_kategori',
            'jenis_kategori' => 'required',
        ]);

        $kategori = Kategori::findOrFail($kode_kategori);

        // Jika kode_kategori diubah, update primary key
        $kategori->kode_kategori = $request->kode_kategori;
        $kategori->jenis_kategori = $request->jenis_kategori;
        $kategori->save();

        return redirect()->route('kategori.index')->with('success', 'Data kategori berhasil diupdate.');
    }

    public function destroy($kode_kategori)
    {
        $kategori = Kategori::findOrFail($kode_kategori);
        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Data kategori berhasil dihapus.');
    }
}