<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BOP;
use App\Models\BopRealisasi;
use App\Models\AsetTetap;
use App\Models\JurnalUmum;
use App\Models\JurnalDetail;
use App\Helpers\JurnalHelper;
use Illuminate\Support\Facades\DB;

class OverheadController extends Controller
{
    // Daftar overhead aktual
    public function index(Request $request)
    {
        // Ambil data overhead, bisa difilter per bulan
        $periode = $request->input('periode');
        $query = \App\Models\BopRealisasi::with('bop');

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

        // Hitung total penyusutan aset tetap bulan ini
        $totalPenyusutan = 0;
        foreach ($asetTetap as $aset) {
            if ($aset->umur_ekonomis > 0) {
                $penyusutan = ($aset->harga_perolehan - $aset->nilai_sisa) / ($aset->umur_ekonomis * 12);
                $totalPenyusutan += $penyusutan;
            }
        }

        // Ambil bulan dan tahun dari request atau default
        $bulan = request('bulan') ?? date('m');
        $tahun = request('tahun') ?? date('Y');

        // Ambil total biaya listrik dari jurnal detail dengan kode akun 5001 pada bulan penyesuaian
        $totalListrik = DB::table('t_jurnal_detail as jd')
            ->join('t_jurnal_umum as ju', 'jd.no_jurnal', '=', 'ju.no_jurnal')
            ->where('jd.kode_akun', 5001)
            ->whereMonth('ju.tanggal', $bulan)
            ->whereYear('ju.tanggal', $tahun)
            ->sum('jd.debit');

        // Parse value bulat
        $totalListrik = intval(round($totalListrik));

        // Siapkan bopList, isi jumlah pada BOP002 saja
        $bopList = [];
        foreach ($bop as $item) {
            $bopList[] = [
                'kode_bop' => $item->kode_bop,
                'nama_bop' => $item->nama_bop,
                'jumlah'   => $item->kode_bop == 'BOP001' ? $totalListrik : ($item->kode_bop == 'BOP002' ? $totalPenyusutan : null),
                'keterangan' => $item->kode_bop == 'BOP002' ? 'Otomatis dihitung sistem' : ''
            ];
        }
        return view('overhead.create', [
            'bopList' => $bopList,
            'periode' => request('periode'),
            'totalListrik' => $totalListrik,
            'bulan' => $bulan,      // Tambahkan ini
            'tahun' => $tahun       // Tambahkan ini
        ]);
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

        // Cek apakah sudah ada data untuk periode ini
        $sudahAda = BopRealisasi::where('periode', $periode)->exists();
        if ($sudahAda) {
            return redirect()->back()
                ->withErrors(['periode' => 'Data overhead untuk periode ini sudah ada.']);
        }

        foreach ($request->bop as $item) {
            if (!empty($item['jumlah'])) {
                BopRealisasi::updateOrCreate(
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

        // Hitung total overhead realisasi bulan ini
        $totalRealisasi = 0;
        foreach ($request->bop as $item) {
            if (!empty($item['jumlah'])) {
                $totalRealisasi += $item['jumlah'];
            }
        }

        // Ambil total overhead perkiraan dari HPP bulan ini
        $totalPerkiraan = \App\Models\HppPerProduk::whereMonth('tanggal_input', date('m', strtotime($periode)))
            ->whereYear('tanggal_input', date('Y', strtotime($periode)))
            ->sum('total_overhead');

        // Jurnal penyesuaian overhead
        $bulan = date('m', strtotime($periode));
        $tahun = date('Y', strtotime($periode));

        // Ambil total biaya listrik dari jurnal detail dengan kode akun 5001 pada bulan penyesuaian
        $totalListrik = DB::table('t_jurnal_detail as jd')
            ->join('t_jurnal_umum as ju', 'jd.no_jurnal', '=', 'ju.no_jurnal')
            ->where('jd.kode_akun', 5001)
            ->whereMonth('ju.tanggal', $bulan)
            ->whereYear('ju.tanggal', $tahun)
            ->sum('jd.debit'); // Ganti 'debet' dengan nama kolom yang benar

        $selisih = $totalRealisasi - $totalPerkiraan;

        if ($selisih != 0) {
            $no_jurnal = JurnalHelper::generateNoJurnal();

            JurnalUmum::create([
                'no_jurnal' => $no_jurnal,
                'tanggal' => now()->toDateString(),
                'keterangan' => 'Penyesuaian Overhead Bulan ' . date('F Y', strtotime($periode)),
            ]);

            if ($selisih > 0) {
                // Laba: Debit Overhead Pabrik, Kredit Pendapatan Lain-lain
                JurnalDetail::create([
                    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                    'no_jurnal' => $no_jurnal,
                    'kode_akun' => JurnalHelper::$akunMap['overhead'],
                    'debit' => $selisih,
                    'kredit' => 0,
                ]);
                JurnalDetail::create([
                    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                    'no_jurnal' => $no_jurnal,
                    'kode_akun' => JurnalHelper::$akunMap['pendapatan_lain'],
                    'debit' => 0,
                    'kredit' => $selisih,
                ]);
            } else {
                // Rugi: Debit Beban Lain-lain, Kredit Overhead Pabrik
                JurnalDetail::create([
                    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                    'no_jurnal' => $no_jurnal,
                    'kode_akun' => JurnalHelper::$akunMap['beban_lain'],
                    'debit' => abs($selisih),
                    'kredit' => 0,
                ]);
                JurnalDetail::create([
                    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                    'no_jurnal' => $no_jurnal,
                    'kode_akun' => JurnalHelper::$akunMap['overhead'],
                    'debit' => 0,
                    'kredit' => abs($selisih),
                ]);
            }
        }

        return redirect()->route('overhead.index')->with('success', 'Data overhead & jurnal penyesuaian berhasil disimpan.');
    }

    public function ajaxOverhead(Request $request)
    {
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');

        // Hitung total penyusutan aset tetap bulan ini
        $asetTetap = \App\Models\AsetTetap::all();
        $totalPenyusutan = 0;
        foreach ($asetTetap as $aset) {
            if ($aset->umur_ekonomis > 0) {
                $penyusutan = ($aset->harga_perolehan - $aset->nilai_sisa) / ($aset->umur_ekonomis * 12);
                $totalPenyusutan += $penyusutan;
            }
        }

        // Ambil total biaya listrik dari jurnal detail dengan kode akun 5001 pada bulan penyesuaian
        $totalListrik = \DB::table('t_jurnal_detail as jd')
            ->join('t_jurnal_umum as ju', 'jd.no_jurnal', '=', 'ju.no_jurnal')
            ->where('jd.kode_akun', 5001)
            ->whereMonth('ju.tanggal', $bulan)
            ->whereYear('ju.tanggal', $tahun)
            ->sum('jd.debit');

        return response()->json([
            'totalPenyusutan' => intval(round($totalPenyusutan)),
            'totalListrik' => intval(round($totalListrik)),
        ]);
    }
}