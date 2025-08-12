<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consignor;

class ConsignorController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Consignor::with('produkKonsinyasi');
        if ($request->filled('search')) {
            $query->where('nama_consignor', 'like', '%' . $request->search . '%');
        }
        $consignor = $query->get();
        return view('consignor.index', compact('consignor'));
    }

    public function create()
    {
        // Generate kode_consignor otomatis dengan prefix "CR"
        $last = Consignor::orderBy('kode_consignor', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->kode_consignor, 2));
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
            'kode_consignor' => 'required|unique:t_consignor,kode_consignor',
            'nama_consignor' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
            'bank' => 'required',
            'rekening' => 'required',
        ]);

        $rekening = $request->bank . ' ' . $request->rekening;

        Consignor::create([
            'kode_consignor' => $request->kode_consignor,
            'nama_consignor' => $request->nama_consignor,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'rekening' => $rekening,
        ]);

        return redirect()->route('consignor.index')->with('success', 'Data consignor berhasil disimpan!');
    }

    public function edit($kode_consignor)
    {
        $consignor = Consignor::findOrFail($kode_consignor);

        // Parsing rekening menjadi bank dan no rekening
        $bank = '';
        $no_rekening = '';
        if ($consignor->rekening) {
            $parts = explode(' ', $consignor->rekening, 2);
            $bank = $parts[0] ?? '';
            $no_rekening = $parts[1] ?? '';
        }

        // Parsing keterangan menjadi nama_produk dan jumlah
        $nama_produk = '';
        $jumlah = '';
        if ($consignor->keterangan) {
            preg_match('/Produk:\s*(.*),\s*Jumlah:\s*(\d+)\s*unit/i', $consignor->keterangan, $matches);
            if ($matches) {
                $nama_produk = $matches[1] ?? '';
                $jumlah = $matches[2] ?? '';
            }
        }

        return view('consignor.edit', compact('consignor', 'bank', 'no_rekening', 'nama_produk', 'jumlah'));
    }

    public function update(Request $request, $kode_consignor)
    {
        $request->validate([
            'nama_consignor' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
            'bank' => 'required',
            'rekening' => 'required',
        ]);

        // Gabungkan bank dan rekening
        $rekening = $request->bank . ' ' . $request->rekening;

        $consignor = Consignor::findOrFail($kode_consignor);
        $consignor->update([
            'nama_consignor' => $request->nama_consignor,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'rekening' => $rekening,
        ]);

        return redirect()->route('consignor.index')->with('success', 'Data consignor berhasil diupdate.');
    }

    public function destroy($kode_consignor)
    {
        $consignor = Consignor::findOrFail($kode_consignor);
        $consignor->delete();

        return redirect()->route('consignor.index')->with('success', 'Data consignor berhasil dihapus.');
    }
}