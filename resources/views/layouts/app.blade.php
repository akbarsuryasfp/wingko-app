<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'SISTEM INFORMASI AKUNTANSI PRATAMA')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    {{-- Topbar --}}
    @include('partials.topbar')

    <div class="container-fluid">
        <div class="row">
            {{-- Sidebar --}}
            <div class="col-md-3 col-lg-2 p-0">
                @include('partials.sidebar')
            </div>
            {{-- Main Content --}}
            <div class="col-md-9 col-lg-10">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>