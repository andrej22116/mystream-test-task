<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body class="antialiased">
    <div class="container">
        <header>
            <div class="container d-flex justify-content-center">
                @if(\Illuminate\Support\Facades\Auth::check())
                    <div class="row">
                        <div class="col m-2">
                            Welcome, {{ \Illuminate\Support\Facades\Auth::user()->name }}
                        </div>

                        <div class="col m-2">
                            <a class="btn btn-danger" href="{{ route('http.logout') }}">Logout</a>
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col m-2">
                            <a class="btn btn-success" href="{{ route('http.auth.page') }}">SignIn</a>
                        </div>

                        <div class="col m-2">
                            <a class="btn btn-primary" href="{{ route('http.reg.page') }}">SignUp</a>
                        </div>
                    </div>
                @endif
            </div>
        </header>

        @yield('content')

        <footer>

        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    @stack('scripts')
</body>
</html>
