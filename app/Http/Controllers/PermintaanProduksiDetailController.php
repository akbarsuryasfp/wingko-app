<?php

namespace App\Http\Controllers;

use App\Models\PermintaanProduksiDetail;
use Illuminate\Http\Request;

class PermintaanProduksiDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PermintaanProduksiDetail $permintaanProduksiDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PermintaanProduksiDetail $permintaanProduksiDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    $request->validate([
        'unit' => 'required|integer|min:1',
    ]);

    $detail = PermintaanProduksiDetail::findOrFail($id);
    $detail->update(['unit' => $request->unit]);

    return back()->with('success', 'Jumlah unit berhasil diperbarui.');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    $detail = PermintaanProduksiDetail::findOrFail($id);
    $detail->delete();

    return back()->with('success', 'Detail berhasil dihapus.');
}

}
