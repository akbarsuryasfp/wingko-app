<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\JurnalHelper;
use App\Helpers\AkunHelper;

class StokOpnameController extends Controller
{
    public function store(Request $request)
    {
        $tabAktif = $request->tab_aktif;

        $request->validate([
            'tanggal' => 'required|date',
            'stok_fisik' => 'required|array',
            'stok_sistem' => 'required|array',
        ]);

        $last = DB::table('t_penyesuaian')->where('no_penyesuaian', 'like', 'SOP%')->orderByDesc('no_penyesuaian')->value('no_penyesuaian');
        if ($last) {
            $num = (int)substr($last, 3) + 1;
            $no_opname = 'SOP' . str_pad($num, 6, '0', STR_PAD_LEFT);
        } else {
            $no_opname = 'SOP000001';
        }

        $tanggal = $request->tanggal;
        $keterangan_umum = $request->keterangan_umum;

        $lastId = DB::table('t_kartupersbahan')->max('id');
        $nextId = $lastId ? $lastId + 1 : 1;

        // Ambil file bukti stok opname jika ada
        $buktiStok = null;
        if ($request->hasFile('bukti_stokopname')) {
            $buktiStok = $request->file('bukti_stokopname')->store('bukti_stokopname', 'public');
        }

        \Log::debug('Data opname bahan', [
            'stok_fisik' => $request->stok_fisik,
            'stok_sistem' => $request->stok_sistem,
        ]);

        try {
            DB::beginTransaction();

            // 1. Insert ke t_penyesuaian (header)
            DB::table('t_penyesuaian')->insert([
                'no_penyesuaian' => $no_opname,
                'tanggal'        => $tanggal,
                'keterangan'     => 'Penyesuaian Stok Opname ' . $no_opname,
                'bukti_stokopname' => $buktiStok,
            ]);
            \Log::info('Insert t_penyesuaian', ['no_penyesuaian' => $no_opname]);

            // 2. Insert ke t_penyesuaian_detail (detail)
            $detailData = [];
            foreach ($request->stok_fisik as $kode_bahan => $stok_fisik) {
                if (is_null($stok_fisik)) continue;

                $bahan = DB::table('t_bahan')->where('kode_bahan', $kode_bahan)->first();
                if (!$bahan || $bahan->kode_kategori !== $tabAktif) continue;

                $stok_sistem = DB::table('t_kartupersbahan')
                    ->where('kode_bahan', $kode_bahan)
                    ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
                    ->value('stok') ?? 0;
                $selisih = $stok_fisik - $stok_sistem;
                $keterangan_bahan = $request->keterangan[$kode_bahan] ?? '';
                $keterangan = 'Stok Opname: ' . trim($keterangan_umum . ($keterangan_bahan ? ' - ' . $keterangan_bahan : ''));

                if ($selisih == 0) continue;

                if ($selisih < 0) {
                    $harga = getHargaFIFO($kode_bahan, abs($selisih));
                } else {
                    $harga = DB::table('t_kartupersbahan')
                        ->where('kode_bahan', $bahan->kode_bahan)
                        ->where('masuk', '>', 0)
                        ->orderBy('tanggal', 'desc')
                        ->value('harga') ?? 0;
                }

                $total_nilai = abs($selisih) * $harga;

                $detailData[] = [
                    'no_penyesuaian' => $no_opname,
                    'tipe_item'      => 'BAHAN',
                    'kode_item'      => $bahan->kode_bahan ?? null,
                    'jumlah'         => $selisih,
                    'harga_satuan'   => $harga,
                    'total_nilai'    => $total_nilai,
                    'alasan'         => $keterangan,
                ];
                \Log::info('Prepare t_penyesuaian_detail', [
                    'kode_item' => $bahan->kode_bahan,
                    'jumlah' => $selisih,
                    'harga' => $harga,
                    'total_nilai' => $total_nilai
                ]);
            }
            if (count($detailData) > 0) {
                DB::table('t_penyesuaian_detail')->insert($detailData);
                \Log::info('Insert t_penyesuaian_detail', ['count' => count($detailData)]);
            }

            // 3. Setelah data masuk ke penyesuaian & detail, baru update kartu stok & stok bahan
            foreach ($detailData as $detail) {
    $bahan = DB::table('t_bahan')->where('kode_bahan', $detail['kode_item'])->first();
    if (!$bahan) continue;

    if ($detail['jumlah'] < 0) {
        // Selisih kurang: keluarkan stok per batch FIFO
        $qty_keluar = abs($detail['jumlah']);
        $stokMasuk = DB::table('t_kartupersbahan')
            ->where('kode_bahan', $bahan->kode_bahan)
            ->whereRaw('(masuk - keluar) > 0')
            ->orderBy('tanggal', 'asc')
            ->get();

        foreach ($stokMasuk as $row) {
            if ($qty_keluar <= 0) break;
            $stok_tersedia = $row->masuk - $row->keluar;
            $ambil = min($qty_keluar, $stok_tersedia);

            DB::table('t_kartupersbahan')->insert([
                'id'           => $nextId++,
                'no_transaksi' => $no_opname,
                'tanggal'      => $tanggal,
                'kode_bahan'   => $bahan->kode_bahan,
                'masuk'        => 0,
                'keluar'       => $ambil,
                'harga'        => $row->harga,
                'satuan'       => $bahan->satuan,
                'keterangan'   => $detail['alasan'],
                'tanggal_exp'  => $row->tanggal_exp,
            ]);
            $qty_keluar -= $ambil;
        }
    } else {
        // Selisih lebih: tambah stok, ambil harga & tanggal_exp dari stok masuk terakhir
        $lastMasuk = DB::table('t_kartupersbahan')
            ->where('kode_bahan', $bahan->kode_bahan)
            ->where('masuk', '>', 0)
            ->orderBy('tanggal', 'desc')
            ->first();

        DB::table('t_kartupersbahan')->insert([
            'id'           => $nextId++,
            'no_transaksi' => $no_opname,
            'tanggal'      => $tanggal,
            'kode_bahan'   => $bahan->kode_bahan,
            'masuk'        => $detail['jumlah'],
            'keluar'       => 0,
            'harga'        => $lastMasuk ? $lastMasuk->harga : 0,
            'satuan'       => $bahan->satuan,
            'keterangan'   => $detail['alasan'],
            'tanggal_exp'  => $lastMasuk ? $lastMasuk->tanggal_exp : null,
        ]);
    }

    // Update stok akhir pada t_bahan
    $stok_akhir = DB::table('t_kartupersbahan')
        ->where('kode_bahan', $bahan->kode_bahan)
        ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
        ->value('stok') ?? 0;
    DB::table('t_bahan')
        ->where('kode_bahan', $bahan->kode_bahan)
        ->update(['stok' => $stok_akhir]);
}

// Hitung total debet dan kredit
$total_debet = 0;   // Untuk selisih kurang (stok hilang)
$total_kredit = 0;  // Untuk selisih lebih (stok bertambah)

foreach ($detailData as $detail) {
    if ($detail['jumlah'] < 0) {
        $total_debet += abs($detail['jumlah']) * $detail['harga_satuan'];
    } else if ($detail['jumlah'] > 0) {
        $total_kredit += abs($detail['jumlah']) * $detail['harga_satuan'];
    }
}

if ($total_debet > 0 || $total_kredit > 0) {
    $no_jurnal = JurnalHelper::generateNoJurnal();

    $kode_akun_persediaan = JurnalHelper::getKodeAkun('persediaan_bahan');
    $kode_akun_beban = JurnalHelper::getKodeAkun('beban_kerugian');
    $kode_akun_pendapatan = JurnalHelper::getKodeAkun('pendapatan_lain');

    DB::table('t_jurnal_umum')->insert([
        'no_jurnal'   => $no_jurnal,
        'tanggal'     => $tanggal,
        'keterangan'  => 'Stok Opname ' . $no_opname,
        'nomor_bukti' => $no_opname,
        'jenis_jurnal'=> 'penyesuaian'
    ]);

    // Selisih kurang (stok hilang): Debit Beban, Kredit Persediaan
    if ($total_debet > 0) {
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => $kode_akun_beban,
            'debit'            => $total_debet,
            'kredit'           => 0,
        ]);
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => $kode_akun_persediaan,
            'debit'            => 0,
            'kredit'           => $total_debet,
        ]);
    }

    // Selisih lebih (stok bertambah): Debit Persediaan, Kredit Pendapatan Lain-lain
    if ($total_kredit > 0) {
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => $kode_akun_persediaan,
            'debit'            => $total_kredit,
            'kredit'           => 0,
        ]);
        DB::table('t_jurnal_detail')->insert([
            'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
            'no_jurnal'        => $no_jurnal,
            'kode_akun'        => $kode_akun_pendapatan,
            'debit'            => 0,
            'kredit'           => $total_kredit,
        ]);
    }
}
            DB::commit();
            return redirect()->back()->with('success', 'Stok opname berhasil disimpan. No Opname: ' . $no_opname);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal menyimpan stok opname bahan', ['error' => $e->getMessage()]);
            return back()->withErrors(['msg' => $e->getMessage()]);
        }
    }
    public function create()
    {
        // Generate nomor opname otomatis
        $last = DB::table('t_penyesuaian')->where('no_penyesuaian', 'like', 'SOP%')->orderByDesc('no_penyesuaian')->value('no_penyesuaian');
        if ($last) {
            $num = (int)substr($last, 3) + 1;
            $no_opname = 'SOP' . str_pad($num, 6, '0', STR_PAD_LEFT);
        } else {
            $no_opname = 'SOP000001';
        }

        // Ambil semua bahan untuk ditampilkan di form
        $bahanList = DB::table('t_bahan')->get()->map(function($bahan) {
            $stok_sistem = DB::table('t_kartupersbahan')
                ->where('kode_bahan', $bahan->kode_bahan)
                ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
                ->value('stok') ?? 0;
            $bahan->stok_sistem = $stok_sistem;
            return $bahan;
        });

        return view('stokopname.bahan', compact('no_opname', 'bahanList'));
    }

public function storeProduk(Request $request)
{
    \Log::info('StokOpnameProduk: Mulai proses', [
        'tanggal' => $request->tanggal,
        'stok_fisik' => $request->stok_fisik,
        'stok_sistem' => $request->stok_sistem,
        'file' => $request->file('bukti_stokopname')
    ]);

    $request->validate([
        'tanggal' => 'required|date',
        'stok_fisik' => 'required|array',
        'stok_sistem' => 'required|array',
    ]);

    // Generate nomor opname otomatis (SOP000001, SOP000002, dst)
    $last = DB::table('t_penyesuaian')->where('no_penyesuaian', 'like', 'SOP%')->orderByDesc('no_penyesuaian')->value('no_penyesuaian');
    if ($last) {
        $num = (int)substr($last, 3) + 1;
        $no_opname = 'SOP' . str_pad($num, 6, '0', STR_PAD_LEFT);
    } else {
        $no_opname = 'SOP000001';
    }
    \Log::info('StokOpnameProduk: Nomor opname', ['no_opname' => $no_opname]);

    $tanggal = $request->tanggal;
    $keterangan_umum = $request->keterangan_umum;

    $lastId = DB::table('t_kartupersproduk')->max('id');
    $nextId = $lastId ? $lastId + 1 : 1;

    // Ambil file bukti stok opname jika ada
    $buktiStok = null;
    if ($request->hasFile('bukti_stokopname')) {
        $buktiStok = $request->file('bukti_stokopname')->store('bukti_stokopname', 'public');
        \Log::info('StokOpnameProduk: File bukti terupload', ['buktiStok' => $buktiStok]);
    }

    try {
        DB::beginTransaction();

        // Insert ke t_penyesuaian (header)
        DB::table('t_penyesuaian')->insert([
            'no_penyesuaian' => $no_opname,
            'tanggal'        => $tanggal,
            'keterangan'     => 'Penyesuaian Stok Opname Produk ' . $no_opname,
            'bukti_stokopname' => $buktiStok,
        ]);
        \Log::info('StokOpnameProduk: Insert t_penyesuaian', ['no_penyesuaian' => $no_opname]);

        $detailData = [];
        foreach ($request->stok_fisik as $kode_produk => $stok_fisik) {
            if (is_null($stok_fisik)) continue;

            $produk = DB::table('t_produk')->where('kode_produk', $kode_produk)->first();
            if (!$produk) {
                \Log::warning('StokOpnameProduk: Produk tidak ditemukan', ['kode_produk' => $kode_produk]);
                continue;
            }

            $stok_sistem = DB::table('t_kartupersproduk')
                ->where('kode_produk', $kode_produk)
                ->where('lokasi', 1)
                ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
                ->value('stok') ?? 0;
            $selisih = $stok_fisik - $stok_sistem;
            $keterangan_produk = $request->keterangan[$kode_produk] ?? '';
            $keterangan = 'Stok Opname: ' . trim($keterangan_umum . ($keterangan_produk ? ' - ' . $keterangan_produk : ''));

            if ($selisih == 0) continue;

            // Hitung hpp
            if ($selisih < 0) {
                $hpp = getHargaFIFOProduk($kode_produk, abs($selisih));
            } else {
                $lastMasuk = DB::table('t_kartupersproduk')
                    ->where('kode_produk', $produk->kode_produk)
                    ->where('masuk', '>', 0)
                    ->orderBy('tanggal', 'desc')
                    ->first();
                $hpp = $lastMasuk ? $lastMasuk->hpp : 0;
            }

            $total_nilai = abs($selisih) * $hpp;

            $detailData[] = [
                'no_penyesuaian' => $no_opname,
                'tipe_item'      => 'PRODUK',
                'kode_item'      => $produk->kode_produk,
                'jumlah'         => $selisih,
                'harga_satuan'   => $hpp,
                'total_nilai'    => $total_nilai,
                'alasan'         => $keterangan,
            ];
            \Log::info('StokOpnameProduk: Prepare detail', [
                'kode_produk' => $produk->kode_produk,
                'stok_fisik' => $stok_fisik,
                'stok_sistem' => $stok_sistem,
                'selisih' => $selisih,
                'hpp' => $hpp,
                'total_nilai' => $total_nilai
            ]);
        }

        if (count($detailData) > 0) {
            DB::table('t_penyesuaian_detail')->insert($detailData);
            \Log::info('StokOpnameProduk: Insert t_penyesuaian_detail', ['count' => count($detailData)]);
        }

        // Insert ke kartu persediaan produk & update stok
        foreach ($detailData as $detail) {
            $produk = DB::table('t_produk')->where('kode_produk', $detail['kode_item'])->first();
            if (!$produk) continue;

            if ($detail['jumlah'] < 0) {
                $qty_keluar = abs($detail['jumlah']);
                $stokMasuk = DB::table('t_kartupersproduk')
                    ->where('kode_produk', $produk->kode_produk)
                    ->where('lokasi', 1)
                    ->whereRaw('(masuk - keluar) > 0')
                    ->orderBy('tanggal', 'asc')
                    ->get();

                foreach ($stokMasuk as $row) {
                    if ($qty_keluar <= 0) break;
                    $stok_tersedia = $row->masuk - $row->keluar;
                    $ambil = min($qty_keluar, $stok_tersedia);

                    DB::table('t_kartupersproduk')->insert([
                        'id'           => $nextId++,
                        'no_transaksi' => $no_opname,
                        'tanggal'      => $tanggal,
                        'kode_produk'  => $produk->kode_produk,
                        'masuk'        => 0,
                        'keluar'       => $ambil,
                        'hpp'          => $row->hpp,
                        'satuan'       => $produk->satuan,
                        'keterangan'   => $detail['alasan'],
                        'tanggal_exp'  => $row->tanggal_exp,
                        'lokasi'       => 1,
                    ]);
                    \Log::info('StokOpnameProduk: Insert kartu keluar', [
                        'kode_produk' => $produk->kode_produk,
                        'keluar' => $ambil,
                        'hpp' => $row->hpp,
                        'tanggal_exp' => $row->tanggal_exp
                    ]);
                    $qty_keluar -= $ambil;
                }
            } else {
                $lastMasuk = DB::table('t_kartupersproduk')
                    ->where('kode_produk', $produk->kode_produk)
                    ->where('masuk', '>', 0)
                    ->orderBy('tanggal', 'desc')
                    ->first();

                DB::table('t_kartupersproduk')->insert([
                    'id'           => $nextId++,
                    'no_transaksi' => $no_opname,
                    'tanggal'      => $tanggal,
                    'kode_produk'  => $produk->kode_produk,
                    'masuk'        => $detail['jumlah'],
                    'keluar'       => 0,
                    'hpp'          => $lastMasuk ? $lastMasuk->hpp : 0,
                    'satuan'       => $produk->satuan,
                    'keterangan'   => $detail['alasan'],
                    'tanggal_exp'  => $lastMasuk ? $lastMasuk->tanggal_exp : null,
                    'lokasi'       => 1,
                ]);
                \Log::info('StokOpnameProduk: Insert kartu masuk', [
                    'kode_produk' => $produk->kode_produk,
                    'masuk' => $detail['jumlah'],
                    'hpp' => $lastMasuk ? $lastMasuk->hpp : 0,
                    'tanggal_exp' => $lastMasuk ? $lastMasuk->tanggal_exp : null
                ]);
            }

            // Update stok akhir pada t_produk
          
        }

        // Jurnal
        $total_debet = 0;
        $total_kredit = 0;
        foreach ($detailData as $detail) {
            if ($detail['jumlah'] < 0) {
                $total_debet += abs($detail['jumlah']) * $detail['harga_satuan'];
            } else if ($detail['jumlah'] > 0) {
                $total_kredit += abs($detail['jumlah']) * $detail['harga_satuan'];
            }
        }

        if ($total_debet > 0 || $total_kredit > 0) {
            $no_jurnal = JurnalHelper::generateNoJurnal();

            $kode_akun_persediaan = JurnalHelper::getKodeAkun('persediaan_produk');
            $kode_akun_beban = JurnalHelper::getKodeAkun('beban_kerugian');
            $kode_akun_pendapatan = JurnalHelper::getKodeAkun('pendapatan_lain');

            DB::table('t_jurnal_umum')->insert([
                'no_jurnal'   => $no_jurnal,
                'tanggal'     => $tanggal,
                'keterangan'  => 'Stok Opname Produk ' . $no_opname,
                'nomor_bukti' => $no_opname,
                'jenis_jurnal'=> 'penyesuaian'
            ]);
            \Log::info('StokOpnameProduk: Insert jurnal umum', ['no_jurnal' => $no_jurnal]);

            // Selisih kurang (stok hilang): Debit Beban, Kredit Persediaan
            if ($total_debet > 0) {
                DB::table('t_jurnal_detail')->insert([
                    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                    'no_jurnal'        => $no_jurnal,
                    'kode_akun'        => $kode_akun_beban,
                    'debit'            => $total_debet,
                    'kredit'           => 0,
                ]);
                DB::table('t_jurnal_detail')->insert([
                    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                    'no_jurnal'        => $no_jurnal,
                    'kode_akun'        => $kode_akun_persediaan,
                    'debit'            => 0,
                    'kredit'           => $total_debet,
                ]);
                \Log::info('StokOpnameProduk: Insert jurnal detail debet', ['total_debet' => $total_debet]);
            }

            // Selisih lebih (stok bertambah): Debit Persediaan, Kredit Pendapatan Lain-lain
            if ($total_kredit > 0) {
                DB::table('t_jurnal_detail')->insert([
                    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                    'no_jurnal'        => $no_jurnal,
                    'kode_akun'        => $kode_akun_persediaan,
                    'debit'            => $total_kredit,
                    'kredit'           => 0,
                ]);
                DB::table('t_jurnal_detail')->insert([
                    'no_jurnal_detail' => JurnalHelper::generateNoJurnalDetail($no_jurnal),
                    'no_jurnal'        => $no_jurnal,
                    'kode_akun'        => $kode_akun_pendapatan,
                    'debit'            => 0,
                    'kredit'           => $total_kredit,
                ]);
                \Log::info('StokOpnameProduk: Insert jurnal detail kredit', ['total_kredit' => $total_kredit]);
            }
        }

        DB::commit();
        \Log::info('StokOpnameProduk: Sukses simpan opname', ['no_opname' => $no_opname]);
        return redirect()->back()->with('success', 'Stok opname produk berhasil disimpan. No Opname: ' . $no_opname);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Gagal menyimpan stok opname produk', ['error' => $e->getMessage()]);
        return back()->withErrors(['msg' => $e->getMessage()]);
    }
}
    public function produk()
{
    // Generate nomor opname otomatis untuk produk (SOP000001)
    $last = DB::table('t_penyesuaian')->where('no_penyesuaian', 'like', 'SOP%')->orderByDesc('no_penyesuaian')->value('no_penyesuaian');
    if ($last) {
        $num = (int)substr($last, 3) + 1;
        $no_opname = 'SOP' . str_pad($num, 6, '0', STR_PAD_LEFT);
    } else {
        $no_opname = 'SOP000001';
    }

    // Ambil semua produk dan hitung stok sistem dari kartu persediaan produk
    $produkList = DB::table('t_produk')->get()->map(function($produk) {
        $stok_sistem = DB::table('t_kartupersproduk')
            ->where('kode_produk', $produk->kode_produk)
            ->where('lokasi', 1) // hanya ambil stok di lokasi 1 (Gudang)
            ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
            ->value('stok') ?? 0;
        $produk->stok_sistem = $stok_sistem;
        return $produk;
    });

    return view('stokopname.produk', compact('no_opname', 'produkList'));
}

public function riwayatBahan(Request $request)
{
    $periodeAwal = $request->periode_awal ?? date('Y-m-01');
    $periodeAkhir = $request->periode_akhir ?? date('Y-m-d');
    $riwayat = DB::table('t_penyesuaian as p')
        ->join('t_penyesuaian_detail as d', 'p.no_penyesuaian', '=', 'd.no_penyesuaian')
        ->leftJoin('t_bahan as b', 'd.kode_item', '=', 'b.kode_bahan')
        ->where('d.tipe_item', 'BAHAN')
        ->whereBetween('p.tanggal', [$periodeAwal, $periodeAkhir])
        ->select(
            'p.no_penyesuaian',
            'p.tanggal',
            'p.bukti_stokopname',
            'd.tipe_item',
            'd.jumlah',
            'd.alasan',
            'b.nama_bahan'
        )
        ->orderByDesc('p.tanggal')
        ->paginate(10);

    return view('stokopname.riwayat_bahan', compact('riwayat', 'periodeAwal', 'periodeAkhir'));
}

public function riwayatProduk(Request $request)
{
    $periodeAwal = $request->periode_awal ?? date('Y-m-01');
    $periodeAkhir = $request->periode_akhir ?? date('Y-m-d');
    $riwayat = DB::table('t_penyesuaian as p')
        ->join('t_penyesuaian_detail as d', 'p.no_penyesuaian', '=', 'd.no_penyesuaian')
        ->where('d.tipe_item', 'PRODUK')
        ->whereBetween('p.tanggal', [$periodeAwal, $periodeAkhir])
        ->select('p.no_penyesuaian', 'p.tanggal', 'p.bukti_stokopname', 'd.tipe_item')
        ->orderByDesc('p.tanggal')
        ->paginate(10);

    return view('stokopname.riwayat_produk', compact('riwayat', 'periodeAwal', 'periodeAkhir'));
}
}

/**
 * Ambil harga satuan FIFO dari stok masuk yang masih ada stok.
 * @param string $kode_bahan
 * @param int $qty_keluar
 * @return float
 */
function getHargaFIFO($kode_bahan, $qty_keluar) {
    $stokMasuk = DB::table('t_kartupersbahan')
        ->where('kode_bahan', $kode_bahan)
        ->where('masuk', '>', 0)
        ->orderBy('tanggal', 'asc')
        ->get(['masuk', 'keluar', 'harga']);

    $qty_sisa = $qty_keluar;
    $harga_total = 0;
    $qty_diambil = 0;

    foreach ($stokMasuk as $row) {
        $stok_tersedia = $row->masuk - $row->keluar;
        if ($stok_tersedia <= 0) continue;

        $ambil = min($qty_sisa, $stok_tersedia);
        $harga_total += $ambil * $row->harga;
        $qty_diambil += $ambil;
        $qty_sisa -= $ambil;

        if ($qty_sisa <= 0) break;
    }

    return $qty_diambil > 0 ? ($harga_total / $qty_diambil) : 0;
}

/**
 * Ambil harga satuan FIFO dari stok masuk yang masih ada stok untuk produk.
 * @param string $kode_produk
 * @param int $qty_keluar
 * @return float
 */
function getHargaFIFOProduk($kode_produk, $qty_keluar) {
    $stokMasuk = DB::table('t_kartupersproduk')
        ->where('kode_produk', $kode_produk)
        ->where('masuk', '>', 0)
        ->orderBy('tanggal', 'asc')
        ->get(['masuk', 'keluar', 'hpp']);

    $qty_sisa = $qty_keluar;
    $harga_total = 0;
    $qty_diambil = 0;

    foreach ($stokMasuk as $row) {
        $stok_tersedia = $row->masuk - $row->keluar;
        if ($stok_tersedia <= 0) continue;

        $ambil = min($qty_sisa, $stok_tersedia);
        $harga_total += $ambil * $row->hpp;
        $qty_diambil += $ambil;
        $qty_sisa -= $ambil;

        if ($qty_sisa <= 0) break;
    }

    return $qty_diambil > 0 ? ($harga_total / $qty_diambil) : 0;
}

/**
 * Generate nomor jurnal otomatis
 * @return int
 */
function generateNoJurnal() {
    $lastJurnal = DB::table('t_jurnal_umum')->orderBy('no_jurnal', 'desc')->first();
    return $lastJurnal ? $lastJurnal->no_jurnal + 1 : 1;
}

/**
 * Generate nomor jurnal detail otomatis
 * @return int
 */
function generateNoJurnalDetail() {
    $lastDetail = DB::table('t_jurnal_detail')->orderBy('no_jurnal_detail', 'desc')->first();
    return $lastDetail ? $lastDetail->no_jurnal_detail + 1 : 1;
}