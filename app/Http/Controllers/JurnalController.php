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
            ->orderBy('no_jurnal')
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
            $mutasi = JurnalDetail::with(['jurnalUmum'])
                ->where('kode_akun', $kode_akun)
                ->orderByHas('jurnalUmum', function($q) {
                    $q->orderBy('tanggal');
                })
                ->get();
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
}