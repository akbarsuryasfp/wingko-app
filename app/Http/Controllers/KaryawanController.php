<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawan = Karyawan::all();
        return view('karyawan.index', compact('karyawan'));
    }

    public function create()
    {
        // Ambil kode terakhir dari database
        $last = \DB::table('t_karyawan')
            ->orderBy('kode_karyawan', 'desc')
            ->first();

        if ($last && preg_match('/PGT(\d+)/', $last->kode_karyawan, $m)) {
            $next = (int)$m[1] + 1;
        } else {
            $next = 1;
        }
        $kodeBaru = 'PGT' . str_pad($next, 4, '0', STR_PAD_LEFT);

        // Enum value untuk dropdown
        $jabatan = ['Staff', 'Kepala Bagian'];
        $departemen = ['Produksi', 'Penjualan', 'Gudang'];

        return view('karyawan.create', compact('kodeBaru', 'jabatan', 'departemen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_karyawan' => 'required|unique:t_karyawan,kode_karyawan',
            'nama' => 'required',
            'jabatan' => 'nullable',
            'departemen' => 'nullable',
            'gaji' => 'nullable|numeric',
            'tanggal_masuk' => 'nullable|date',
            'alamat' => 'nullable',
            'no_telepon' => 'nullable',
        ]);

        Karyawan::create($request->all());
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil ditambahkan!');
    }

    public function edit($kode_karyawan)
    {
        $karyawan = Karyawan::findOrFail($kode_karyawan);
        return view('karyawan.edit', compact('karyawan'));
    }

    public function update(Request $request, $kode_karyawan)
    {
        $request->validate([
            'nama' => 'required',
            'jabatan' => 'nullable',
            'departemen' => 'nullable',
            'gaji' => 'nullable|numeric',
            'tanggal_masuk' => 'nullable|date',
            'alamat' => 'nullable',
            'no_telepon' => 'nullable',
        ]);

        $karyawan = Karyawan::findOrFail($kode_karyawan);
        $karyawan->update($request->all());
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diupdate!');
    }
}