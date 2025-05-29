<?php

namespace App\Http\Controllers;

use App\Models\OrderBeli;
use App\Models\Supplier;
use App\Models\Bahan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class OrderBeliController extends Controller
{
    public function index()
{
    $orders = OrderBeli::with('supplier')->get();

    foreach ($orders as $order) {
        $details = \DB::table('t_order_detail')
            ->join('t_bahan', 't_order_detail.kode_bahan', '=', 't_bahan.kode_bahan')
            ->where('t_order_detail.no_order_beli', $order->no_order_beli)
            ->select(
                't_order_detail.kode_bahan',
                't_bahan.nama_bahan',
                't_bahan.satuan',
                't_order_detail.jumlah_beli',
                't_order_detail.harga_beli',
                't_order_detail.total'
            )
            ->get();

        // tambahkan manual ke objek $order
        $order->details = $details;
    }

    return view('orderbeli.index', compact('orders'));
}

    public function create()
    {
        // Generate kode order otomatis
        $last = OrderBeli::orderBy('no_order_beli', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->no_order_beli, 2));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $no_order_beli = 'OB' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        $suppliers = Supplier::all();
        $bahans = \App\Models\Bahan::all(); 

        return view('orderbeli.create', compact('no_order_beli', 'suppliers', 'bahans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_order_beli' => 'required|unique:t_order_beli,no_order_beli',
            'tanggal_order' => 'required|date',
            'kode_supplier' => 'required',
            'total_order' => 'required|numeric',
            'detail_json' => 'required'
        ]);

        // Simpan order utama
        $order = \App\Models\OrderBeli::create([
            'no_order_beli' => $request->no_order_beli,
            'tanggal_order' => $request->tanggal_order,
            'kode_supplier' => $request->kode_supplier,
            'total_order' => $request->total_order,
        ]);

        // Simpan detail bahan
        $details = json_decode($request->detail_json, true);
        foreach ($details as $i => $detail) {
            \DB::table('t_order_detail')->insert([
                'no_orderdetail' => $order->no_order_beli . '-' . ($i+1), // contoh generate no_orderdetail
                'no_order_beli' => $order->no_order_beli,
                'kode_bahan' => $detail['kode_bahan'],
                'harga_beli' => $detail['harga_beli'],
                'jumlah_beli' => $detail['jumlah_beli'],
                'total' => $detail['total'],
            ]);
        }

        return redirect()->route('orderbeli.index')->with('success', 'Order pembelian berhasil disimpan!');
    }

    public function setujui($no_order_beli)
    {
        $order = \App\Models\OrderBeli::findOrFail($no_order_beli);
        $order->status = 'Disetujui';
        $order->save();

        return redirect()->route('orderbeli.index')->with('success', 'Order berhasil disetujui!');
    }

    public function show($no_order_beli)
    {
        $order = OrderBeli::with('supplier')->findOrFail($no_order_beli);
        $details = \DB::table('t_order_detail')
            ->join('t_bahan', 't_order_detail.kode_bahan', '=', 't_bahan.kode_bahan')
            ->where('t_order_detail.no_order_beli', $no_order_beli)
            ->select(
                't_order_detail.kode_bahan',
                't_bahan.nama_bahan',
                't_bahan.satuan',
                't_order_detail.jumlah_beli',
                't_order_detail.harga_beli',
                't_order_detail.total'
            )
            ->get();

        return view('orderbeli.show', compact('order', 'details'));
    }

    

public function cetak($no_order_beli)
{
    $order = \App\Models\OrderBeli::with('supplier')->findOrFail($no_order_beli);

    $details = \DB::table('t_order_detail')
        ->join('t_bahan', 't_order_detail.kode_bahan', '=', 't_bahan.kode_bahan')
        ->where('t_order_detail.no_order_beli', $no_order_beli)
        ->select(
            't_order_detail.kode_bahan',
            't_bahan.nama_bahan',
            't_bahan.satuan',
            't_order_detail.jumlah_beli'
        )
        ->get();

    $pdf = Pdf::loadView('orderbeli.cetak', compact('order', 'details'));
    return $pdf->stream('order_pembelian_' . $order->no_order_beli . '.pdf');
}
public function simpanUangMuka(Request $request, $no_order_beli)
{
    $request->validate([
        'uang_muka' => 'required|numeric|min:0',
        'metode_bayar' => 'required|string|max:50',
    ]);

    $order = OrderBeli::where('no_order_beli', $no_order_beli)->firstOrFail();

    // Simpan uang muka dan metode bayar
    $order->uang_muka = $request->uang_muka;
    $order->metode_bayar = $request->metode_bayar;
    $order->save();

    return redirect()->route('orderbeli.index', $no_order_beli)
        ->with('success', 'Pembayaran uang muka berhasil disimpan.');
}
public function edit($no_order_beli)
{
    $order = OrderBeli::with('details')->where('no_order_beli', $no_order_beli)->firstOrFail();
    $suppliers = Supplier::all();
    $bahans = Bahan::all();

    // Ambil detail dengan join ke tabel bahan
    $details = \DB::table('t_order_detail')
        ->join('t_bahan', 't_order_detail.kode_bahan', '=', 't_bahan.kode_bahan')
        ->where('t_order_detail.no_order_beli', $no_order_beli)
        ->select(
            't_order_detail.kode_bahan',
            't_bahan.nama_bahan',
            't_bahan.satuan',
            't_order_detail.jumlah_beli',
            't_order_detail.harga_beli',
            \DB::raw('t_order_detail.jumlah_beli * t_order_detail.harga_beli as total')
        )
        ->get();

    return view('orderbeli.edit', compact('order', 'suppliers', 'bahans', 'details'));
}

public function update(Request $request, $no_order_beli)
{
    $request->validate([
        'tanggal_order' => 'required|date',
        'kode_supplier' => 'required',
        'total_order' => 'required|numeric',
        'detail_json' => 'required|json',
    ]);

    DB::transaction(function () use ($request, $no_order_beli) {
        $order = OrderBeli::where('no_order_beli', $no_order_beli)->firstOrFail();
        $order->update([
            'tanggal_order' => $request->tanggal_order,
            'kode_supplier' => $request->kode_supplier,
            'total_order' => $request->total_order,
        ]);

        // Hapus detail lama
        \DB::table('t_order_detail')->where('no_order_beli', $no_order_beli)->delete();

        // Simpan detail baru
        $details = json_decode($request->detail_json, true);
        foreach ($details as $detail) {
            \DB::table('t_order_detail')->insert([
                'no_order_beli' => $no_order_beli,
                'kode_bahan' => $detail['kode_bahan'],
                'jumlah_beli' => $detail['jumlah_beli'],
                'harga_beli' => $detail['harga_beli'],
                'total' => $detail['jumlah_beli'] * $detail['harga_beli'],
                // tambahkan kolom lain jika perlu
            ]);
        }
    });

    return redirect()->route('orderbeli.index')->with('success', 'Order berhasil diupdate!');
}
}