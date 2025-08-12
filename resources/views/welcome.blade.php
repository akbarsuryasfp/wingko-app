@extends('layouts.app')

@section('content')

@if(session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            alert({!! json_encode(session('warning')) !!});
        });
    </script>
@endif

@if(auth()->user() && auth()->user()->role == 'admin' && isset($orderMenunggu) && count($orderMenunggu) > 0)
    <div class="alert alert-warning d-flex align-items-center mt-3" role="alert" style="font-size:1.1em;">
        <i class="ri-notification-3-line fs-4 me-2"></i>
        <div>
            <strong>{{ count($orderMenunggu) }} Order Beli</strong> perlu disetujui.
            <a href="{{ route('orderbeli.index', ['status' => 'Menunggu Persetujuan']) }}" class="btn btn-warning btn-sm ms-2">
                Lihat Order
            </a>
        </div>
    </div>
@endif
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold mb-1">Dashboard Sistem Informasi Akuntansi Pratama</h2>
            <p class="text-muted">Ringkasan penjualan & stok hari ini</p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="bg-white rounded shadow-sm p-2 d-inline-flex align-items-center gap-2">
                <i class="ri-calendar-line text-primary"></i>
                {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
                <span class="ms-3"><i class="ri-time-line text-primary"></i> <span id="jam"></span>:<span id="menit"></span>:<span id="detik"></span></span>
            </div>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center p-3 shadow-sm border-0" style="background: #eef2ff;">
                <i class="ri-shopping-cart-2-line fs-2 text-primary mb-2"></i>
                <div class="fw-bold fs-4 text-primary">{{ $penjualanHariIni->total_transaksi ?? 0 }}</div>
                <div class="text-muted small">Transaksi Hari Ini</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center p-3 shadow-sm border-0 penjualan-lokasi-trigger" style="background: #ecfdf5; cursor:pointer;" data-bs-toggle="modal" data-bs-target="#penjualanLokasiModal">
                <i class="ri-money-dollar-circle-line fs-2 text-success mb-2"></i>
                <div class="fw-bold fs-4 text-success">Rp {{ number_format($penjualanHariIni->total_penjualan ?? 0, 0, ',', '.') }}</div>
                <div class="text-muted small">Penjualan Hari Ini</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center p-3 shadow-sm border-0 stokmin-trigger" style="background: #fffbeb; cursor:pointer;" data-bs-toggle="modal" data-bs-target="#stokMinBahanModal">
                <i class="ri-alert-line fs-2 text-warning mb-2"></i>
                <div class="fw-bold fs-4 text-warning">{{ $stokMinBahan->count() }}</div>
                <div class="text-muted small">Bahan Stok Minimum</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center p-3 shadow-sm border-0 kadaluarsa-trigger" style="background: #fef2f2; cursor:pointer;" data-bs-toggle="modal" data-bs-target="#kadaluarsaModal">
                <i class="ri-inbox-line fs-2 text-danger mb-2"></i>
                <div class="fw-bold fs-4 text-danger">{{ $kadaluarsa->count() + $kadaluarsaProduk->count() }}</div>
                <div class="text-muted small">Kadaluarsa</div>
            </div>
        </div>
    </div>

    <!-- Grafik Penjualan Bulan Ini (Bar Chart) -->
     @if(auth()->user()->role == 'admin' || auth()->user()->role == 'penjualan')
    <div class="card mb-4 shadow-sm border-0" style="background: #f9fafb;">
        <div class="card-body">
            <h5 class="fw-semibold mb-3"><i class="ri-bar-chart-2-line text-primary me-2"></i>Grafik Penjualan Bulan Ini</h5>
            <canvas id="salesChart" height="80"></canvas>
        </div>
    </div>
    @endif

  
    <div class="row g-4 mt-4">
        <!-- Modul Bahan Hampir Kadaluarsa -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="background: #fffbeb;">
                <div class="card-header fw-semibold d-flex align-items-center bg-transparent border-0">
                    <i class="ri-error-warning-line text-warning fs-4 me-2"></i>
                    Bahan Hampir Kadaluarsa (≤ 3 hari)
                </div>
                <div class="row g-2 p-3">
                    @forelse($hampir as $bahan)
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm mb-2" style="background: #fffbe6;">
                                <div class="card-body d-flex align-items-center gap-2">
                                    <i class="ri-capsule-fill fs-3 text-warning"></i>
                                    <div>
                                        <div class="fw-semibold">{{ $bahan->nama_bahan }}</div>
                                        <span class="badge bg-warning text-dark">{{ \Carbon\Carbon::parse($bahan->tanggal_exp)->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><span class="text-muted">Tidak ada bahan hampir kadaluarsa.</span></div>
                    @endforelse
                </div>
            </div>
        </div>
        <!-- Modul Produk Hampir Kadaluarsa -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="background: #fffbeb;">
                <div class="card-header fw-semibold d-flex align-items-center bg-transparent border-0">
                    <i class="ri-error-warning-line text-warning fs-4 me-2"></i>
                    Produk Hampir Kadaluarsa (≤ 3 hari)
                </div>
                <div class="row g-2 p-3">
                    @forelse($hampirProduk as $produk)
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow-sm mb-2" style="background: #fffbe6;">
                                <div class="card-body d-flex align-items-center gap-2">
                                    <i class="ri-inbox-fill fs-3 text-warning"></i>
                                    <div>
                                        <div class="fw-semibold">{{ $produk->nama_produk }}</div>
                                        <span class="badge bg-warning text-dark">{{ \Carbon\Carbon::parse($produk->tanggal_exp)->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><span class="text-muted">Tidak ada produk hampir kadaluarsa.</span></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-4 mt-4">
        <!-- Laporan Singkat Stok Bahan -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="background: #e0f2fe;">
                <div class="card-header fw-semibold d-flex align-items-center bg-transparent border-0">
                    <i class="ri-capsule-fill text-info fs-4 me-2"></i>
                    Laporan Stok Bahan
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @forelse($stokBahan as $bahan)
                            <div class="col-12 col-lg-6">
                                <div class="card border-0 shadow-sm mb-2" style="background: #f0f9ff;">
                                    <div class="card-body d-flex align-items-center gap-2">
                                        <i class="ri-capsule-fill fs-3 text-info"></i>
                                        <div>
                                            <div class="fw-semibold">{{ $bahan->nama_bahan }}</div>
                                            <span class="badge bg-info text-dark">Stok: {{ $bahan->stok }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12"><span class="text-muted">Tidak ada data stok bahan.</span></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <!-- Laporan Singkat Stok Produk -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="background: #e0f2fe;">
                <div class="card-header fw-semibold d-flex align-items-center bg-transparent border-0">
                    <i class="ri-inbox-fill text-info fs-4 me-2"></i>
                    Laporan Stok Produk
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @forelse($stokProduk as $produk)
                            <div class="col-12 col-lg-6">
                                <div class="card border-0 shadow-sm mb-2" style="background: #f0f9ff;">
                                    <div class="card-body d-flex align-items-center gap-2">
                                        <i class="ri-inbox-fill fs-3 text-info"></i>
                                        <div>
                                            <div class="fw-semibold">{{ $produk->nama_produk }}</div>
                                            <span class="badge bg-info text-dark">Stok: {{ $produk->stok }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12"><span class="text-muted">Tidak ada data stok produk.</span></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kadaluarsa -->
<div class="modal fade" id="kadaluarsaModal" tabindex="-1" aria-labelledby="kadaluarsaModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="kadaluarsaModalLabel"><i class="ri-inbox-line me-2"></i>Daftar Kadaluarsa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <!-- Card Bahan Kadaluarsa -->
          <div class="col-md-6">
            <div class="card border-danger shadow-sm h-100">
              <div class="card-header bg-danger text-white fw-semibold d-flex align-items-center">
                <i class="ri-capsule-fill fs-4 me-2"></i> Bahan Kadaluarsa
              </div>
              <div class="card-body">
                <div class="mb-3">
                  @forelse($kadaluarsa as $bahan)
                    <div class="d-flex align-items-center mb-2">
                      <i class="ri-capsule-fill fs-5 text-danger me-2"></i>
                      <div>
                        <div class="fw-semibold">{{ $bahan->nama_bahan }}</div>
                        <span class="badge bg-danger">Exp: {{ \Carbon\Carbon::parse($bahan->tanggal_exp)->format('d M Y') }}</span>
                      </div>
                    </div>
                  @empty
                    <span class="text-muted">Tidak ada bahan kadaluarsa.</span>
                  @endforelse
                </div>
                <a href="{{ route('penyesuaian.exp', ['tipe' => 'bahan']) }}" class="btn btn-danger btn-sm">
                  Penyesuaian
                </a>
              </div>
            </div>
          </div>
          <!-- Card Produk Kadaluarsa -->
          <div class="col-md-6">
            <div class="card border-danger shadow-sm h-100">
              <div class="card-header bg-danger text-white fw-semibold d-flex align-items-center">
                <i class="ri-inbox-fill fs-4 me-2"></i> Produk Kadaluarsa
              </div>
              <div class="card-body">
                <div class="mb-3">
                  @forelse($kadaluarsaProduk as $produk)
                    <div class="d-flex align-items-center mb-2">
                      <i class="ri-inbox-fill fs-5 text-danger me-2"></i>
                      <div>
                        <div class="fw-semibold">{{ $produk->nama_produk }}</div>
                        <span class="badge bg-danger">Exp: {{ \Carbon\Carbon::parse($produk->tanggal_exp)->format('d M Y') }}</span>
                      </div>
                    </div>
                  @empty
                    <span class="text-muted">Tidak ada produk kadaluarsa.</span>
                  @endforelse
                </div>
                <a href="{{ route('penyesuaian.exp', ['tipe' => 'produk']) }}" class="btn btn-danger btn-sm">
                  Penyesuaian
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Penjualan Per Lokasi -->
<div class="modal fade" id="penjualanLokasiModal" tabindex="-1" aria-labelledby="penjualanLokasiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="penjualanLokasiModalLabel"><i class="ri-map-pin-line me-2"></i>Penjualan Per Lokasi Hari Ini</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          @if(isset($penjualanPerLokasi) && $penjualanPerLokasi->count())
            @foreach($penjualanPerLokasi as $lokasi)
              <div class="col-md-6">
                <div class="card border-info shadow-sm h-100">
                  <div class="card-header bg-info text-white fw-semibold d-flex align-items-center">
                    <i class="ri-map-pin-user-line fs-4 me-2"></i> {{ $lokasi->nama_lokasi }}
                  </div>
                  <div class="card-body">
                    <div class="fw-bold fs-5 text-info mb-2">Rp {{ number_format($lokasi->total, 0, ',', '.') }}</div>
                    <div class="text-muted">Transaksi: {{ $lokasi->jumlah_transaksi }}</div>
                  </div>
                </div>
              </div>
            @endforeach
          @else
            <div class="col-12"><span class="text-muted">Belum ada data penjualan per lokasi hari ini.</span></div>
          @endif
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Bahan Stok Minimum -->
<div class="modal fade" id="stokMinBahanModal" tabindex="-1" aria-labelledby="stokMinBahanModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="stokMinBahanModalLabel"><i class="ri-alert-line me-2"></i>Bahan Stok Minimum</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2">
          @forelse($stokMinBahan as $bahan)
            <div class="col-12 col-lg-6">
              <div class="card border-0 shadow-sm mb-2" style="background: #fffbe6;">
                <div class="card-body d-flex align-items-center gap-2">
                  <i class="ri-capsule-fill fs-3 text-warning"></i>
                  <div>
                    <div class="fw-semibold">{{ $bahan->nama_bahan }}</div>
                    <span class="badge bg-warning text-dark">Stok: {{ $bahan->stok }}</span>
                  </div>
                </div>
              </div>
            </div>
          @empty
            <div class="col-12"><span class="text-muted">Stok bahan aman.</span></div>
          @endforelse
        </div>
      </div>
      <div class="modal-footer">
        <a href="{{ route('orderbeli.index') }}" class="btn btn-primary btn-sm">
          <i class="ri-shopping-bag-3-line me-1"></i>Order Beli
        </a>
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>


<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [{
                label: 'Penjualan (Rp)',
                data: {!! json_encode($dataGrafik) !!},
                backgroundColor: 'rgba(99,102,241,0.7)',
                borderColor: '#6366f1',
                borderWidth: 2,
                borderRadius: 6,
                maxBarThickness: 28
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: '#6b7280' } },
                y: { beginAtZero: true, ticks: { color: '#6366f1' } }
            }
        }
    });
});
</script>

@if(!session('lokasi_aktif'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            fetch("{{ route('lokasi.set') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Lokasi tidak ditemukan di database!');
                }
            });
        }, function(error) {
            alert('Gagal mendeteksi lokasi: ' + error.message);
        });
    } else {
        alert('Browser tidak mendukung geolocation');
    }
});
</script>
@endif

@endsection