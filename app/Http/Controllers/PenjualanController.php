<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Penjualan;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'asc');
        $query = Penjualan::with('pelanggan');

        // Tambahkan filter jenis_penjualan
        if ($request->filled('jenis_penjualan')) {
            $query->where('jenis_penjualan', $request->jenis_penjualan);
        }
        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }
        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('tanggal_jual', '>=', $request->tanggal_awal);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('tanggal_jual', '<=', $request->tanggal_akhir);
        }

        $penjualan = $query->orderBy('no_jual', $sort)->get();

        foreach ($penjualan as $jual) {
            $details = DB::table('t_penjualan_detail')
                ->leftJoin('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
                ->leftJoin('t_produk_konsinyasi', 't_penjualan_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
                ->where('t_penjualan_detail.no_jual', $jual->no_jual)
                ->select(
                    't_penjualan_detail.*',
                    DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                    DB::raw('COALESCE(t_produk.satuan, t_produk_konsinyasi.satuan) as satuan')
                )
                ->get();
            $jual->details = $details;
        }

        return view('penjualan.index', compact('penjualan'));
    }

    public function create(Request $request)
    {
        // Generate kode penjualan otomatis
        $last = DB::table('t_penjualan')->orderBy('no_jual', 'desc')->first();
        if ($last) {
            $lastNumber = intval(substr($last->no_jual, 2));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        $no_jual = 'PJ' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);

        $pelanggan = DB::table('t_pelanggan')->get();
        // Ambil produk sendiri
        $produkSendiri = DB::table('t_produk')->get();
        // Ambil produk konsinyasi
        $produkKonsinyasi = DB::table('t_produk_konsinyasi')->get();
        // Gabungkan
        $produk = $produkSendiri->concat($produkKonsinyasi);
        $jenis_penjualan = $request->get('jenis_penjualan', 'langsung');

        return view('penjualan.create', compact('no_jual', 'pelanggan', 'produk', 'jenis_penjualan'));
    }

    public function store(Request $request)
    {
        \Log::info('PenjualanController@store - request data', $request->all());

        // Mapping agar field 'total' selalu ada
        $input = $request->all();
        if (empty($input['total']) && !empty($input['total_jual'])) {
            $input['total'] = $input['total_jual'];
        } elseif (empty($input['total']) && !empty($input['total_harga'])) {
            $input['total'] = $input['total_harga'];
        }
        // Setelah mapping input
        if (empty($input['status_pembayaran'])) {
            $input['status_pembayaran'] = (!empty($input['piutang']) && $input['piutang'] > 0) ? 'belum lunas' : 'lunas';
        }
        $request->replace($input);

        try {
            $rules = [
                'no_jual' => 'required|unique:t_penjualan,no_jual',
                'tanggal_jual' => 'required|date',
                'kode_pelanggan' => 'required',
                'total' => 'required|numeric',
                'metode_pembayaran' => 'required|in:tunai,kredit,non tunai',
                'status_pembayaran' => 'required|in:lunas,belum lunas',
                'keterangan' => 'nullable|string|max:100',
                'detail_json' => 'required|json',
            ];
            $request->validate($rules);
            \Log::info('PenjualanController@store - validasi sukses');

            DB::transaction(function () use ($request) {
                // Simpan penjualan
                DB::table('t_penjualan')->insert([
                    'no_jual' => $request->no_jual,
                    'tanggal_jual' => $request->tanggal_jual,
                    'kode_pelanggan' => $request->kode_pelanggan,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'total' => $request->total,
                    'diskon' => $request->diskon ?? 0,
                    'piutang' => $request->piutang ?? 0,
                    'status_pembayaran' => $request->status_pembayaran, // sudah otomatis benar
                    'keterangan' => $request->keterangan,
                ]);

                // Simpan detail penjualan
                $details = json_decode($request->detail_json, true);
                foreach ($details as $i => $detail) {
                    DB::table('t_penjualan_detail')->insert([
                        'no_detailjual' => $request->no_jual . '-' . ($i+1),
                        'no_jual' => $request->no_jual,
                        'kode_produk' => $detail['kode_produk'],
                        'jumlah' => $detail['jumlah'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'subtotal' => $detail['subtotal'],
                    ]);
                }

                // Jika ada piutang (penjualan kredit), catat ke t_piutang
                if (
                    $request->metode_pembayaran === 'kredit' ||
                    ($request->piutang ?? 0) > 0
                ) {
                    DB::table('t_piutang')->insert([
                        'no_piutang' => 'PTG' . $request->no_jual,
                        'no_jual' => $request->no_jual,
                        'total_tagihan' => $request->total,
                        'total_bayar' => $request->total_bayar ?? 0,
                        'sisa_piutang' => $request->piutang ?? ($request->total ?? 0),
                        'status_piutang' => ($request->piutang ?? 0) > 0 ? 'belum lunas' : 'lunas',
                        'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo ?? null,
                    ]);
                }
            });

            \Log::info('PenjualanController@store - transaksi sukses');
            return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil disimpan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('PenjualanController@store - validasi gagal', $e->errors());
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('PenjualanController@store - exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan penjualan.')->withInput();
        }
    }

    public function edit($no_jual)
    {
        $penjualan = DB::table('t_penjualan')->where('no_jual', $no_jual)->first();
        $pelanggan = DB::table('t_pelanggan')->get();
        $produk = DB::table('t_produk')->get();

        $details = DB::table('t_penjualan_detail')
            ->join('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->where('t_penjualan_detail.no_jual', $no_jual)
            ->select(
                't_penjualan_detail.kode_produk',
                't_produk.nama_produk',
                't_penjualan_detail.jumlah',
                't_penjualan_detail.harga_satuan',
                't_penjualan_detail.subtotal'
            )
            ->get();

        $detailsArr = [];
        foreach ($details as $d) {
            $detailsArr[] = [
                'kode_produk' => $d->kode_produk,
                'nama_produk' => $d->nama_produk,
                'jumlah' => $d->jumlah,
                'harga_satuan' => $d->harga_satuan,
                'subtotal' => $d->subtotal, // perbaikan di sini
            ];
        }

        return view('penjualan.edit', [
            'penjualan' => $penjualan,
            'pelanggan' => $pelanggan,
            'produk' => $produk,
            'details' => $detailsArr
        ]);
    }

    public function update(Request $request, $no_jual)
    {
        $request->validate([
            'tanggal_jual' => 'required|date',
            'kode_pelanggan' => 'required',
            'total_harga' => 'required|numeric',
            'diskon' => 'required|numeric',
            'total_jual' => 'required|numeric',
            'total_bayar' => 'required|numeric',
            'kembalian' => 'required|numeric',
            'piutang' => 'required|numeric',
            'metode_pembayaran' => 'required|in:tunai,non tunai',
            'status_pembayaran' => 'nullable',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json'
        ]);

        // Cek jenis penjualan
        $jenis_penjualan = DB::table('t_penjualan')->where('no_jual', $no_jual)->value('jenis_penjualan');
        $status_pembayaran = ($request->piutang == 0) ? 'lunas' : 'belum lunas';

        DB::transaction(function () use ($request, $no_jual, $status_pembayaran, $jenis_penjualan) {
            DB::table('t_penjualan')->where('no_jual', $no_jual)->update([
                'tanggal_jual' => $request->tanggal_jual,
                'kode_pelanggan' => $request->kode_pelanggan,
                'diskon' => $request->diskon,
                'total_jual' => $request->total_jual,
                'total_bayar' => $request->total_bayar,
                'kembalian' => $request->kembalian,
                'piutang' => $request->piutang,
                'metode_pembayaran' => $request->metode_pembayaran,
                'status_pembayaran' => $status_pembayaran,
                'keterangan' => $request->keterangan,
            ]);

            // Jika jenis_penjualan bukan pesanan, detail produk boleh diupdate
            if ($jenis_penjualan !== 'pesanan') {
                DB::table('t_penjualan_detail')->where('no_jual', $no_jual)->delete();
                $details = json_decode($request->detail_json, true);
                foreach ($details as $i => $detail) {
                    DB::table('t_penjualan_detail')->insert([
                        'no_detailjual' => $no_jual . '-' . ($i+1),
                        'no_jual' => $no_jual,
                        'kode_produk' => $detail['kode_produk'],
                        'jumlah' => $detail['jumlah'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'subtotal' => $detail['subtotal'],
                    ]);
                    // Jika produk konsinyasi, catat ke kartu stok konsinyasi (barang keluar)
                    $isKonsinyasi = DB::table('t_produk_konsinyasi')->where('kode_produk', $detail['kode_produk'])->exists();
                    if ($isKonsinyasi) {
                        $lastStok = DB::table('t_kartuperskonsinyasi')
                            ->where('kode_produk', $detail['kode_produk'])
                            ->where('lokasi', 'Gudang')
                            ->orderByDesc('tanggal')
                            ->orderByDesc('id')
                            ->value('sisa');
                        $lastStok = $lastStok ?? 0;
                        $sisaBaru = $lastStok - $detail['jumlah'];
                        DB::table('t_kartuperskonsinyasi')->insert([
                            'tanggal' => $request->tanggal_jual,
                            'kode_produk' => $detail['kode_produk'],
                            'masuk' => 0,
                            'keluar' => $detail['jumlah'],
                            'sisa' => $sisaBaru,
                            'harga_konsinyasi' => $detail['harga_satuan'],
                            'lokasi' => 'Gudang',
                            'keterangan' => 'Penjualan'
                        ]);
                    }
                }
            }

            // Sinkronisasi ke t_piutang jika ada
            $piutangRow = DB::table('t_piutang')->where('no_jual', $no_jual)->first();
            if ($piutangRow) {
                $sisa_piutang = $request->piutang;
                $total_bayar = $request->total_bayar;
                $status_piutang = ($sisa_piutang <= 0) ? 'lunas' : 'belum lunas';
                DB::table('t_piutang')->where('no_jual', $no_jual)->update([
                    'total_tagihan'      => $request->total_jual, // pastikan ini sesuai dengan field di form
                    'sisa_piutang'       => $sisa_piutang,
                    'total_bayar'        => $total_bayar,
                    'status_piutang'     => $status_piutang,
                    'kode_pelanggan'     => $request->kode_pelanggan,
                    'tanggal_jatuh_tempo'=> $request->tanggal_jatuh_tempo ?? null,
                ]);
            } else if (($request->piutang ?? 0) > 0) {
                // Jika belum ada, insert baru
                DB::table('t_piutang')->insert([
                    'no_piutang'         => 'PTG' . $no_jual,
                    'no_jual'            => $no_jual,
                    'total_tagihan'      => $request->total_jual,
                    'total_bayar'        => $request->total_bayar ?? 0,
                    'sisa_piutang'       => $request->piutang ?? 0,
                    'status_piutang'     => ($request->piutang ?? 0) > 0 ? 'belum lunas' : 'lunas',
                    'kode_pelanggan'     => $request->kode_pelanggan,
                    'tanggal_jatuh_tempo'=> $request->tanggal_jatuh_tempo ?? null,
                ]);
            }
        });

        return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil diupdate!');
    }

    public function destroy($no_jual)
    {
        DB::transaction(function () use ($no_jual) {
            // Hapus piutang terkait sebelum menghapus penjualan
            DB::table('t_piutang')->where('no_jual', $no_jual)->delete();
            DB::table('t_penjualan_detail')->where('no_jual', $no_jual)->delete();
            DB::table('t_penjualan')->where('no_jual', $no_jual)->delete();
        });

        return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil dihapus!');
    }

    public function show($no_jual)
    {
        $penjualan = DB::table('t_penjualan')
            ->leftJoin('t_pelanggan', 't_penjualan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->where('no_jual', $no_jual)
            ->select('t_penjualan.*', 't_pelanggan.nama_pelanggan')
            ->first();

        // Ambil detail produk dari t_penjualan_detail saja (termasuk produk sendiri & konsinyasi jika sudah masuk di sini)
        $details = DB::table('t_penjualan_detail')
            ->leftJoin('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->leftJoin('t_produk_konsinyasi', 't_penjualan_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->where('t_penjualan_detail.no_jual', $no_jual)
            ->select(
                't_penjualan_detail.*',
                DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                DB::raw('COALESCE(t_produk.satuan, t_produk_konsinyasi.satuan) as satuan')
            )
            ->get();

        return view('penjualan.detail', compact('penjualan', 'details'));
    }

    public function cetak($no_jual)
    {
        $penjualan = DB::table('t_penjualan')
            ->leftJoin('t_pelanggan', 't_penjualan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->where('no_jual', $no_jual)
            ->select('t_penjualan.*', 't_pelanggan.nama_pelanggan')
            ->first();

        // Ambil detail produk dari t_penjualan_detail saja (termasuk produk sendiri & konsinyasi jika sudah masuk di sini)
        $details = DB::table('t_penjualan_detail')
            ->leftJoin('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->leftJoin('t_produk_konsinyasi', 't_penjualan_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->where('t_penjualan_detail.no_jual', $no_jual)
            ->select(
                't_penjualan_detail.*',
                DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                DB::raw('COALESCE(t_produk.satuan, t_produk_konsinyasi.satuan) as satuan')
            )
            ->get();

        return view('penjualan.cetak', compact('penjualan', 'details'));
    }

    public function createPesanan(Request $request)
    {
        $last = DB::table('t_penjualan')->orderBy('no_jual', 'desc')->first();
        $no_jual = $last ? 'PJ' . str_pad(intval(substr($last->no_jual, 2)) + 1, 6, '0', STR_PAD_LEFT) : 'PJ000001';

        // Ambil semua no_pesanan yang sudah pernah dipakai di penjualan (jenis pesanan)
        $usedPesanan = DB::table('t_penjualan')
            ->whereNotNull('no_pesanan')
            ->pluck('no_pesanan')
            ->toArray();

        // Ambil pesanan yang belum pernah dipakai di penjualan
        $pesanan = DB::table('t_pesanan')
            ->join('t_pelanggan', 't_pesanan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->whereNotIn('t_pesanan.no_pesanan', $usedPesanan)
            ->select('t_pesanan.*', 't_pelanggan.nama_pelanggan')
            ->get();

        $pesananDetails = [];
        foreach ($pesanan as $psn) {
            $details = DB::table('t_pesanan_detail')
                ->join('t_produk', 't_pesanan_detail.kode_produk', '=', 't_produk.kode_produk')
                ->where('t_pesanan_detail.no_pesanan', $psn->no_pesanan)
                ->select(
                    't_pesanan_detail.kode_produk',
                    't_produk.nama_produk',
                    't_pesanan_detail.jumlah',
                    't_pesanan_detail.harga_satuan'
                )->get()->toArray();
            $pesananDetails[$psn->no_pesanan] = $details;
        }

        $jenis_penjualan = $request->get('jenis_penjualan', 'pesanan');

        return view('penjualan.create_pesanan', compact('no_jual', 'pesanan', 'pesananDetails', 'jenis_penjualan'));
    }

    public function cetakTagihan($no_jual)
    {
        // Ambil data penjualan beserta relasi pelanggan dan details+produk
        $penjualan = \App\Models\Penjualan::with(['pelanggan', 'details.produk'])->where('no_jual', $no_jual)->firstOrFail();

        // Pastikan hanya status "belum lunas" yang bisa dicetak tagihannya
        if ($penjualan->status_pembayaran !== 'belum lunas') {
            abort(404, 'Tagihan hanya untuk penjualan yang belum lunas.');
        }

        $details = $penjualan->details;

        // Kirim ke view cetak_tagihan
        return view('penjualan.cetak_tagihan', compact('penjualan', 'details'));
    }
}