<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'SISTEM INFORMASI AKUNTANSI PRATAMA')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/build/assets/app-CT_xoRCA.css">
</head>
<body>
    <div id="topbar-fixed">
        @include('partials.topbar')
    </div>
    <div id="app-wrapper">
        @include('partials.sidebar')
        <div class="main-content">
            @yield('content')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    <script src="/build/assets/app-BCJKuDgl.js"></script>
    <script>
    document.getElementById('sidebarToggle').onclick = function() {
        var sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
    };
    </script>
</body>
</html>