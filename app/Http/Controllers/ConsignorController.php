<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consignor;

class ConsignorController extends Controller
{
    public function index()
    {
        $consignor = Consignor::all();
        return view('consignor.index', compact('consignor'));
    }

    public function create()
    {
        // Generate kode_consignor otomatis
        $last = Consignor::orderBy('kode_consignor', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_consignor, 2)); // ambil angka setelah 'CR'
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $kode_consignor = 'CR' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        return view('consignor.create', compact('kode_consignor'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_consignor' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
            'keterangan' => 'nullable',
        ]);

        // Generate kode_consignor otomatis
        $last = Consignor::orderBy('kode_consignor', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_consignor, 2)); // ubah dari 1 ke 2
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $kode_consignor = 'CR' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        Consignor::create([
            'kode_consignor' => $kode_consignor,
            'nama_consignor' => $request->nama_consignor,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('consignor.index')->with('success', 'Data consignor berhasil ditambahkan.');
    }

    public function edit($kode_consignor)
    {
        $consignor = Consignor::findOrFail($kode_consignor);
        return view('consignor.edit', compact('consignor'));
    }

    public function update(Request $request, $kode_consignor)
    {
        $request->validate([
            'nama_consignor' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
            'keterangan' => 'nullable',
        ]);

        $consignor = Consignor::findOrFail($kode_consignor);
        $consignor->update($request->all());

        return redirect()->route('consignor.index')->with('success', 'Data consignor berhasil diupdate.');
    }

    public function destroy($kode_consignor)
    {
        $consignor = Consignor::findOrFail($kode_consignor);
        $consignor->delete();

        return redirect()->route('consignor.index')->with('success', 'Data consignor berhasil dihapus.');
    }
}