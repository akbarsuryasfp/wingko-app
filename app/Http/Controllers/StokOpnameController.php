<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StokOpnameController extends Controller
{
    public function create()
    {
        $bahanList = \App\Models\Bahan::all();
        $akunList = \App\Models\Akun::all();

        // Generate nomor bukti opname otomatis
        $lastOpname = DB::table('t_jurnal_umum')
            ->where('nomor_bukti', 'like', 'SOP%')
            ->orderByDesc('nomor_bukti')
            ->first();

        if ($lastOpname && preg_match('/SOP(\d+)/', $lastOpname->nomor_bukti, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $no_opname = 'SOP' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        return view('stokopname.bahan', compact('bahanList', 'akunList', 'no_opname'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Generate nomor bukti opname otomatis
            $lastOpname = DB::table('t_jurnal_umum')
                ->where('nomor_bukti', 'like', 'SOP%')
                ->orderByDesc('nomor_bukti')
                ->first();

            if ($lastOpname && preg_match('/SOP(\d+)/', $lastOpname->nomor_bukti, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }
            $no_opname = 'SOP' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // 1. Buat id_jurnal baru
            $lastJurnal = DB::table('t_jurnal_umum')->orderBy('id_jurnal', 'desc')->first();
            $id_jurnal = $lastJurnal ? $lastJurnal->id_jurnal + 1 : 1;

            // 2. Insert ke t_jurnal_umum
            DB::table('t_jurnal_umum')->insert([
                'id_jurnal'   => $id_jurnal,
                'tanggal'     => $request->tanggal,
                'keterangan'  => $request->keterangan_umum,
                'nomor_bukti' => $no_opname,
            ]);

            $lastDetail = DB::table('t_jurnal_detail')->orderBy('id_jurnal_detail', 'desc')->first();
            $id_jurnal_detail = $lastDetail ? $lastDetail->id_jurnal_detail + 1 : 1;

            // 3. Loop setiap bahan untuk jurnal detail & kartu stok
            foreach ($request->stok_fisik as $id => $stok_fisik) {
                $stok_sistem = $request->stok_sistem[$id];
                $selisih = $stok_fisik - $stok_sistem;
                $keterangan = $request->keterangan[$id] ?? null;

                if ($selisih == 0) continue;

                // Ambil harga FIFO/LIFO
                $harga = $selisih < 0
                    ? $this->getHargaBahan($id, 'FIFO')
                    : $this->getHargaBahan($id, 'LIFO');

                if ($harga <= 0) {
                    Log::warning('Harga tidak ditemukan', [
                        'id' => $id,
                        'table' => 't_kartupersbahan/t_kartupersproduk',
                        'kode' => $kode_bahan ?? $kode_produk,
                    ]);
                    continue;
                }

                $nominal = abs($selisih) * $harga;
                $kode_akun_persediaan = \App\Models\Bahan::find($id)?->kode_akun ?? '103'; // Default akun persediaan
                $akun_kontra = '519'; // sesuaikan kode akun

                // Jurnal detail
                if ($selisih > 0) {
                    DB::table('t_jurnal_detail')->insert([
                        'id_jurnal_detail' => $id_jurnal_detail++,
                        'id_jurnal'        => $id_jurnal,
                        'kode_akun'        => $kode_akun_persediaan,
                        'debit'            => $nominal,
                        'kredit'           => 0,
                    ]);
                    DB::table('t_jurnal_detail')->insert([
                        'id_jurnal_detail' => $id_jurnal_detail++,
                        'id_jurnal'        => $id_jurnal,
                        'kode_akun'        => $akun_kontra,
                        'debit'            => 0,
                        'kredit'           => $nominal,
                    ]);
                } else {
                    DB::table('t_jurnal_detail')->insert([
                        'id_jurnal_detail' => $id_jurnal_detail++,
                        'id_jurnal'        => $id_jurnal,
                        'kode_akun'        => $akun_kontra,
                        'debit'            => $nominal,
                        'kredit'           => 0,
                    ]);
                    DB::table('t_jurnal_detail')->insert([
                        'id_jurnal_detail' => $id_jurnal_detail++,
                        'id_jurnal'        => $id_jurnal,
                        'kode_akun'        => $kode_akun_persediaan,
                        'debit'            => 0,
                        'kredit'           => $nominal,
                    ]);
                }

                // Kartu stok
                DB::table('t_kartu_stok')->insert([
                    'tanggal'      => $request->tanggal,
                    'no_transaksi' => $no_opname,
                    'kode_bahan'   => $id,
                    'jenis'        => 'opname',
                    'masuk'        => $selisih > 0 ? abs($selisih) : 0,
                    'keluar'       => $selisih < 0 ? abs($selisih) : 0,
                    'keterangan'   => 'Stok Opname: ' . ($keterangan ?? '-'),
                ]);

                // Kartu persediaan bahan (PASTIKAN FIELD HARGA ADA)
                DB::table('t_kartupersbahan')->insert([
                    'tanggal'      => $request->tanggal,
                    'no_transaksi' => $no_opname,
                    'kode_bahan'   => $id,
                    'jenis'        => 'opname',
                    'masuk'        => $selisih > 0 ? abs($selisih) : 0,
                    'keluar'       => $selisih < 0 ? abs($selisih) : 0,
                    'harga'        => $harga,
                    'keterangan'   => 'Stok Opname: ' . ($keterangan ?? '-'),
                ]);
            }

            DB::commit();
            Log::info('Stok opname bahan berhasil', [
                'no_opname' => $no_opname,
                'tanggal' => $request->tanggal,
                'user_id' => auth()->id() ?? null,
            ]);
            return redirect()->route('stokopname.create')->with('success', 'Stok opname berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    public function produk()
    {
        $produkList = \App\Models\Produk::all();

        // Generate nomor bukti opname otomatis
        $lastOpname = DB::table('t_jurnal_umum')
            ->where('nomor_bukti', 'like', 'SOP%')
            ->orderByDesc('nomor_bukti')
            ->first();

        if ($lastOpname && preg_match('/SOP(\d+)/', $lastOpname->nomor_bukti, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }
        $no_opname = 'SOP' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

        return view('stokopname.produk', compact('produkList', 'no_opname'));
    }

    public function storeProduk(Request $request)
    {
        DB::beginTransaction();
        try {
            // Generate nomor bukti opname otomatis
            $lastOpname = DB::table('t_jurnal_umum')
                ->where('nomor_bukti', 'like', 'SOP%')
                ->orderByDesc('nomor_bukti')
                ->first();

            if ($lastOpname && preg_match('/SOP(\d+)/', $lastOpname->nomor_bukti, $matches)) {
                $nextNumber = intval($matches[1]) + 1;
            } else {
                $nextNumber = 1;
            }
            $no_opname = 'SOP' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // 1. Buat id_jurnal baru
            $lastJurnal = DB::table('t_jurnal_umum')->orderBy('id_jurnal', 'desc')->first();
            $id_jurnal = $lastJurnal ? $lastJurnal->id_jurnal + 1 : 1;

            // 2. Insert ke t_jurnal_umum
            DB::table('t_jurnal_umum')->insert([
                'id_jurnal'   => $id_jurnal,
                'tanggal'     => $request->tanggal,
                'keterangan'  => $request->keterangan_umum,
                'nomor_bukti' => $no_opname,
            ]);

            $lastDetail = DB::table('t_jurnal_detail')->orderBy('id_jurnal_detail', 'desc')->first();
            $id_jurnal_detail = $lastDetail ? $lastDetail->id_jurnal_detail + 1 : 1;

            // 3. Loop setiap produk untuk jurnal detail & kartu stok
            foreach ($request->stok_fisik as $id => $stok_fisik) {
                $stok_sistem = $request->stok_sistem[$id];
                $selisih = $stok_fisik - $stok_sistem;
                $keterangan = $request->keterangan[$id] ?? null;

                if ($selisih == 0) continue;

                $kode_akun_persediaan = $bahan->kode_akun_persediaan ?? '103'; // atau ambil dari master produk jika ada
                $akun_kontra = '519'; // kode akun penyesuaian persediaan, sesuaikan jika perlu

                if ($selisih > 0) {
                    // Debit persediaan
                    DB::table('t_jurnal_detail')->insert([
                        'id_jurnal_detail' => $id_jurnal_detail++,
                        'id_jurnal'        => $id_jurnal,
                        'kode_akun'        => $kode_akun_persediaan,
                        'debit'            => abs($selisih),
                        'kredit'           => 0,
                    ]);
                    // Kredit akun kontra (Penyesuaian Persediaan)
                    DB::table('t_jurnal_detail')->insert([
                        'id_jurnal_detail' => $id_jurnal_detail++,
                        'id_jurnal'        => $id_jurnal,
                        'kode_akun'        => $akun_kontra,
                        'debit'            => 0,
                        'kredit'           => abs($selisih),
                    ]);
                } else {
                    // Debit akun kontra (Penyesuaian Persediaan)
                    DB::table('t_jurnal_detail')->insert([
                        'id_jurnal_detail' => $id_jurnal_detail++,
                        'id_jurnal'        => $id_jurnal,
                        'kode_akun'        => $akun_kontra,
                        'debit'            => abs($selisih),
                        'kredit'           => 0,
                    ]);
                    // Kredit persediaan
                    DB::table('t_jurnal_detail')->insert([
                        'id_jurnal_detail' => $id_jurnal_detail++,
                        'id_jurnal'        => $id_jurnal,
                        'kode_akun'        => $kode_akun_persediaan,
                        'debit'            => 0,
                        'kredit'           => abs($selisih),
                    ]);
                }

                // Catat transaksi di kartu stok produk
                DB::table('t_kartu_stok')->insert([
                    'tanggal'      => $request->tanggal,
                    'kode_produk'  => $id,
                    'jenis'        => 'opname',
                    'masuk'        => $selisih > 0 ? abs($selisih) : 0,
                    'keluar'       => $selisih < 0 ? abs($selisih) : 0,
                    'keterangan'   => 'Stok Opname: ' . ($keterangan ?? '-'),
                    'no_referensi' => $no_opname,
                ]);

                // Insert ke t_kartupersproduk
                DB::table('t_kartupersproduk')->insert([
                    'tanggal'      => $request->tanggal,
                    'kode_produk'  => $id,
                    'jenis'        => 'opname',
                    'masuk'        => $selisih > 0 ? abs($selisih) : 0,
                    'keluar'       => $selisih < 0 ? abs($selisih) : 0,
                    'harga'        => $harga,
                    'keterangan'   => 'Stok Opname: ' . ($keterangan ?? '-'),
                    'no_referensi' => $no_opname,
                ]);
            }

            DB::commit();
            Log::info('Stok opname produk berhasil', [
                'no_opname' => $no_opname,
                'tanggal' => $request->tanggal,
                'user_id' => auth()->id() ?? null,
            ]);
            return redirect()->route('stokopname.produk')->with('success', 'Stok opname produk berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => $e->getMessage()]);
        }
    }

    private function getHargaBahan($kode_bahan, $jenis = 'FIFO') {
        $query = DB::table('t_kartupersbahan')
            ->where('kode_bahan', $kode_bahan)
            ->where('masuk', '>', 0);

        // Ganti 'id' dengan primary key tabel Anda jika bukan 'id'
        $orderField = DB::getSchemaBuilder()->hasColumn('t_kartupersbahan', 'id') ? 'id' : 'id_kartupersbahan';

        if ($jenis === 'FIFO') {
            $row = $query->orderBy('tanggal', 'asc')->orderBy($orderField, 'asc')->first();
        } else {
            $row = $query->orderBy('tanggal', 'desc')->orderBy($orderField, 'desc')->first();
        }
        return $row ? $row->harga : 0;
    }

    private function getHargaProduk($kode_produk, $jenis = 'FIFO') {
        $query = DB::table('t_kartupersproduk')
            ->where('kode_produk', $kode_produk)
            ->where('masuk', '>', 0);

        if ($jenis === 'FIFO') {
            $row = $query->orderBy('tanggal', 'asc')->orderBy('id', 'asc')->first();
        } else {
            $row = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->first();
        }
        return $row ? $row->harga : 0;
    }
}