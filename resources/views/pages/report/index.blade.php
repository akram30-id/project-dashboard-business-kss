@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('') }}assets/css/report.css">

    <div id="wrapper" class="w-100">
        @include('layouts.sidebar')
        <div id="content-wrapper" class="d-flex flex-column w-100">
            <div id="content" class="flex-grow-1">
                @include('layouts.topbar')
                <div class="container-fluid p-3 p-md-4">
                    <div
                        class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Report</li>
                            </ol>
                        </nav>
                        <a class="btn btn-warning bi bi-arrows-fullscreen mt-3 mt-md-0" id="btnDetailInvoiceAnnual"
                            data-toggle="modal" data-target="#reportTableModal" href="#"> Show Screens </a>
                        {{-- <a type="button" class="" data-bs-toggle="modal" data-bs-target="#reportTableModal"> Show Screens
                        </a> --}}
                    </div>

                    <div class="dashboard-container">
                        @include('pages.report.year-selector')
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="row g-3">
                                    <div class="col-lg-5 col-12">
                                        @include('pages.report.stats-cards')
                                    </div>
                                    <div class="col-lg-7 col-12">
                                        @include('pages.report.chart-container')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Table -->
                    <div class="modal fade" id="reportTableModal" data-bs-backdrop="static" tabindex="-1"
                        aria-labelledby="reportTableModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" style="max-width: 95vw; width: 100%;">
                            <div class="modal-content" style="height: 95vh;">
                                <div class="modal-header bg-dark text-white">
                                    <h5 class="modal-title fw-bold" id="reportTableModalLabel">Report Data</h5>
                                    <div class="ms-auto d-flex align-items-center">
                                        <button type="button" class="btn btn-light btn-sm me-2" id="fullscreenBtn">
                                            <i class="bi bi-arrows-fullscreen"></i>
                                        </button>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                </div>
                                <div class="modal-body p-0 d-flex flex-column" style="height: calc(100% - 60px);">
                                    <div class="p-3" id="yearSelectorContainer">
                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <select name="year" id="yearSelect" class="form-control">
                                                    <option value="" disabled>- Pilih Tahun -</option>
                                                    <?php $currentYear = date('Y');
                                                        for ($i=0; $i < 5; $i++) {
                                                            $printYear = $currentYear--; ?>
                                                    <option value="{{ $printYear }}">{{ $printYear }}</option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <select name="detailLimit" id="detailLimit" class="form-control">
                                                    <option value="" disabled>Show</option>
                                                    <option value="10">10</option>
                                                    <option value="25">25</option>
                                                    <option value="50">50</option>
                                                    <option value="100">100</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive flex-grow-1 m-0" style="overflow-y: auto;">
                                        <table class="table table-bordered table-striped table-hover m-0" id="reportTable">
                                            <thead class="table-dark position-sticky top-0">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Nama Pekerjaan</th>
                                                    <th>Nama Customer</th>
                                                    <th>No. CO</th>
                                                    <th>Tanggal CO</th>
                                                    <th>No. DO</th>
                                                    <th>Tanggal DO</th>
                                                    <th>Nominal Invoice</th>
                                                    <th>Status Invoice</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tableBody">
                                                <!-- Data akan diisi melalui JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="p-3">
                                        <nav aria-label="Page navigation">
                                            <ul class="pagination justify-content-center mb-0" id="paginationDetailAnnual">
                                                <nav>
                                                    <ul class="pagination">
                                                        <li class="page-item" id="prevDetailAnnual">
                                                            <a class="page-link" href="#" aria-label="Previous">
                                                                <span aria-hidden="true">&laquo;</span>
                                                            </a>
                                                        </li>
                                                        <li class="page-item" id="nextDetailAnnual">
                                                            <a class="page-link" href="#" aria-label="Next">
                                                                <span aria-hidden="true">&raquo;</span>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </nav>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    @include('components.logout')

    @include('pages.report.js.main')
@endsection
