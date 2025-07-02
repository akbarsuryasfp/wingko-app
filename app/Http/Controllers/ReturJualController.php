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
            ->leftJoin(DB::raw('(
                SELECT 
                    t_returjual_detail.no_returjual, 
                    GROUP_CONCAT(CONCAT(t_returjual_detail.jumlah_retur, "x ", t_produk.nama_produk) SEPARATOR ", ") as produk_jumlah
                FROM t_returjual_detail
                JOIN t_produk ON t_returjual_detail.kode_produk = t_produk.kode_produk
                GROUP BY t_returjual_detail.no_returjual
            ) as produk'), 't_returjual.no_returjual', '=', 'produk.no_returjual')
            ->select('t_returjual.*', 't_pelanggan.nama_pelanggan', 'produk.produk_jumlah');

        // Filter periode tanggal retur
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('t_returjual.tanggal_returjual', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('t_returjual.tanggal_returjual', '<=', $request->tanggal_akhir);
        }

        $returjual = $query->orderBy('t_returjual.no_returjual', $sort)->get();

        return view('returjual.index', compact('returjual'));
    }

    public function create()
    {
        // Ambil semua no_jual yang sudah pernah diretur
        $noJualSudahRetur = DB::table('t_returjual')->pluck('no_jual')->toArray();

        // Hanya tampilkan penjualan yang belum pernah diretur
        $penjualan = DB::table('t_penjualan')
            ->whereNotIn('no_jual', $noJualSudahRetur)
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

        return view('returjual.create', compact('no_returjual', 'penjualan', 'pelanggan', 'produk', 'penjualanDetail'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_returjual' => 'required|unique:t_returjual,no_returjual',
            'no_jual' => 'required',
            'tanggal_returjual' => 'required|date',
            'kode_pelanggan' => 'required',
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
            ->join('t_produk', 't_returjual_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_returjual_detail.no_returjual', $no_returjual) // Perbaikan di sini
            ->select(
                't_returjual_detail.kode_produk',
                't_produk.nama_produk',
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

        return view('returjual.edit', [
            'returjual' => $returjual,
            'penjualan' => $penjualan,
            'pelanggan' => $pelanggan,
            'produk' => $produk,
            'details' => $detailsArr,
            'penjualanDetail' => $penjualanDetail
        ]);
    }

    public function update(Request $request, $no_returjual)
    {
        $request->validate([
            'no_jual' => 'required',
            'tanggal_returjual' => 'required|date',
            'kode_pelanggan' => 'required',
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
            ->join('t_produk', 't_returjual_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_returjual_detail.no_returjual', $no_returjual) // Perbaikan di sini
            ->select(
                't_returjual_detail.*',
                't_produk.nama_produk'
            )
            ->get();

        return view('returjual.detail', compact('returjual', 'details'));
    }

    public function cetak($no_returjual)
    {
        $returjual = \App\Models\Returjual::with('pelanggan')->where('no_returjual', $no_returjual)->firstOrFail();

        // Ambil detail dengan join ke produk agar nama_produk selalu ada
        $details = \DB::table('t_returjual_detail')
            ->join('t_produk', 't_returjual_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_returjual_detail.no_returjual', $no_returjual)
            ->select('t_returjual_detail.*', 't_produk.nama_produk')
            ->get();

        return view('returjual.cetak', compact('returjual', 'details'));
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
            ->join('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_penjualan_detail.no_jual', $no_jual)
            ->select('t_penjualan_detail.kode_produk', 't_produk.nama_produk', 't_penjualan_detail.jumlah', 't_penjualan_detail.harga_satuan')
            ->get();

        $penjualan = \DB::table('t_penjualan')->where('no_jual', $no_jual)->first();

        return response()->json([
            'details' => $details,
            'kode_pelanggan' => $penjualan->kode_pelanggan ?? null
        ]);
    }
}