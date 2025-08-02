<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consignee;

class ConsigneeController extends Controller
{
    public function index()
    {
        $query = Consignee::query();
        if (request()->filled('search')) {
            $search = request('search');
            $query->where('nama_consignee', 'like', "%$search%");
        }
        $consignee = $query->get();
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
            'produk_setor' => 'array',
            'produk_setor.*.kode_produk' => 'required_with:produk_setor.*.jumlah_setor|nullable',
            'produk_setor.*.jumlah_setor' => 'required_with:produk_setor.*.kode_produk|nullable|numeric|min:1',
        ]);

        // Generate kode_consignee otomatis
        $last = \App\Models\Consignee::orderBy('kode_consignee', 'desc')->first();
        $kode_consignee = $last
            ? 'CE' . str_pad(intval(substr($last->kode_consignee, 2)) + 1, 5, '0', STR_PAD_LEFT)
            : 'CE00001';




        // Gabungkan keterangan setor produk
        $keteranganSetor = null;
        $produkSetorAda = $request->has('produk_setor') && is_array($request->produk_setor) && count($request->produk_setor) > 0;
        if ($produkSetorAda) {
            $produkKodeArr = array_column($request->produk_setor, 'kode_produk');
            $produkList = [];
            if (count($produkKodeArr)) {
                $produkList = \DB::table('t_produk')
                    ->whereIn('kode_produk', $produkKodeArr)
                    ->pluck('nama_produk', 'kode_produk');
            }
            $keteranganArr = [];
            foreach ($request->produk_setor as $row) {
                if (empty($row['kode_produk']) || empty($row['jumlah_setor'])) continue;
                $namaProduk = $produkList[$row['kode_produk']] ?? $row['kode_produk'];
                $keteranganArr[] = $namaProduk . ': ' . $row['jumlah_setor'];
            }
            if (count($keteranganArr)) {
                $keteranganSetor = implode(', ', $keteranganArr);
            }
        }
        if (!$keteranganSetor) {
            $keteranganSetor = $request->keterangan;
        }

        // Simpan data consignee, langsung dengan keterangan (hasil join produk atau manual)
        $consignee = \App\Models\Consignee::create([
            'kode_consignee' => $kode_consignee,
            'nama_consignee' => $request->nama_consignee,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'keterangan' => $keteranganSetor,
        ]);

        // Simpan setor jika ada
        $kode_consignee_setor = $request->kode_consignee_setor;
        if (!preg_match('/^CS\d{6}\d{2}$/', $kode_consignee_setor ?? '')) {
            $kode_consignee_setor = 'CS' . date('ymd') . sprintf('%02d', rand(0,99));
        }
        if ($produkSetorAda) {
            foreach ($request->produk_setor as $row) {
                if (empty($row['kode_produk']) || empty($row['jumlah_setor'])) continue;
                \DB::table('t_consignee_setor')->insert([
                    'kode_consignee' => $kode_consignee,
                    'kode_produk' => $row['kode_produk'],
                    'jumlah_setor' => $row['jumlah_setor'],
                    'kode_consignee_setor' => $kode_consignee_setor,
                ]);
            }
        }

        return redirect()->route('consignee.index')->with('success', 'Data consignee berhasil ditambahkan.');
    }

    public function edit($kode_consignee)
    {
        $consignee = \App\Models\Consignee::findOrFail($kode_consignee);
        $produkList = \DB::table('t_produk')->get();
        $setorList = \DB::table('t_consignee_setor')
            ->where('kode_consignee', $kode_consignee)
            ->get();
        return view('consignee.edit', compact('consignee', 'produkList', 'setorList'));
    }

    public function update(Request $request, $kode_consignee)
    {
        $request->validate([
            'nama_consignee' => 'required',
            'alamat' => 'required',
            'no_telp' => 'required',
            'keterangan' => 'nullable',
            'produk_setor' => 'array',
            'produk_setor.*.kode_produk' => 'required_with:produk_setor.*.jumlah_setor|nullable',
            'produk_setor.*.jumlah_setor' => 'required_with:produk_setor.*.kode_produk|nullable|numeric|min:1',
        ]);

        // Validasi produk tidak boleh ganda
        $produkArr = array_filter(array_column($request->produk_setor ?? [], 'kode_produk'));
        if (count($produkArr) !== count(array_unique($produkArr))) {
            return back()->withErrors(['Produk setor tidak boleh ganda!'])->withInput();
        }


        // Gabungkan keterangan setor produk
        $keteranganSetor = '';
        if ($request->has('produk_setor')) {
            $produkKodeArr = array_column($request->produk_setor, 'kode_produk');
            $produkList = [];
            if (count($produkKodeArr)) {
                $produkList = \DB::table('t_produk')
                    ->whereIn('kode_produk', $produkKodeArr)
                    ->pluck('nama_produk', 'kode_produk');
            }
            foreach ($request->produk_setor as $row) {
                if (!$row['kode_produk'] || !$row['jumlah_setor']) continue;
                $namaProduk = $produkList[$row['kode_produk']] ?? $row['kode_produk'];
                $keteranganSetor .= $namaProduk . ': ' . $row['jumlah_setor'] . ", ";
            }
            $keteranganSetor = rtrim($keteranganSetor, ', ');
        }


        $consignee = \App\Models\Consignee::findOrFail($kode_consignee);

        // Hapus semua setor lama
        \DB::table('t_consignee_setor')->where('kode_consignee', $kode_consignee)->delete();

        // Insert ulang setor sesuai input
        // Kode max 10 karakter: CSyymmddNN
        // Pastikan kode_consignee_setor selalu 10 karakter: CSyymmddNN
        $kode_consignee_setor = $request->kode_consignee_setor;
        if (!preg_match('/^CS\d{6}\d{2}$/', $kode_consignee_setor ?? '')) {
            $kode_consignee_setor = 'CS' . date('ymd') . sprintf('%02d', rand(0,99));
        }
        if ($request->has('produk_setor')) {
            foreach ($request->produk_setor as $row) {
                if (!$row['kode_produk'] || !$row['jumlah_setor']) continue;
                \DB::table('t_consignee_setor')->insert([
                    'kode_consignee' => $kode_consignee,
                    'kode_produk' => $row['kode_produk'],
                    'jumlah_setor' => $row['jumlah_setor'],
                    'kode_consignee_setor' => $kode_consignee_setor,
                ]);
            }
        }

        // Update seluruh field setelah setor produk diinput ulang
        $consignee->update([
            'nama_consignee' => $request->nama_consignee,
            'alamat' => $request->alamat,
            'no_telp' => $request->no_telp,
            'keterangan' => $keteranganSetor ?: $request->keterangan,
        ]);

        return redirect()->route('consignee.index')->with('success', 'Data consignee berhasil diupdate.');
    }

    public function destroy($kode_consignee)
    {
        $consignee = Consignee::findOrFail($kode_consignee);
        $consignee->delete();

        return redirect()->route('consignee.index')->with('success', 'Data consignee berhasil dihapus.');
    }

    public function setor($kode_consignee)
    {
        $consignee = Consignee::findOrFail($kode_consignee);
        // Ambil daftar produk jika perlu
        $produkList = \DB::table('t_produk')->get();
        return view('consignee.setor', compact('consignee', 'produkList'));
    }

    public function storeSetor(Request $request, $kode_consignee)
    {
        $request->validate([
            'kode_produk' => 'required',
            'jumlah_setor' => 'required|numeric|min:1',
        ]);

        // Simpan setor produk baru
        \DB::table('t_consignee_setor')->insert([
            'kode_consignee' => $kode_consignee,
            'kode_produk' => $request->kode_produk,
            'jumlah_setor' => $request->jumlah_setor,
        ]);

        return redirect()->route('consignee.index')->with('success', 'Data setor berhasil disimpan.');
    }
}