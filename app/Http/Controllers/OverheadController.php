<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BOP;
use App\Models\BopTransaksi;
use App\Models\AsetTetap;

class OverheadController extends Controller
{
    // Daftar overhead aktual
    public function index(Request $request)
    {
        // Ambil data overhead, bisa difilter per bulan
        $periode = $request->input('periode');
        $query = \App\Models\BopTransaksi::with('bop');

        if ($periode) {
            $query->where('periode', 'like', $periode.'%');
        }

        $overheads = $query->orderBy('periode', 'desc')->orderBy('kode_bop')->paginate(20);

        return view('overhead.index', compact('overheads', 'periode'));
    }

    // Form input overhead aktual
    public function create()
    {
        $bop = BOP::all();
        $asetTetap = AsetTetap::all();
        $totalPenyusutan = 0;
        foreach ($asetTetap as $aset) {
            if ($aset->umur_ekonomis > 0) {
                $penyusutan = ($aset->harga_perolehan - $aset->nilai_sisa) / ($aset->umur_ekonomis * 12);
                $totalPenyusutan += $penyusutan;
            }
        }
        // Kirim ke view, bisa juga per aset jika ingin detail
        return view('overhead.create', compact('bop', 'totalPenyusutan'));
    }

    // Simpan overhead aktual
    public function store(Request $request)
    {
        $request->validate([
            'periode' => 'required|date_format:Y-m',
            'bop.*.kode_bop' => 'required',
            'bop.*.jumlah' => 'nullable|numeric|min:0',
        ]);
        $periode = $request->periode . '-01';
        foreach ($request->bop as $item) {
            if (!empty($item['jumlah'])) {
                BopTransaksi::updateOrCreate(
                    [
                        'kode_bop' => $item['kode_bop'],
                        'periode' => $periode,
                    ],
                    [
                        'jumlah' => $item['jumlah'],
                        'keterangan' => $item['keterangan'] ?? null,
                    ]
                );
            }
        }
        return redirect()->route('overhead.create')->with('success', 'Data overhead berhasil disimpan.');
    }
}