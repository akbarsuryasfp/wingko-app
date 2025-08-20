<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JurnalUmum;
use App\Models\JurnalDetail;
use App\Models\Akun;

class JurnalController extends Controller
{
    // Halaman Jurnal Umum
    public function index(Request $request)
    {
        $query = JurnalUmum::with(['details.akun'])
            ->where('jenis_jurnal', 'umum'); // hanya jurnal umum

        // Filter tanggal
        if ($request->filled('tanggal_awal')) {
            $query->where('tanggal', '>=', $request->tanggal_awal);
        }
    
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal', '<=', $request->tanggal_akhir);
        }

        // Filter keterangan atau no jurnal
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('keterangan', 'like', "%$q%")
                    ->orWhere('no_jurnal', 'like', "%$q%");
            });
        }

        $jurnals = $query->orderBy('tanggal', 'desc')
            ->orderBy('no_jurnal', 'desc')
            ->get();

        return view('jurnal.index', compact('jurnals'));
    }

    // Halaman Buku Besar
    public function bukuBesar(Request $request)
    {
        $kode_akun = $request->input('kode_akun');
        $akuns = Akun::orderBy('kode_akun')->get();

        $mutasi = [];
        if ($kode_akun) {
            $mutasi = JurnalDetail::with('jurnalUmum')
                ->where('kode_akun', $kode_akun)
                ->get()
                ->sortBy(function($item) {
                    return $item->jurnalUmum->tanggal ?? '';
                });
        }

        return view('jurnal.buku_besar', compact('akuns', 'mutasi', 'kode_akun'));
    }

    // Halaman Jurnal Penyesuaian
    public function penyesuaian(Request $request)
    {
        $query = JurnalUmum::with(['details.akun'])
            ->where('jenis_jurnal', 'penyesuaian'); // hanya jurnal penyesuaian

        // Filter tanggal dan q jika perlu
        if ($request->filled('tanggal_awal')) {
            $query->where('tanggal', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal', '<=', $request->tanggal_akhir);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('keterangan', 'like', "%$q%")
                    ->orWhere('no_jurnal', 'like', "%$q%");
            });
        }

        $jurnals = $query->orderBy('tanggal', 'desc')
            ->orderBy('no_jurnal')
            ->get();

        return view('jurnal.penyesuaian', compact('jurnals'));
    }

    // Form input jurnal manual
    public function create()
    {
        $akuns = \App\Models\Akun::orderBy('kode_akun')->get();
        return view('jurnal.create', compact('akuns'));
    }

    // Simpan jurnal manual
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required',
            'details.*.kode_akun' => 'required',
            'details.*.debit' => 'nullable|numeric',
            'details.*.kredit' => 'nullable|numeric',
        ]);

        $no_jurnal = \App\Helpers\JurnalHelper::generateNoJurnal();
        $jurnal = \App\Models\JurnalUmum::create([
            'no_jurnal' => $no_jurnal,
            'tanggal' => $request->tanggal,
            'keterangan' => $request->keterangan,
            'nomor_bukti' => $request->nomor_bukti ?? null,
        ]);

        foreach ($request->details as $i => $detail) {
            \App\Models\JurnalDetail::create([
                'no_jurnal_detail' => \App\Helpers\JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal' => $no_jurnal,
                'kode_akun' => $detail['kode_akun'],
                'debit' => $detail['debit'] ?? 0,
                'kredit' => $detail['kredit'] ?? 0,
            ]);
        }

        return redirect()->route('jurnal.index')->with('success', 'Jurnal berhasil ditambahkan!');
    }
}