<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Penjualan;
use App\Models\KartuPersKonsinyasi;
use Barryvdh\DomPDF\Facade\Pdf;

class PenjualanController extends Controller
{

    public function index(Request $request)
    {
        $sort = $request->get('sort', 'asc');
        $query = Penjualan::with('pelanggan');

        // Filter jenis_penjualan
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
        // Filter search: no_jual atau nama pelanggan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_jual', 'like', "%$search%")
                  ->orWhereHas('pelanggan', function($qq) use ($search) {
                      $qq->where('nama_pelanggan', 'like', "%$search%");
                  });
            });
        }
        $penjualan = $query->orderBy('no_jual', $sort)->get();
        // Ambil sisa piutang terbaru dari t_piutang jika ada
        foreach ($penjualan as $jual) {
            $piutang = DB::table('t_piutang')->where('no_jual', $jual->no_jual)->first();
            if ($piutang) {
                $jual->piutang = $piutang->sisa_piutang;
            }
        }
        return view('penjualan.index', compact('penjualan'));
    }
    public function createPesanan(Request $request)
    {
        // Generate kode penjualan otomatis
        $last = DB::table('t_penjualan')->orderBy('no_jual', 'desc')->first();
        $no_jual = $last ? 'PJ' . str_pad(intval(substr($last->no_jual, 2)) + 1, 6, '0', STR_PAD_LEFT) : 'PJ000001';

        // Ambil semua no_pesanan yang sudah pernah dipakai di penjualan (jenis pesanan)
        $usedPesanan = DB::table('t_penjualan')
            ->whereNotNull('no_pesanan')
            ->pluck('no_pesanan')
            ->toArray();

        // Ambil daftar pesanan pelanggan dari t_pesanan yang belum pernah dipakai di penjualan
        // dan statusnya masih aktif (misal: status = 'open' atau null)
        $pesanan = DB::table('t_pesanan')
            ->join('t_pelanggan', 't_pesanan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->whereNotIn('t_pesanan.no_pesanan', $usedPesanan)
            //->where(function($q){ $q->whereNull('t_pesanan.status')->orWhere('t_pesanan.status', 'open'); }) // jika ada kolom status
            ->select('t_pesanan.*', 't_pelanggan.nama_pelanggan')
            ->orderBy('t_pesanan.no_pesanan', 'asc')
            ->get();

        // Ambil detail pesanan dari t_pesanan_detail untuk setiap pesanan
        $pesananDetails = [];
        foreach ($pesanan as $psn) {
            $details = DB::table('t_pesanan_detail')
                ->leftJoin('t_produk', 't_pesanan_detail.kode_produk', '=', 't_produk.kode_produk')
                ->leftJoin('t_produk_konsinyasi', 't_pesanan_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
                ->where('t_pesanan_detail.no_pesanan', $psn->no_pesanan)
                ->select(
                    't_pesanan_detail.kode_produk',
                    DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                    DB::raw('COALESCE(t_produk.satuan, t_produk_konsinyasi.satuan) as satuan'),
                    't_pesanan_detail.jumlah',
                    't_pesanan_detail.harga_satuan',
                    't_pesanan_detail.diskon_produk as diskon_satuan'
                )
                ->get()
                ->toArray();
            $pesananDetails[$psn->no_pesanan] = $details;
        }

        $jenis_penjualan = $request->get('jenis_penjualan', 'pesanan');

        return view('penjualan.create_pesanan', compact('no_jual', 'pesanan', 'pesananDetails', 'jenis_penjualan'));
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
        // Debug: log input mentah
        \Log::debug('PenjualanController@store - RAW INPUT', $request->all());

        // Mapping agar field 'total' selalu ada
        $input = $request->all();
        \Log::debug('PenjualanController@store - input sebelum mapping', $input);
        // Bersihkan format Rp dan titik pada field total_harga, diskon, total_jual, total_bayar, kembalian, piutang
        foreach (['total_harga', 'diskon', 'total_jual', 'total_bayar', 'kembalian', 'piutang', 'total'] as $field) {
            if (!empty($input[$field])) {
                $input[$field] = preg_replace('/[^0-9]/', '', $input[$field]);
            }
        }
        if (empty($input['total']) && !empty($input['total_jual'])) {
            $input['total'] = $input['total_jual'];
        } elseif (empty($input['total']) && !empty($input['total_harga'])) {
            $input['total'] = $input['total_harga'];
        }
        // Setelah mapping input
        if (empty($input['status_pembayaran'])) {
            $input['status_pembayaran'] = (!empty($input['piutang']) && $input['piutang'] > 0) ? 'belum lunas' : 'lunas';
        }
        // Pastikan tanggal_jatuh_tempo hanya dikirim jika piutang > 0
        if (empty($input['piutang']) || $input['piutang'] == 0) {
            unset($input['tanggal_jatuh_tempo']);
        }
        // Jika piutang > 0 tapi tanggal_jatuh_tempo kosong, set null (biar validasi jalan)
        if (!empty($input['piutang']) && $input['piutang'] > 0 && empty($input['tanggal_jatuh_tempo'])) {
            $input['tanggal_jatuh_tempo'] = null;
        }
        \Log::debug('PenjualanController@store - input setelah mapping', $input);
        $request->replace($input);

        try {
            \Log::debug('PenjualanController@store - sebelum validasi', $request->all());
            $rules = [
                'no_jual' => 'required|unique:t_penjualan,no_jual',
                'tanggal_jual' => 'required|date',
                'kode_pelanggan' => 'required',
                'total' => 'required|numeric',
                'metode_pembayaran' => 'required|in:tunai,non tunai', // hanya tunai dan non tunai
                'status_pembayaran' => 'required|in:lunas,belum lunas',
                'keterangan' => 'nullable|string|max:100',
            ];
            if ($request->jenis_penjualan !== 'pesanan') {
                $rules['detail_json'] = 'required|json';
            }
            if (($request->piutang ?? 0) > 0) {
                $rules['tanggal_jatuh_tempo'] = 'required|date';
            }
            $request->validate($rules);
            \Log::info('PenjualanController@store - validasi sukses', $request->all());

            $status_pembayaran = ($request->piutang == 0) ? 'lunas' : 'belum lunas';

            DB::transaction(function () use ($request, $status_pembayaran) {
                \Log::debug('PenjualanController@store - sebelum insert t_penjualan', $request->all());
                DB::table('t_penjualan')->insert([
                    'no_jual' => $request->no_jual,
                    'tanggal_jual' => $request->tanggal_jual,
                    'kode_pelanggan' => $request->kode_pelanggan,
                    'no_pesanan' => $request->no_pesanan ?? null,
                    'jenis_penjualan' => $request->jenis_penjualan ?? 'langsung',
                    'total_harga' => $request->total_harga ?? 0,
                    'diskon' => $request->diskon ?? 0,
                    'total_jual' => $request->total_jual ?? 0,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'total_bayar' => $request->total_bayar ?? 0,
                    'kembalian' => $request->kembalian ?? 0,
                    'piutang' => $request->piutang ?? 0,
                    'status_pembayaran' => $request->status_pembayaran,
                    'keterangan' => $request->keterangan,
                ]);

                \Log::debug('PenjualanController@store - setelah insert t_penjualan', ['no_jual' => $request->no_jual]);
                $details = json_decode($request->detail_json, true);
                \Log::debug('PenjualanController@store - detail_json', $details);
                foreach ($details as $i => $detail) {
                    // Deteksi jenis produk (sendiri/konsinyasi) jika belum ada di detail
                    $jenis = isset($detail['jenis']) ? $detail['jenis'] : null;
                    if (!$jenis) {
                        $isProdukSendiri = DB::table('t_produk')->where('kode_produk', $detail['kode_produk'])->exists();
                        $isKonsinyasi = DB::table('t_produk_konsinyasi')->where('kode_produk', $detail['kode_produk'])->exists();
                        if ($isProdukSendiri) {
                            $jenis = 'sendiri';
                        } else if ($isKonsinyasi) {
                            $jenis = 'konsinyasi';
                        } else {
                            $jenis = 'sendiri'; // fallback
                        }
                    }
                    \Log::debug('PenjualanController@store - sebelum insert t_penjualan_detail', ['no_jual' => $request->no_jual, 'detail' => $detail]);
                    DB::table('t_penjualan_detail')->insert([
                        'no_detailjual' => $request->no_jual . '-' . ($i+1),
                        'no_jual' => $request->no_jual,
                        'kode_produk' => $detail['kode_produk'],
                        'jumlah' => $detail['jumlah'],
                        'harga_satuan' => $detail['harga_satuan'],
                        'diskon_produk' => isset($detail['diskon_satuan']) ? $detail['diskon_satuan'] : 0,
                        'subtotal' => $detail['subtotal'],
                        // 'jenis' => $jenis, // jika ingin simpan ke detail, tambahkan kolom di DB
                    ]);

                    \Log::debug('PenjualanController@store - setelah insert t_penjualan_detail', ['no_jual' => $request->no_jual, 'detail' => $detail]);
                    if ($jenis === 'sendiri') {
                        
                        // FIFO HPP: Ambil batch masuk tertua, split insert jika perlu
                        $qtyKeluar = $detail['jumlah'];
                        $kode_produk = $detail['kode_produk'];
                        $tanggal_jual = $request->tanggal_jual;
                        $no_jual = $request->no_jual;
                        $logDetail = [];
                        // Ambil batch masuk (stok masuk, sisa > 0), urutkan dari paling awal
                        $batches = DB::table('t_kartupersproduk')
                            ->where('kode_produk', $kode_produk)
                            ->where('lokasi', 'Gudang')
                            ->where('masuk', '>', 0)
                            ->orderBy('tanggal', 'asc')
                            ->orderBy('id', 'asc')
                            ->get();
                        $qtySisa = $qtyKeluar;
                        foreach ($batches as $batch) {
                            // Hitung sisa batch: masuk - keluar untuk batch ini
                            $keluarBatch = DB::table('t_kartupersproduk')
                                ->where('kode_produk', $kode_produk)
                                ->where('lokasi', 'Gudang')
                                ->where('tanggal', '>=', $batch->tanggal)
                                ->where('id', '>=', $batch->id)
                                ->where('masuk', 0)
                                ->sum('keluar');
                            $sisaBatch = $batch->masuk - $keluarBatch;
                            if ($sisaBatch <= 0) continue;
                            $ambil = min($qtySisa, $sisaBatch);
                            // Pastikan field hpp ada pada batch, jika tidak ada fallback ke 0
                            $hppBatch = property_exists($batch, 'hpp') ? $batch->hpp : (isset($batch->hpp) ? $batch->hpp : 0);
                            DB::table('t_kartupersproduk')->insert([
                                'tanggal' => $tanggal_jual,
                                'kode_produk' => $kode_produk,
                                'masuk' => 0,
                                'keluar' => $ambil,
                                'hpp' => $hppBatch,
                                'lokasi' => 'Gudang',
                                'keterangan' => 'Penjualan',
                                'no_transaksi' => $no_jual
                            ]);
                            $logDetail[] = ['batch_id' => $batch->id, 'keluar' => $ambil, 'hpp' => $hppBatch];
                            $qtySisa -= $ambil;
                            if ($qtySisa <= 0) break;
                        }
                        if ($qtySisa > 0) {
                            // Jika stok batch tidak cukup, tetap insert keluar dengan hpp 0 (atau bisa error)
                            DB::table('t_kartupersproduk')->insert([
                                'tanggal' => $tanggal_jual,
                                'kode_produk' => $kode_produk,
                                'masuk' => 0,
                                'keluar' => $qtySisa,
                                'hpp' => 0,
                                'lokasi' => 'Gudang',
                                'keterangan' => 'Penjualan (stok minus)',
                                'no_transaksi' => $no_jual
                            ]);
                            $logDetail[] = ['batch_id' => null, 'keluar' => $qtySisa, 'hpp' => 0];
                        }
                        \Log::debug('PenjualanController@store - FIFO HPP insert t_kartupersproduk', ['no_jual' => $no_jual, 'detail' => $detail, 'fifo' => $logDetail]);
                    } else if ($jenis === 'konsinyasi') {
                        $lastStok = DB::table('t_kartuperskonsinyasi')
                            ->where('kode_produk', $detail['kode_produk'])
                            ->where('lokasi', 'Gudang')
                            ->orderByDesc('tanggal')
                            ->orderByDesc('id')
                            ->value('sisa');
                        $lastStok = $lastStok ?? 0;
                        $sisaBaru = $lastStok - $detail['jumlah'];
                        // Ambil harga_konsinyasi dari batch masuk tertua (FIFO), fallback ke harga_titip jika tidak ada
                        $batchKonsinyasi = DB::table('t_kartuperskonsinyasi')
                            ->where('kode_produk', $detail['kode_produk'])
                            ->where('lokasi', 'Gudang')
                            ->where('masuk', '>', 0)
                            ->orderBy('tanggal', 'asc')
                            ->orderBy('id', 'asc')
                            ->first();
                        $hargaKonsinyasi = $batchKonsinyasi && property_exists($batchKonsinyasi, 'harga_konsinyasi') ? $batchKonsinyasi->harga_konsinyasi : null;
                        if ($hargaKonsinyasi === null) {
                            $hargaKonsinyasi = DB::table('t_konsinyasimasuk_detail')
                                ->where('kode_produk', $detail['kode_produk'])
                                ->orderByDesc('no_detailkonsinyasimasuk')
                                ->value('harga_titip');
                        }
                        \Log::debug('PenjualanController@store - sebelum insert t_kartuperskonsinyasi', ['no_jual' => $request->no_jual, 'detail' => $detail, 'harga_konsinyasi' => $hargaKonsinyasi]);
                        DB::table('t_kartuperskonsinyasi')->insert([
                            'tanggal' => $request->tanggal_jual,
                            'kode_produk' => $detail['kode_produk'],
                            'masuk' => 0,
                            'keluar' => $detail['jumlah'],
                            'sisa' => $sisaBaru,
                            'harga_konsinyasi' => $hargaKonsinyasi,
                            'lokasi' => 'Gudang',
                            'keterangan' => 'Penjualan',
                            'no_transaksi' => $request->no_jual
                        ]);
                        \Log::debug('PenjualanController@store - setelah insert t_kartuperskonsinyasi', ['no_jual' => $request->no_jual, 'detail' => $detail, 'harga_konsinyasi' => $hargaKonsinyasi]);
                    }
                }

                \Log::debug('PenjualanController@store - selesai semua detail, sebelum update pesanan/piutang', ['no_jual' => $request->no_jual]);
                if ($request->jenis_penjualan === 'pesanan' && $request->no_pesanan) {
                    $total_pesanan = 0;
                    foreach ($details as $detail) {
                        $diskon_satuan = isset($detail['diskon_satuan']) ? $detail['diskon_satuan'] : 0;
                        $harga_satuan = isset($detail['harga_satuan']) ? $detail['harga_satuan'] : 0;
                        $jumlah = isset($detail['jumlah']) ? $detail['jumlah'] : 0;
                        $subtotal = $jumlah * max($harga_satuan - $diskon_satuan, 0);
                        $total_pesanan += $subtotal;
                    }
                    DB::table('t_pesanan')->where('no_pesanan', $request->no_pesanan)->update([
                        'total_pesanan' => $total_pesanan
                    ]);
                }

                if ($request->piutang > 0) {
                    \Log::debug('PenjualanController@store - sebelum insert t_piutang', ['no_jual' => $request->no_jual, 'piutang' => $request->piutang]);
                    $last = DB::table('t_piutang')->where('no_piutang', 'like', 'PI%')->orderBy('no_piutang', 'desc')->first();
                    if ($last && preg_match('/PI(\d+)/', $last->no_piutang, $match)) {
                        $nextNumber = (int)$match[1] + 1;
                    } else {
                        $nextNumber = 1;
                    }
                    do {
                        $no_piutang = 'PI' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
                        $exists = DB::table('t_piutang')->where('no_piutang', $no_piutang)->exists();
                        $nextNumber++;
                    } while ($exists);
                    DB::table('t_piutang')->insert([
                        'no_piutang' => $no_piutang,
                        'no_jual' => $request->no_jual,
                        'kode_pelanggan' => $request->kode_pelanggan,
                        'total_tagihan' => $request->total,
                        'total_bayar' => $request->total_bayar ?? 0,
                        'sisa_piutang' => $request->piutang ?? ($request->total ?? 0),
                        'status_piutang' => ($request->piutang ?? 0) > 0 ? 'belum lunas' : 'lunas',
                        'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo ?? null,
                    ]);
                    \Log::debug('PenjualanController@store - setelah insert t_piutang', ['no_jual' => $request->no_jual]);
                }
                \Log::debug('PenjualanController@store - END TRANSACTION', ['no_jual' => $request->no_jual]);
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
        // Ambil tanggal_jatuh_tempo dari t_piutang jika ada
        $piutang = DB::table('t_piutang')->where('no_jual', $no_jual)->first();
        if ($piutang && isset($piutang->tanggal_jatuh_tempo)) {
            $penjualan->tanggal_jatuh_tempo = $piutang->tanggal_jatuh_tempo;
        }
        $pelanggan = DB::table('t_pelanggan')->get();
        $produk = DB::table('t_produk')->get();

        $details = DB::table('t_penjualan_detail')
            ->leftJoin('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->leftJoin('t_produk_konsinyasi', 't_penjualan_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->where('t_penjualan_detail.no_jual', $no_jual)
            ->select(
                't_penjualan_detail.kode_produk',
                DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                DB::raw('COALESCE(t_produk.satuan, t_produk_konsinyasi.satuan) as satuan'),
                't_penjualan_detail.jumlah',
                't_penjualan_detail.harga_satuan',
                't_penjualan_detail.diskon_produk as diskon_satuan',
                't_penjualan_detail.subtotal'
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
                'diskon_satuan' => isset($d->diskon_satuan) ? $d->diskon_satuan : 0,
                'subtotal' => $d->subtotal,
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
        \Log::info('PenjualanController@update - request', $request->all());

        $rules = [
            'tanggal_jual' => 'required|date',
            'kode_pelanggan' => 'required',
            'total_harga' => 'required|numeric',
            'diskon' => 'required|numeric',
            'total_jual' => 'required|numeric',
            'total_bayar' => 'required|numeric',
            'kembalian' => 'required|numeric',
            'piutang' => 'required|numeric',
            'metode_pembayaran' => 'required|in:tunai,non tunai', // hanya tunai dan non tunai
            'status_pembayaran' => 'nullable',
            'keterangan' => 'nullable|string|max:255',
            'detail_json' => 'required|json'
        ];
        // tanggal_jatuh_tempo hanya wajib jika piutang > 0
        if (($request->piutang ?? 0) > 0) {
            $rules['tanggal_jatuh_tempo'] = 'required|date';
        }
        $request->validate($rules);
        // Pastikan tanggal_jatuh_tempo hanya dikirim jika piutang > 0
        if (($request->piutang ?? 0) == 0) {
            $request->merge(['tanggal_jatuh_tempo' => null]);
        }

        $jenis_penjualan = DB::table('t_penjualan')->where('no_jual', $no_jual)->value('jenis_penjualan');
        $status_pembayaran = ($request->piutang == 0) ? 'lunas' : 'belum lunas';

        try {
            DB::transaction(function () use ($request, $no_jual, $status_pembayaran, $jenis_penjualan) {
                \Log::info('PenjualanController@update - sebelum update t_penjualan', [
                    'no_jual' => $no_jual,
                    'data' => [
                        'tanggal_jual' => $request->tanggal_jual,
                        'kode_pelanggan' => $request->kode_pelanggan,
                        'total_harga' => $request->total_harga,
                        'diskon' => $request->diskon,
                        'total_jual' => $request->total_jual,
                        'total_bayar' => $request->total_bayar,
                        'kembalian' => $request->kembalian,
                        'piutang' => $request->piutang,
                        'metode_pembayaran' => $request->metode_pembayaran,
                        'status_pembayaran' => $status_pembayaran,
                        'keterangan' => $request->keterangan,
                    ]
                ]);
                DB::table('t_penjualan')->where('no_jual', $no_jual)->update([
                    'tanggal_jual' => $request->tanggal_jual,
                    'kode_pelanggan' => $request->kode_pelanggan,
                    'total_harga' => $request->total_harga,
                    'diskon' => $request->diskon,
                    'total_jual' => $request->total_jual,
                    'total_bayar' => $request->total_bayar,
                    'kembalian' => $request->kembalian,
                    'piutang' => $request->piutang,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'status_pembayaran' => $status_pembayaran,
                    'keterangan' => $request->keterangan,
                ]);
                \Log::info('PenjualanController@update - sesudah update t_penjualan', ['no_jual' => $no_jual]);

                // Jika jenis_penjualan bukan pesanan, detail produk boleh diupdate
                DB::table('t_penjualan_detail')->where('no_jual', $no_jual)->delete();
                $details = json_decode($request->detail_json, true);
                foreach ($details as $i => $detail) {
                    // Logic sama persis dengan edit.blade: jumlah bisa diedit, diskon_satuan tidak boleh lebih besar dari harga_satuan
                    $harga_satuan = isset($detail['harga_satuan']) ? (int)$detail['harga_satuan'] : 0;
                    $diskon_satuan = isset($detail['diskon_satuan']) ? (int)$detail['diskon_satuan'] : 0;
                    $jumlah = isset($detail['jumlah']) ? (int)$detail['jumlah'] : 0;
                    if ($diskon_satuan > $harga_satuan) $diskon_satuan = $harga_satuan;
                    if ($jumlah < 1) $jumlah = 1;
                    $subtotal = ($harga_satuan - $diskon_satuan) * $jumlah;
                    DB::table('t_penjualan_detail')->insert([
                        'no_detailjual' => $no_jual . '-' . ($i+1),
                        'no_jual' => $no_jual,
                        'kode_produk' => $detail['kode_produk'],
                        'jumlah' => $jumlah,
                        'harga_satuan' => $harga_satuan,
                        'diskon_produk' => $diskon_satuan,
                        'subtotal' => $subtotal,
                    ]);
                    \Log::info('PenjualanController@update - insert t_penjualan_detail', [
                        'no_jual' => $no_jual,
                        'detail' => $detail
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
                        $sisaBaru = $lastStok - $jumlah;
                        // Ambil harga titip terakhir dari konsinyasi masuk detail
                        $hargaTitip = DB::table('t_konsinyasimasuk_detail')
                            ->where('kode_produk', $detail['kode_produk'])
                            ->orderByDesc('no_detailkonsinyasimasuk')
                            ->value('harga_titip');
                        DB::table('t_kartuperskonsinyasi')->insert([
                            'tanggal' => $request->tanggal_jual,
                            'kode_produk' => $detail['kode_produk'],
                            'masuk' => 0,
                            'keluar' => $jumlah,
                            'sisa' => $sisaBaru,
                            'harga_konsinyasi' => $hargaTitip,
                            'lokasi' => 'Gudang',
                            'keterangan' => 'Penjualan',
                            'no_transaksi' => $no_jual
                        ]);
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
                        'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo ?? null,
                    ]);
                } else if (($request->piutang ?? 0) > 0) {
                    // Jika belum ada, insert baru
                    $last = DB::table('t_piutang')->where('no_piutang', 'like', 'PI%')->orderBy('no_piutang', 'desc')->first();
                    if ($last && preg_match('/PI(\d+)/', $last->no_piutang, $match)) {
                        $nextNumber = (int)$match[1] + 1;
                    } else {
                        $nextNumber = 1;
                    }
                }
            });
            \Log::info('PenjualanController@update - sukses', ['no_jual' => $no_jual]);
            return redirect()->route('penjualan.index')->with('success', 'Penjualan berhasil diupdate!');
        } catch (\Exception $e) {
            \Log::error('PenjualanController@update - gagal', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan saat mengupdate penjualan.')->withInput();
        }
    }

    public function destroy($no_jual)
    {
        DB::transaction(function () use ($no_jual) {
            // Hapus piutang terkait sebelum menghapus penjualan
            DB::table('t_piutang')->where('no_jual', $no_jual)->delete();

            // Hapus semua transaksi keluar konsinyasi yang terkait penjualan ini
            DB::table('t_kartuperskonsinyasi')->where('no_transaksi', $no_jual)->delete();

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
            ->select('t_penjualan.*', 't_pelanggan.nama_pelanggan', 't_penjualan.jenis_penjualan')
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('penjualan.cetak', compact('penjualan', 'details'));
        $pdf->setPaper('a5', 'landscape');
        return $pdf->stream('nota-'.$no_jual.'.pdf');
    }


    public function cetakTagihan($no_jual)
    {
        \Log::info('cetakTagihan: Mulai proses cetak tagihan', ['no_jual' => $no_jual]);
        // Ambil data penjualan beserta relasi pelanggan
        $penjualan = \App\Models\Penjualan::with(['pelanggan'])->where('no_jual', $no_jual)->firstOrFail();
        \Log::debug('cetakTagihan: Data penjualan', ['penjualan' => $penjualan]);
        // Ambil tanggal_jatuh_tempo dari t_piutang jika ada
        $piutang = \DB::table('t_piutang')->where('no_jual', $no_jual)->first();
        \Log::debug('cetakTagihan: Data piutang', ['piutang' => $piutang]);
        if ($piutang && isset($piutang->tanggal_jatuh_tempo)) {
            $penjualan->tanggal_jatuh_tempo = $piutang->tanggal_jatuh_tempo;
            \Log::debug('cetakTagihan: Set tanggal_jatuh_tempo dari piutang', ['tanggal_jatuh_tempo' => $piutang->tanggal_jatuh_tempo]);
        }

        // Pastikan hanya status "belum lunas" yang bisa dicetak tagihannya
        if ($penjualan->status_pembayaran !== 'belum lunas') {
            \Log::warning('cetakTagihan: Status pembayaran bukan belum lunas', ['status_pembayaran' => $penjualan->status_pembayaran]);
            abort(404, 'Tagihan hanya untuk penjualan yang belum lunas.');
        }

        // Ambil detail produk dari t_penjualan_detail, join ke produk & produk konsinyasi
        $details = \DB::table('t_penjualan_detail')
            ->leftJoin('t_produk', 't_penjualan_detail.kode_produk', '=', 't_produk.kode_produk')
            ->leftJoin('t_produk_konsinyasi', 't_penjualan_detail.kode_produk', '=', 't_produk_konsinyasi.kode_produk')
            ->where('t_penjualan_detail.no_jual', $no_jual)
            ->select(
                't_penjualan_detail.*',
                \DB::raw('COALESCE(t_produk.nama_produk, t_produk_konsinyasi.nama_produk) as nama_produk'),
                \DB::raw('COALESCE(t_produk.satuan, t_produk_konsinyasi.satuan) as satuan')
            )
            ->get();
        \Log::debug('cetakTagihan: Detail produk', ['details' => $details]);

        // Render PDF langsung (bukan view HTML)
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('penjualan.cetak_tagihan', compact('penjualan', 'details'));
        $pdf->setPaper('a5', 'portrait');
        \Log::info('cetakTagihan: PDF berhasil dibuat dan akan di-stream', ['no_jual' => $no_jual]);
        return $pdf->stream('tagihan-'.$no_jual.'.pdf');
    }

    public function cetakTagihanPdf($no_jual)
    {
        $penjualan = \App\Models\Penjualan::with(['pelanggan'])->where('no_jual', $no_jual)->firstOrFail();
        $details = \DB::table('t_penjualan_detail')
            ->where('no_jual', $no_jual)
            ->get();
        $pdf = Pdf::loadView('penjualan.cetak_tagihan', compact('penjualan', 'details'));
        $pdf->setPaper('a5', 'portrait');
        return $pdf->stream('tagihan-'.$no_jual.'.pdf');
    }
        /**
     * Cetak laporan rekap penjualan (filter: tanggal, pelanggan, status, dsb)
     */
    public function cetakLaporan(Request $request)
    {
        // Ambil filter dari request
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');
        $kode_pelanggan = $request->input('kode_pelanggan');
        $status_pembayaran = $request->input('status_pembayaran');
        $jenis_penjualan = $request->input('jenis_penjualan');

        $query = DB::table('t_penjualan')
            ->leftJoin('t_pelanggan', 't_penjualan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->select('t_penjualan.*', 't_pelanggan.nama_pelanggan');

        if ($tanggal_awal) {
            $query->whereDate('t_penjualan.tanggal_jual', '>=', $tanggal_awal);
        }
        if ($tanggal_akhir) {
            $query->whereDate('t_penjualan.tanggal_jual', '<=', $tanggal_akhir);
        }
        if ($kode_pelanggan) {
            $query->where('t_penjualan.kode_pelanggan', $kode_pelanggan);
        }
        if ($status_pembayaran) {
            $query->where('t_penjualan.status_pembayaran', $status_pembayaran);
        }
        if ($jenis_penjualan) {
            $query->where('t_penjualan.jenis_penjualan', $jenis_penjualan);
        }

        $penjualan = $query->orderBy('t_penjualan.no_jual', 'asc')->get();

        // Ambil detail produk untuk setiap penjualan (opsional, jika ingin rekap per produk)
        $detailPenjualan = [];
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
            $detailPenjualan[$jual->no_jual] = $details;
        }

        // Kirim ke view cetak_laporan (buat view jika belum ada)
        return view('penjualan.cetak_laporan', compact('penjualan', 'detailPenjualan', 'tanggal_awal', 'tanggal_akhir', 'kode_pelanggan', 'status_pembayaran'));
    }

    public function cetakLaporanPdf(Request $request)
    {
        // Ambil filter dari request
        $tanggal_awal = $request->input('tanggal_awal');
        $tanggal_akhir = $request->input('tanggal_akhir');
        $kode_pelanggan = $request->input('kode_pelanggan');
        $status_pembayaran = $request->input('status_pembayaran');
        $jenis_penjualan = $request->input('jenis_penjualan');

        $query = DB::table('t_penjualan')
            ->leftJoin('t_pelanggan', 't_penjualan.kode_pelanggan', '=', 't_pelanggan.kode_pelanggan')
            ->select('t_penjualan.*', 't_pelanggan.nama_pelanggan');

        if ($tanggal_awal) {
            $query->whereDate('t_penjualan.tanggal_jual', '>=', $tanggal_awal);
        }
        if ($tanggal_akhir) {
            $query->whereDate('t_penjualan.tanggal_jual', '<=', $tanggal_akhir);
        }
        if ($kode_pelanggan) {
            $query->where('t_penjualan.kode_pelanggan', $kode_pelanggan);
        }
        if ($status_pembayaran) {
            $query->where('t_penjualan.status_pembayaran', $status_pembayaran);
        }
        if ($jenis_penjualan) {
            $query->where('t_penjualan.jenis_penjualan', $jenis_penjualan);
        }

        $penjualan = $query->orderBy('t_penjualan.no_jual', 'asc')->get();

        // Jika ingin detailPenjualan, bisa tambahkan di sini (opsional)
        $detailPenjualan = [];
        foreach ($penjualan as $jual) {
            $details = DB::table('t_penjualan_detail')
                ->where('no_jual', $jual->no_jual)
                ->get();
            $detailPenjualan[$jual->no_jual] = $details;
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('penjualan.cetak_laporan', compact('penjualan', 'detailPenjualan', 'tanggal_awal', 'tanggal_akhir', 'kode_pelanggan', 'status_pembayaran'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('laporan-penjualan.pdf');
    }
}