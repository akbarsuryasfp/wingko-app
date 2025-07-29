<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SISTEM INFORMASI AKUNTANSI PRATAMA')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/build/assets/app-DiyRD1g1.css">
    @stack('styles')

</head>
<body>
    <div id="root-layout">
        <div id="topbar-fixed">
            @include('partials.topbar')
        </div>
            @include('partials.sidebar')
        <div class="main-content">
            @yield('content')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    <script src="/build/assets/app-BoTkVbuK.js"></script>
    
</body>
</html>