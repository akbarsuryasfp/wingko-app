<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TerimaBahan;

class TerimaBahanController extends Controller
{
    public function index()
    {
        $terimabahan = DB::table('t_terimabahan')
            ->leftJoin('t_supplier', 't_terimabahan.kode_supplier', '=', 't_supplier.kode_supplier')
            ->select('t_terimabahan.*')
            ->orderBy('t_terimabahan.tanggal_terima', 'desc')
            ->get();

        foreach ($terimabahan as $item) {
            $item->details = DB::table('t_terimab_detail')
                ->where('no_terima_bahan', $item->no_terima_bahan)
                ->get();
        }

        return view('terimabahan.index', compact('terimabahan'));
    }

    public function create()
    {
        $last = DB::table('t_terimabahan')->orderBy('no_terima_bahan', 'desc')->first();
        if ($last) {
            $num = (int)substr($last->no_terima_bahan, 2) + 1;
            $kode = 'TB' . str_pad($num, 6, '0', STR_PAD_LEFT);
        } else {
            $kode = 'TB000001';
        }

        $orderbeli = DB::table('t_order_beli')
            ->join('t_supplier', 't_order_beli.kode_supplier', '=', 't_supplier.kode_supplier')
            ->select('t_order_beli.no_order_beli', 't_order_beli.kode_supplier', 't_supplier.nama_supplier', 't_order_beli.tanggal_order')
            ->get()
            ->map(function($order) {
                $bahans = DB::table('t_order_detail')
                    ->join('t_bahan', 't_order_detail.kode_bahan', '=', 't_bahan.kode_bahan')
                    ->where('t_order_detail.no_order_beli', $order->no_order_beli)
                    ->pluck('t_bahan.nama_bahan')->toArray();
                $order->ringkasan_bahan = implode(', ', $bahans);
                return $order;
            });
        $supplier = DB::table('t_supplier')->get();
        $bahans = DB::table('t_bahan')->get();

        return view('terimabahan.create', compact('kode', 'orderbeli', 'supplier', 'bahans'));
    }

    public function store(Request $request)
    {
        \Log::info('REQUEST DATA', $request->all());
        \Log::info('DETAIL JSON', json_decode($request->detail_json, true));

        try {
            $request->validate([
                'no_terima_bahan' => 'required|unique:t_terimabahan,no_terima_bahan',
                'no_order_beli' => 'required',
                'tanggal_terima' => 'required|date',
                'kode_supplier' => 'required',
                'detail_json' => 'required|json'
            ]);

            DB::transaction(function () use ($request) {
                DB::table('t_terimabahan')->insert([
                    'no_terima_bahan' => $request->no_terima_bahan,
                    'no_order_beli' => $request->no_order_beli,
                    'tanggal_terima' => $request->tanggal_terima,
                    'kode_supplier' => $request->kode_supplier
                ]);

                $details = array_filter(json_decode($request->detail_json, true), function($d) {
                    return floatval($d['bahan_masuk']) > 0;
                });

                foreach ($details as $d) {
                    // Hitung total bahan masuk sebelumnya
                    $totalMasuk = DB::table('t_terimab_detail')
                        ->where('kode_bahan', $d['kode_bahan'])
                        ->whereIn('no_terima_bahan', function($q) use ($request) {
                            $q->select('no_terima_bahan')
                              ->from('t_terimabahan')
                              ->where('no_order_beli', $request->no_order_beli);
                        })
                        ->sum('bahan_masuk');
                    // Ambil jumlah order
                    $jumlahOrder = DB::table('t_order_detail')
                        ->where('no_order_beli', $request->no_order_beli)
                        ->where('kode_bahan', $d['kode_bahan'])
                        ->value('jumlah_beli');
                    // Validasi
                    if ($totalMasuk + $d['bahan_masuk'] > $jumlahOrder) {
                        throw new \Exception('Total bahan masuk melebihi jumlah order untuk bahan ' . $d['kode_bahan']);
                    }
                    DB::table('t_terimab_detail')->insert([
                        'no_terima_bahan' => $request->no_terima_bahan,
                        'kode_bahan' => $d['kode_bahan'],
                        'bahan_masuk' => $d['bahan_masuk'],
                        'harga_beli' => $d['harga_beli'],
                        'total' => $d['total'],
                        'tanggal_exp' => $d['tanggal_exp'],
                    ]);
                }
            });

            return redirect()->route('terimabahan.index')->with('success', 'Penerimaan bahan berhasil disimpan!');
        } catch (\Exception $e) {
            \Log::error('TerimaBahan ERROR: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $terima = TerimaBahan::find($id);
        $details = $terima->details();

        return view('terimabahan.show', compact('terima', 'details'));
    }

    // Endpoint untuk cek sisa bahan yang boleh diterima per order
    public function getSisaOrder($no_order_beli)
    {
        $orderDetails = DB::table('t_order_detail')
            ->where('no_order_beli', $no_order_beli)
            ->get();

        $result = [];
        foreach ($orderDetails as $detail) {
            $totalMasuk = DB::table('t_terimab_detail')
                ->where('kode_bahan', $detail->kode_bahan)
                ->whereIn('no_terima_bahan', function($q) use ($no_order_beli) {
                    $q->select('no_terima_bahan')
                      ->from('t_terimabahan')
                      ->where('no_order_beli', $no_order_beli);
                })
                ->sum('bahan_masuk');
            $result[] = [
                'kode_bahan' => $detail->kode_bahan,
                'jumlah_order' => $detail->jumlah_beli,
                'sisa' => $detail->jumlah_beli - $totalMasuk,
            ];
        }
        return response()->json($result);
    }
}