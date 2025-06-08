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
        return view('karyawan.create');
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
            'email' => 'nullable|email',
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
            'email' => 'nullable|email',
            'no_telepon' => 'nullable',
        ]);

        $karyawan = Karyawan::findOrFail($kode_karyawan);
        $karyawan->update($request->all());
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diupdate!');
    }
}