@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="fw-bold mb-4">Pengaturan Sistem</h3>
    @if(session('success'))
        <div class="alert alert-success rounded-3 shadow-sm">{{ session('success') }}</div>
    @endif

    <ul class="nav nav-tabs mb-4 border-0" id="settingTab" role="tablist" style="gap: 8px;">
        <li class="nav-item" role="presentation">
            <a class="nav-link active rounded-pill px-4 py-2 fw-semibold" id="lokasi-tab" data-bs-toggle="tab" href="#lokasi" role="tab">
                <i class="bi bi-geo-alt"></i> Lokasi
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link rounded-pill px-4 py-2 fw-semibold" id="akun-tab" data-bs-toggle="tab" href="#akun" role="tab">
                <i class="bi bi-journal-text"></i> Akun Jurnal
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link rounded-pill px-4 py-2 fw-semibold" id="user-tab" data-bs-toggle="tab" href="#user" role="tab">
                <i class="bi bi-people"></i> User
            </a>
        </li>
    </ul>
    <div class="tab-content" id="settingTabContent">
        <!-- Tab Lokasi -->
        <div class="tab-pane fade show active" id="lokasi" role="tabpanel">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold mb-0">Daftar Lokasi</h5>
                <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahLokasi">
                    <i class="bi bi-geo-alt"></i> Tambah Lokasi
                </button>
            </div>
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">

            <!-- Modal Tambah Lokasi -->
            <div class="modal fade" id="modalTambahLokasi" tabindex="-1" aria-labelledby="modalTambahLokasiLabel" aria-hidden="true">
              <div class="modal-dialog">
                <form method="POST" action="{{ route('setting.lokasi.store') }}">
                    @csrf
                    <input type="hidden" name="tab" id="activeTabInputLokasi" value="lokasi">
                    <div class="modal-content rounded-4 shadow">
                      <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="modalTambahLokasiLabel">Tambah Lokasi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body pt-0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nama Lokasi</label>
                            <input type="text" name="nama_lokasi" class="form-control rounded-3" placeholder="Nama lokasi" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Alamat</label>
                            <input type="text" name="alamat" class="form-control rounded-3" placeholder="Alamat lokasi">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Telepon</label>
                            <input type="text" name="telepon" class="form-control rounded-3" placeholder="Nomor telepon">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-semibold small">Latitude</label>
                                <input type="text" name="latitude" id="modal_latitude" class="form-control rounded-3" placeholder="Latitude">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-semibold small">Longitude</label>
                                <input type="text" name="longitude" id="modal_longitude" class="form-control rounded-3" placeholder="Longitude">
                            </div>
                        </div>
                        <button type="button" class="btn btn-info btn-sm d-flex align-items-center gap-2 shadow-sm" onclick="deteksiLangsungKeModal()">
                            <i class="bi bi-crosshair"></i> Deteksi Lokasi Otomatis
                        </button>
                      </div>
                      <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary shadow-sm">Simpan</button>
                      </div>
                    </div>
                </form>
              </div>
            </div>

            <script>
            function detectLocation() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        document.getElementById('latitude').value = position.coords.latitude;
                        document.getElementById('longitude').value = position.coords.longitude;
                        alert('Lokasi terdeteksi: ' + position.coords.latitude + ', ' + position.coords.longitude);
                    }, function(error) {
                        alert('Gagal mendeteksi lokasi: ' + error.message);
                    });
                } else {
                    alert('Browser tidak mendukung geolocation');
                }
            }

            // Fungsi untuk mengisi field latitude & longitude di modal dari hasil deteksi
            function deteksiLangsungKeModal() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        document.getElementById('modal_latitude').value = position.coords.latitude;
                        document.getElementById('modal_longitude').value = position.coords.longitude;
                        alert('Lokasi berhasil dideteksi dan diisi otomatis!');
                    }, function(error) {
                        alert('Gagal mendeteksi lokasi: ' + error.message);
                    });
                } else {
                    alert('Browser tidak mendukung geolocation');
                }
            }
            </script>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover rounded-4 overflow-hidden shadow-sm mt-3 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Lokasi</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lokasiList as $lokasi)
                        <tr>
                            <td>{{ $lokasi->nama_lokasi }}</td>
                            <td>{{ $lokasi->alamat }}</td>
                            <td>{{ $lokasi->telepon }}</td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('setting.lokasi.pilih', $lokasi->kode_lokasi) }}" style="display:inline;">
                                    @csrf
                                    <button class="btn btn-success btn-sm rounded-3 shadow-sm d-flex align-items-center gap-2" 
                                        @if(session('lokasi_aktif') == $lokasi->kode_lokasi) disabled @endif>
                                        <i class="bi bi-check-circle"></i> Pilih
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('setting.lokasi.destroy', $lokasi->kode_lokasi) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm rounded-3 shadow-sm d-flex align-items-center gap-2" onclick="return confirm('Hapus lokasi ini?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Tab Akun Jurnal -->
        <div class="tab-pane fade" id="akun" role="tabpanel">
            <h5 class="fw-bold mt-3">Pengaturan Akun Jurnal</h5>
            <div class="alert alert-info mt-3">Silakan tambahkan pengaturan akun jurnal di sini.</div>
            <!-- Form dan tabel akun jurnal -->
        </div>
        <!-- Tab User -->
        <div class="tab-pane fade" id="user" role="tabpanel">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold mb-0">Daftar User</h5>
                @if(Auth::user() && Auth::user()->role == 'admin')
                <button class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                    <i class="bi bi-person-plus"></i> Tambah User
                </button>
                @endif
            </div>
            @if(Auth::user() && Auth::user()->role == 'admin')
            <!-- Modal Tambah User -->
            <div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="modalTambahUserLabel" aria-hidden="true">
              <div class="modal-dialog">
                <form method="POST" action="{{ route('setting.user.store') }}">
                    @csrf
                    <input type="hidden" name="tab" id="activeTabInputUser" value="user">
                    <div class="modal-content rounded-4 shadow">
                      <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="modalTambahUserLabel">Tambah User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body pt-0">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nama</label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="Nama lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Username</label>
                            <input type="text" name="username" class="form-control rounded-3" placeholder="Username unik" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Email</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="Alamat email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Password</label>
                            <input type="password" name="password" class="form-control rounded-3" placeholder="Password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control rounded-3" placeholder="Ulangi password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Role</label>
                            <select name="role" class="form-select rounded-3" required>
                                <option value="admin">Admin</option>
                                <option value="gudang">Gudang</option>
                                <option value="produksi">Produksi</option>
                                <option value="penjualan">Penjualan</option>
                            </select>
                        </div>
                      </div>
                      <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary shadow-sm">Simpan</button>
                      </div>
                    </div>
                </form>
              </div>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover rounded-4 overflow-hidden shadow-sm mt-3 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($userList as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $user->role ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if(Auth::user() && Auth::user()->role == 'admin')
                                <button class="btn btn-warning btn-sm rounded-3 d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalUbahPassword{{ $user->id }}">
                                    <i class="bi bi-key"></i> Ubah Password
                                </button>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Ubah Password -->
                        <div class="modal fade" id="modalUbahPassword{{ $user->id }}" tabindex="-1" aria-labelledby="modalUbahPasswordLabel{{ $user->id }}" aria-hidden="true">
                          <div class="modal-dialog">
                            <form method="POST" action="{{ route('setting.user.update', $user->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="modal-content rounded-4 shadow">
                                  <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold" id="modalUbahPasswordLabel{{ $user->id }}">Ubah Password User: {{ $user->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body pt-0">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small">Password Baru</label>
                                        <input type="password" name="password" class="form-control rounded-3" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small">Konfirmasi Password</label>
                                        <input type="password" name="password_confirmation" class="form-control rounded-3" required>
                                    </div>
                                  </div>
                                  <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary shadow-sm">Simpan</button>
                                  </div>
                                </div>
                            </form>
                          </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
/* Custom tab style for modern look */
.nav-tabs .nav-link {
    border: none !important;
    color: #555;
    background: #f8f9fa;
    transition: background 0.2s, color 0.2s;
}
.nav-tabs .nav-link.active, .nav-tabs .nav-link:hover {
    background: #0d6efd !important;
    color: #fff !important;
}
.nav-tabs {
    border-bottom: none;
}
.table thead th {
    vertical-align: middle;
}
</style>