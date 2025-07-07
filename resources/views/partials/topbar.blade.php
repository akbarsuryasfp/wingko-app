<nav class="navbar navbar-light bg-white shadow-sm px-3 py-2">
    <div class="d-flex align-items-center w-100">
        <!-- Sidebar Toggle Button -->
        <button id="sidebarToggle" class="btn btn-outline-secondary me-3 d-flex align-items-center justify-content-center" type="button" style="width:40px; height:40px;">
            <i class="bi bi-list fs-4"></i>
        </button>
        <!-- Brand -->
        <div class="d-flex flex-column align-items-start me-auto">
            <span class="navbar-brand mb-0 h1 fw-bold" style="letter-spacing:1px; line-height:1;">SIAP</span>
            <span class="brand-subtitle text-muted small" style="margin-top:-2px;">Sistem Informasi Akuntansi Pratama</span>
        </div>
        <!-- Search Form -->
        <form class="d-none d-md-flex me-3" method="GET" action="#" style="max-width:250px;">
            <input class="form-control me-2" type="search" placeholder="Cari..." aria-label="Search">
            <button class="btn btn-outline-success" type="submit">
                <i class="bi bi-search"></i>
            </button>
        </form>
        <!-- User Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle fs-3"></i>
                <span class="ms-2 d-none d-md-inline">{{ Auth::user()->name ?? 'User' }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item" type="submit">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
<style>
    .brand-subtitle {
        font-size: 0.85rem;
        color: #6c757d !important;
        letter-spacing: 0.5px;
        font-weight: 400;
        margin-left: 2px;
    }
</style>
<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
