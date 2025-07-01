<?php
namespace App\Http\Controllers;

use App\Models\AsetTetap;
use Illuminate\Http\Request;

class AsetTetapController extends Controller
{
    public function index()
    {
        $data = AsetTetap::all();
        return view('aset_tetap.index', compact('data'));
    }

    public function create()
    {
        // Generate kode otomatis
        $last = \App\Models\AsetTetap::orderBy('kode_aset_tetap', 'desc')->first();
        if ($last) {
            $num = (int)substr($last->kode_aset_tetap, 2) + 1;
        } else {
            $num = 1;
        }
        $kodeBaru = 'AT' . str_pad($num, 3, '0', STR_PAD_LEFT);
        return view('aset_tetap.create', compact('kodeBaru'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_aset_tetap' => 'required|unique:t_aset_tetap,kode_aset_tetap',
            'nama_aset' => 'required',
            'tanggal_beli' => 'required|date',
            'harga_perolehan' => 'required|numeric',
            'umur_ekonomis' => 'required|integer',
            'nilai_sisa' => 'required|numeric',
        ]);
        $persentaseResidu = [
            'mesin' => 0.10,
            'kendaraan' => 0.20,
            'peralatan' => 0.00,
        ];
        $nilaiSisa = 0;
        if (isset($persentaseResidu[$request->tipe_aset])) {
            $nilaiSisa = $request->harga_perolehan * $persentaseResidu[$request->tipe_aset];
        }
        AsetTetap::create([
            'kode_aset_tetap' => $request->kode_aset_tetap,
            'nama_aset' => $request->nama_aset,
            'tipe_aset' => $request->tipe_aset,
            'tanggal_beli' => $request->tanggal_beli,
            'harga_perolehan' => $request->harga_perolehan,
            'umur_ekonomis' => $request->umur_ekonomis,
            'nilai_sisa' => $nilaiSisa,
            'keterangan' => $request->keterangan,
        ]);
        return redirect()->route('aset-tetap.index')->with('success', 'Aset tetap berhasil ditambahkan');
    }

    private function generateKodeAset()
    {
        $last = \App\Models\AsetTetap::orderBy('kode_aset_tetap', 'desc')->first();
        $num = $last ? (int)substr($last->kode_aset_tetap, 2) + 1 : 1;
        return 'AT' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}