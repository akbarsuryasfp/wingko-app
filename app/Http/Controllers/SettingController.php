<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lokasi;
use App\Models\User;
// Tambahkan model lain jika perlu

class SettingController extends Controller
{
    public function index()
    {
        $lokasiList = Lokasi::all();
        $userList = \App\Models\User::all();
        // Tambahkan data lain jika perlu

        return view('setting', compact('lokasiList', 'userList'));
    }

    // CRUD Lokasi
    public function storeLokasi(Request $request)
    {
        $request->validate([
            'nama_lokasi' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        Lokasi::create($request->only(['nama_lokasi',  'alamat', 'telepon']));
        return back()->with('success', 'Lokasi berhasil ditambah');
    }

    public function updateLokasi(Request $request, $id)
    {
        $lokasi = Lokasi::findOrFail($id);
        $lokasi->update($request->only(['nama_lokasi',  'alamat', 'telepon']));
        return back()->with('success', 'Lokasi berhasil diupdate');
    }

    public function destroyLokasi($id)
    {
        Lokasi::destroy($id);
        return back()->with('success', 'Lokasi berhasil dihapus');
    }

    // Tambahkan method untuk akun jurnal, user, dsb sesuai kebutuhan

    // Tambahkan field latitude & longitude di tabel t_lokasi
    public function detectLokasi(Request $request)
    {
        $lat = $request->latitude;
        $lng = $request->longitude;

        // Ambil semua lokasi beserta koordinat
        $lokasiList = \App\Models\Lokasi::all();

        // Fungsi haversine
        function haversine($lat1, $lon1, $lat2, $lon2) {
            $earthRadius = 6371; // km
            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);
            $a = sin($dLat/2) * sin($dLat/2) +
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                sin($dLon/2) * sin($dLon/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            return $earthRadius * $c;
        }

        $minDistance = null;
        $lokasiTerdekat = null;
        foreach ($lokasiList as $lokasi) {
            if (is_null($lokasi->latitude) || is_null($lokasi->longitude)) continue;
            $distance = haversine($lat, $lng, $lokasi->latitude, $lokasi->longitude);
            if (is_null($minDistance) || $distance < $minDistance) {
                $minDistance = $distance;
                $lokasiTerdekat = $lokasi;
            }
        }

        if ($lokasiTerdekat) {
            session(['lokasi_aktif' => $lokasiTerdekat->kode_lokasi]);
            return back()->with('success', 'Lokasi terdeteksi: ' . $lokasiTerdekat->nama_lokasi);
        } else {
            return back()->with('error', 'Tidak ada lokasi yang ditemukan.');
        }
    }

    public function setLokasi(Request $request)
    {
        $lat = $request->latitude;
        $lng = $request->longitude;

        $lokasiList = \App\Models\Lokasi::all();

        // Fungsi haversine
        function haversine2($lat1, $lon1, $lat2, $lon2) {
            $earthRadius = 6371; // km
            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);
            $a = sin($dLat/2) * sin($dLat/2) +
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                sin($dLon/2) * sin($dLon/2);
            $c = 2 * atan2(sqrt($a), sqrt(1-$a));
            return $earthRadius * $c;
        }

        $minDistance = null;
        $lokasiTerdekat = null;
        foreach ($lokasiList as $lokasi) {
            if (is_null($lokasi->latitude) || is_null($lokasi->longitude)) continue;
            $distance = haversine2($lat, $lng, $lokasi->latitude, $lokasi->longitude);
            if (is_null($minDistance) || $distance < $minDistance) {
                $minDistance = $distance;
                $lokasiTerdekat = $lokasi;
            }
        }

        if ($lokasiTerdekat) {
            session(['lokasi_aktif' => $lokasiTerdekat->kode_lokasi]);
            return response()->json(['success' => true, 'lokasi' => $lokasiTerdekat->nama_lokasi]);
        } else {
            return response()->json(['success' => false, 'message' => 'Lokasi tidak ditemukan']);
        }
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = [];
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:6|confirmed',
            ]);
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);
        return back()->with('success', 'Password user berhasil diubah');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'username' => 'required|string|max:30|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string'
        ]);
        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ]);
        return back()->with('success', 'User berhasil ditambah');
    }

    public function pilihLokasi($kode_lokasi)
    {
        session(['lokasi_aktif' => $kode_lokasi]);
        return redirect()->back()->with('success', 'Lokasi aktif berhasil diubah!');
    }
}