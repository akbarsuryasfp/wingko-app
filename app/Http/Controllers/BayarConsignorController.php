<?php
namespace App\Http\Controllers;

use App\Models\BayarConsignor;
use App\Models\BayarConsignorDetail;
use App\Models\Consignor;
use App\Models\KonsinyasiMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BayarConsignorController extends Controller
{
    // Cetak laporan pembayaran consignor
    public function cetakLaporan(Request $request)
    {
        $sort = $request->get('sort', 'asc');
        $tanggal_awal = $request->get('tanggal_awal');
        $tanggal_akhir = $request->get('tanggal_akhir');
        $query = \App\Models\BayarConsignor::with(['details.produk', 'consignor'])->orderBy('no_bayarconsignor', $sort);
        if ($tanggal_awal) {
            $query->where('tanggal_bayar', '>=', $tanggal_awal);
        }
        if ($tanggal_akhir) {
            $query->where('tanggal_bayar', '<=', $tanggal_akhir);
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('no_bayarconsignor', 'like', "%$search%")
                  ->orWhereHas('consignor', function($q2) use ($search) {
                      $q2->where('nama_consignor', 'like', "%$search%");
                  });
            });
        }
        $list = $query->get();
        return view('bayarconsignor.cetak_laporan', compact('list'));
    }

    // Hapus pembayaran consignor
    public function destroy($no_bayarconsignor)
    {
        // Hapus detail dulu
        DB::table('t_pembayaranconsignor_detail')->where('no_bayarconsignor', $no_bayarconsignor)->delete();
        // Hapus header
        DB::table('t_pembayaranconsignor')->where('no_bayarconsignor', $no_bayarconsignor)->delete();
        return redirect()->route('bayarconsignor.index')->with('success', 'Data pembayaran consignor berhasil dihapus!');
    }
    // Tampilkan detail pembayaran consignor
    public function show($no_bayarconsignor)
    {
        $header = BayarConsignor::with(['details.produk', 'consignor'])->where('no_bayarconsignor', $no_bayarconsignor)->firstOrFail();
        return view('bayarconsignor.detail', compact('header'));
    }
    // Cetak bukti pembayaran consignor
    public function cetak($no_bayarconsignor)
    {
        $header = BayarConsignor::with(['details.produk', 'consignor'])->where('no_bayarconsignor', $no_bayarconsignor)->firstOrFail();
        // Ambil jumlah_stok dari t_konsinyasimasuk_detail untuk setiap detail
        foreach ($header->details as $detail) {
            // Coba ambil no_konsinyasimasuk dari detail jika ada, jika tidak, skip
            $no_konsinyasimasuk = $detail->no_konsinyasimasuk ?? null;
            $kode_produk = $detail->kode_produk ?? null;
            if ($no_konsinyasimasuk && $kode_produk) {
                $stok = DB::table('t_konsinyasimasuk_detail')
                    ->where('no_konsinyasimasuk', $no_konsinyasimasuk)
                    ->where('kode_produk', $kode_produk)
                    ->value('jumlah_stok');
                $detail->jumlah_stok = $stok;
            } else {
                // fallback: cari stok berdasarkan kode_produk saja (ambil stok terakhir)
                $stok = DB::table('t_konsinyasimasuk_detail')
                    ->where('kode_produk', $kode_produk)
                    ->orderByDesc('no_konsinyasimasuk')
                    ->value('jumlah_stok');
                $detail->jumlah_stok = $stok;
            }
        }
        return view('bayarconsignor.cetak', compact('header'));
    }
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'no_bayarconsignor' => 'required',
            'tanggal_bayar' => 'required|date',
            'metode_pembayaran' => 'required',
            'keterangan' => 'nullable',
            'kode_consignor' => 'required',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Proses upload file bukti pembayaran
        $bukti = null;
        if ($request->hasFile('bukti_pembayaran')) {
            $file = $request->file('bukti_pembayaran');
            $bukti = 'bukti_' . $request->no_bayarconsignor . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/bukti_bayarconsignor'), $bukti);
        }

        // Ambil produk konsinyasi untuk consignor terpilih
        $produk = DB::table('t_produk_konsinyasi')
            ->where('kode_consignor', $request->kode_consignor)
            ->select('kode_produk', 'nama_produk')
            ->get();

        $total_bayar = 0;
        foreach ($produk as $p) {
            $total_penjualan = DB::table('t_penjualan_detail')
                ->where('kode_produk', $p->kode_produk)
                ->sum(DB::raw('jumlah * harga_satuan'));
            $total_bayar += $total_penjualan;
        }

        // Simpan ke tabel t_pembayaranconsignor
        DB::table('t_pembayaranconsignor')->insert([
            'no_bayarconsignor' => $request->no_bayarconsignor,
            'tanggal_bayar' => $request->tanggal_bayar,
            'metode_pembayaran' => $request->metode_pembayaran,
            'total_bayar' => $total_bayar,
            'keterangan' => $request->keterangan,
            'kode_consignor' => $request->kode_consignor,
            'bukti' => $bukti,
        ]);

        // Simpan detail produk ke t_pembayaranconsignor_detail
        $i = 1;
        foreach ($produk as $p) {
            $detail = DB::table('t_penjualan_detail')
                ->where('kode_produk', $p->kode_produk)
                ->select(DB::raw('SUM(jumlah) as jumlah_terjual'), DB::raw('MAX(harga_satuan) as harga_satuan'))
                ->first();
            $subtotal = DB::table('t_penjualan_detail')
                ->where('kode_produk', $p->kode_produk)
                ->sum(DB::raw('jumlah * harga_satuan'));

            // Format: [no_bayarconsignor]-[urut 3 digit]
            $no_detail = $request->no_bayarconsignor . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $i++;

            DB::table('t_pembayaranconsignor_detail')->insert([
                'no_detailbayarconsignor' => $no_detail,
                'no_bayarconsignor' => $request->no_bayarconsignor,
                'kode_produk' => $p->kode_produk,
                'jumlah_terjual' => $detail->jumlah_terjual ?? 0,
                'harga_satuan' => $detail->harga_satuan ?? 0,
                'subtotal' => $subtotal,
            ]);
        }

        // Hitung total bayar dari harga titip x jumlah terjual
        $total_bayar_harga_titip = 0;
        foreach ($produk as $p) {
            // Jumlah terjual
            $jumlah_terjual = DB::table('t_penjualan_detail')
                ->where('kode_produk', $p->kode_produk)
                ->sum('jumlah');
            // Harga titip terakhir
            $harga_titip = DB::table('t_konsinyasimasuk_detail')
                ->where('kode_produk', $p->kode_produk)
                ->orderByDesc('no_konsinyasimasuk')
                ->value('harga_titip') ?? 0;
            $total_bayar_harga_titip += $harga_titip * $jumlah_terjual;
        }

        // JURNAL PEMBAYARAN CONSIGNOR
        $no_jurnal = \App\Helpers\JurnalHelper::generateNoJurnal();
        $keterangan_jurnal = 'Pembayaran hutang konsinyasi ' . $request->no_bayarconsignor;
        $tanggal_jurnal = $request->tanggal_bayar;
        $nomor_bukti = $request->no_bayarconsignor;

        // Buat header jurnal
        $jurnal = \App\Models\JurnalUmum::create([
            'no_jurnal' => $no_jurnal,
            'tanggal' => $tanggal_jurnal,
            'keterangan' => $keterangan_jurnal,
            'nomor_bukti' => $nomor_bukti,
        ]);

        // Ambil kode akun dari helper
        $kode_akun_kas = \App\Helpers\JurnalHelper::getKodeAkun('kas_bank');
        $kode_akun_hutang_konsinyasi = \App\Helpers\JurnalHelper::getKodeAkun('hutang_konsinyasi');

        // Jurnal: Debit Hutang Konsinyasi, Kredit Kas/Bank
        \App\Models\JurnalDetail::create([
            'no_jurnal_detail' => \App\Helpers\JurnalHelper::generateNoJurnalDetail($no_jurnal),
            'no_jurnal' => $no_jurnal,
            'kode_akun' => $kode_akun_hutang_konsinyasi,
            'debit' => $total_bayar_harga_titip,
            'kredit' => 0,
        ]);
        \App\Models\JurnalDetail::create([
            'no_jurnal_detail' => \App\Helpers\JurnalHelper::generateNoJurnalDetail($no_jurnal),
            'no_jurnal' => $no_jurnal,
            'kode_akun' => $kode_akun_kas,
            'debit' => 0,
            'kredit' => $total_bayar_harga_titip,
        ]);

        return redirect()->route('bayarconsignor.index')->with('success', 'Pembayaran consignor berhasil disimpan!');
    }
    public function create()
    {
        // Ambil semua consignor
        $allConsignors = DB::table('t_consignor')->get();

        // Cari tanggal terakhir pembayaran untuk setiap consignor
        $lastPaid = DB::table('t_pembayaranconsignor')
            ->select('kode_consignor', DB::raw('MAX(tanggal_bayar) as last_paid'))
            ->groupBy('kode_consignor')
            ->get()
            ->keyBy('kode_consignor');

        // Filter consignor: hanya yang belum pernah dibayar atau tanggal terakhir pembayaran < hari ini
        $consignors = $allConsignors->filter(function($c) use ($lastPaid) {
            if (!isset($lastPaid[$c->kode_consignor])) return true;
            return $lastPaid[$c->kode_consignor]->last_paid < date('Y-m-d');
        });

        $konsinyasiMasuk = \App\Models\KonsinyasiMasuk::all();

        // Generate no_bayarconsignor otomatis
        $last = DB::table('t_pembayaranconsignor')->orderBy('no_bayarconsignor', 'desc')->first();
        $urut = $last ? ((int)substr($last->no_bayarconsignor, -4)) + 1 : 1;
        $no_bayarconsignor = 'BC' . date('Ymd') . str_pad($urut, 4, '0', STR_PAD_LEFT);

        return view('bayarconsignor.create', compact('consignors', 'konsinyasiMasuk', 'no_bayarconsignor'));
    }

    // Endpoint AJAX untuk ambil produk titipan dan data penjualan
    public function produkByConsignor($kode_consignor)
    {
        // Ambil produk konsinyasi yang belum pernah dibayar penuh
        $produk = DB::table('t_produk_konsinyasi')
            ->where('kode_consignor', $kode_consignor)
            ->select('kode_produk', 'nama_produk', 'satuan')
            ->get();

        $result = [];
        $total_bayar = 0;
        foreach ($produk as $p) {
            $terjual = DB::table('t_penjualan_detail')
                ->where('kode_produk', $p->kode_produk)
                ->sum('jumlah');

            $total_penjualan = DB::table('t_penjualan_detail')
                ->where('kode_produk', $p->kode_produk)
                ->sum(DB::raw('jumlah * harga_satuan'));

            $sudah_bayar = DB::table('t_pembayaranconsignor_detail')
                ->where('kode_produk', $p->kode_produk)
                ->sum('jumlah_terjual');

            // Jika semua produk sudah dibayar, skip
            if ($terjual <= $sudah_bayar) {
                continue;
            }

            $result[] = [
                'kode_produk' => $p->kode_produk,
                'nama_produk' => $p->nama_produk,
                'satuan' => $p->satuan,
                'terjual' => $terjual - $sudah_bayar, // hanya sisa yang belum dibayar
                'total_penjualan' => $total_penjualan - ($sudah_bayar * ($total_penjualan/$terjual ?: 0)),
                'sudah_bayar' => $sudah_bayar,
                'sisa_bayar' => $total_penjualan - ($sudah_bayar * ($total_penjualan/$terjual ?: 0)),
            ];
            $total_bayar += $total_penjualan - ($sudah_bayar * ($total_penjualan/$terjual ?: 0));
        }

        // Return HTML tabel identik dengan view, dan total bayar pakai input group
        $html = '<table class="table table-bordered text-center align-middle mt-3">'
            . '<thead>'
            . '<tr>'
            . '<th>No</th>'
            . '<th>Kode Produk</th>'
            . '<th>Nama Produk</th>'
            . '<th>Satuan</th>'
            . '<th>Jumlah Terjual</th>'
            . '<th>Total Penjualan</th>'
            . '</tr>'
            . '</thead>'
            . '<tbody>';
        if (count($result) === 0) {
            $html .= '<tr><td colspan="6" class="text-center">Data produk tidak ditemukan.</td></tr>';
        } else {
            foreach ($result as $i => $p) {
                $html .= '<tr>'
                    . '<td>' . ($i+1) . '</td>'
                    . '<td>' . $p['kode_produk'] . '</td>'
                    . '<td>' . $p['nama_produk'] . '</td>'
                    . '<td>' . ($p['satuan'] ?? '-') . '</td>'
                    . '<td>' . $p['terjual'] . '</td>'
                    . '<td>Rp ' . number_format($p['total_penjualan'],0,',','.') . '</td>'
                    . '</tr>';
            }
        }
        $html .= '</tbody></table>';
        // Total Bayar input group (mirip pesananpenjualan)
        $html .= '<div class="d-flex justify-content-end align-items-center mt-2" style="max-width:400px;margin-left:auto;">'
            . '<label class="me-2 fw-bold mb-0">Total Bayar</label>'
            . '<div class="input-group" style="width:220px;">'
            . '<span class="input-group-text">Rp</span>'
            . '<input type="text" id="total_bayar_display" class="form-control fw-bold" value="' . number_format($total_bayar,0,',','.') . '" readonly tabindex="-1" style="background:#e9ecef;pointer-events:none;">'
            . '</div>'
            . '<input type="hidden" id="total_bayar" name="total_bayar" value="' . $total_bayar . '">' 
            . '</div>';

        return response($html);
    }

    public function index()
    {
        // Ambil filter consignor dan sort jika ada
        $kode_consignor = request('kode_consignor');
        $sort = request('sort', 'asc');
        $tanggal_awal = request('tanggal_awal');
        $tanggal_akhir = request('tanggal_akhir');

        // Ambil semua consignor untuk dropdown
        $consignors = DB::table('t_consignor')->get();

        // Query pembayaran consignor, filter jika ada kode_consignor, tanggal_awal, tanggal_akhir, urutkan sesuai sort
        $query = DB::table('t_pembayaranconsignor')->orderBy('no_bayarconsignor', $sort);
        if ($kode_consignor) {
            $query->where('kode_consignor', $kode_consignor);
        }
        if ($tanggal_awal) {
            $query->where('tanggal_bayar', '>=', $tanggal_awal);
        }
        if ($tanggal_akhir) {
            $query->where('tanggal_bayar', '<=', $tanggal_akhir);
        }
        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('no_bayarconsignor', 'like', "%$search%")
                  ->orWhereIn('kode_consignor', function($sub) use ($search) {
                      $sub->select('kode_consignor')
                          ->from('t_consignor')
                          ->where('nama_consignor', 'like', "%$search%");
                  });
            });
        }
        $list = $query->get();
        $no = 1;

        // Ambil detail produk untuk setiap pembayaran
        foreach ($list as $row) {
            $details = DB::table('t_pembayaranconsignor_detail')
                ->where('no_bayarconsignor', $row->no_bayarconsignor)
                ->get();

            // Gabungkan detail produk ke objek pembayaran
            $row->details = [];
            foreach ($details as $d) {
                $produk = DB::table('t_produk_konsinyasi')
                    ->where('kode_produk', $d->kode_produk)
                    ->first();
                // Tambahkan info produk ke detail
                $d = (array) $d;
                $d['produk'] = $produk;
                $row->details[] = (object) $d;
            }
        }

        return view('bayarconsignor.index', compact('list', 'no', 'consignors'));
    }
}


