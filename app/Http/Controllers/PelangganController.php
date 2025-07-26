<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Pelanggan::query();

        if ($request->filled('search')) {
            $query->where('nama_pelanggan', 'like', '%' . $request->search . '%');
        }

        $pelanggan = $query->get();

        return view('pelanggan.index', compact('pelanggan'));
    }

    public function create()
    {
        // Generate kode pelanggan otomatis
        $last = Pelanggan::orderBy('kode_pelanggan', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_pelanggan, 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $kode_pelanggan = 'P' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        return view('pelanggan.create', compact('kode_pelanggan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
        ]);

        // Generate kode pelanggan otomatis
        $last = Pelanggan::orderBy('kode_pelanggan', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_pelanggan, 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $kode_pelanggan = 'P' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        Pelanggan::create([
            'kode_pelanggan' => $kode_pelanggan,
            'nama_pelanggan' => $request->nama_pelanggan,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
        ]);

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil ditambahkan.');
    }

    public function edit($kode_pelanggan)
    {
        $pelanggan = Pelanggan::findOrFail($kode_pelanggan);
        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, $kode_pelanggan)
    {
        $request->validate([
            'nama_pelanggan' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
        ]);

        $pelanggan = Pelanggan::findOrFail($kode_pelanggan);
        $pelanggan->update($request->all());

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diupdate.');
    }

    public function destroy($kode_pelanggan)
    {
        $pelanggan = Pelanggan::findOrFail($kode_pelanggan);
        $pelanggan->delete();

        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil dihapus.');
    }
}