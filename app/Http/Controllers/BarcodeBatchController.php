<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Picqer\Barcode\BarcodeGeneratorPNG; // Tambahkan di atas class

class BarcodeBatchController extends Controller
{
    public function info(Request $request)
    {
        $no_transaksi = $request->no_transaksi;
        $batch = DB::table('t_kartupersproduk')
            ->where('no_transaksi', $no_transaksi)
            ->first();

        if (!$batch) {
            return response()->json(['success' => false, 'message' => 'Batch tidak ditemukan']);
        }

        $nama_produk = DB::table('t_produk')->where('kode_produk', $batch->kode_produk)->value('nama_produk');
        $batch->nama_produk = $nama_produk;
        $batch->sisa = ($batch->masuk ?? 0) - ($batch->keluar ?? 0);

        return response()->json(['success' => true, 'batch' => $batch]);
    }

    public function printBatchBarcodes()
    {
        $batches = DB::table('t_kartupersproduk')
            ->join('t_produk', 't_kartupersproduk.kode_produk', '=', 't_produk.kode_produk')
            ->select(
                't_kartupersproduk.no_transaksi',
                't_kartupersproduk.kode_produk',
                't_produk.nama_produk',
                't_kartupersproduk.masuk',
                't_kartupersproduk.keluar',
                't_kartupersproduk.tanggal_expired'
            )
            ->orderBy('t_kartupersproduk.tanggal', 'desc')
            ->get();

        return view('barcode-print-batch', compact('batches'));
    }

    public function barcodeImage(Request $request)
    {
        $code = $request->code;
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $barcode = $generator->getBarcode($code, $generator::TYPE_CODE_128, 2, 60);
        return response($barcode)->header('Content-Type', 'image/png');
    }
}