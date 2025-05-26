<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $supplier = Supplier::all();
        return view('supplier.index', compact('supplier'));
    }

    public function create()
    {
        $last = \App\Models\Supplier::orderBy('kode_supplier', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_supplier, 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $kode_supplier = 'S' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        return view('supplier.create', compact('kode_supplier'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
            'no_rek' => 'nullable',
            'keterangan' => 'nullable',
        ]);

        // Generate kode_supplier otomatis
        $last = Supplier::orderBy('kode_supplier', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_supplier, 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $kode_supplier = 'S' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);

        Supplier::create([
            'kode_supplier' => $kode_supplier,
            'nama_supplier' => $request->nama_supplier,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'no_rek' => $request->no_rek,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('supplier.index')->with('success', 'Data supplier berhasil ditambahkan.');
    }

    public function edit($kode_supplier)
    {
        $supplier = Supplier::findOrFail($kode_supplier);
        return view('supplier.edit', compact('supplier'));
    }

    public function update(Request $request, $kode_supplier)
    {
        $request->validate([
            'nama_supplier' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
            'no_rek' => 'nullable',
            'keterangan' => 'nullable',
        ]);

        $supplier = Supplier::findOrFail($kode_supplier);
        $supplier->update($request->all());

        return redirect()->route('supplier.index')->with('success', 'Data supplier berhasil diupdate.');
    }

    public function destroy($kode_supplier)
    {
        $supplier = Supplier::findOrFail($kode_supplier);
        $supplier->delete();

        return redirect()->route('supplier.index')->with('success', 'Data supplier berhasil dihapus.');
    }
}