<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    {{-- Icon boostrap --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom fonts for this template-->
    <link href="{{ asset('') }}assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom styles for this template-->
    <link href="{{ asset('') }}assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        {{-- @include('layouts.navigation') --}}

        <!-- Page Heading -->
        {{-- @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset --}}

        <!-- Page Content -->
        {{-- <main>
            {{ $slot }}
        </main> --}}

        <main>
            @yield('content')
        </main>

    </div>


    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('') }}assets/vendor/jquery/jquery.min.js"></script>
    <script src="{{ asset('') }}assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('') }}assets/vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('') }}assets/js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="{{ asset('') }}assets/vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="{{ asset('') }}assets/js/demo/chart-area-demo.js"></script>
    <script src="{{ asset('') }}assets/js/demo/chart-pie-demo.js"></script>


    <!-- Page level custom scripts -->
    <script src="{{ asset('') }}assets/js/demo/chart-area-demo.js"></script>
    <script src="{{ asset('') }}assets/js/demo/chart-pie-demo.js"></script>
    <script src="{{ asset('') }}assets/js/demo/chart-bar-demo.js"></script>


</body>

</html>
