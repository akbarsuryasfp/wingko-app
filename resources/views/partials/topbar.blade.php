<nav class="navbar navbar-expand-lg navbar-dark shadow-sm px-3 py-2" id="mainNavbar" style="background: linear-gradient(135deg, #1e3a8a, #3b82f6);">
<div class="container-fluid">
        <!-- Sidebar Toggle Button -->
        <button id="sidebarToggle" class="btn btn-light me-3 d-flex align-items-center justify-content-center" type="button" style="width:40px; height:40px; border-radius:50%;">
            <i class="bi bi-list fs-5 text-primary"></i>
        </button>
        
        <!-- Brand -->
        <div class="d-flex flex-column align-items-start me-auto">
            <span class="navbar-brand mb-0 h1 fw-bold text-white" style="letter-spacing:1px; line-height:1; text-shadow: 1px 1px 2px rgba(0,0,0,0.2);">SIAP</span>
            <span class="brand-subtitle text-white-50 small" style="margin-top:-2px;">Sistem Informasi Akuntansi Pratama</span>
        </div>
        
        <!-- Search Form -->
        <form class="d-none d-md-flex me-3" method="GET" action="#" style="max-width:300px;">
            <div class="input-group">
                <input class="form-control border-end-0" type="search" placeholder="Cari..." aria-label="Search" style="box-shadow: none;">
                <button class="btn btn-light border-start-0" type="submit" style="background-color: white;">
                    <i class="bi bi-search text-secondary"></i>
                </button>
            </div>
        </form>
        
        <!-- User Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="position-relative">
                    <i class="bi bi-person-circle fs-3 text-white"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle">
                        <span class="visually-hidden">Online</span>
                    </span>
                </div>
                <span class="ms-2 d-none d-md-inline text-white">{{ Auth::user()->name ?? 'User' }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown" style="border:none;">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .brand-subtitle {
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        font-weight: 400;
    }
    
    .dropdown-menu {
        font-size: 0.9rem;
        border-radius: 0.5rem;
        border: none;
    }
    
    .dropdown-item {
        padding: 0.5rem 1rem;
        transition: all 0.2s;
    }
    
    .dropdown-item:hover {
        background-color: #f0f7ff;
        color: #1e3a8a;
        transform: translateX(2px);
    }
    
    #sidebarToggle {
        transition: all 0.3s;
    }
    
    #sidebarToggle:hover {
        transform: scale(1.1);
        box-shadow: 0 0 10px rgba(255,255,255,0.3);
    }
    
    .navbar {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .input-group .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
    }
</style>