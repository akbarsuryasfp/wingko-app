<?php

namespace App\Http\Controllers;
use App\Models\OrderBeli;
use App\Models\Supplier;
use App\Models\Bahan;
use App\Helpers\JurnalHelper;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\JadwalProduksi;
use Carbon\Carbon;

class OrderBeliController extends Controller
{
public function index()
{
    $orders = \App\Models\OrderBeli::with('supplier')
        ->when(request('search'), function($query) {
            $query->where('no_order_beli', 'like', '%'.request('search').'%')
                  ->orWhereHas('supplier', function($q) {
                      $q->where('nama_supplier', 'like', '%'.request('search').'%');
                  });
        })
        ->when(request('tanggal_mulai') && request('tanggal_selesai'), function($query) {
            $query->whereBetween('tanggal_order', [
                request('tanggal_mulai'),
                request('tanggal_selesai')
            ]);
        })
        ->when(request('status'), function($query) {
            if (request('status') === 'Menunggu Persetujuan') {
                $query->whereNull('status');
            } else {
                $query->where('status', request('status'));
            }
        })
        ->orderBy('no_order_beli', 'asc')
        ->get();

    // Rest of your existing code for processing orders...
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

        $semuaDiterima = true;
        $adaYangMasuk = false;

        $noTerimaList = \DB::table('t_terimabahan')
            ->where('no_order_beli', $order->no_order_beli)
            ->pluck('no_terima_bahan')
            ->toArray();

        foreach ($order->details as $detail) {
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

        if (!$order->status) {
            $order->status_penerimaan = 'Menunggu Persetujuan';
        } elseif ($order->status == 'Disetujui') {
            if (!$adaYangMasuk) {
                $order->status_penerimaan = 'Disetujui';
            } elseif ($semuaDiterima) {
                $order->status_penerimaan = 'Diterima Sepenuhnya';
            } else {
                $order->status_penerimaan = 'Diterima Sebagian';
            }
        } else {
            $order->status_penerimaan = $order->status;
        }
    }

    return view('orderbeli.index', compact('orders'));
}

    public function create(Request $request)
    {
        $last = OrderBeli::orderBy('no_order_beli', 'desc')->first();
        $newNumber = $last ? intval(substr($last->no_order_beli, 2)) + 1 : 1;
        $no_order_beli = 'OB' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        $suppliers = \App\Models\Supplier::all();
        $bahans = $this->bahanPerluDibeli();

        // Ambil kebutuhan bahan dari jadwal produksi terakhir
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
                                'satuan'     => $rdetail->satuan,
                                'jumlah'     => 0,
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
            $stok = DB::table('t_kartupersbahan')
                ->where('kode_bahan', $kode_bahan)
                ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
                ->value('stok');
            if ($stok < $b['jumlah']) {
                $bahanKurang[] = [
                    'kode_bahan'  => $kode_bahan,
                    'nama_bahan'  => $b['nama_bahan'],
                    'satuan'      => $b['satuan'],
                    'jumlah_beli' => $b['jumlah'] - $stok,
                ];
            }
        }

        // Ambil bahan yang stok < stokmin
        $stokMinList = DB::table('t_bahan')
            ->leftJoin('t_kartupersbahan', 't_bahan.kode_bahan', '=', 't_kartupersbahan.kode_bahan')
            ->select(
                't_bahan.kode_bahan',
                't_bahan.nama_bahan',
                't_bahan.satuan',
                't_bahan.stokmin',
                DB::raw('COALESCE(SUM(t_kartupersbahan.masuk),0) - COALESCE(SUM(t_kartupersbahan.keluar),0) as stok')
            )
            ->groupBy('t_bahan.kode_bahan', 't_bahan.nama_bahan', 't_bahan.satuan', 't_bahan.stokmin')
            ->havingRaw('stok < t_bahan.stokmin')
            ->get()
            ->toArray();

        // Ambil bahan prediksi (interval belum tercapai)
        $bahansPrediksi = \App\Models\Bahan::whereIn('frekuensi_pembelian', [
            'Mingguan', 'Dua Mingguan', 'Bulanan', 'Tiga Bulanan'
        ])->get()->map(function($bahan) {
            $stok = \DB::table('t_kartupersbahan')
                ->where('kode_bahan', $bahan->kode_bahan)
                ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
                ->value('stok');
            $bahan->stok = $stok;
            $bahan->jumlah_per_order = $bahan->jumlah_per_order ?? $bahan->interval ?? 1;
            return $bahan;
        });

        // Filter bahan prediksi agar tidak tampil jika sudah diorder dalam interval
        $bahansPrediksi = collect($bahansPrediksi)->filter(function($bahan) {
            $now = \Carbon\Carbon::now();
            $frekuensi = strtolower(trim($bahan->frekuensi_pembelian ?? ''));
            $interval = intval($bahan->interval ?? 1);

            switch ($frekuensi) {
                case 'minggu':
                case 'mingguan':
                    $batas = $now->copy()->subWeeks(1);
                    break;
                case 'bulan':
                case 'bulanan':
                    $batas = $now->copy()->subMonths(1);
                    break;
                default:
                    $batas = $now->copy()->subDays(1);
            }

            $jumlah_dibeli = \DB::table('t_order_detail')
                ->join('t_order_beli', 't_order_detail.no_order_beli', '=', 't_order_beli.no_order_beli')
                ->where('t_order_detail.kode_bahan', $bahan->kode_bahan)
                ->where('t_order_beli.tanggal_order', '>=', $batas)
                ->count();

            return $jumlah_dibeli < $interval;
        })->values();

        // Gabungkan bahan yang stok < stokmin ke prediksi jika belum ada
        $semuaBahan = DB::table('t_bahan')->get();
        foreach ($semuaBahan as $bahanMin) {
            $stok = DB::table('t_kartupersbahan')
                ->where('kode_bahan', $bahanMin->kode_bahan)
                ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
                ->value('stok');
            if ($stok < $bahanMin->stokmin) {
                $sudahAda = $bahansPrediksi->contains(function($b) use ($bahanMin) {
                    return $b->kode_bahan == $bahanMin->kode_bahan;
                });
                if (!$sudahAda) {
                    $bahansPrediksi->push((object)[
                        'kode_bahan' => $bahanMin->kode_bahan,
                        'nama_bahan' => $bahanMin->nama_bahan,
                        'satuan' => $bahanMin->satuan,
                        'frekuensi_pembelian' => $bahanMin->frekuensi_pembelian ?? 'Stok Minimal',
                        'interval' => $bahanMin->interval ?? 1,
                        'jumlah_per_order' => $bahanMin->jumlah_per_order ?? $bahanMin->interval ?? 1,
                        'stokmin' => $bahanMin->stokmin,
                        'stok' => $stok,
                    ]);
                }
            }
        }

        return view('orderbeli.create', compact(
            'no_order_beli',
            'suppliers',
            'bahans',
            'bahanKurang',
            'stokMinList',
            'bahansPrediksi'
        ));
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

public function setujui(Request $request, $no_order_beli)
{
    $request->validate([
        'uang_muka' => 'nullable|numeric|min:0',
        'metode_bayar' => 'nullable|string|max:50',
    ]);

    $order = \App\Models\OrderBeli::findOrFail($no_order_beli);

    // Simpan status, uang muka, dan metode bayar
    $order->status = 'Disetujui';
    $order->uang_muka = $request->uang_muka ?? 0;
    $order->metode_bayar = $request->metode_bayar ?? null;
    $keterangan = ($request->no_referensi ?? '') . ' | ' . ($request->keterangan ?? '') . ' | ' . $request->penerima;

    $order->save();

    // === JURNAL UMUM & DETAIL ===
    if ($order->uang_muka > 0) {
        $no_jurnal = \App\Helpers\JurnalHelper::generateNoJurnal();

        DB::table('t_jurnal_umum')->insert([
            'no_jurnal'   => $no_jurnal,
            'tanggal'     => now(),
            'keterangan'  => 'Uang Muka Order Pembelian ' . $order->no_order_beli,
            'nomor_bukti' => $order->no_order_beli,
        ]);

        // Debit Uang Muka Pembelian
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => \App\Helpers\JurnalHelper::generateNoJurnalDetail($no_jurnal),
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => \App\Helpers\JurnalHelper::getKodeAkun('uang_muka'),
            'debit'            => $order->uang_muka,
            'kredit'           => 0,
        ]);
        // Kredit Kas/Bank
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => \App\Helpers\JurnalHelper::generateNoJurnalDetail($no_jurnal),
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => \App\Helpers\JurnalHelper::getKodeAkun('kas_bank'),
            'debit'            => 0,
            'kredit'           => $order->uang_muka,
        ]);
    }

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

    return view('orderbeli.detail', compact('order', 'details'));
}

public function updatePembayaran(Request $request, $no_order_beli)
{
    $request->validate([
        'uang_muka' => 'nullable|numeric|min:0',
        'metode_bayar' => 'nullable|string|max:50',
    ]);

    $order = \App\Models\OrderBeli::findOrFail($no_order_beli);

    // Simpan/update uang muka dan metode bayar
    $order->uang_muka = $request->uang_muka ?? 0;
    $order->metode_bayar = $request->metode_bayar ?? null;

    if ($request->action === 'setujui') {
        $order->status = 'Disetujui';
        if ($order->uang_muka > 0) {
            // Cek apakah sudah ada jurnal untuk order ini
            $jurnal = DB::table('t_jurnal_umum')->where('nomor_bukti', $order->no_order_beli)->first();
            if (!$jurnal) {
                $no_jurnal = \App\Helpers\JurnalHelper::generateNoJurnal();
                DB::table('t_jurnal_umum')->insert([
                    'no_jurnal'   => $no_jurnal,
                    'tanggal'     => now(),
                    'keterangan'  => 'Uang Muka Order Pembelian ' . $order->no_order_beli,
                    'nomor_bukti' => $order->no_order_beli,
                ]);
                // Debit Uang Muka Pembelian
                DB::table('t_jurnal_detail')->insert([
                    'no_jurnal_detail' => \App\Helpers\JurnalHelper::generateNoJurnalDetail($no_jurnal),
                    'no_jurnal'        => $no_jurnal,
                    'kode_akun'        => \App\Helpers\JurnalHelper::getKodeAkun('uang_muka'),
                    'debit'            => $order->uang_muka,
                    'kredit'           => 0,
                ]);
                // Kredit Kas/Bank
                DB::table('t_jurnal_detail')->insert([
                    'no_jurnal_detail' => \App\Helpers\JurnalHelper::generateNoJurnalDetail($no_jurnal),
                    'no_jurnal'        => $no_jurnal,
                    'kode_akun'        => \App\Helpers\JurnalHelper::getKodeAkun('kas_bank'),
                    'debit'            => 0,
                    'kredit'           => $order->uang_muka,
                ]);
            } else {
                // Jika sudah ada jurnal, update nilai uang muka di jurnal detail
                DB::table('t_jurnal_detail')
                    ->where('no_jurnal', $jurnal->no_jurnal)
                    ->where('kode_akun', \App\Helpers\JurnalHelper::getKodeAkun('uang_muka'))
                    ->update(['debit' => $order->uang_muka]);
                DB::table('t_jurnal_detail')
                    ->where('no_jurnal', $jurnal->no_jurnal)
                    ->where('kode_akun', \App\Helpers\JurnalHelper::getKodeAkun('kas_bank'))
                    ->update(['kredit' => $order->uang_muka]);
            }
        }
        $order->save();
        return redirect()->route('orderbeli.index')->with('success', 'Order berhasil disetujui!');
    } else {
        $order->save();
        return redirect()->route('orderbeli.index')->with('success', 'Pembayaran berhasil diupdate!');
    }
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
            't_order_detail.jumlah_beli',
            't_order_detail.harga_beli', // <-- tambahkan ini!
            't_order_detail.total'
        )
        ->get();

    $pdf = Pdf::loadView('orderbeli.cetak', compact('order', 'details'));
    return $pdf->stream('order_pembelian_' . $order->no_order_beli . '.pdf');
}

public function edit($no_order_beli)
{
    $order = \App\Models\OrderBeli::with('supplier')->where('no_order_beli', $no_order_beli)->first();
    $suppliers = \App\Models\Supplier::all();
    $bahans = \App\Models\Bahan::all();

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

    // Ambil kebutuhan bahan dari jadwal produksi terakhir
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
                            'satuan'     => $rdetail->satuan,
                            'jumlah'     => 0,
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
        $stok = DB::table('t_kartupersbahan')
            ->where('kode_bahan', $kode_bahan)
            ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
            ->value('stok');
        if ($stok < $b['jumlah']) {
            $bahanKurang[] = [
                'kode_bahan'  => $kode_bahan,
                'nama_bahan'  => $b['nama_bahan'],
                'satuan'      => $b['satuan'],
                'jumlah_beli' => $b['jumlah'] - $stok,
            ];
        }
    }

    // Ambil semua bahan yang stok < stokmin atau perlu dibeli berdasarkan frekuensi
    $bahansPrediksi = \App\Models\Bahan::all()->map(function($bahan) {
        $stok = \DB::table('t_kartupersbahan')
            ->where('kode_bahan', $bahan->kode_bahan)
            ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
            ->value('stok');
        
        $bahan->stok = $stok;
        $bahan->jumlah_per_order = $bahan->jumlah_per_order ?? $bahan->interval ?? 1;
        return $bahan;
    });

    // Filter bahan yang perlu ditampilkan (stok < stokmin atau belum memenuhi frekuensi order)
    $bahansPrediksi = collect($bahansPrediksi)->filter(function($bahan) {
        $now = \Carbon\Carbon::now();
        $frekuensi = strtolower(trim($bahan->frekuensi_pembelian ?? ''));
        $interval = intval($bahan->interval ?? 1);

        // Cek jika stok di bawah minimal
        if ($bahan->stok < $bahan->stokmin) {
            return true;
        }

        // Jika ada frekuensi pembelian, cek apakah sudah waktunya order
        if ($frekuensi) {
            switch ($frekuensi) {
                case 'minggu':
                case 'mingguan':
                    $batas = $now->copy()->subWeeks(1);
                    break;
                case 'bulan':
                case 'bulanan':
                    $batas = $now->copy()->subMonths(1);
                    break;
                default:
                    $batas = $now->copy()->subDays(1);
            }

            $jumlah_dibeli = \DB::table('t_order_detail')
                ->join('t_order_beli', 't_order_detail.no_order_beli', '=', 't_order_beli.no_order_beli')
                ->where('t_order_detail.kode_bahan', $bahan->kode_bahan)
                ->where('t_order_beli.tanggal_order', '>=', $batas)
                ->count();

            return $jumlah_dibeli < $interval;
        }

        return false;
    })->values();

    return view('orderbeli.edit', compact(
        'order', 
        'suppliers', 
        'bahans', 
        'details',
        'bahanKurang',
        'bahansPrediksi'
    ));
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

public function destroy($no_order_beli)
{
    // Hapus detail terlebih dahulu jika perlu (jika pakai foreign key tanpa cascade delete)
    DB::table('t_order_detail')->where('no_order_beli', $no_order_beli)->delete();

    // Hapus order utama
    $order = OrderBeli::findOrFail($no_order_beli);
    $order->delete();

    return redirect()->route('orderbeli.index')->with('success', 'Order pembelian berhasil dihapus.');
}

// Fungsi untuk ambil bahan yang perlu dibeli (belum dibeli dalam interval)
private function bahanPerluDibeli()
{
    $now = Carbon::now();
    $bahans = DB::table('t_bahan')->get();
    $result = [];

    foreach ($bahans as $bahan) {
        $frekuensi = strtolower(trim($bahan->frekuensi_pembelian ?? ''));
        $interval = intval($bahan->interval ?? 1);

        switch ($frekuensi) {
            case 'minggu':
            case 'mingguan':
                $batas = $now->copy()->subWeeks(1);
                break;
            case 'bulan':
            case 'bulanan':
                $batas = $now->copy()->subMonths(1);
                break;
            default:
                $batas = $now->copy()->subDays(1);
        }

        // Hitung stok akumulasi dari kartu persediaan
        $stok = DB::table('t_kartupersbahan')
            ->where('kode_bahan', $bahan->kode_bahan)
            ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
            ->value('stok');
        $bahan->stok = $stok;

        // Hitung jumlah pembelian bahan dalam periode frekuensi
        $jumlah_dibeli = DB::table('t_order_detail')
            ->join('t_order_beli', 't_order_detail.no_order_beli', '=', 't_order_beli.no_order_beli')
            ->where('t_order_detail.kode_bahan', $bahan->kode_bahan)
            ->where('t_order_beli.tanggal_order', '>=', $batas)
            ->count();

        // Jika jumlah pembelian < interval atau stok < stokmin, tambahkan ke list
        if ($jumlah_dibeli < $interval || ($stok < $bahan->stokmin)) {
            $result[] = $bahan;
        }
    }

    return $result;
}
}