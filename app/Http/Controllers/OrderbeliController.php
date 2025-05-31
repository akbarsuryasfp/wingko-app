<?php

namespace App\Http\Controllers;

use App\Models\OrderBeli;
use App\Models\Supplier;
use App\Models\Bahan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\JadwalProduksi;

class OrderBeliController extends Controller
{
    public function index()
{
    $orders = \App\Models\OrderBeli::with('supplier')->orderBy('tanggal_order', 'desc')->get();

    foreach ($orders as $order) {
        $order->details = \DB::table('t_order_detail')
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
    }

    return view('orderbeli.index', compact('orders'));
}

    public function create(Request $request)
    {
        $last = OrderBeli::orderBy('no_order_beli', 'desc')->first();
        $newNumber = $last ? intval(substr($last->no_order_beli, 2)) + 1 : 1;
        $no_order_beli = 'OB' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        $suppliers = \App\Models\Supplier::all();
        $bahans = \App\Models\Bahan::all();

        // Ambil bahan kurang dari GET jika ada (dari jadwal produksi)
        $bahanKurang = [];
        if ($request->has('bahan_kurang')) {
            $bahanKurang = json_decode($request->bahan_kurang, true);
        } else {
            // Cek semua bahan yang stok < stokmin
            foreach ($bahans as $bahan) {
                if ($bahan->stok < $bahan->stokmin) {
                    $bahanKurang[] = [
                        'kode_bahan' => $bahan->kode_bahan,
                        'nama_bahan' => $bahan->nama_bahan,
                        'satuan' => $bahan->satuan,
                        'jumlah_beli' => $bahan->stokmin - $bahan->stok,
                    ];
                }
            }
        }

        // Jika ada kebutuhan bahan dari request (misal dari halaman jadwal produksi)
        if ($request->has('bahan_kurang')) {
            $bahanKurang = json_decode($request->bahan_kurang, true);
        } else {
            // Ambil kebutuhan bahan dari jadwal produksi terakhir (atau sesuai kebutuhan)
            $jadwal = JadwalProduksi::latest('tanggal_jadwal')->with('details.produk.resep.details.bahan')->first();
            $kebutuhan = [];

            if ($jadwal) {
                foreach ($jadwal->details as $detail) {
                    $produk = $detail->produk;
                    $jumlah = $detail->jumlah;
                    if ($produk && $produk->resep) {
                        foreach ($produk->resep->details as $rdetail) {
                            $kode_bahan = $rdetail->kode_bahan;
                            $total = $jumlah * $rdetail->jumlah_kebutuhan;
                            if (!isset($kebutuhan[$kode_bahan])) {
                                $kebutuhan[$kode_bahan] = [
                                    'kode_bahan' => $kode_bahan,
                                    'nama_bahan' => $rdetail->bahan->nama_bahan ?? $kode_bahan,
                                    'satuan' => $rdetail->satuan,
                                    'jumlah' => 0,
                                ];
                            }
                            $kebutuhan[$kode_bahan]['jumlah'] += $total;
                        }
                    }
                }
            }

            // Bandingkan kebutuhan dengan stok
            $bahanKurang = [];
            foreach ($kebutuhan as $kode_bahan => $b) {
                $stok = \App\Models\Bahan::where('kode_bahan', $kode_bahan)->value('stok') ?? 0;
                if ($stok < $b['jumlah']) {
                    $bahanKurang[] = [
                        'kode_bahan' => $kode_bahan,
                        'nama_bahan' => $b['nama_bahan'],
                        'satuan' => $b['satuan'],
                        'jumlah_beli' => $b['jumlah'] - $stok,
                    ];
                }
            }
        }

        return view('orderbeli.create', compact('no_order_beli', 'suppliers', 'bahans', 'bahanKurang'));
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
    $order = \App\Models\OrderBeli::with('supplier')->where('no_order_beli', $no_order_beli)->first();
    $suppliers = \App\Models\Supplier::all();
    $bahans = \App\Models\Bahan::all();

    // Ambil detail order dengan query builder, bukan relasi
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
public function getDetail($no_order_beli)
{
    $details = \DB::table('t_order_detail')
        ->join('t_bahan', 't_order_detail.kode_bahan', '=', 't_bahan.kode_bahan')
        ->where('t_order_detail.no_order_beli', $no_order_beli)
        ->select(
            't_order_detail.kode_bahan',
            't_bahan.nama_bahan',
            't_order_detail.jumlah_beli',
            't_order_detail.harga_beli'
        )
        ->get();
    return response()->json($details);
}
}