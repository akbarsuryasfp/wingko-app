<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProduksiDetail;
use App\Models\HppBahanBaku;
use App\Models\HppTenagaKerja;
use App\Models\HppOverhead;
use App\Models\Bahan;
use App\Models\Karyawan;
use App\Models\BOP;
use App\Models\HppPerProduk;
use App\Models\JurnalUmum;
use App\Models\JurnalDetail;
use App\Helpers\AkunHelper;
use App\Helpers\JurnalHelper;

class HppController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');

        $query = ProduksiDetail::with(['produk', 'produksi']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('t_produksi_detail.no_produksi', 'like', "%$search%")
                  ->orWhere('t_produksi_detail.kode_produk', 'like', "%$search%")
                  ->orWhere('t_produksi_detail.jumlah_unit', 'like', "%$search%")
                  ->orWhereHas('produk', function($q2) use ($search) {
                      $q2->where('nama_produk', 'like', "%$search%");
                  })
                  ->orWhereHas('produksi', function($q3) use ($search) {
                      $q3->where('tanggal_produksi', 'like', "%$search%");
                  });
            });
        }

        $query->join('t_produksi', 't_produksi_detail.no_produksi', '=', 't_produksi.no_produksi')
              ->orderByDesc('t_produksi_detail.no_detail_produksi')
              ->select('t_produksi_detail.*');

        $produksiDetails = $query->paginate($perPage)->withQueryString();
        $hppSudahInput = HppPerProduk::pluck('no_detail_produksi')->toArray();

        return view('hpp.index', compact('produksiDetails', 'hppSudahInput', 'perPage', 'search'));
    }

    public function create($no_detail)
    {
        $detail = ProduksiDetail::with('produk', 'produksi')->findOrFail($no_detail);
        $jumlahProduksi = $detail->jumlah_unit;
        $resep = $detail->produk->resep->details ?? collect();
        $bahan = Bahan::all();
        $karyawan = Karyawan::where('departemen', 'Produksi')->get();
        $bop = BOP::all();

        // Ambil data pemakaian bahan dari produksi (FIFO per batch)
        $bahanProduksi = \DB::table('t_produksi_bahan')
            ->where('no_detail_produksi', $no_detail)
            ->join('t_bahan', 't_bahan.kode_bahan', '=', 't_produksi_bahan.kode_bahan')
            ->select(
                't_produksi_bahan.kode_bahan',
                't_bahan.nama_bahan',
                \DB::raw('SUM(t_produksi_bahan.jumlah * t_produksi_bahan.harga) as total_biaya')
            )
            ->groupBy('t_produksi_bahan.kode_bahan', 't_bahan.nama_bahan')
            ->get();

        // Ambil estimasi tarif BOP per unit dari HPP bulan sebelumnya
        // Ambil semua no_detail_produksi HPP bulan lalu
        $hppBulanLalu = \App\Models\HppPerProduk::whereMonth('tanggal_input', now()->subMonth()->month)->pluck('no_detail_produksi');

        // Hitung total overhead dan total produksi bulan lalu
        $total_overhead = \App\Models\HppPerProduk::whereIn('no_detail_produksi', $hppBulanLalu)->sum('total_overhead');
        $total_produksi = \App\Models\ProduksiDetail::whereIn('no_detail_produksi', $hppBulanLalu)->sum('jumlah_unit');

        // Hitung tarif bop per unit
        $tarif_bop_per_unit = $total_produksi > 0 ? $total_overhead / $total_produksi : 0;
        $tarif_bop_per_unit = round($tarif_bop_per_unit,2); // bulatkan ke integer
        // Jika ingin 2 desimal: round($tarif_bop_per_unit, 2);

        // Jika belum ada data, fallback ke 0
        if (!$tarif_bop_per_unit || is_nan($tarif_bop_per_unit)) {
            $tarif_bop_per_unit = 0;
        }

        return view('hpp.create', compact(
            'detail', 'resep', 'jumlahProduksi', 'bahan', 'karyawan', 'bop', 'tarif_bop_per_unit', 'bahanProduksi'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_detail_produksi' => 'required',
            // validasi lain...
        ]);

        $no_detail = $request->no_detail_produksi;
        $tanggal = now()->format('Ymd');
        $jumlahProduksi = ProduksiDetail::where('no_detail_produksi', $no_detail)->value('jumlah_unit');

        // Simpan HPP Bahan Baku
        $total_bahan = 0;
        foreach ($request->bahan as $i => $b) {
            $no_hpp_bahan = 'HB' . $tanggal . '-' . $no_detail . '-' . $b['kode_bahan']. '-' . $i;
            $total_bahan += $b['total'];
            HppBahanBaku::create([
                'no_hpp_bahan' => $no_hpp_bahan,
                'no_detail_produksi' => $no_detail,
                'kode_bahan' => $b['kode_bahan'],
                'total_bahan' => $b['total'],
            ]);
        }

        // Simpan HPP Tenaga Kerja
        $total_tk = 0;
        foreach ($request->tk as $i => $tk) {
            $no_hpp_btkl = 'HT' . $tanggal . '-' . $no_detail . '-' . $tk['kode_karyawan']. '-' . $i;
            $total_tk += $tk['total'];
            HppTenagaKerja::create([
                'no_hpp_btkl' => $no_hpp_btkl,
                'no_detail_produksi' => $no_detail,
                'kode_karyawan' => $tk['kode_karyawan'],
                'jumlah_jam' => $tk['jam'],
                'tarif_per_jam' => $tk['tarif'],
                'total_biaya_kerja' => $tk['total'],
            ]);
        }

        // Simpan HPP Overhead
        $total_overhead = 0;
        foreach ($request->overhead as $i => $ov) {
            $no_hpp_bop = 'HO' . $tanggal . '-' . $no_detail . '-' . $ov['kode_bop']. '-' . $i;
            $total_overhead += $ov['biaya'];
            HppOverhead::create([
                'no_hpp_bop' => $no_hpp_bop,
                'no_detail_produksi' => $no_detail,
                'kode_bop' => $ov['kode_bop'],
                'biaya_bop' => $ov['biaya'],
            ]);
        }

        // Hitung total HPP dan HPP per produk
        $total_hpp = $total_bahan + $total_tk + $total_overhead;
        $hpp_per_produk = $jumlahProduksi > 0 ? $total_hpp / $jumlahProduksi : 0;

        // Cari id terkecil yang belum terpakai
        $usedIds = HppPerProduk::pluck('id')->toArray();
        $newId = 1;
        while (in_array($newId, $usedIds)) {
            $newId++;
        }

        // Simpan ke t_hpp_per_produk
        HppPerProduk::create([
            'id' => $newId,
            'no_detail_produksi' => $no_detail,
            'kode_produk' => $request->kode_produk ?? ProduksiDetail::where('no_detail_produksi', $no_detail)->value('kode_produk'),
            'total_bahan' => $total_bahan,
            'total_tenaga_kerja' => $total_tk,
            'total_overhead' => $total_overhead,
            'total_hpp' => $total_hpp,
            'hpp_per_produk' => $hpp_per_produk,
            'tanggal_input' => now(),
        ]);

        JurnalHelper::catatJurnalHpp($no_detail, $total_hpp, $total_bahan, $total_tk, $total_overhead);

        return redirect()->route('hpp.index')->with('success', 'Data HPP berhasil disimpan!');
    }

    public function edit($no_detail)
    {
        $detail = ProduksiDetail::with('produk', 'produksi')->where('no_detail_produksi', $no_detail)->firstOrFail();
        $jumlahProduksi = $detail->jumlah_unit;
        $resep = $detail->produk->resep->details ?? collect();
        $bahanHpp = HppBahanBaku::where('no_detail_produksi', $no_detail)->get()->keyBy('kode_bahan');
        $karyawan = Karyawan::where('departemen', 'Produksi')->get();
        $tkHpp = HppTenagaKerja::where('no_detail_produksi', $no_detail)->get()->keyBy('kode_karyawan');
        $bop = BOP::all();
        $bopHpp = HppOverhead::where('no_detail_produksi', $no_detail)->get()->keyBy('kode_bop');

        return view('hpp.edit', compact('detail', 'resep', 'jumlahProduksi', 'bahanHpp', 'karyawan', 'tkHpp', 'bop', 'bopHpp'));
    }

    public function update(Request $request, $no_detail)
    {
        // Update HPP Tenaga Kerja
        foreach ($request->tk as $i => $tk) {
            HppTenagaKerja::updateOrCreate(
                [
                    'no_detail_produksi' => $no_detail,
                    'kode_karyawan' => $tk['kode_karyawan'],
                ],
                [
                    'jumlah_jam' => $tk['jam'],
                    'tarif_per_jam' => $tk['tarif'],
                    'total_biaya_kerja' => $tk['total'],
                ]
            );
        }

        // Update HPP Bahan Baku
        foreach ($request->bahan as $i => $b) {
            HppBahanBaku::updateOrCreate(
                [
                    'no_detail_produksi' => $no_detail,
                    'kode_bahan' => $b['kode_bahan'],
                ],
                [
                    'jumlah_bahan' => $b['jumlah'],
                    'harga_bahan' => $b['harga'],
                    'total_bahan' => $b['total'],
                ]
            );
        }

        // Update HPP Overhead
        foreach ($request->overhead as $i => $ov) {
            HppOverhead::updateOrCreate(
                [
                    'no_detail_produksi' => $no_detail,
                    'kode_bop' => $ov['kode_bop'],
                ],
                [
                    'biaya_bop' => $ov['biaya'],
                ]
            );
        }

        // Hitung ulang total HPP dan update HppPerProduk
        $total_bahan = HppBahanBaku::where('no_detail_produksi', $no_detail)->sum('total_bahan');
        $total_tk = HppTenagaKerja::where('no_detail_produksi', $no_detail)->sum('total_biaya_kerja');
        $total_overhead = HppOverhead::where('no_detail_produksi', $no_detail)->sum('biaya_bop');
        $total_hpp = $total_bahan + $total_tk + $total_overhead;

        $jumlahProduksi = ProduksiDetail::where('no_detail_produksi', $no_detail)->value('jumlah_unit');
        $hpp_per_produk = $jumlahProduksi > 0 ? $total_hpp / $jumlahProduksi : 0;

        HppPerProduk::where('no_detail_produksi', $no_detail)->update([
            'total_bahan' => $total_bahan,
            'total_tenaga_kerja' => $total_tk,
            'total_overhead' => $total_overhead,
            'total_hpp' => $total_hpp,
            'hpp_per_produk' => $hpp_per_produk,
            'tanggal_input' => now(),
        ]);

        // Hapus jurnal lama
        $nomor_bukti = 'AUTO-HPP-' . $no_detail;
        $jurnal = JurnalUmum::where('nomor_bukti', $nomor_bukti)->first();
        if ($jurnal) {
            JurnalDetail::where('id_jurnal', $jurnal->id_jurnal)->delete();
            $jurnal->delete();
        }

        // Buat jurnal baru
        JurnalHelper::catatJurnalHpp($no_detail, $total_hpp, $total_bahan, $total_tk, $total_overhead);

        return redirect()->route('hpp.index')->with('success', 'Data HPP berhasil diupdate!');
    }
}
