<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                    $harga = DB::table('t_kartupersbahan')
                        ->where('kode_bahan', $bahan->kode_bahan)
                        ->where('masuk', '>', 0)
                        ->orderBy('tanggal', 'asc')
                        ->value('harga') ?? 0;
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

                DB::table('t_kartupersbahan')->insert([
                    'id'           => $nextId++,
                    'no_transaksi' => $no_opname,
                    'tanggal'      => $tanggal,
                    'kode_bahan'   => $bahan->kode_bahan,
                    'masuk'        => $detail['jumlah'] > 0 ? abs($detail['jumlah']) : 0,
                    'keluar'       => $detail['jumlah'] < 0 ? abs($detail['jumlah']) : 0,
                    'harga'        => $detail['harga_satuan'],
                    'satuan'       => $bahan->satuan,
                    'keterangan'   => $detail['alasan'],
                ]);
                DB::table('t_bahan')
                    ->where('kode_bahan', $bahan->kode_bahan)
                    ->update(['stok' => $detail['jumlah'] + (
                        DB::table('t_kartupersbahan')
                            ->where('kode_bahan', $bahan->kode_bahan)
                            ->selectRaw('COALESCE(SUM(masuk),0) - COALESCE(SUM(keluar),0) as stok')
                            ->value('stok') ?? 0
                    )]);
                \Log::info('Update stok & kartu persediaan', [
                    'kode_bahan' => $bahan->kode_bahan,
                    'jumlah' => $detail['jumlah']
                ]);
            }

            // Hitung total nominal untuk jurnal hanya dari tab aktif
            $total_debet = 0;
            $total_kredit = 0;
            foreach ($request->stok_fisik as $kode_bahan => $stok_fisik) {
                $stok_sistem = $request->stok_sistem[$kode_bahan] ?? 0;
                $selisih = $stok_fisik - $stok_sistem;
                if ($selisih == 0) continue;

                // Ambil data bahan dan pastikan hanya tab aktif
                $bahan = DB::table('t_bahan')->where('kode_bahan', $kode_bahan)->first();
                if (!$bahan || $bahan->kode_kategori !== $tabAktif) continue;

                // Ambil harga: minus = harga paling awal (FIFO), plus = harga paling akhir (LIFO)
                if ($selisih < 0) {
                    $harga = DB::table('t_kartupersbahan')
                        ->where('kode_bahan', $kode_bahan)
                        ->where('masuk', '>', 0)
                        ->orderBy('tanggal', 'asc')
                        ->value('harga') ?? 0;
                } else {
                    $harga = DB::table('t_kartupersbahan')
                        ->where('kode_bahan', $kode_bahan)
                        ->where('masuk', '>', 0)
                        ->orderBy('tanggal', 'desc')
                        ->value('harga') ?? 0;
                }
                $nominal = abs($selisih) * $harga;
                if ($selisih < 0) {
                    $total_debet += $nominal;
                } else {
                    $total_kredit += $nominal;
                }
            }

            // Insert ke jurnal jika ada selisih
            if ($total_debet > 0 || $total_kredit > 0) {
                $lastJurnal = DB::table('t_jurnal_umum')->orderBy('id_jurnal', 'desc')->first();
                $id_jurnal = $lastJurnal ? $lastJurnal->id_jurnal + 1 : 1;

                DB::table('t_jurnal_umum')->insert([
                    'id_jurnal'   => $id_jurnal,
                    'tanggal'     => $tanggal,
                    'keterangan'  => 'Stok Opname ' . $tabAktif . ' ' . $request->no_opname,
                    'nomor_bukti' => $request->no_opname,
                ]);

                $id_jurnal_detail = DB::table('t_jurnal_detail')->max('id_jurnal_detail') ?? 0;
                $id_jurnal_detail++;

                // Tentukan kode akun persediaan sesuai tab
                $kode_akun_persediaan = '103'; // Default bahan baku
                if ($tabAktif == 'BP') $kode_akun_persediaan = '104'; // Bahan Penolong
                if ($tabAktif == 'BHP') $kode_akun_persediaan = '106'; // Bahan Habis Pakai

                // Selisih minus: Debet 510, Kredit persediaan
                if ($total_debet > 0) {
                    DB::table('t_jurnal_detail')->insert([
                        [
                            'id_jurnal_detail' => $id_jurnal_detail++,
                            'id_jurnal'        => $id_jurnal,
                            'kode_akun'        => '510', // Beban Selisih Persediaan
                            'debit'            => $total_debet,
                            'kredit'           => 0,
                        ],
                        [
                            'id_jurnal_detail' => $id_jurnal_detail++,
                            'id_jurnal'        => $id_jurnal,
                            'kode_akun'        => $kode_akun_persediaan,
                            'debit'            => 0,
                            'kredit'           => $total_debet,
                        ],
                    ]);
                }
                // Selisih plus: Debet persediaan, Kredit 710
                if ($total_kredit > 0) {
                    DB::table('t_jurnal_detail')->insert([
                        [
                            'id_jurnal_detail' => $id_jurnal_detail++,
                            'id_jurnal'        => $id_jurnal,
                            'kode_akun'        => $kode_akun_persediaan,
                            'debit'            => $total_kredit,
                            'kredit'           => 0,
                        ],
                        [
                            'id_jurnal_detail' => $id_jurnal_detail++,
                            'id_jurnal'        => $id_jurnal,
                            'kode_akun'        => '710', // Pendapatan Lain-lain
                            'debit'            => 0,
                            'kredit'           => $total_kredit,
                        ],
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

        $tanggal = $request->tanggal;
        $keterangan_umum = $request->keterangan_umum;

        $lastId = DB::table('t_kartupersproduk')->max('id');
        $nextId = $lastId ? $lastId + 1 : 1;

        \Log::debug('Data opname produk', [
            'stok_fisik' => $request->stok_fisik,
            'stok_sistem' => $request->stok_sistem,
        ]);

        try {
            DB::beginTransaction();

            // Insert ke t_penyesuaian (SEBELUM proses lain)
            DB::table('t_penyesuaian')->insert([
                'no_penyesuaian' => $no_opname,
                'tanggal'        => $tanggal,
                'keterangan'     => 'Penyesuaian Stok Opname Produk ' . $no_opname,
            ]);

            // Simpan ke t_kartupersproduk & update stok
            foreach ($request->stok_fisik as $kode_produk => $stok_fisik) {
                $stok_sistem = $request->stok_sistem[$kode_produk] ?? 0;
                $selisih = $stok_fisik - $stok_sistem;
                $keterangan_produk = $request->keterangan[$kode_produk] ?? '';
                $keterangan = 'Stok Opname: ' . trim($keterangan_umum . ($keterangan_produk ? ' - ' . $keterangan_produk : ''));

                if ($selisih == 0) continue; // Data dengan selisih 0 tidak diproses

                // Ambil data produk
                $produk = DB::table('t_produk')->where('kode_produk', $kode_produk)->first();
                if (!$produk) continue;

                // Ambil harga: minus = harga paling awal (FIFO), plus = harga paling akhir (LIFO)
                if ($selisih < 0) {
                    $harga = DB::table('t_kartupersproduk')
                        ->where('kode_produk', $produk->kode_produk)
                        ->where('masuk', '>', 0)
                        ->orderBy('tanggal', 'asc')
                        ->value('harga') ?? 0;
                } else {
                    $harga = DB::table('t_kartupersproduk')
                        ->where('kode_produk', $produk->kode_produk)
                        ->where('masuk', '>', 0)
                        ->orderBy('tanggal', 'desc')
                        ->value('harga') ?? 0;
                }

                DB::table('t_kartupersproduk')->insert([
                    'id'           => $nextId++,
                    'no_transaksi' => $no_opname,
                    'tanggal'      => $tanggal,
                    'kode_produk'  => $produk->kode_produk,
                    'masuk'        => $selisih > 0 ? abs($selisih) : 0,
                    'keluar'       => $selisih < 0 ? abs($selisih) : 0,
                    'harga'        => $harga,
                    'satuan'       => $produk->satuan,
                    'keterangan'   => $keterangan,
                ]);

                // Update stok akhir pada t_produk
                DB::table('t_produk')
                    ->where('kode_produk', $produk->kode_produk)
                    ->update(['stok' => $stok_fisik]);
            }

            // Hitung total nominal untuk jurnal
            $total_debet = 0;
            $total_kredit = 0;
            foreach ($request->stok_fisik as $kode_produk => $stok_fisik) {
                $stok_sistem = $request->stok_sistem[$kode_produk] ?? 0;
                $selisih = $stok_fisik - $stok_sistem;
                if ($selisih == 0) continue; // Data dengan selisih 0 tidak dihitung jurnal

                if ($selisih < 0) {
                    $harga = DB::table('t_kartupersproduk')
                        ->where('kode_produk', $kode_produk)
                        ->where('masuk', '>', 0)
                        ->orderBy('tanggal', 'asc')
                        ->value('harga') ?? 0;
                } else {
                    $harga = DB::table('t_kartupersproduk')
                        ->where('kode_produk', $kode_produk)
                        ->where('masuk', '>', 0)
                        ->orderBy('tanggal', 'desc')
                        ->value('harga') ?? 0;
                }
                $nominal = abs($selisih) * $harga;
                if ($selisih < 0) {
                    $total_debet += $nominal;
                } else {
                    $total_kredit += $nominal;
                }
            }

            // Insert ke jurnal jika ada selisih
            if ($total_debet > 0 || $total_kredit > 0) {
                $lastJurnal = DB::table('t_jurnal_umum')->orderBy('id_jurnal', 'desc')->first();
                $id_jurnal = $lastJurnal ? $lastJurnal->id_jurnal + 1 : 1;

                DB::table('t_jurnal_umum')->insert([
                    'id_jurnal'   => $id_jurnal,
                    'tanggal'     => $tanggal,
                    'keterangan'  => 'Stok Opname Produk ' . $no_opname,
                    'nomor_bukti' => $no_opname,
                ]);

                $id_jurnal_detail = DB::table('t_jurnal_detail')->max('id_jurnal_detail') ?? 0;
                $id_jurnal_detail++;

                // Selisih minus: Debet 510, Kredit 105
                if ($total_debet > 0) {
                    DB::table('t_jurnal_detail')->insert([
                        [
                            'id_jurnal_detail' => $id_jurnal_detail++,
                            'id_jurnal'        => $id_jurnal,
                            'kode_akun'        => '510', // Beban Selisih Persediaan
                            'debit'            => $total_debet,
                            'kredit'           => 0,
                        ],
                        [
                            'id_jurnal_detail' => $id_jurnal_detail++,
                            'id_jurnal'        => $id_jurnal,
                            'kode_akun'        => '105', // Persediaan Produk
                            'debit'            => 0,
                            'kredit'           => $total_debet,
                        ],
                    ]);
                }

                // Selisih plus: Debet 105, Kredit 710
                if ($total_kredit > 0) {
                    DB::table('t_jurnal_detail')->insert([
                        [
                            'id_jurnal_detail' => $id_jurnal_detail++,
                            'id_jurnal'        => $id_jurnal,
                            'kode_akun'        => '105', // Persediaan Produk
                            'debit'            => $total_kredit,
                            'kredit'           => 0,
                        ],
                        [
                            'id_jurnal_detail' => $id_jurnal_detail++,
                            'id_jurnal'        => $id_jurnal,
                            'kode_akun'        => '710', // Pendapatan Lain-lain
                            'debit'            => 0,
                            'kredit'           => $total_kredit,
                        ],
                    ]);
                }
            }

            DB::commit();
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

        // Ambil semua produk untuk ditampilkan di form
        $produkList = DB::table('t_produk')->get();

        return view('stokopname.produk', compact('no_opname', 'produkList'));
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