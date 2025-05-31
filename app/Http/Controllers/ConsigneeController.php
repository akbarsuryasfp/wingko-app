<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consignee;

class ConsigneeController extends Controller
{
    public function index()
    {
        $consignee = Consignee::all();
        return view('consignee.index', compact('consignee'));
    }

    public function create()
    {
        // Generate kode_consignee otomatis dengan prefix "CE"
        $last = Consignee::orderBy('kode_consignee', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_consignee, 2));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $kode_consignee = 'CE' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        return view('consignee.create', compact('kode_consignee'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_consignee' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
            'keterangan' => 'nullable',
        ]);

        // Generate kode_consignee otomatis
        $last = Consignee::orderBy('kode_consignee', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_consignee, 2));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $kode_consignee = 'CE' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        Consignee::create([
            'kode_consignee' => $kode_consignee,
            'nama_consignee' => $request->nama_consignee,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('consignee.index')->with('success', 'Data consignee berhasil ditambahkan.');
    }

    public function edit($kode_consignee)
    {
        $consignee = Consignee::findOrFail($kode_consignee);
        return view('consignee.edit', compact('consignee'));
    }

    public function update(Request $request, $kode_consignee)
    {
        $request->validate([
            'nama_consignee' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
            'keterangan' => 'nullable',
        ]);

        $consignee = Consignee::findOrFail($kode_consignee);
        $consignee->update($request->all());

        return redirect()->route('consignee.index')->with('success', 'Data consignee berhasil diupdate.');
    }

    public function destroy($kode_consignee)
    {
        $consignee = Consignee::findOrFail($kode_consignee);
        $consignee->delete();

        return redirect()->route('consignee.index')->with('success', 'Data consignee berhasil dihapus.');
    }
}