@extends('layouts.app')
@section('content')
    {{-- Include external CSS file instead of inline styles --}}
    <link rel="stylesheet" href="{{ asset('') }}assets/css/dashboard.css">

    <!-- Page Wrapper -->
    <div id="wrapper">
        {{-- Sidebar menu --}}
        @include('layouts.sidebar')

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                @include('layouts.topbar')
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                            </ol>
                        </nav>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-download fa-sm text-white-50"></i> Print Report
                        </a>
                    </div>

                    <!-- Yearly Dashboard -->
                    <div class="container">
                        <div class="card border-0">
                            <div class="card-body">
                                <div class="row">
                                    <!-- Stats Cards for Current Year -->
                                    <div id="stats-cards-container" class="d-flex justify-content-between w-100">
                                        @include('components.dashboard.stats-card', [
                                            'type' => 'primary',
                                            'id' => 'revenue-total',
                                            'value' => 'Rp ' . number_format($totalRevenue, 0, ',', '.'),
                                            'label' => 'Total Revenue',
                                            'icon' => 'fas fa-money-bill-wave',
                                            'chartIcon' => 'fas fa-chart-line',
                                            'year' => '2025'
                                        ])

                                        @include('components.dashboard.stats-card', [
                                            'type' => 'success',
                                            'id' => 'invoice-total',
                                            'value' => 'Rp ' . number_format($totalInvoice, 0, ',', '.'),
                                            'label' => 'Total Invoice',
                                            'icon' => 'fas fa-file-invoice',
                                            'chartIcon' => 'fas fa-file-invoice-dollar',
                                            'year' => '2025'
                                        ])

                                        @include('components.dashboard.stats-card', [
                                            'type' => 'info',
                                            'id' => 'accrue-total',
                                            'value' => 'Rp ' . number_format($totalAccrue, 0, ',', '.'),
                                            'label' => 'Total Accrue',
                                            'icon' => 'fas fa-chart-pie',
                                            'chartIcon' => 'fas fa-calculator',
                                            'year' => '2025'
                                        ])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            @include('layouts.footer')
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    @include('components.logout')

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>

    <!-- Dashboard Scripts -->
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endsection
