<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JurnalHelper;
use Carbon\Carbon;

class KasKeluarController extends Controller
{
// In your KasKeluarController.php
public function index(Request $request)
{
    $startDate = $request->input('tanggal_awal', date('Y-m-01'));
    $endDate = $request->input('tanggal_akhir', date('Y-m-d'));
    $filterPenerima = $request->filter_penerima;
    $search = $request->search;

    $perPage = $request->input('per_page', 10);

    $query = DB::table('t_jurnal_umum as ju')
        ->join('t_jurnal_detail as jd', function($join) {
            $join->on('ju.no_jurnal', '=', 'jd.no_jurnal')
                 ->where('jd.kredit', '>', 0)
                 ->where('jd.kode_akun', '=', JurnalHelper::getKodeAkun('kas_bank'));
        })
        ->where('ju.jenis_jurnal', 'umum')
        ->where('ju.nomor_bukti', 'like', 'BKK%')
        ->whereDate('ju.tanggal', '>=', $startDate)
        ->whereDate('ju.tanggal', '<=', $endDate)
        ->where(function($query) {
            $query->whereNotNull('ju.keterangan')
                  ->where('ju.keterangan', '<>', '')
                  ->where('ju.keterangan', 'not like', '%Pembayaran utang%')
                  ->where('ju.keterangan', 'not like', '%Uang muka%');
        })
        ->select([
            'ju.no_jurnal',
            'ju.tanggal',
            'ju.nomor_bukti',
            'ju.keterangan',
            'jd.kredit as jumlah'
        ])
        ->orderBy('ju.tanggal', 'desc');

    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('ju.keterangan', 'like', "%$search%");
        });
    }

    if ($perPage == 'all') {
        $kaskeluar = $query->get();
    } else {
        $kaskeluar = $query->paginate($perPage)->withQueryString();
    }

    // Proses data setelah paginate/get
    foreach ($kaskeluar as $row) {
        $row->nomor_bukti = preg_replace('/\.\d+E\+\d+/', '', $row->nomor_bukti);
        $keterangan = trim($row->keterangan);
        $row->keterangan_teks = $keterangan;
        $row->penerima = '-';

        if (str_contains(strtolower($keterangan), 'upah lembur')) {
            $row->keterangan_teks = 'Upah Lembur';
            $row->penerima = 'Karyawan';
        } elseif (str_contains($keterangan, '|')) {
            $parts = array_map('trim', explode('|', $keterangan));
            if (count($parts) >= 3) {
                $row->keterangan_teks = $parts[1];
                $row->penerima = $parts[2];
            } elseif (count($parts) == 2) {
                $row->keterangan_teks = $parts[0];
                $row->penerima = $parts[1];
            }
        } elseif (preg_match('/^(.*?)\s+([^\s]+)$/', $keterangan, $matches)) {
            $row->keterangan_teks = trim($matches[1]);
            $row->penerima = trim($matches[2]);
        }

        $row->jumlah_rupiah = 'Rp ' . number_format($row->jumlah, 0, ',', '.');
    }

    return view('kaskeluar.index', compact(
        'kaskeluar',
        'startDate',
        'endDate',
        'filterPenerima',
        'search'
    ));
}


    public function create()
    {

        $today = date('Ymd');
$last = \DB::table('t_jurnal_umum')
    ->where('nomor_bukti', 'like', 'BKK'.$today.'-%')
    ->selectRaw('MAX(CAST(SUBSTRING_INDEX(nomor_bukti, "-", -1) AS UNSIGNED)) as max_bkk')
    ->first();
$next = ($last && $last->max_bkk) ? $last->max_bkk + 1 : 1;
$no_BKK = 'BKK' . $today . '-' . $next;

        $akun = DB::table('t_akun')->where('kode_akun', '!=', JurnalHelper::getKodeAkun('kas_bank'))->get();
        return view('kaskeluar.create', compact('no_BKK', 'akun'));
    }

public function store(Request $request)
{
        \Log::info('Store KasKeluar dipanggil', $request->all());
    $request->validate([
        'no_BKK'      => 'required|string|unique:t_jurnal_umum,nomor_bukti',
        'tanggal'     => 'required|date',
        'kas_digunakan' => 'required|in:kas_bank,kas_kecil',
        'kode_akun'   => 'required|string',
        'jumlah'      => 'required|numeric|min:1',
        'penerima'    => 'required|string',
        'no_referensi'=> 'nullable|string',
        'keterangan'  => 'nullable|string',
        'bukti_nota' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ]);

    $no_jurnal = 'JU-' . date('YmdHis') . '-' . rand(100,999);
    $keterangan = ($request->no_referensi ?? '') . ' | ' . ($request->keterangan ?? '') . ' | ' . $request->penerima;

    $bukti_nota_path = null;
    if ($request->hasFile('bukti_nota')) {
        $bukti_nota_path = $request->file('bukti_nota')->store('bukti_kaskeluar', 'public');
    }

    DB::beginTransaction();
    try {
        DB::table('t_jurnal_umum')->insert([
            'no_jurnal'   => $no_jurnal,
            'tanggal'     => $request->tanggal,
            'keterangan'  => $keterangan,
            'nomor_bukti' => $request->no_BKK,
            'jenis_jurnal' => 'umum',
        ]);

        $no_jurnal_detail1 = 'JD-' . date('YmdHis') . '-' . rand(100,999);
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => $no_jurnal_detail1,
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => $request->kode_akun,
            'debit'            => $request->jumlah,
            'kredit'           => 0,
        ]);

        $no_jurnal_detail2 = 'JD-' . date('YmdHis') . '-' . rand(100,999);
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => $no_jurnal_detail2,
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => JurnalHelper::getKodeAkun($request->kas_digunakan),
            'debit'            => 0,
            'kredit'           => $request->jumlah,
        ]);

        // Simpan ke t_buktikaskeluar
        DB::table('t_buktikaskeluar')->insert([
            'no_jurnal'         => $no_jurnal,
            'bukti_nota'        => $bukti_nota_path,
        ]);

        DB::commit();
        return redirect()->route('kaskeluar.index')->with('success', 'Kas keluar berhasil disimpan.');
    } catch (\Exception $e) {
        DB::rollBack();
        dd($e->getMessage());
    }
}


public function edit($id)
{
    $kas = DB::table('t_jurnal_umum')->where('no_jurnal', $id)->first();

    // Ambil detail akun lawan (debit) dan jumlah
    $detail = DB::table('t_jurnal_detail')
        ->where('no_jurnal', $id)
        ->where('kredit', 0)
        ->first();

    if ($detail) {
        $kas->kode_akun = $detail->kode_akun;
        $kas->jumlah = $detail->debit;
    } else {
        $kas->kode_akun = null;
        $kas->jumlah = null;
    }

    // Ambil detail kas (kredit) untuk menentukan kas_digunakan
    $detail_kas = DB::table('t_jurnal_detail')
        ->where('no_jurnal', $id)
        ->where('debit', 0)
        ->whereIn('kode_akun', [
            JurnalHelper::getKodeAkun('kas_bank'),
            JurnalHelper::getKodeAkun('kas_kecil')
        ])
        ->first();

    if ($detail_kas) {
        if ($detail_kas->kode_akun == JurnalHelper::getKodeAkun('kas_kecil')) {
            $kas->kas_digunakan = 'kas_kecil';
        } else {
            $kas->kas_digunakan = 'kas_bank';
        }
    } else {
        $kas->kas_digunakan = null;
    }

    // Ekstrak info tambahan dari keterangan
    $keteranganArr = explode('|', $kas->keterangan);
    $kas->no_referensi = isset($keteranganArr[0]) ? trim($keteranganArr[0]) : '';
    $kas->keterangan_teks = isset($keteranganArr[1]) ? trim($keteranganArr[1]) : '';
    $kas->penerima = isset($keteranganArr[2]) ? trim($keteranganArr[2]) : '';

    $akun = DB::table('t_akun')->where('kode_akun', '!=', JurnalHelper::getKodeAkun('kas_bank'))->get();

    return view('kaskeluar.edit', compact('kas', 'akun'));
}

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal'     => 'required|date',
            'kode_akun'   => 'required|string',
            'jumlah'      => 'required|numeric|min:1',
            'penerima'    => 'required|string',
            'no_referensi'=> 'nullable|string',
            'keterangan'  => 'nullable|string',
            'bukti_nota'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $keterangan = ($request->no_referensi ?? '') . ' | ' . ($request->keterangan ?? '') . ' | ' . $request->penerima;

        // Update t_jurnal_umum
        DB::table('t_jurnal_umum')->where('no_jurnal', $id)->update([
            'tanggal'    => $request->tanggal,
            'keterangan' => $keterangan,
        ]);

        // Update t_jurnal_detail (debit akun lawan)
        DB::table('t_jurnal_detail')
            ->where('no_jurnal', $id)
            ->where('kredit', 0)
            ->update([
                'kode_akun' => $request->kode_akun,
                'debit'     => $request->jumlah,
            ]);

        // Update t_jurnal_detail (kredit kas)
        DB::table('t_jurnal_detail')
            ->where('no_jurnal', $id)
            ->whereIn('kode_akun', [
                JurnalHelper::getKodeAkun('kas_bank'),
                JurnalHelper::getKodeAkun('kas_kecil')
            ])
            ->update([
                'kode_akun' => JurnalHelper::getKodeAkun($request->kas_digunakan),
                'kredit'    => $request->jumlah,
                'debit'     => 0,
            ]);

        // Update bukti nota jika ada upload baru
        if ($request->hasFile('bukti_nota')) {
            $bukti_nota_path = $request->file('bukti_nota')->store('bukti_kaskeluar', 'public');
            DB::table('t_buktikaskeluar')->updateOrInsert(
                ['no_jurnal' => $id],
                ['bukti_nota' => $bukti_nota_path]
            );
        }

        return redirect()->route('kaskeluar.index')->with('success', 'Kas keluar berhasil diupdate.');
    }

    public function destroy($id)
    {
        // Hapus detail dulu
        DB::table('t_jurnal_detail')->where('no_jurnal', $id)->delete();
        // Hapus header
        DB::table('t_jurnal_umum')->where('no_jurnal', $id)->delete();

        return redirect()->route('kaskeluar.index')->with('success', 'Kas keluar berhasil dihapus.');
    }
    public function show($id)
{
    // Tidak digunakan
    abort(404);
}

public function laporan(Request $request)
{
    $startDate = $request->input('start_date', date('Y-m-01'));
    $endDate = $request->input('end_date', date('Y-m-d'));
    $filterPenerima = $request->filter_penerima;
    $search = $request->search;

    $queryResults = DB::table('t_jurnal_umum as ju')
        ->join('t_jurnal_detail as jd', function($join) {
            $join->on('ju.no_jurnal', '=', 'jd.no_jurnal')
                 ->where('jd.kredit', '>', 0)
                 ->where('jd.kode_akun', '=', JurnalHelper::getKodeAkun('kas_bank'));
        })
        ->where('ju.jenis_jurnal', 'umum')
        ->where('ju.nomor_bukti', 'like', 'BKK%')
        ->whereDate('ju.tanggal', '>=', $startDate)
        ->whereDate('ju.tanggal', '<=', $endDate)
        ->where(function($query) {
            $query->whereNotNull('ju.keterangan')
                  ->where('ju.keterangan', '<>', '')
                  ->where('ju.keterangan', 'not like', '%Pembayaran utang%')
                  ->where('ju.keterangan', 'not like', '%Uang muka%');
        })
        ->select([
            'ju.no_jurnal',
            'ju.tanggal',
            'ju.nomor_bukti',
            'ju.keterangan',
            'jd.kredit as jumlah'
        ])
        ->orderBy('ju.tanggal', 'asc')
        ->get();

    $kaskeluar = [];
    foreach ($queryResults as $row) {
        $row->nomor_bukti = preg_replace('/\.\d+E\+\d+/', '', $row->nomor_bukti);
        $keterangan = trim($row->keterangan);
        $row->keterangan_teks = $keterangan;
        $row->penerima = '-';

        if (str_contains(strtolower($keterangan), 'upah lembur')) {
            $row->keterangan_teks = 'Upah Lembur';
            $row->penerima = 'Karyawan';
        } elseif (str_contains($keterangan, '|')) {
            $parts = array_map('trim', explode('|', $keterangan));
            if (count($parts) >= 3) {
                $row->keterangan_teks = $parts[1];
                $row->penerima = $parts[2];
            } elseif (count($parts) == 2) {
                $row->keterangan_teks = $parts[0];
                $row->penerima = $parts[1];
            }
        } elseif (preg_match('/^(.*?)\s+([^\s]+)$/', $keterangan, $matches)) {
            $row->keterangan_teks = trim($matches[1]);
            $row->penerima = trim($matches[2]);
        }

        $row->jumlah_rupiah = $row->jumlah;

        if ($filterPenerima && stripos($row->penerima, $filterPenerima) === false) {
            continue;
        }
        if ($search) {
            if (
                stripos($row->keterangan_teks, $search) === false &&
                stripos($row->penerima, $search) === false
            ) {
                continue;
            }
        }
        $kaskeluar[] = $row;
    }

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kaskeluar.laporan', [
        'kaskeluar' => $kaskeluar,
        'start_date' => $startDate,
        'end_date' => $endDate
    ]);
    return $pdf->stream('laporan-pengeluaran-kas-'.date('Ymd').'.pdf');
}
}