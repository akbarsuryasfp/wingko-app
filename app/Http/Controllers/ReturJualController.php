<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturJualController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'asc');

        $query = DB::table('t_returjual')
            ->leftJoin('t_pelanggan', 't_returjual.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->select('t_returjual.*', 't_pelanggan.nama_pelanggan');

        // Filter periode tanggal retur
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('t_returjual.tanggal_returjual', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('t_returjual.tanggal_returjual', '<=', $request->tanggal_akhir);
        }

        // Filter jenis retur
        if ($request->filled('jenis_retur')) {
            $query->where('t_returjual.jenis_retur', $request->jenis_retur);
        }

        // Search by no_returjual or nama_pelanggan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('t_returjual.no_returjual', 'like', "%$search%")
                  ->orWhere('t_pelanggan.nama_pelanggan', 'like', "%$search%");
            });
        }

        $returjual = $query->orderBy('t_returjual.no_returjual', $sort)->get();

        $allDetails = DB::table('t_returjual_detail')
            ->leftJoin('t_produk', 't_returjual_detail.kode_produk', '=', 't_produk.kode_produk')
            ->leftJoin('t_produk_konsinyasi', 't_returjual_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->select(
                't_returjual_detail.no_returjual',
                't_returjual_detail.jumlah_retur',
                DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                't_returjual_detail.alasan'
            )
            ->get()
            ->groupBy('no_returjual');

        foreach ($returjual as $rj) {
            $key = trim((string) $rj->no_returjual);
            $details = $allDetails[$key] ?? [];
            $produkList = [];
            foreach ($details as $detail) {
                $produkText = "<b>{$detail->jumlah_retur}</b> x {$detail->nama_produk}";
                if (!empty($detail->alasan)) {
                    $produkText .= " ({$detail->alasan})";
                }
                $produkList[] = $produkText;
            }
            $rj->produk_jumlah = !empty($produkList) ? implode('<br>', $produkList) : '-';
        }

        $jenisList = ['Barang', 'Uang'];
        return view('returjual.index', compact('returjual', 'jenisList'));
    }
    public function cetakLaporan(Request $request)
    {
        $sort = $request->get('sort', 'asc');

        $query = DB::table('t_returjual')
            ->leftJoin('t_pelanggan', 't_returjual.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->select('t_returjual.*', 't_pelanggan.nama_pelanggan');

        // Filter periode tanggal retur
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('t_returjual.tanggal_returjual', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('t_returjual.tanggal_returjual', '<=', $request->tanggal_akhir);
        }

        // Filter jenis retur
        if ($request->filled('jenis_retur')) {
            $query->where('t_returjual.jenis_retur', $request->jenis_retur);
        }

        // Search by no_returjual or nama_pelanggan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('t_returjual.no_returjual', 'like', "%$search%")
                  ->orWhere('t_pelanggan.nama_pelanggan', 'like', "%$search%");
            });
        }

        $returjual = $query->orderBy('t_returjual.no_returjual', $sort)->get();

        $allDetails = DB::table('t_returjual_detail')
            ->leftJoin('t_produk', 't_returjual_detail.kode_produk', '=', 't_produk.kode_produk')
            ->leftJoin('t_produk_konsinyasi', 't_returjual_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->select(
                't_returjual_detail.no_returjual',
                't_returjual_detail.jumlah_retur',
                DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                DB::raw('COALESCE(t_produk.satuan, t_produk_konsinyasi.satuan) as satuan'),
                't_returjual_detail.harga_satuan',
                't_returjual_detail.alasan',
                't_returjual_detail.subtotal'
            )
            ->get()
            ->groupBy('no_returjual');

        // Tidak perlu lagi membangun produk_jumlah, detail sudah lengkap untuk cetak_laporan

        return view('returjual.cetak_laporan', compact('returjual'));
    }

    public function create()
    {
        // Ambil semua no_jual yang sudah pernah diretur
        $noJualSudahRetur = DB::table('t_returjual')->pluck('no_jual')->toArray();

        // Hanya tampilkan penjualan yang belum pernah diretur dan tanggal jual < 2x24 jam dari sekarang
        $penjualan = DB::table('t_penjualan')
            ->whereNotIn('no_jual', $noJualSudahRetur)
            ->where('tanggal_jual', '>=', now()->subDays(2)->toDateString())
            ->get();

        // Generate kode returjual otomatis
        $last = DB::table('t_returjual')->orderBy('no_returjual', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->no_returjual, 2));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $no_returjual = 'RJ' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        $pelanggan = DB::table('t_pelanggan')->get();
        $produk = DB::table('t_produk')->get();

        // Ambil detail penjualan untuk semua penjualan yang bisa diretur
        $penjualanDetail = [];
        foreach ($penjualan as $pj) {
            $details = DB::table('t_penjualan_detail')
                ->where('no_jual', $pj->no_jual)
                ->get()
                ->keyBy('kode_produk');
            $penjualanDetail[$pj->no_jual] = $details;
        }

        $jenisList = ['Penjualan', 'Pengembalian'];
        return view('returjual.create', compact('no_returjual', 'penjualan', 'pelanggan', 'produk', 'penjualanDetail', 'jenisList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_returjual' => 'required|unique:t_returjual,no_returjual',
            'no_jual' => 'required',
            'tanggal_returjual' => 'required|date',
            'kode_pelanggan' => 'required',
            'jenis_retur' => 'required',
            'total_nilai_retur' => 'required|numeric',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json'
        ]);

        $details = json_decode($request->detail_json, true);
        // Ambil data penjualan detail
        $penjualanDetail = DB::table('t_penjualan_detail')
            ->where('no_jual', $request->no_jual)
            ->get()
            ->keyBy('kode_produk');

        foreach ($details as $detail) {
            $max = $penjualanDetail[$detail['kode_produk']]->jumlah ?? 0;
            if ($detail['jumlah_retur'] > $max) {
                return back()->withErrors(['Jumlah retur untuk produk ' . $detail['kode_produk'] . ' melebihi jumlah penjualan (' . $max . ')'])->withInput();
            }
        }

        DB::transaction(function () use ($request, $details) {
            DB::table('t_returjual')->insert([
                'no_returjual' => $request->no_returjual,
                'no_jual' => $request->no_jual,
                'tanggal_returjual' => $request->tanggal_returjual,
                'kode_pelanggan' => $request->kode_pelanggan,
                'jenis_retur' => $request->jenis_retur,
                'total_nilai_retur' => $request->total_nilai_retur,
                'keterangan' => $request->keterangan,
            ]);

            foreach ($details as $i => $detail) {
                DB::table('t_returjual_detail')->insert([
                    'no_detailreturjual' => $request->no_returjual . '-' . ($i+1),
                    'no_returjual' => $request->no_returjual,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah_retur' => $detail['jumlah_retur'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'alasan' => $detail['alasan'] ?? '', // <-- tambahkan ini
                    'subtotal' => $detail['subtotal'] ?? ($detail['jumlah_retur'] * $detail['harga_satuan']), // <-- tambahkan ini
                ]);
            }
        });

        return redirect()->route('returjual.index')->with('success', 'Retur Penjualan berhasil disimpan!');
    }

    public function edit($no_returjual)
    {
        $returjual = DB::table('t_returjual')->where('no_returjual', $no_returjual)->first();
        $penjualan = DB::table('t_penjualan')->get();
        $pelanggan = DB::table('t_pelanggan')->get();
        $produk = DB::table('t_produk')->get();

        $details = DB::table('t_returjual_detail')
            ->leftJoin('t_produk', 't_returjual_detail.kode_produk', '=', 't_produk.kode_produk')
            ->leftJoin('t_produk_konsinyasi', 't_returjual_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->where('t_returjual_detail.no_returjual', $no_returjual)
            ->select(
                't_returjual_detail.kode_produk',
                DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                DB::raw('COALESCE(t_produk.satuan, t_produk_konsinyasi.satuan) as satuan'),
                't_returjual_detail.jumlah_retur',
                't_returjual_detail.harga_satuan',
                't_returjual_detail.alasan',
                't_returjual_detail.subtotal'
            )
            ->get();

        $detailsArr = [];
        foreach ($details as $d) {
            $detailsArr[] = [
                'kode_produk' => $d->kode_produk,
                'nama_produk' => $d->nama_produk,
                'satuan' => $d->satuan,
                'jumlah_retur' => $d->jumlah_retur,
                'harga_satuan' => $d->harga_satuan,
                'alasan' => $d->alasan,
                'subtotal' => $d->subtotal,
            ];
        }

        // Ambil detail penjualan dari no_jual terkait
        $penjualanDetail = DB::table('t_penjualan_detail')
            ->where('no_jual', $returjual->no_jual)
            ->get()
            ->keyBy('kode_produk');

        $jenisList = ['Penjualan', 'Pengembalian'];
        return view('returjual.edit', [
            'returjual' => $returjual,
            'penjualan' => $penjualan,
            'pelanggan' => $pelanggan,
            'produk' => $produk,
            'details' => $detailsArr,
            'penjualanDetail' => $penjualanDetail,
            'jenisList' => $jenisList
        ]);
    }

    public function update(Request $request, $no_returjual)
    {
        $request->validate([
            'no_jual' => 'required',
            'tanggal_returjual' => 'required|date',
            'kode_pelanggan' => 'required',
            'jenis_retur' => 'required',
            'total_nilai_retur' => 'required|numeric',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json'
        ]);

        $details = json_decode($request->detail_json, true);
        $penjualanDetail = DB::table('t_penjualan_detail')
            ->where('no_jual', $request->no_jual)
            ->get()
            ->keyBy('kode_produk');

        foreach ($details as $detail) {
            $max = $penjualanDetail[$detail['kode_produk']]->jumlah ?? 0;
            if ($detail['jumlah_retur'] > $max) {
                return back()->withErrors(['Jumlah retur untuk produk ' . $detail['kode_produk'] . ' melebihi jumlah penjualan (' . $max . ')'])->withInput();
            }
        }

        DB::transaction(function () use ($request, $no_returjual, $details) {
            DB::table('t_returjual')->where('no_returjual', $no_returjual)->update([
                'no_jual' => $request->no_jual,
                'tanggal_returjual' => $request->tanggal_returjual,
                'kode_pelanggan' => $request->kode_pelanggan,
                'jenis_retur' => $request->jenis_retur,
                'total_nilai_retur' => $request->total_nilai_retur,
                'keterangan' => $request->keterangan,
            ]);

            DB::table('t_returjual_detail')->where('no_returjual', $no_returjual)->delete(); // Perbaikan di sini

            foreach ($details as $i => $detail) {
                DB::table('t_returjual_detail')->insert([
                    'no_detailreturjual' => $no_returjual . '-' . ($i+1),
                    'no_returjual' => $no_returjual,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah_retur' => $detail['jumlah_retur'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'alasan' => $detail['alasan'] ?? '', // <-- tambahkan ini
                    'subtotal' => $detail['subtotal'] ?? ($detail['jumlah_retur'] * $detail['harga_satuan']), // <-- tambahkan ini
                ]);
            }
        });

        return redirect()->route('returjual.index')->with('success', 'Retur Penjualan berhasil diupdate!');
    }

    public function destroy($no_returjual)
    {
        DB::transaction(function () use ($no_returjual) {
            DB::table('t_returjual_detail')->where('no_returjual', $no_returjual)->delete(); // Perbaikan di sini
            DB::table('t_returjual')->where('no_returjual', $no_returjual)->delete();
        });

        return redirect()->route('returjual.index')->with('success', 'Retur Penjualan berhasil dihapus!');
    }

    public function show($no_returjual)
    {
        $returjual = DB::table('t_returjual')
            ->leftJoin('t_pelanggan', 't_returjual.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->where('no_returjual', $no_returjual)
            ->select('t_returjual.*', 't_pelanggan.nama_pelanggan')
            ->first();

        $details = DB::table('t_returjual_detail')
            ->leftJoin('t_produk', 't_returjual_detail.kode_produk', '=', 't_produk.kode_produk')
            ->leftJoin('t_produk_konsinyasi', 't_returjual_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->where('t_returjual_detail.no_returjual', $no_returjual)
            ->select(
                't_returjual_detail.*',
                DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                DB::raw('COALESCE(t_produk.satuan, t_produk_konsinyasi.satuan) as satuan')
            )
            ->get();

        return view('returjual.detail', compact('returjual', 'details'));
    }

    public function cetak($no_returjual)
    {
        $returjual = \App\Models\Returjual::with('pelanggan')->where('no_returjual', $no_returjual)->firstOrFail();

        // Ambil detail dengan join ke produk agar nama_produk selalu ada
        $details = \DB::table('t_returjual_detail')
            ->leftJoin('t_produk', 't_returjual_detail.kode_produk', '=', 't_produk.kode_produk')
            ->leftJoin('t_produk_konsinyasi', 't_returjual_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->where('t_returjual_detail.no_returjual', $no_returjual)
            ->select(
                't_returjual_detail.*',
                \DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                \DB::raw('COALESCE(t_produk.satuan, t_produk_konsinyasi.satuan) as satuan')
            )
            ->get();

        // Generate PDF langsung stream, ukuran A5 landscape
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('returjual.cetak', compact('returjual', 'details'));
        $pdf->setPaper('A5', 'landscape');
        return $pdf->stream('nota_retur_penjualan.pdf');
    }

    public function filterPenjualan(Request $request)
    {
        $kode_produk = $request->kode_produk;
        $penjualan = DB::table('t_penjualan')
            ->join('t_penjualan_detail', 't_penjualan.no_jual', '=', 't_penjualan_detail.no_jual')
            ->join('t_pelanggan', 't_penjualan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->where('t_penjualan_detail.kode_produk', $kode_produk)
            ->select('t_penjualan.no_jual', 't_penjualan.tanggal_jual', 't_pelanggan.nama_pelanggan')
            ->distinct()
            ->get();

        return response()->json($penjualan);
    }

    public function getDetailPenjualan($no_jual)
    {
        $details = \DB::table('t_penjualan_detail')
            ->leftJoin('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->leftJoin('t_produk_konsinyasi', 't_penjualan_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->where('t_penjualan_detail.no_jual', $no_jual)
            ->select(
                't_penjualan_detail.kode_produk',
                \DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                't_penjualan_detail.jumlah',
                't_penjualan_detail.harga_satuan',
                \DB::raw('COALESCE(t_produk.satuan, t_produk_konsinyasi.satuan) as satuan')
            )
            ->get();

        $penjualan = \DB::table('t_penjualan')->where('no_jual', $no_jual)->first();

        return response()->json([
            'details' => $details,
            'kode_pelanggan' => $penjualan->kode_pelanggan ?? null
        ]);
    }

        /**
     * Cetak laporan retur penjualan sebagai PDF (stream, bukan download)
     */
    public function cetakLaporanPdf(Request $request)
    {
        $sort = $request->get('sort', 'asc');

        $query = DB::table('t_returjual')
            ->leftJoin('t_pelanggan', 't_returjual.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->select('t_returjual.*', 't_pelanggan.nama_pelanggan');

        // Filter periode tanggal retur
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('t_returjual.tanggal_returjual', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('t_returjual.tanggal_returjual', '<=', $request->tanggal_akhir);
        }

        // Filter jenis retur
        if ($request->filled('jenis_retur')) {
            $query->where('t_returjual.jenis_retur', $request->jenis_retur);
        }

        // Search by no_returjual or nama_pelanggan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('t_returjual.no_returjual', 'like', "%$search%")
                  ->orWhere('t_pelanggan.nama_pelanggan', 'like', "%$search%");
            });
        }

        $returjual = $query->orderBy('t_returjual.no_returjual', $sort)->get();

        // View sudah handle query detail per row
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('returjual.cetak_laporan', compact('returjual'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('laporan-retur-penjualan.pdf');
    }

}