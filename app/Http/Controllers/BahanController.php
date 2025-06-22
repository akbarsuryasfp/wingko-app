<?php

namespace App\Http\Controllers;

use App\Models\Bahan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
    public function updateStokBahan($kode_bahan)
{
    $stok = \DB::table('t_kartupersbahan')
        ->where('kode_bahan', $kode_bahan)
        ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
        ->value('stok');

    \DB::table('t_bahan')->where('kode_bahan', $kode_bahan)->update(['stok' => $stok]);
}
public function updateSemuaStokBahan()
{
    $kodeBahans = \DB::table('t_bahan')->pluck('kode_bahan');
    foreach ($kodeBahans as $kode_bahan) {
        $this->updateStokBahan($kode_bahan);
    }
    return redirect()->back()->with('success', 'Stok semua bahan telah disinkronkan.');
}
public function reminderKadaluarsa()
{
    $today = Carbon::today();

    // Ambil semua batch bahan yang masih punya stok
    $data = DB::table('t_terimab_detail as d')
        ->join('t_bahan as b', 'd.kode_bahan', '=', 'b.kode_bahan')
        ->select(
            'd.no_terimab_detail',
            'd.kode_bahan',
            'b.nama_bahan',
            'd.tanggal_exp',
            'd.harga_beli',
            'd.bahan_masuk'
        )
        ->whereNotNull('d.tanggal_exp')
        ->orderBy('d.tanggal_exp')
        ->get()
        ->filter(function($row) {
            // Hitung total keluar untuk batch ini (berdasarkan kode_bahan, harga_beli, tanggal_exp)
            $keluar = DB::table('t_kartupersbahan')
                ->where('kode_bahan', $row->kode_bahan)
                ->where('harga', $row->harga_beli)
                ->where('tanggal_exp', $row->tanggal_exp)
                ->sum('keluar');
            return ($row->bahan_masuk - $keluar) > 0;
        });

    return view('bahan.reminder', compact('data'));
}
public static function getReminderKadaluarsa($days = 7)
{
    $today = \Carbon\Carbon::today();

    // Ambil batch bahan yang exp <= hari ini + $days dan masih ada stok
    $data = \DB::table('t_terimab_detail as d')
        ->join('t_bahan as b', 'd.kode_bahan', '=', 'b.kode_bahan')
        ->select(
            'd.kode_bahan',
            'b.nama_bahan',
            'd.tanggal_exp'
        )
        ->whereNotNull('d.tanggal_exp')
        ->where('d.tanggal_exp', '<=', $today->copy()->addDays($days))
        ->orderBy('d.tanggal_exp')
        ->get();

    return $data;
}
}

