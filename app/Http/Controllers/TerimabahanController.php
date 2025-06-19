<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TerimaBahan;

class TerimaBahanController extends Controller
{
    public function index()
    {
        $terimabahan = \DB::table('t_terimabahan')
            ->leftJoin('t_supplier', 't_terimabahan.kode_supplier', '=', 't_supplier.kode_supplier')
            ->leftJoin('t_pembelian', 't_terimabahan.no_pembelian', '=', 't_pembelian.no_pembelian')
            ->leftJoin('t_order_beli', 't_terimabahan.no_order_beli', '=', 't_order_beli.no_order_beli')
            ->select(
                't_terimabahan.*',
                't_supplier.nama_supplier',
                't_pembelian.no_pembelian',
                't_order_beli.no_order_beli',
                't_order_beli.status as status_order',
                't_terimabahan.status as status_penerimaan'
            )
            ->orderBy('t_terimabahan.no_terima_bahan', 'asc')
            ->get();

        // Ambil detail untuk setiap penerimaan (jika perlu)
        foreach ($terimabahan as $item) {
            $item->details = \DB::table('t_terimab_detail')
                ->join('t_bahan', 't_terimab_detail.kode_bahan', '=', 't_bahan.kode_bahan')
                ->where('t_terimab_detail.no_terima_bahan', $item->no_terima_bahan)
                ->select('t_terimab_detail.*', 't_bahan.nama_bahan')
                ->get();
        }

        return view('terimabahan.index', compact('terimabahan'));
    }

    public function create(Request $request)
    {
        $last = DB::table('t_terimabahan')
            ->whereRaw("no_terima_bahan REGEXP '^TB[0-9]{6}$'")
            ->orderBy('no_terima_bahan', 'desc')
            ->first();

        $kode = $last
            ? 'TB' . str_pad((int)substr($last->no_terima_bahan, 2) + 1, 6, '0', STR_PAD_LEFT)
            : 'TB000001';

        // Ambil semua order yang BELUM diterima sepenuhnya
        $orderbeli = DB::table('t_order_beli')
            ->leftJoin('t_supplier', 't_order_beli.kode_supplier', '=', 't_supplier.kode_supplier')
            ->select('t_order_beli.*', 't_supplier.nama_supplier')
            ->orderBy('no_order_beli', 'asc')
            ->get()
            ->filter(function($order) {
                // Hitung total beli dan total masuk
                $details = DB::table('t_order_detail')->where('no_order_beli', $order->no_order_beli)->get();
                $noTerimaList = DB::table('t_terimabahan')->where('no_order_beli', $order->no_order_beli)->pluck('no_terima_bahan')->toArray();
                $semuaDiterima = true;
                foreach ($details as $detail) {
                    $masuk = 0;
                    if (!empty($noTerimaList)) {
                        $masuk = DB::table('t_terimab_detail')
                            ->where('kode_bahan', $detail->kode_bahan)
                            ->whereIn('no_terima_bahan', $noTerimaList)
                            ->sum('bahan_masuk');
                    }
                    if ($masuk < $detail->jumlah_beli) {
                        $semuaDiterima = false;
                    }
                }
                return !$semuaDiterima; // hanya tampilkan yang BELUM diterima sepenuhnya
            })->values();

        $order_selected = null;
        $order_details = [];
        if ($request->has('order')) {
            $order_selected = DB::table('t_order_beli')
                ->leftJoin('t_supplier', 't_order_beli.kode_supplier', '=', 't_supplier.kode_supplier')
                ->select('t_order_beli.*', 't_supplier.nama_supplier')
                ->where('no_order_beli', $request->order)
                ->first();

            $order_details = DB::table('t_order_detail')
                ->join('t_bahan', 't_order_detail.kode_bahan', '=', 't_bahan.kode_bahan')
                ->where('no_order_beli', $request->order)
                ->select('t_order_detail.*', 't_bahan.nama_bahan', 't_bahan.satuan')
                ->get();
        }

        return view('terimabahan.create', compact('kode', 'orderbeli', 'order_selected', 'order_details'));
    }

    public function store(Request $request)
    {
        // Simpan header
        $header = [
            'no_terima_bahan' => $request->no_terima_bahan,
            'no_order_beli' => $request->no_order_beli,
            'tanggal_terima' => $request->tanggal_terima,
            'kode_supplier' => $request->kode_supplier,
        ];
        \DB::table('t_terimabahan')->insert($header);

        $details = json_decode($request->detail_json, true);
        foreach ($details as $detail) {
            if (isset($detail['bahan_masuk']) && $detail['bahan_masuk'] > 0) {
                $no_terimab_detail = 'TD' . date('ymdHis') . rand(100,999);
                \DB::table('t_terimab_detail')->insert([
                    'no_terimab_detail' => $no_terimab_detail,
                    'no_terima_bahan'   => $request->no_terima_bahan,
                    'kode_bahan'        => $detail['kode_bahan'],
                    'bahan_masuk'       => $detail['bahan_masuk'],
                    'harga_beli'        => $detail['harga_beli'] ?? 0,
                    'total'             => $detail['total'] ?? 0,
                    'tanggal_exp'       => $detail['tanggal_exp'] ?? null,
                ]);

                // --- Tambahkan ke t_kartupersbahan ---
                $satuan = \DB::table('t_bahan')->where('kode_bahan', $detail['kode_bahan'])->value('satuan') ?? '';
                $lastId = \DB::table('t_kartupersbahan')->max('id');
                $nextId = $lastId ? $lastId + 1 : 1;

                \DB::table('t_kartupersbahan')->insert([
                    'id'           => $nextId,
                    'no_transaksi' => $request->no_terima_bahan,
                    'tanggal'      => $request->tanggal_terima,
                    'kode_bahan'   => $detail['kode_bahan'],
                    'masuk'        => $detail['bahan_masuk'],
                    'keluar'       => 0,
                    'harga'        => $detail['harga_beli'] ?? 0,
                    'satuan'       => $satuan,
                    'keterangan'   => 'Penerimaan Bahan',
                ]);
                app('App\Http\Controllers\BahanController')->updateStokBahan($detail['kode_bahan']);

            }
        }

        // Cek status penerimaan untuk order terkait
        if ($request->no_order_beli) {
            $order = \App\Models\OrderBeli::where('no_order_beli', $request->no_order_beli)->first();

            // Hitung status penerimaan
            $details = \DB::table('t_order_detail')->where('no_order_beli', $order->no_order_beli)->get();
            $noTerimaList = \DB::table('t_terimabahan')
                ->where('no_order_beli', $order->no_order_beli)
                ->pluck('no_terima_bahan')
                ->toArray();

            $semuaDiterima = true;
            $adaYangMasuk = false;

            foreach ($details as $detail) {
                $jumlah_beli = $detail->jumlah_beli;
                $masuk = 0;
                if (!empty($noTerimaList)) {
                    $masuk = \DB::table('t_terimab_detail')
                        ->where('kode_bahan', trim($detail->kode_bahan))
                        ->whereIn('no_terima_bahan', $noTerimaList)
                        ->sum('bahan_masuk');
                }
                if ($masuk < $jumlah_beli) {
                    $semuaDiterima = false;
                }
                if ($masuk > 0) {
                    $adaYangMasuk = true;
                }
            }

            // Perbaiki logika status
            if ($adaYangMasuk && $semuaDiterima) {
                $order->status = 'Diterima Sepenuhnya';
            } elseif ($adaYangMasuk) {
                $order->status = 'Diterima Sebagian';
            } else {
                $order->status = 'Disetujui';
            }
            $order->save();
        }

        return redirect()->route('terimabahan.index')->with('success', 'Penerimaan bahan berhasil disimpan!');
    }

    public function show($id)
    {
        $terima = DB::table('t_terimabahan')
            ->where('no_terima_bahan', $id)
            ->leftJoin('t_supplier', 't_terimabahan.kode_supplier', '=', 't_supplier.kode_supplier')
            ->select('t_terimabahan.*', 't_supplier.nama_supplier')
            ->first();

        $details = DB::table('t_terimab_detail')
            ->join('t_bahan', 't_terimab_detail.kode_bahan', '=', 't_bahan.kode_bahan')
            ->where('no_terima_bahan', $id)
            ->select('t_terimab_detail.*', 't_bahan.nama_bahan')
            ->get();

        return view('terimabahan.detail', compact('terima', 'details'));
    }

    public function getSisaOrder($no_order_beli)
    {
        $orderDetails = DB::table('t_order_detail')
            ->where('no_order_beli', $no_order_beli)
            ->get();

        $result = [];
        foreach ($orderDetails as $detail) {
            $totalMasuk = DB::table('t_terimab_detail')
                ->where('kode_bahan', $detail->kode_bahan)
                ->whereIn('no_terima_bahan', function ($q) use ($no_order_beli) {
                    $q->select('no_terima_bahan')
                      ->from('t_terimabahan')
                      ->where('no_order_beli', $no_order_beli);
                })
                ->sum('bahan_masuk');

            $result[] = [       
            'kode_bahan' => $detail->kode_bahan,
            'sisa' => max(0, $detail->jumlah_beli - $totalMasuk),
        ];
        }

        return response()->json($result);
    }

    public function edit($id)
{
    // Ambil data utama penerimaan bahan + join supplier supaya ada properti supplier
    $terimaBahan = DB::table('t_terimabahan')
        ->leftJoin('t_supplier', 't_terimabahan.kode_supplier', '=', 't_supplier.kode_supplier')
        ->where('no_terima_bahan', $id)
        ->select('t_terimabahan.*', 't_supplier.nama_supplier', 't_supplier.kode_supplier as supplier_kode_supplier')
        ->first();

    if (!$terimaBahan) {
        abort(404, "Data terima bahan tidak ditemukan.");
    }

    // Ambil detail bahan
$details = DB::table('t_terimab_detail')
    ->join('t_bahan', 't_terimab_detail.kode_bahan', '=', 't_bahan.kode_bahan')
    ->leftJoin('t_order_detail', function($join) use ($terimaBahan) {
        $join->on('t_terimab_detail.kode_bahan', '=', 't_order_detail.kode_bahan')
             ->where('t_order_detail.no_order_beli', '=', $terimaBahan->no_order_beli);
    })
    ->where('t_terimab_detail.no_terima_bahan', $id)
    ->select(
        't_terimab_detail.*',
        't_bahan.nama_bahan',
        't_order_detail.jumlah_beli as jumlah_order'
    )
    ->get();

$terimaBahan->details = $details;

    // Karena sekarang $terimaBahan tidak punya objek supplier, kita buat dummy objek supplier
    $terimaBahan->supplier = (object) [
        'nama_supplier' => $terimaBahan->nama_supplier,
        'kode_supplier' => $terimaBahan->supplier_kode_supplier,
    ];

    $details = DB::table('t_terimab_detail')
    ->join('t_bahan', 't_terimab_detail.kode_bahan', '=', 't_bahan.kode_bahan')
    ->where('no_terima_bahan', $id)
    ->select('t_terimab_detail.*', 't_bahan.nama_bahan')
    ->get();

    // Ambil data order beli untuk dropdown
    $orderbeli = DB::table('t_order_beli')
        ->join('t_supplier', 't_order_beli.kode_supplier', '=', 't_supplier.kode_supplier')
        ->select('t_order_beli.no_order_beli', 't_order_beli.kode_supplier', 't_supplier.nama_supplier', 't_order_beli.tanggal_order')
        ->get()
        ->map(function ($order) {
            $bahans = DB::table('t_order_detail')
                ->join('t_bahan', 't_order_detail.kode_bahan', '=', 't_bahan.kode_bahan')
                ->where('t_order_detail.no_order_beli', $order->no_order_beli)
                ->pluck('t_bahan.nama_bahan')->toArray();
            $order->ringkasan_bahan = implode(', ', $bahans);
            return $order;
        });

    return view('terimabahan.edit', compact('terimaBahan', 'orderbeli'));
}


    // Proses update data penerimaan bahan
    public function update(Request $request, $id)
    {
        $request->validate([
            'no_order_beli' => 'required|string',
            'tanggal_terima' => 'required|date',
            'kode_supplier' => 'required|string',
            'detail_json' => 'required|string',
        ]);

        $terimaBahan = TerimaBahan::findOrFail($id);

        // Update data utama
        $terimaBahan->no_order_beli = $request->no_order_beli;
        $terimaBahan->tanggal_terima = $request->tanggal_terima;
        $terimaBahan->kode_supplier = $request->kode_supplier;
        $terimaBahan->save();

        $detailData = json_decode($request->detail_json, true);

        // Hapus semua detail lama (pakai query builder)
        DB::table('t_terimab_detail')->where('no_terima_bahan', $id)->delete();

        // Insert detail baru
        foreach ($detailData as $detail) {
            DB::table('t_terimab_detail')->insert([
                'no_terimab_detail' => 'TD' . date('ymdHis') . rand(100,999),
                'no_terima_bahan'   => $id,
                'kode_bahan'        => $detail['kode_bahan'],
                'bahan_masuk'       => $detail['bahan_masuk'],
                'harga_beli'        => $detail['harga_beli'],
                'total'             => $detail['bahan_masuk'] * $detail['harga_beli'],
                'tanggal_exp'       => $detail['tanggal_exp'] ?? null,
            ]);
            // Update stok untuk bahan ini
            app('App\Http\Controllers\BahanController')->updateStokBahan($detail['kode_bahan']);
        }

        // Update status order beli hanya saat update
        if ($request->no_order_beli) {
            $order = \App\Models\OrderBeli::where('no_order_beli', $request->no_order_beli)->first();

            // Hitung status penerimaan
            $details = \DB::table('t_order_detail')->where('no_order_beli', $order->no_order_beli)->get();
            $noTerimaList = \DB::table('t_terimabahan')
                ->where('no_order_beli', $order->no_order_beli)
                ->pluck('no_terima_bahan')
                ->toArray();

            $semuaDiterima = true;
            $adaYangMasuk = false;

            foreach ($details as $detail) {
                $jumlah_beli = $detail->jumlah_beli;
                $masuk = 0;
                if (!empty($noTerimaList)) {
                    $masuk = \DB::table('t_terimab_detail')
                        ->where('kode_bahan', trim($detail->kode_bahan))
                        ->whereIn('no_terima_bahan', $noTerimaList)
                        ->sum('bahan_masuk');
                }
                if ($masuk < $jumlah_beli) {
                    $semuaDiterima = false;
                }
                if ($masuk > 0) {
                    $adaYangMasuk = true;
                }
            }

            // Perbaiki logika status
            if ($adaYangMasuk && $semuaDiterima) {
                $order->status = 'Diterima Sepenuhnya';
            } elseif ($adaYangMasuk) {
                $order->status = 'Diterima Sebagian';
            } else {
                $order->status = 'Disetujui';
            }
            $order->save();
        }

        return redirect()->route('terimabahan.index')->with('success', 'Data penerimaan bahan berhasil diupdate.');
    }

    public function destroy($id)
    {
        // Ambil semua kode_bahan yang akan dihapus
        $kode_bahan_list = DB::table('t_terimab_detail')
            ->where('no_terima_bahan', $id)
            ->pluck('kode_bahan')
            ->toArray();

        // Hapus detail kartu stok terkait penerimaan ini
        DB::table('t_kartupersbahan')->where('no_transaksi', $id)->where('keterangan', 'Penerimaan Bahan')->delete();

        // Hapus detail penerimaan
        DB::table('t_terimab_detail')->where('no_terima_bahan', $id)->delete();

        // Ambil data header sebelum dihapus untuk dapatkan no_order_beli
        $header = DB::table('t_terimabahan')->where('no_terima_bahan', $id)->first();

        // Hapus header
        $terimaBahan = TerimaBahan::findOrFail($id);
        $terimaBahan->delete();

        // Jika ada no_order_beli, update status order beli
        if ($header && $header->no_order_beli) {
            $order = \App\Models\OrderBeli::where('no_order_beli', $header->no_order_beli)->first();

            $details = \DB::table('t_order_detail')->where('no_order_beli', $order->no_order_beli)->get();
            $noTerimaList = \DB::table('t_terimabahan')
                ->where('no_order_beli', $order->no_order_beli)
                ->pluck('no_terima_bahan')
                ->toArray();

            $semuaDiterima = true;
            $adaYangMasuk = false;

            foreach ($details as $detail) {
                $jumlah_beli = $detail->jumlah_beli;
                $masuk = 0;
                if (!empty($noTerimaList)) {
                    $masuk = \DB::table('t_terimab_detail')
                        ->where('kode_bahan', trim($detail->kode_bahan))
                        ->whereIn('no_terima_bahan', $noTerimaList)
                        ->sum('bahan_masuk');
                }
                if ($masuk < $jumlah_beli) {
                    $semuaDiterima = false;
                }
                if ($masuk > 0) {
                    $adaYangMasuk = true;
                }
            }

            // Perbaiki logika status
            if ($adaYangMasuk && $semuaDiterima) {
                $order->status = 'Diterima Sepenuhnya';
            } elseif ($adaYangMasuk) {
                $order->status = 'Diterima Sebagian';
            } else {
                $order->status = 'Disetujui';
            }
            $order->save();
        }

        // Update stok untuk semua bahan yang terlibat
        foreach ($kode_bahan_list as $kode_bahan) {
            app('App\Http\Controllers\BahanController')->updateStokBahan($kode_bahan);
        }

        return redirect()->route('terimabahan.index')->with('success', 'Data penerimaan bahan berhasil dihapus.');
    }
}