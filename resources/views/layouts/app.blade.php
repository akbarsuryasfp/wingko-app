<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Aplikasi Bahan')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">TOKO BARU</a>
        </div>
    </nav>

    <div class="container">
        @yield('content')
    </div>

</body>
</html>