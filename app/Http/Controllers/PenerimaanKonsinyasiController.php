<?php
namespace App\Http\Controllers;

use App\Models\PenerimaanKonsinyasi;
use App\Models\PenerimaanKonsinyasiDetail;
use App\Models\Consignee;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JurnalHelper;

class PenerimaanKonsinyasiController extends Controller
{
    public function cetakLaporan(Request $request)
    {
        $query = \App\Models\PenerimaanKonsinyasi::with(['consignee', 'details.produk']);

        // Filter periode
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_terima', [$request->tanggal_awal, $request->tanggal_akhir]);
        } elseif ($request->filled('tanggal_awal')) {
            $query->where('tanggal_terima', '>=', $request->tanggal_awal);
        } elseif ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_terima', '<=', $request->tanggal_akhir);
        }

        // Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_penerimaankonsinyasi', 'like', "%$search%")
                  ->orWhereHas('consignee', function($qc) use ($search) {
                      $qc->where('nama_consignee', 'like', "%$search%");
                  });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'asc');
        $query->orderBy('no_penerimaankonsinyasi', $sort === 'desc' ? 'desc' : 'asc');

        $penerimaanKonsinyasiList = $query->get();
        return view('penerimaankonsinyasi.cetak_laporan', compact('penerimaanKonsinyasiList'));
    }
    public function index(Request $request)
    {
        $query = PenerimaanKonsinyasi::with(['consignee', 'details.produk']);

        // Filter periode
        if ($request->filled('tanggal_awal') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_terima', [$request->tanggal_awal, $request->tanggal_akhir]);
        } elseif ($request->filled('tanggal_awal')) {
            $query->where('tanggal_terima', '>=', $request->tanggal_awal);
        } elseif ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_terima', '<=', $request->tanggal_akhir);
        }

        // Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_penerimaankonsinyasi', 'like', "%$search%")
                  ->orWhereHas('consignee', function($qc) use ($search) {
                      $qc->where('nama_consignee', 'like', "%$search%");
                  });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'asc');
        $query->orderBy('no_penerimaankonsinyasi', $sort === 'desc' ? 'desc' : 'asc');

        $penerimaanKonsinyasiList = $query->get();
        return view('penerimaankonsinyasi.index', compact('penerimaanKonsinyasiList'));
    }

    public function create()
    {
        $consigneeList = Consignee::all();
        $produkList = Produk::all();
        $no_penerimaankonsinyasi = $this->generateNoPenerimaan();
        // Ambil semua konsinyasi keluar (beserta consignee)
        $konsinyasiKeluarList = \App\Models\KonsinyasiKeluar::with('consignee')->orderBy('tanggal_setor', 'desc')->get();
        // Ambil no_konsinyasikeluar yang sudah dipakai di penerimaan konsinyasi
        $sudahDipakaiKonsinyasiKeluar = \App\Models\PenerimaanKonsinyasi::pluck('no_konsinyasikeluar')->toArray();
        return view('penerimaankonsinyasi.create', compact('consigneeList', 'produkList', 'no_penerimaankonsinyasi', 'konsinyasiKeluarList', 'sudahDipakaiKonsinyasiKeluar'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_terima' => 'required|date',
            'detail_json' => 'required',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
            DB::beginTransaction();
            try {
                $bukti = null;
                // Debug: log if file is present
                \Log::info('store: hasFile(bukti): ' . ($request->hasFile('bukti') ? 'yes' : 'no'));
                if ($request->hasFile('bukti')) {
                    $file = $request->file('bukti');
                    \Log::info('store: bukti original name: ' . $file->getClientOriginalName());
                    $bukti = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads'), $bukti);
                    \Log::info('store: bukti saved as: ' . $bukti);
                }
                $dataToInsert = [
                    'no_penerimaankonsinyasi' => $request->no_penerimaankonsinyasi,
                    'no_konsinyasikeluar' => $request->no_konsinyasikeluar,
                    'tanggal_terima' => $request->tanggal_terima,
                    'kode_consignee' => $request->kode_consignee,
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'total_terima' => $request->total_terima,
                    'keterangan' => $request->keterangan,
                    'bukti' => $bukti,
                ];
                \Log::info('store: data to insert into penerimaankonsinyasi', $dataToInsert);
                $header = PenerimaanKonsinyasi::create($dataToInsert);
            $details = json_decode($request->detail_json, true);
            $total_hpp = 0;
            foreach ($details as $idx => $d) {
                $no_detail = $header->no_penerimaankonsinyasi . '-' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                PenerimaanKonsinyasiDetail::create([
                    'no_detailpenerimaankonsinyasi' => $no_detail,
                    'no_penerimaankonsinyasi' => $header->no_penerimaankonsinyasi,
                    'kode_produk' => $d['kode_produk'],
                    'jumlah_setor' => $d['jumlah_setor'],
                    'jumlah_terjual' => $d['jumlah_terjual'],
                    'satuan' => $d['satuan'],
                    'harga_satuan' => $d['harga_satuan'],
                    'subtotal' => $d['subtotal'],
                ]);
                // Hitung HPP dari kartu persediaan (FIFO)
                $qtyTerjual = $d['jumlah_terjual'];
                $kode_produk = $d['kode_produk'];
                $kode_lokasi = '1'; // kode lokasi Gudang
                $kartuKeluar = DB::table('t_kartupersproduk')
                    ->where('kode_produk', $kode_produk)
                    ->where('lokasi', $kode_lokasi)
                    ->where('keluar', '>', 0)
                    ->where('no_transaksi', $request->no_konsinyasikeluar)
                    ->get();
                foreach ($kartuKeluar as $k) {
                    $total_hpp += ($k->keluar * $k->hpp);
                }
            }

            // --- Penjurnalan Penjualan Konsinyasi Keluar ---
            $no_jurnal = JurnalHelper::generateNoJurnal();
            $tanggal = $request->tanggal_terima;
            $nomor_bukti = $header->no_penerimaankonsinyasi;
            $keterangan = 'Penjualan konsinyasi keluar ' . $header->no_penerimaankonsinyasi;

            $jurnal = \App\Models\JurnalUmum::create([
                'no_jurnal' => $no_jurnal,
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'nomor_bukti' => $nomor_bukti,
            ]);

            // Debit Kas/Bank/Piutang
            $kode_akun_kas = JurnalHelper::getKodeAkun('kas_bank');
            \App\Models\JurnalDetail::create([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal' => $no_jurnal,
                'kode_akun' => $kode_akun_kas,
                'debit' => $request->total_terima,
                'kredit' => 0,
                'keterangan' => 'Penerimaan penjualan konsinyasi keluar'
            ]);
            // Kredit Penjualan
            $kode_akun_penjualan = JurnalHelper::getKodeAkun('penjualan');
            \App\Models\JurnalDetail::create([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal' => $no_jurnal,
                'kode_akun' => $kode_akun_penjualan,
                'debit' => 0,
                'kredit' => $request->total_terima,
                'keterangan' => 'Penjualan konsinyasi keluar'
            ]);
            // Debit HPP
            $kode_akun_hpp = JurnalHelper::getKodeAkun('hpp');
            \App\Models\JurnalDetail::create([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal' => $no_jurnal,
                'kode_akun' => $kode_akun_hpp,
                'debit' => $total_hpp,
                'kredit' => 0,
                'keterangan' => 'Harga pokok penjualan konsinyasi keluar'
            ]);
            // Kredit Persediaan Barang Dagang
            $kode_akun_persediaan = JurnalHelper::getKodeAkun('persediaan_jadi');
            \App\Models\JurnalDetail::create([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal' => $no_jurnal,
                'kode_akun' => $kode_akun_persediaan,
                'debit' => 0,
                'kredit' => $total_hpp,
                'keterangan' => 'Persediaan keluar penjualan konsinyasi'
            ]);
            // --- End Penjurnalan ---

            DB::commit();
            \Log::info('store: DB commit success for no_penerimaankonsinyasi: ' . $header->no_penerimaankonsinyasi);
            return redirect()->route('penerimaankonsinyasi.index')->with('success', 'Data berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('store: exception: ' . $e->getMessage());
            \Log::error('store: exception trace: ' . $e->getTraceAsString());
            return back()->withErrors(['msg' => 'Gagal simpan: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $header = PenerimaanKonsinyasi::with(['consignee', 'details.produk'])->findOrFail($id);
        return view('penerimaankonsinyasi.detail', compact('header'));
    }

    public function edit($id)
    {
        $header = PenerimaanKonsinyasi::with(['consignee', 'details.produk'])->findOrFail($id);
        $consigneeList = Consignee::all();
        $produkList = Produk::all();
        $detailList = $header->details;
        return view('penerimaankonsinyasi.edit', compact('header', 'consigneeList', 'produkList', 'detailList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal_terima' => 'required|date',
            'metode_pembayaran' => 'required',
            'keterangan' => 'nullable',
            'detail' => 'required|array',
            'total_terima' => 'required|numeric',
            'bukti' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        DB::beginTransaction();
        try {
            $header = PenerimaanKonsinyasi::findOrFail($id);
            // Handle file upload
            if ($request->hasFile('bukti')) {
                // Hapus file lama jika ada
                if ($header->bukti && file_exists(public_path('uploads/' . $header->bukti))) {
                    @unlink(public_path('uploads/' . $header->bukti));
                }
                $file = $request->file('bukti');
                $bukti = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads'), $bukti);
                $header->bukti = $bukti;
            }
            foreach ($request->detail as $d) {
                $detail = PenerimaanKonsinyasiDetail::where('no_detailpenerimaankonsinyasi', $d['no_detailpenerimaankonsinyasi'])->first();
                if ($detail) {
                    $jumlah_setor = isset($d['jumlah_setor']) ? (int)$d['jumlah_setor'] : $detail->jumlah_setor;
                    $harga_satuan = isset($d['harga_satuan']) ? (int)$d['harga_satuan'] : $detail->harga_satuan;
                    $jumlah_terjual = max(0, min((int)$d['jumlah_terjual'], $jumlah_setor));
                    $subtotal = isset($d['subtotal']) ? (int)$d['subtotal'] : ($jumlah_terjual * $harga_satuan);
                    $detail->update([
                        'jumlah_terjual' => $jumlah_terjual,
                        'subtotal' => $subtotal,
                        'jumlah_setor' => $jumlah_setor,
                        'harga_satuan' => $harga_satuan,
                    ]);
                }
            }
            $header->update([
                'tanggal_terima' => $request->tanggal_terima,
                'metode_pembayaran' => $request->metode_pembayaran,
                'keterangan' => $request->keterangan,
                'total_terima' => $request->total_terima,
                'bukti' => $header->bukti,
            ]);

            // --- Hapus jurnal lama ---
            $jurnalLama = \App\Models\JurnalUmum::where('nomor_bukti', $header->no_penerimaankonsinyasi)->first();
            if ($jurnalLama) {
                \App\Models\JurnalDetail::where('no_jurnal', $jurnalLama->no_jurnal)->delete();
                $jurnalLama->delete();
            }

            // --- Hitung ulang total HPP ---
            $details = PenerimaanKonsinyasiDetail::where('no_penerimaankonsinyasi', $header->no_penerimaankonsinyasi)->get();
            $total_hpp = 0;
            foreach ($details as $d) {
                $qtyTerjual = $d->jumlah_terjual;
                $kode_produk = $d->kode_produk;
                $kode_lokasi = '1';
                $kartuKeluar = DB::table('t_kartupersproduk')
                    ->where('kode_produk', $kode_produk)
                    ->where('lokasi', $kode_lokasi)
                    ->where('keluar', '>', 0)
                    ->where('no_transaksi', $header->no_konsinyasikeluar)
                    ->get();
                foreach ($kartuKeluar as $k) {
                    $total_hpp += ($k->keluar * $k->hpp);
                }
            }

            // --- Buat ulang jurnal ---
            $no_jurnal = JurnalHelper::generateNoJurnal();
            $tanggal = $header->tanggal_terima;
            $nomor_bukti = $header->no_penerimaankonsinyasi;
            $keterangan = 'Penjualan konsinyasi keluar ' . $header->no_penerimaankonsinyasi;

            $jurnal = \App\Models\JurnalUmum::create([
                'no_jurnal' => $no_jurnal,
                'tanggal' => $tanggal,
                'keterangan' => $keterangan,
                'nomor_bukti' => $nomor_bukti,
            ]);

            $kode_akun_kas = JurnalHelper::getKodeAkun('kas_bank');
            \App\Models\JurnalDetail::create([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal' => $no_jurnal,
                'kode_akun' => $kode_akun_kas,
                'debit' => $header->total_terima,
                'kredit' => 0,
                'keterangan' => 'Penerimaan penjualan konsinyasi keluar'
            ]);
            $kode_akun_penjualan = JurnalHelper::getKodeAkun('penjualan');
            \App\Models\JurnalDetail::create([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal' => $no_jurnal,
                'kode_akun' => $kode_akun_penjualan,
                'debit' => 0,
                'kredit' => $header->total_terima,
                'keterangan' => 'Penjualan konsinyasi keluar'
            ]);
            $kode_akun_hpp = JurnalHelper::getKodeAkun('hpp');
            \App\Models\JurnalDetail::create([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal' => $no_jurnal,
                'kode_akun' => $kode_akun_hpp,
                'debit' => $total_hpp,
                'kredit' => 0,
                'keterangan' => 'Harga pokok penjualan konsinyasi keluar'
            ]);
            $kode_akun_persediaan = JurnalHelper::getKodeAkun('persediaan_jadi');
            \App\Models\JurnalDetail::create([
                'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                'no_jurnal' => $no_jurnal,
                'kode_akun' => $kode_akun_persediaan,
                'debit' => 0,
                'kredit' => $total_hpp,
                'keterangan' => 'Persediaan keluar penjualan konsinyasi'
            ]);
            // --- End jurnal ulang ---

            DB::commit();
            return redirect()->route('penerimaankonsinyasi.index')->with('success', 'Data berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Gagal update: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $header = PenerimaanKonsinyasi::findOrFail($id);
            // Hapus detail penerimaan konsinyasi
            PenerimaanKonsinyasiDetail::where('no_penerimaankonsinyasi', $header->no_penerimaankonsinyasi)->delete();

             $jurnalLama = \App\Models\JurnalUmum::where('nomor_bukti', $header->no_penerimaankonsinyasi)->first();
            if ($jurnalLama) {
                \App\Models\JurnalDetail::where('no_jurnal', $jurnalLama->no_jurnal)->delete();
                $jurnalLama->delete();
            }

            // Cari dan hapus retur consignee yang terhubung, hanya yang input dari create_returterima (alasan semua detailnya 'Tidak Terjual')
            $returConsignees = \App\Models\ReturConsignee::where('no_konsinyasikeluar', $header->no_konsinyasikeluar)->get();
            foreach ($returConsignees as $retur) {
                $allTidakTerjual = true;
                $details = \App\Models\ReturConsigneeDetail::where('no_returconsignee', $retur->no_returconsignee)->get();
                if ($details->count() == 0) {
                    $allTidakTerjual = false;
                } else {
                    foreach ($details as $d) {
                        $alasan = strtolower(trim($d->alasan ?? ''));
                        // Hanya hapus jika semua alasan persis 'tidak terjual'
                        if ($alasan !== 'tidak terjual') {
                            $allTidakTerjual = false;
                            break;
                        }
                    }
                }
                if ($allTidakTerjual) {
                    // Hapus detail retur
                    \App\Models\ReturConsigneeDetail::where('no_returconsignee', $retur->no_returconsignee)->delete();
                    // Hapus retur
                    $retur->delete();
                }
            }

            // Hapus header penerimaan konsinyasi
            $header->delete();
            DB::commit();
            return redirect()->route('penerimaankonsinyasi.index')->with('success', 'Data berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => 'Gagal hapus: ' . $e->getMessage()]);
        }
    }

    private function generateNoPenerimaan()
    {
        $prefix = 'PK' . date('Ymd');
        $last = PenerimaanKonsinyasi::where('no_penerimaankonsinyasi', 'like', $prefix.'%')
            ->orderBy('no_penerimaankonsinyasi', 'desc')->first();
        if ($last) {
            $num = (int)substr($last->no_penerimaankonsinyasi, -4) + 1;
        } else {
            $num = 1;
        }
        return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    // API: Ambil Konsinyasi Keluar & detail berdasarkan Consignee
    public function apiKonsinyasiKeluarByConsignee($kode_consignee)
    {
        $konsinyasi = \App\Models\KonsinyasiKeluar::with(['details.produk'])
            ->where('kode_consignee', $kode_consignee)
            ->orderBy('tanggal_setor', 'desc')
            ->first();
        if (!$konsinyasi) {
            return response()->json(['no_konsinyasikeluar' => null, 'produkList' => []]);
        }
        $produkList = $konsinyasi->details->map(function($d) {
            return [
                'kode_produk' => $d->kode_produk,
                'nama_produk' => $d->produk ? $d->produk->nama_produk : '',
                'jumlah_setor' => $d->jumlah_setor,
                'satuan' => $d->satuan,
                'harga_satuan' => $d->harga_setor,
            ];
        });
        return response()->json([
            'no_konsinyasikeluar' => $konsinyasi->no_konsinyasikeluar,
            'produkList' => $produkList,
        ]);
    }

    // API: Ambil detail konsinyasi keluar & detail produk berdasarkan no konsinyasi keluar
    public function apiKonsinyasiKeluarDetail($no_konsinyasikeluar)
    {
        $konsinyasi = \App\Models\KonsinyasiKeluar::with(['consignee', 'details.produk'])
            ->where('no_konsinyasikeluar', $no_konsinyasikeluar)
            ->first();
        if (!$konsinyasi) {
            return response()->json(['success' => false, 'msg' => 'Data tidak ditemukan']);
        }
        $produkList = $konsinyasi->details->map(function($d) {
            // Tidak perlu cek retur, karena retur langsung ditukar
            return [
                'kode_produk' => $d->kode_produk,
                'nama_produk' => $d->produk ? $d->produk->nama_produk : '',
                'jumlah_setor' => $d->jumlah_setor,
                'jumlah_retur' => 0, // selalu 0, tidak mengurangi maksimal
                'satuan' => $d->satuan,
                'harga_satuan' => $d->harga_setor,
            ];
        });
        return response()->json([
            'success' => true,
            'no_konsinyasikeluar' => $konsinyasi->no_konsinyasikeluar,
            'tanggal_setor' => $konsinyasi->tanggal_setor,
            'kode_consignee' => $konsinyasi->kode_consignee,
            'nama_consignee' => $konsinyasi->consignee ? $konsinyasi->consignee->nama_consignee : '',
            'produkList' => $produkList,
        ]);
    }
}
