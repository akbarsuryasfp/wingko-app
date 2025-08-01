<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PesananPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'asc');
        $query = DB::table('t_pesanan')
            ->leftJoin('t_pelanggan', 't_pesanan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->select('t_pesanan.*', 't_pelanggan.nama_pelanggan'); // status dihapus

        // Tambahkan filter periode tanggal_pesanan
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('t_pesanan.tanggal_pesanan', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('t_pesanan.tanggal_pesanan', '<=', $request->tanggal_akhir);
        }

        $pesanan = $query->orderBy('t_pesanan.no_pesanan', $sort)->get();

        foreach ($pesanan as $psn) {
            $details = DB::table('t_pesanan_detail')
                ->join('t_produk', 't_pesanan_detail.kode_produk', '=', 't_produk.kode_produk')
                ->where('t_pesanan_detail.no_pesanan', $psn->no_pesanan)
                ->select(
                    't_pesanan_detail.*',
                    't_produk.nama_produk'
                )
                ->get();
            $psn->details = $details;
            // Cek apakah sudah ada transaksi penjualan untuk pesanan ini (t_penjualan)
            $psn->sudah_terjual = DB::table('t_penjualan')->where('no_pesanan', $psn->no_pesanan)->exists();
        }

        return view('pesananpenjualan.index', compact('pesanan'));
        $sort = $request->get('sort', 'asc');
        $query = DB::table('t_pesanan')
            ->leftJoin('t_pelanggan', 't_pesanan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->select('t_pesanan.*', 't_pelanggan.nama_pelanggan');

        // Tambahkan filter periode tanggal_pesanan
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('t_pesanan.tanggal_pesanan', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('t_pesanan.tanggal_pesanan', '<=', $request->tanggal_akhir);
        }

        $pesanan = $query->orderBy('t_pesanan.no_pesanan', $sort)->get();

        foreach ($pesanan as $psn) {
            // Cek apakah sudah ada transaksi penjualan untuk pesanan ini (t_penjualan)
            $penjualan = DB::table('t_penjualan')->where('no_pesanan', $psn->no_pesanan)->first();
            if ($penjualan) {
                // Jika sudah terjual, ambil total dari t_penjualan dan detail dari t_penjualan_detail
                $psn->total_pesanan = $penjualan->total_jual;
                $psn->sudah_terjual = true;
                $details = DB::table('t_penjualan_detail')
                    ->join('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
                    ->where('t_penjualan_detail.no_jual', $penjualan->no_jual)
                    ->select(
                        't_penjualan_detail.kode_produk',
                        't_produk.nama_produk',
                        't_penjualan_detail.jumlah',
                        't_penjualan_detail.harga_satuan',
                        't_penjualan_detail.diskon_satuan',
                        't_penjualan_detail.subtotal'
                    )
                    ->get();
                $psn->details = $details;
            } else {
                // Jika belum terjual, ambil dari t_pesanan_detail
                $psn->sudah_terjual = false;
                $details = DB::table('t_pesanan_detail')
                    ->join('t_produk', 't_pesanan_detail.kode_produk', '=', 't_produk.kode_produk')
                    ->where('t_pesanan_detail.no_pesanan', $psn->no_pesanan)
                    ->select(
                        't_pesanan_detail.kode_produk',
                        't_produk.nama_produk',
                        't_pesanan_detail.jumlah',
                        't_pesanan_detail.harga_satuan',
                        't_pesanan_detail.diskon_produk as diskon_satuan',
                        't_pesanan_detail.subtotal'
                    )
                    ->get();
                $psn->details = $details;
            }
        }

        return view('pesananpenjualan.index', compact('pesanan'));
    }

    public function create()
    {
        $last = DB::table('t_pesanan')->orderBy('no_pesanan', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->no_pesanan, 2));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $no_pesanan = 'PS' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        $pelanggan = DB::table('t_pelanggan')->get();
        $produk = DB::table('t_produk')->get();

        return view('pesananpenjualan.create', compact('no_pesanan', 'pelanggan', 'produk'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_pesanan' => 'required|unique:t_pesanan,no_pesanan',
            'tanggal_pesanan' => 'required|date',
            'tanggal_pengiriman' => 'nullable|date',
            'kode_pelanggan' => 'required',
            'total_pesanan' => 'required|numeric',
            'uang_muka' => 'nullable|numeric',
            'sisa_tagihan' => 'nullable|numeric',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json',
            // 'status' => 'required', // validasi status dihapus
        ]);

        DB::transaction(function () use ($request) {
            DB::table('t_pesanan')->insert([
                'no_pesanan' => $request->no_pesanan,
                'tanggal_pesanan' => $request->tanggal_pesanan,
                'tanggal_pengiriman' => $request->tanggal_pengiriman,
                'kode_pelanggan' => $request->kode_pelanggan,
                'total_pesanan' => $request->total_pesanan,
                'uang_muka' => $request->uang_muka ?? 0,
                'sisa_tagihan' => $request->sisa_tagihan ?? 0,
                'keterangan' => $request->keterangan,
                // 'status' => $request->status, // simpan status dihapus
            ]);

            $details = json_decode($request->detail_json, true);
            foreach ($details as $i => $detail) {
                $diskon_satuan = isset($detail['diskon_satuan']) ? $detail['diskon_satuan'] : 0;
                $harga_satuan = isset($detail['harga_satuan']) ? $detail['harga_satuan'] : 0;
                $jumlah = isset($detail['jumlah']) ? $detail['jumlah'] : 0;
                $subtotal = $jumlah * max($harga_satuan - $diskon_satuan, 0);
                DB::table('t_pesanan_detail')->insert([
                    'no_detailpesanan' => $request->no_pesanan . '-' . ($i+1),
                    'no_pesanan' => $request->no_pesanan,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah' => $jumlah,
                    'harga_satuan' => $harga_satuan,
                    'diskon_produk' => $diskon_satuan, // tetap simpan ke kolom diskon_produk
                    'subtotal' => $subtotal,
                ]);
            }
        });

        return redirect()->route('pesananpenjualan.index')->with('success', 'Pesanan penjualan berhasil disimpan!');
    }

    public function edit($no_pesanan)
    {
        $pesanan = DB::table('t_pesanan')->where('no_pesanan', $no_pesanan)->first();
        $pelanggan = DB::table('t_pelanggan')->get();
        $produk = DB::table('t_produk')->get();

        $details = DB::table('t_pesanan_detail')
            ->join('t_produk', 't_pesanan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_pesanan_detail.no_pesanan', $no_pesanan)
            ->select(
                't_pesanan_detail.kode_produk',
                't_produk.nama_produk',
                't_produk.satuan',
                't_pesanan_detail.jumlah',
                't_pesanan_detail.harga_satuan',
                't_pesanan_detail.diskon_produk',
                't_pesanan_detail.subtotal'
            )
            ->get();

        $detailsArr = [];
        foreach ($details as $d) {
            $detailsArr[] = [
                'kode_produk' => $d->kode_produk,
                'nama_produk' => $d->nama_produk,
                'satuan' => $d->satuan,
                'jumlah' => $d->jumlah,
                'harga_satuan' => $d->harga_satuan,
                'diskon_produk' => $d->diskon_produk,
                'subtotal' => $d->subtotal,
            ];
        }

        $details = $detailsArr;
        return view('pesananpenjualan.edit', compact('pesanan', 'pelanggan', 'produk', 'details'));
    }

    public function update(Request $request, $no_pesanan)
    {
        $request->validate([
            'tanggal_pesanan' => 'required|date',
            'tanggal_pengiriman' => 'nullable|date',
            'kode_pelanggan' => 'required',
            'total_pesanan' => 'required|numeric',
            'uang_muka' => 'nullable|numeric',
            'sisa_tagihan' => 'nullable|numeric',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json',
            // 'status' => 'required', // validasi status dihapus
        ]);

        DB::transaction(function () use ($request, $no_pesanan) {
            DB::table('t_pesanan')->where('no_pesanan', $no_pesanan)->update([
                'tanggal_pesanan' => $request->tanggal_pesanan,
                'tanggal_pengiriman' => $request->tanggal_pengiriman,
                'kode_pelanggan' => $request->kode_pelanggan,
                'total_pesanan' => $request->total_pesanan,
                'uang_muka' => $request->uang_muka ?? 0,
                'sisa_tagihan' => $request->sisa_tagihan ?? 0,
                'keterangan' => $request->keterangan,
            ]);

            DB::table('t_pesanan_detail')->where('no_pesanan', $no_pesanan)->delete();

            $details = json_decode($request->detail_json, true);
            foreach ($details as $i => $detail) {
                $diskon_satuan = isset($detail['diskon_satuan']) ? $detail['diskon_satuan'] : 0;
                $harga_satuan = isset($detail['harga_satuan']) ? $detail['harga_satuan'] : 0;
                $jumlah = isset($detail['jumlah']) ? $detail['jumlah'] : 0;
                $subtotal = $jumlah * max($harga_satuan - $diskon_satuan, 0);
                DB::table('t_pesanan_detail')->insert([
                    'no_detailpesanan' => $no_pesanan . '-' . ($i+1),
                    'no_pesanan' => $no_pesanan,
                    'kode_produk' => $detail['kode_produk'],
                    'jumlah' => $jumlah,
                    'harga_satuan' => $harga_satuan,
                    'diskon_produk' => $diskon_satuan, // tetap simpan ke kolom diskon_produk
                    'subtotal' => $subtotal,
                ]);
            }

            // Sinkronisasi ke t_penjualan jika ada
            $penjualan = DB::table('t_penjualan')->where('no_pesanan', $no_pesanan)->first();
            if ($penjualan) {
                // Hitung ulang total_harga, total_jual, piutang, kembalian
                $total_harga = 0;
                foreach ($details as $detail) {
                    $diskon_satuan = isset($detail['diskon_satuan']) ? $detail['diskon_satuan'] : 0;
                    $subtotal = $detail['jumlah'] * max($detail['harga_satuan'] - $diskon_satuan, 0);
                    $total_harga += $subtotal;
                }
                $diskon = $penjualan->diskon ?? 0;
                $total_jual = $total_harga - $diskon;
                $total_bayar = $penjualan->total_bayar ?? 0;
                $kembalian = $total_bayar > $total_jual ? $total_bayar - $total_jual : 0;
                $piutang = $total_jual > $total_bayar ? $total_jual - $total_bayar : 0;
                DB::table('t_penjualan')->where('no_pesanan', $no_pesanan)->update([
                    'tanggal_jual' => $request->tanggal_pesanan,
                    'kode_pelanggan' => $request->kode_pelanggan,
                    'total_harga' => $total_harga,
                    'total_jual' => $total_jual,
                    'piutang' => $piutang,
                    'kembalian' => $kembalian,
                    'keterangan' => $request->keterangan,
                ]);
                // Update detail penjualan
                DB::table('t_penjualan_detail')->where('no_jual', $penjualan->no_jual)->delete();
                foreach ($details as $i => $detail) {
                    $diskon_satuan = isset($detail['diskon_satuan']) ? $detail['diskon_satuan'] : 0;
                    $subtotal = $detail['jumlah'] * max($detail['harga_satuan'] - $diskon_satuan, 0);
                    DB::table('t_penjualan_detail')->insert([
                        'no_detailjual' => $penjualan->no_jual . '-' . ($i+1),
                        'no_jual' => $penjualan->no_jual,
                        'kode_produk' => $detail['kode_produk'],
                        'jumlah' => $detail['jumlah'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'diskon_produk' => $diskon_satuan, // tetap simpan ke kolom diskon_produk
                        'subtotal' => $subtotal,
                    ]);
                }
                // Sinkronisasi ke t_piutang jika ada
                $piutangRow = DB::table('t_piutang')->where('no_jual', $penjualan->no_jual)->first();
                if ($piutangRow) {
                    $total_bayar = $penjualan->total_bayar ?? 0;
                    $sisa_piutang = $total_jual - $total_bayar;
                    $status_piutang = ($sisa_piutang <= 0) ? 'lunas' : 'belum lunas';
                    DB::table('t_piutang')->where('no_jual', $penjualan->no_jual)->update([
                        'total_tagihan' => $total_jual,
                        'sisa_piutang' => $sisa_piutang,
                        'total_bayar' => $total_bayar,
                        'status_piutang' => $status_piutang,
                        'kode_pelanggan' => $request->kode_pelanggan,
                    ]);
                }
            }
        });

        return redirect()->route('pesananpenjualan.index')->with('success', 'Pesanan penjualan berhasil diupdate!');
    }

    public function destroy($no_pesanan)
    {
        DB::transaction(function () use ($no_pesanan) {
            DB::table('t_pesanan_detail')->where('no_pesanan', $no_pesanan)->delete();
            DB::table('t_pesanan')->where('no_pesanan', $no_pesanan)->delete();
        });

        return redirect()->route('pesananpenjualan.index')->with('success', 'Pesanan penjualan berhasil dihapus!');
    }

    public function show($no_pesanan)
    {
        $pesanan = DB::table('t_pesanan')
            ->leftJoin('t_pelanggan', 't_pesanan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->where('no_pesanan', $no_pesanan)
            ->select('t_pesanan.*', 't_pelanggan.nama_pelanggan')
            ->first();

        $details = DB::table('t_pesanan_detail')
            ->join('t_produk', 't_pesanan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_pesanan_detail.no_pesanan', $no_pesanan)
            ->select(
                't_pesanan_detail.*',
                't_produk.nama_produk',
                't_produk.satuan'
            )
            ->get();

        return view('pesananpenjualan.detail', compact('pesanan', 'details'));
    }

    public function cetak($no_pesanan)
    {
        $pesanan = DB::table('t_pesanan')
            ->leftJoin('t_pelanggan', 't_pesanan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->where('no_pesanan', $no_pesanan)
            ->select('t_pesanan.*', 't_pelanggan.nama_pelanggan')
            ->first();

        $details = DB::table('t_pesanan_detail')
            ->join('t_produk', 't_pesanan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_pesanan_detail.no_pesanan', $no_pesanan)
            ->select(
                't_pesanan_detail.*',
                't_produk.nama_produk',
                't_produk.satuan'
            )
            ->get();

        return view('pesananpenjualan.cetak', compact('pesanan', 'details'));
    }
}